<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


/**
 * @property integer transaction_id
 * @property integer weight
 * @property string address
 */
class TxSign extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'weight',
        'address',
    ];
}