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

/**
 *  * @SWG\Definition(
 *     definition="Slash",
 *     type="object",
 *
 *     @SWG\Property(property="block",     type="integer",   example="12345"),
 *     @SWG\Property(property="timestamp", type="timestamp", example="2018-05-18 15:06:10+00"),
 *     @SWG\Property(property="coin",      type="string",    example="MNT"),
 *     @SWG\Property(property="address",   type="string",    example="Mx444c4f1953ea170f74eabef4eee52ed8276a7d5e"),
 *     @SWG\Property(property="validator", type="string",    example="Mpee580b2be5176c054beb65e7c0d5cbea6307d6e3dc1c8ba68e2ec6c9cc7f1ccc"),
 *     @SWG\Property(property="amount",    type="float",     example="26.056374544")
 * )
 */
class Slash extends Model
{
    public $timestamps = false;

    public function block()
    {
        return $this->hasOne(Block::class, 'height', 'block_height');
    }

}