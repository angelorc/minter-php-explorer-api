<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * Class Validator
 * @package App\Models
 *
 * @property int id
 * @property string name
 * @property string address
 * @property string public_key
 *
 * @SWG\Definition(
 *     definition="Validator",
 *     type="object",
 *
 *     @SWG\Property(property="id", type="integer", example="12345"),
 *     @SWG\Property(property="name", type="string", example="SomeValidator"),
 *     @SWG\Property(property="address", type="string", example="XXXXXX"),
 *     @SWG\Property(property="public_key", type="string", example="XXXXX")
 * )
 */
class Validator extends Model
{

    /**
     * The transactions that belong to the block.
     */
    public function blocks()
    {
        return $this->belongsTo(Block::class);
    }

}