<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


/**
 * Class Coin
 * @package App\Models
 *
 * @property integer id
 * @property integer coin_id
 * @property integer currency_id
 * @property float price
 * @property string created_at
 */
class FiatPrice extends Model
{
    protected $dateFormat = 'Y-m-d H:i:sO';

    protected $table = 'fiat_price';

}