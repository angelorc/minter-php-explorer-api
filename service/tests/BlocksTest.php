<?php

class BlocksTest extends TestCase
{
    /**
     * @return void
     */
    public function testSingleBlockResponse(): void
    {
        $this->json('GET', '/api/v1/block/23')
            ->seeJsonStructure([
                'data' => [
                    "latestBlockHeight",
                    "height",
                    "timestamp",
                    "txCount",
                    "reward",
                    "size",
                    "hash",
                    "validators" => [
                        0 => [
                            "name",
                            "address",
                            "publicKey",
                            "absentTimes",
                            "commission",
                            "status"
                        ]
                    ],
                ]
            ]);
    }

    /**
     * @return void
     */
    public function testBlocksResponse(): void
    {
        $this->json('GET', '/api/v1/blocks')->seeJsonStructure([
            'data' => [
                0 => [
                    "latestBlockHeight",
                    "height",
                    "timestamp",
                    "txCount",
                    "reward",
                    "size",
                    "hash",
                    "blockTime",
                    "validators" => [
                        0 => [
                            "name",
                            "address",
                            "publicKey",
                            "absentTimes",
                            "commission",
                            "status"
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
            "meta" => [
                "current_page",
                "from",
                "last_page",
                "path",
                "per_page",
                "to",
                "total"
            ]
        ]);
    }
}
