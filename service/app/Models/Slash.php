<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * Class Slash
 * @package App\Models
 *
 * @property int block_height
 * @property int amount
 * @property string coin
 * @property string address
 * @property string validator_pk
 *
 */
class Slash extends Model
{
    public $timestamps = false;

}