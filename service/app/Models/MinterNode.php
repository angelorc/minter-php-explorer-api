<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * Class MinterNode
 * @package App\Models
 * @property integer id
 * @property string host
 * @property string fullLink
 * @property integer port
 * @property float ping
 * @property bool is_secure
 * @property bool is_active
 * @property bool is_local
 * @property bool is_excluded
 */
class MinterNode extends Model
{
    public $timestamps = false;

    /**
     * Get full URL of Node
     * @return string
     */
    public function getFullLinkAttribute(): string
    {
        $protocol = 'http';

        $protocol .= $this->is_secure ? 's://' : '://';

        return $protocol . $this->host . ':' . $this->port;
    }

}