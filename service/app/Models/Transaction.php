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
 * @property int gas_wanted
 * @property int gas_used
 * @property int commission
 * @property int stake
 * @property float value
 * @property float fee
 * @property float initial_amount
 * @property float initial_reserve
 * @property float constant_reserve_ratio
 * @property string hash
 * @property string service_data
 * @property string from
 * @property string to
 * @property string coin
 * @property string payload
 * @property string pub_key
 * @property string address
 * @property string created_at
 * @property string coin_to_sell
 * @property string coin_to_buy
 * @property string name
 * @property string symbol
 * @property string log
 * @property boolean status
 */
class Transaction extends Model
{
    public const TYPE_SEND = 1;
    public const TYPE_SELL_COIN = 2;
    public const TYPE_BUY_COIN = 3;
    public const TYPE_CREATE_COIN = 4;
    public const TYPE_DECLARE_CANDIDACY = 5;
    public const TYPE_DELEGATE = 6;
    public const TYPE_UNBOUND = 7;
    public const TYPE_REDEEM_CHECK = 8;
    public const TYPE_SET_CANDIDATE_ONLINE = 9;
    public const TYPE_SET_CANDIDATE_OFFLINE = 10;


    public const PAYLOAD = 'payload';
    public const TOGGLE_CANDIDATES_STATUS = 'toggle_status';

    protected $dateFormat = 'Y-m-d H:i:sO';

    /**
     * Get the block that owns the transactions.
     */
    public function block()
    {
        return $this->belongsTo(Block::class);
    }

    /**
     * Get transaction commission
     * @return string
     */
    public function getFeeMntAttribute(): string
    {
        $result = bcmul($this->fee, '1000000000000000');
        return bcmul($result, Coin::PIP_STR, 18);
    }

    /**
     * Get transaction status
     * @return string
     */
    public function getStatusAttribute(): string
    {
        return isset($this->log) ? 'fail' : 'success';
    }

    /**
     * Get transaction type
     * @return string
     */
    public function getTypeStringAttribute(): string
    {
        switch ($this->type){
            case $this::TYPE_SEND:
                return 'send';
            case $this::TYPE_SELL_COIN:
                return 'sellCoin';
            case $this::TYPE_BUY_COIN:
                return 'buyCoin';
            case $this::TYPE_CREATE_COIN:
                return 'createCoin';
            case $this::TYPE_DECLARE_CANDIDACY:
                return 'declareCandidacy';
            case $this::TYPE_DELEGATE:
                return 'delegate';
            case $this::TYPE_UNBOUND:
                return 'unbound';
            case $this::TYPE_REDEEM_CHECK:
                return 'redeemCheckData';
            case $this::TYPE_SET_CANDIDATE_ONLINE:
                return 'setCandidateOnData';
            case $this::TYPE_SET_CANDIDATE_OFFLINE:
                return 'setCandidateOffData';
            default:
                return '';
        }
    }

    /**
     * Get base price
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
            case $this::TYPE_UNBOUND:
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