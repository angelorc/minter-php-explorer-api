<?php
namespace App\Models;


use Illuminate\Database\Eloquent\Model;


/**
 * Class Balance
 * @package App\Models
 *
 * @property string address
 * @property string coin
 * @property string amount
 */
class Balance extends Model
{
    protected $dateFormat = 'Y-m-d H:i:sO';

    protected $fillable = [
        'address',
        'coin',
        'amount'
    ];

}