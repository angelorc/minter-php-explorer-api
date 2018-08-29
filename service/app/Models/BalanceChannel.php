<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * Class BalanceChannel
 * @package App\Models
 *
 * @property string name
 * @property string address
 */
class BalanceChannel extends Model
{
    /**  @var string */
    protected $dateFormat = 'Y-m-d H:i:sO';

    /** @var array */
    protected $fillable = [
        'name',
        'address'
    ];

}