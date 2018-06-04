<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class Block
 * @package App\Models
 *
 * @property int id
 * @property string date
 * @property int transactions_count
 *
 * @SWG\Definition(
 *     definition="TxPerDay",
 *     type="object",
 *
 *     @SWG\Property(property="date",    type="timestamp", example="2018-05-18"),
 *     @SWG\Property(property="txCount", type="integer",   example="4"),
 * )
 *
 */
class TxPerDay extends Model
{

    public $timestamps = false;

    protected $table = 'tx_per_day';

}