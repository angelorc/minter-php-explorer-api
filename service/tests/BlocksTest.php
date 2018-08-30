<?php

class BlocksTest extends TestCase
{
    /**
     * @return void
     */
    public function testSingleBlockStructureResponse(): void
    {
        for ($i = 0; $i <= 10; $i++) {
            /** @var \App\Models\Block $block */
            $block = \App\Models\Block::inRandomOrder()->first();

            echo('Test block ' . $block->height . "\n");

            $this->json('GET', '/api/v1/block/' . $block->height)
                ->seeJsonStructure([
                    'data' => [
                        'latestBlockHeight',
                        'height',
                        'timestamp',
                        'txCount',
                        'reward',
                        'size',
                        'hash',
                        'validators' => [
                            0 => [
                                'name',
                                'address',
                                'publicKey',
                                'absentTimes',
                                'commission',
                                'status'
                            ]
                        ],
                    ]
                ]);
        }
    }

    /**
     * @return void
     */
    public function testBlocksResponse(): void
    {
        $this->json('GET', '/api/v1/blocks')->seeJsonStructure([
            'data' => [
                0 => [
                    'latestBlockHeight',
                    'height',
                    'timestamp',
                    'txCount',
                    'reward',
                    'size',
                    'hash',
                    'blockTime',
                    'validators' => [
                        0 => [
                            'name',
                            'address',
                            'publicKey',
                            'absentTimes',
                            'commission',
                            'status'
                        ]
                    ],
                ],
            ],
            'links' => [
                'first',
                'last',
                'prev',
                'next',
            ],
            'meta' => [
                'current_page',
                'from',
                'last_page',
                'path',
                'per_page',
                'to',
                'total'
            ]
        ]);
    }
}
