<?php

namespace App\Jobs;

use App\Helpers\LogHelper;
use App\Models\Block;
use App\Services\MinterApiService;
use App\Services\ValidatorService;
use App\Traits\NodeTrait;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Cache;

class SaveValidatorsJob extends Job
{
    use NodeTrait;

    /** @var Block */
    protected $block;

    public $queue = 'validators';

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
            Cache::put('activeValidators', \count($validatorsData), new \DateInterval('PT6S'));
            $validators = $validatorService->createFromAipData($validatorsData);
            $this->block->validators()->saveMany($validators);
        } catch (GuzzleException $exception) {
            LogHelper::apiError($exception);
        } catch (\Exception $exception) {
            LogHelper::error($exception);
        }
    }
}
