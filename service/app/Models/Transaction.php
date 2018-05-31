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
 *     @SWG\Property(property="amount", type="float",  example="23.93674623")
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
 *     @SWG\Property(property="type",      type="integer", example="send"),
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
 * @property string payload
 */
class Transaction extends Model
{
    public const PIP = 0.00000001;

    public const TYPE_SEND = 1;
    public const TYPE_CONVERT = 2;
    public const TYPE_CREATE_COIN = 3;
    public const TYPE_DECLARE_CANDIDACY = 4;
    public const TYPE_DELEGATE = 5;
    public const TYPE_UNBOND = 6;
    public const TYPE_REDEEM_CHECK = 7;

    public const PAYLOAD = 8;
    public const TOGGLE_CANDIDATES_STATUS = 9;

    protected $dateFormat = 'Y-m-d H:i:sO';

    /**
     * Get the block that owns the transactions.
     */
    public function block()
    {
        return $this->belongsTo(Block::class);
    }

    /**
     * Коммисия за транзакцию
     * @return float
     */
    public function getFeeAttribute(): float
    {

        $payloadPrice = 0;

        if ( \strlen($this->payload) ){
            $payloadPrice = $this->getBasePrice($this::PAYLOAD) * \strlen($this->payload);
        }

        return ($this->gas_price * $this->getBasePrice($this->type) + $payloadPrice) * $this::PIP;
    }

    /**
     * Статус
     * @return string
     */
    public function getStatusAttribute(): string
    {
        //TODO: добавить реализацию
        return 'success';
    }

    /**
     * Тип транзакции
     * @return string
     */
    public function getTypeStringAttribute(): string
    {
        switch ($this->type){
            case $this::TYPE_SEND:
                return 'send';
            case $this::TYPE_CONVERT:
                return 'convert';
            case $this::TYPE_CREATE_COIN:
                return 'createCoin';
            case $this::TYPE_DECLARE_CANDIDACY:
                return 'declareCandidacy';
            case $this::TYPE_DELEGATE:
                return 'delegate';
            case $this::TYPE_UNBOND:
                return 'unbond';
            default:
                return '';
        }
    }

    /**
     * Базовая стоимость
     * @param int $type
     * @return int
     */
    private function getBasePrice(int $type): int
    {
        switch ($type){
            case $this::PAYLOAD:
                return 500;
                break;
            case $this::TYPE_SEND:
            case $this::TYPE_REDEEM_CHECK:
            case $this::TOGGLE_CANDIDATES_STATUS:
                return 1000;
                break;
            case $this::TYPE_CONVERT:
            case $this::TYPE_UNBOND:
            case $this::TYPE_DELEGATE:
                return 10000;
                break;
            case $this::TYPE_CREATE_COIN:
            case $this::TYPE_DECLARE_CANDIDACY:
                return 100000;
                break;
            default:
                return 0;
                break;
        }
    }

}