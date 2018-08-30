<?php

namespace App\Services;

use GuzzleHttp\Client as HttpClient;
use App\Models\MinterNode;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class MinterApiService implements MinterApiServiceInterface
{
    protected $httpClient;

    protected $node;

    /**
     * MinterApiService constructor.
     * @param $node
     */
    public function __construct(MinterNode $node)
    {
        $this->node = $node;
        $this->httpClient = new HttpClient([
            'base_uri' => $this->node->fullLink,
            'Connection' => 'close',
            CURLOPT_FORBID_REUSE => true,
            CURLOPT_FRESH_CONNECT => true,
        ]);
    }

    /**
     * Get node status data
     * @return array
     */
    public function getNodeStatusData(): array
    {
        try {
            $res = $this->httpClient->request('GET', 'api/status');
            $data = \GuzzleHttp\json_decode($res->getBody()->getContents(), 1);
            return $data['result'];
        } catch (GuzzleException $e) {

                Log::channel('api')->error(
                    $e->getFile() .
                    ' line ' .
                    $e->getLine() . ': ' .
                    $e->getMessage()
                );

                return [];
        }
    }
}