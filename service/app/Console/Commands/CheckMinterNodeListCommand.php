<?php

namespace App\Console\Commands;

use App\Helpers\DateTimeHelper;
use App\Models\MinterNode;
use App\Services\MinterApiService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;

/**
 * Class CheckMinterNodeListCommand
 * @package App\Console\Commands
 */
class CheckMinterNodeListCommand extends Command
{
    const CHECK_SLEEP_PERIOD = 5;

    /** @var string */
    protected $signature = 'minter:check-node-list';

    /** @var string */
    protected $description = 'Check actual minter node from DB table "minter_nodes"';

    /**
     * Console command.
     * Check nodes and update their status in DB
     */
    public function handle(): void
    {
        while (true) {
            MinterNode::where('is_excluded', '!=', true)
                ->get()
                ->each(function ($node) {
                    /** @var MinterNode $node */
                    $node->ping = $this->ping($node->host);
                    if ($node->ping >= 0) {
                        $node->is_active = $this->isNodeSynced($node);
                    }
                    $node->save();
                });

            $this->info('Nodes have been checked');
            sleep($this::CHECK_SLEEP_PERIOD);
        }

    }

    /**
     * Ping host
     * Took from https://github.com/geerlingguy/Ping
     *
     * @param string $host
     * @param int $ttl
     * @param int $timeout
     * @return float Latency, in ms.
     */
    private function ping(string $host, int $ttl = 255, int $timeout = 5): float
    {
        $latency = -1;
        $ttl = escapeshellcmd($ttl);
        $timeout = escapeshellcmd($timeout);
        $host = escapeshellcmd($host);

        if (strtoupper(PHP_OS) === 'DARWIN') {
            // -n = numeric output; -c = number of pings; -m = ttl; -t = timeout.
            $exec_string = 'ping -n -c 1 -m ' . $ttl . ' -t ' . $timeout . ' ' . $host;
        } // Exec string for other UNIX-based systems (Linux).
        else {
            // -n = numeric output; -c = number of pings; -t = ttl; -W = timeout
            $exec_string = 'ping -n -c 1 -t ' . $ttl . ' -W ' . $timeout . ' ' . $host . ' 2>&1';
        }

        exec($exec_string, $output, $return);
        // Strip empty lines and reorder the indexes from 0 (to make results more
        // uniform across OS versions).
        $output = array_values(array_filter($output));
        // If the result line in the output is not empty, parse it.
        if (!empty($output[1])) {
            // Search for a 'time' value in the result line.
            $response = preg_match("/time(?:=|<)(?<time>[\.0-9]+)(?:|\s)ms/", $output[1], $matches);
            // If there's a result and it's greater than 0, return the latency.
            if ($response > 0 && isset($matches['time'])) {
                $latency = $matches['time'];
            }
        }
        return $latency;
    }

    /**
     * Check node sync status
     * @param MinterNode $node
     * @return bool
     */
    private function isNodeSynced(MinterNode $node): bool
    {
        $apiService = new MinterApiService($node);

        try {
            $data = $apiService->getNodeStatusData();
            $node->version = $data['version'];
            $node->save();
        } catch (GuzzleException $e) {
            return false;
        }

        /** @var \DateTime  $dt */
        $dt = DateTimeHelper::parse($data['latest_block_time']);

        if(isset($dt)){
            $diff = time() - (int) $dt->getTimestamp();

            //If last block time less then 10 sec node is synced
            return $diff < 10;
        }

        return false;
    }
}