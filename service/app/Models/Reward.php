<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * Class Reward
 * @package App\Models
 *
 * @property int block_height
 * @property int amount
 * @property string role
 * @property string address
 * @property string validator_pk
 *
 */
class Reward extends Model
{
    public $timestamps = false;

}