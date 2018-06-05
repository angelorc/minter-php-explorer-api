<?php
namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Balance extends Model
{
    protected $dateFormat = 'Y-m-d H:i:sO';

    protected $fillable = [
        'address',
        'coin',
        'amount'
    ];

}