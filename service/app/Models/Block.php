<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class Block
 * @package App\Models
 *
 * @property int height
 * @property string timestamp
 * @property int tx_count
 * @property int size
 * @property string hash
 * @property string created_at
 * @property string formattedDate
 * @property int block_time
 * @property float block_reward
 * @property Collection validators
 *
 * @SWG\Definition(
 *     definition="Block",
 *     type="object",
 *
 *     @SWG\Property(property="height",    type="integer",   example="12345"),
 *     @SWG\Property(property="timestamp", type="timestamp", example="2018-05-18 15:06:10+00"),
 *     @SWG\Property(property="txCount",   type="integer",   example="4"),
 *     @SWG\Property(property="blockTime", type="integer",   example="5"),
 *     @SWG\Property(property="size",      type="integer",   example="1024"),
 *     @SWG\Property(property="hash",      type="string",    example="2BFB56B62A25E0D5853A7790211E64B198BEECFD"),
 *     @SWG\Property(property="reward",    type="string",    example="130.02500008"),
 *     @SWG\Property(property="validators", type="array",
 *         @SWG\Items(ref="#/definitions/Validator")
 *     )
 * )
 *
 */
class Block extends Model
{
    protected $dateFormat = 'Y-m-d H:i:s.uO';

    protected $hidden = ['id'];

    /**
     * The transactions that belong to the block.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * The validators that approved the block.
     */
    public function validators()
    {
        return $this->belongsToMany(Validator::class);
    }

    public function getFormattedDateAttribute()
    {
        return date('Y-m-d H:i:sO', $this->created_at->getTimestamp());
    }
}