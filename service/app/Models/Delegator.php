<?php

namespace App\Models;

/**
 * Class Delegator
 * @package App\Models
 *
 * @property string coin
 * @property string address
 * @property integer value
 */


/**
 * @SWG\Definition(
 *     definition="Delegator",
 *     type="object",
 *
 *     @SWG\Property(property="coin",    type="string", example="MNT"),
 *     @SWG\Property(property="address", type="string", example="Mx2ffe59556ffc6564f8e6132f445bc2e102fd713c"),
 *     @SWG\Property(property="value",   type="string", example="99.2424"),
 * )
 */
class Delegator
{
    public $coin;
    public $address;
    public $value;
}