<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


/**
 * @property integer transaction_id
 * @property string key
 * @property string value
 */
class TxTag extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'key',
        'value',
    ];
}