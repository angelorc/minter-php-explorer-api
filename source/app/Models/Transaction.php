<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * @SWG\Definition(
 *     definition="TransactionData",
 *     type="object",
 *
 *     @SWG\Property(property="from",  type="string", example="Mxa93163fdf10724dc4785ff5cbfb9ac0b5949409f"),
 *     @SWG\Property(property="to",    type="string", example="Mxa93163fdf10724dc4785ff5cbfb9ac0b5949409f"),
 *     @SWG\Property(property="coin",  type="string", example="MNT"),
 *     @SWG\Property(property="value", type="float",  example="23.93674623")
 * )
 */

/**
 * @SWG\Definition(
 *     definition="Transaction",
 *     type="object",
 *
 *     @SWG\Property(property="hash",      type="string",  example="f86e020101a6e58a4d4e540000000000000094a93163..."),
 *     @SWG\Property(property="nonce",     type="integer", example="2"),
 *     @SWG\Property(property="block",     type="integer", example="1023"),
 *     @SWG\Property(property="timestamp", type="string",  example="2018-05-14 14:17:56+03"),
 *     @SWG\Property(property="fee",       type="integer", example="100"),
 *     @SWG\Property(property="type",      type="integer", example="2"),
 *     @SWG\Property(property="status",    type="string",  example="success"),
 *     @SWG\Property(property="data",      ref="#/definitions/TransactionData")
 * )
 */

/**
 * Class Transaction
 * @package App\Models
 *
 * @property int id
 * @property int block_id
 * @property int block
 * @property int type
 * @property int nonce
 * @property int validator_id
 * @property int gas_price
 * @property float value
 * @property string hash
 * @property string from
 * @property string to
 * @property string coin
 */
class Transaction extends Model
{
    public const TYPE_SEND = 1;
    public const TYPE_CONVERT = 2;
    public const TYPE_CREATE_COIN = 3;
    public const TYPE_DECLARE_CANDIDACY = 4;
    public const TYPE_DELEGATE = 5;
    public const TYPE_UNBOND = 6;

    /**
     * Get the block that owns the transactions.
     */
    public function block()
    {
        return $this->belongsTo(Block::class);
    }

    /**
     * @return int
     */
    public function getFeeAttribute(): int
    {
        return $this->gas_price * $this->getBasePrice($this->type);
    }

    /**
     * @param int $type
     * @return int
     */
    private function getBasePrice(int $type): int
    {
        switch ($type){
            case $this::TYPE_SEND:
                return 1000;
                break;
            case $this::TYPE_CONVERT:
                return 10000;
                break;
            case $this::TYPE_CREATE_COIN:
                return 100000;
                break;
            case $this::TYPE_DECLARE_CANDIDACY:
                return 100000;
                break;
            case $this::TYPE_DELEGATE:
                return 10000;
                break;
            case $this::TYPE_UNBOND:
                return 1; // TODO: узнать цену
                break;
            default:
                return 0;
                break;
        }
    }

}