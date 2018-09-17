<?php
namespace App\Traits;


use App\Models\MinterNode;

trait NodeTrait
{
    /**
     * Get actual Minter node
     * @return MinterNode
     */
    protected function getActualNode(): MinterNode
    {
        $node = MinterNode::where('is_excluded', '!=', true)
            ->where('is_active', true)
            ->orderBy('version', 'desc')
            ->orderBy('ping')
            ->first();

        if (!$node) {
            $apiLink = explode(':', env('MINTER_API'));
            $node = new MinterNode;
            $node->is_active = true;
            $node->ping = 0;
            $node->host = $apiLink[0];
            $node->port = $apiLink[1];
        }

        return $node;
    }
}