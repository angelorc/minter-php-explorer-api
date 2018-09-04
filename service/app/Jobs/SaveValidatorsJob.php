<?php

namespace App\Jobs;

use App\Models\Block;
use App\Services\MinterApiService;
use App\Services\ValidatorService;
use App\Traits\NodeTrait;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class SaveValidatorsJob extends Job
{
    use NodeTrait;

    /** @var Block */
    protected $block;

    /**
     * Create a new job instance.
     *
     * @param Block $block
     */
    public function __construct(Block $block)
    {
        $this->block = $block;
    }

    /**
     * Execute the job.
     *a
     * @return void
     */
    public function handle(): void
    {
        $apiService = new MinterApiService($this->getActualNode());
        $validatorService = new ValidatorService();

        try {
            $validatorsData = $apiService->getBlockValidatorsData($this->block->height);
            $validators = $validatorService->createFromAipData($validatorsData);
            $this->block->validators()->saveMany($validators);
        } catch (GuzzleException $exception) {
            Log::channel('api')->error(
                $exception->getFile() . ' line ' .
                $exception->getLine() . ': ' .
                $exception->getMessage()
            );
        }
    }
}
