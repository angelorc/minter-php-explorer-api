<?php

namespace App\Jobs;

use App\Helpers\LogHelper;
use App\Models\Block;
use App\Services\MinterApiService;
use App\Services\ValidatorService;
use App\Traits\NodeTrait;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Queue;

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
            $candidatesData = $apiService->getCandidatesData($this->block->height);
            $validators = $validatorService->createFromAipData($validatorsData);
            $this->block->validators()->saveMany($validators);
            Queue::pushOn('broadcast', new BroadcastStatusPageJob($validators->count(), \count($candidatesData)));
        } catch (GuzzleException $exception) {
            LogHelper::apiError($exception);
        } catch (\Exception $exception) {
            LogHelper::error($exception);
        }
    }
}
