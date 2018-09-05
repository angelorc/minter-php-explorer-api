<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


/**
 * Class Coin
 * @package App\Models
 *
 * @property string  symbol
 * @property string  name
 * @property integer crr
 * @property string  volume
 * @property string  reserve_balance
 * @property string  creator
 * @property string  created_at
 */

/**
 * @SWG\Definition(
 *     definition="Coin",
 *     type="object",
 *
 *     @SWG\Property(property="symbol", type="string", example="MNT"),
 *     @SWG\Property(property="name",   type="string", example="Minter Coin"),
 * )
 */

class Coin extends Model
{
    /**
     * PIP coefficient
     */
    public const PIP = 10 ** -18;
    public const PIP_STR = '0.000000000000000001';
    public const UNIT = 10 ** -15;
    public const UNIT_STR = '0.000000000000001';

    protected $dateFormat = 'Y-m-d H:i:sO';

    protected $fillable = [
        'symbol',
        'name',
        'crr',
        'volume',
        'reserve_balance',
        'creator',
        'created_at',
    ];
}