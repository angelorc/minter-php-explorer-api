<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

/**
* @SWG\Definition(
 *     definition="BlockMetaData",
 *     type="object",
 *
 *     @SWG\Property(property="current_page", type="integer", example="1"),
 *     @SWG\Property(property="from",         type="integer", example="2"),
 *     @SWG\Property(property="last_page",    type="integer", example="4"),
 *     @SWG\Property(property="path",         type="string",  example="http://localhost:8000/api/v1/blocks"),
 *     @SWG\Property(property="per_page",     type="integer", example="50"),
 *     @SWG\Property(property="to",           type="integer", example="50"),
 *     @SWG\Property(property="total",        type="integer", example="130")
 * )
**/
/**
* @SWG\Definition(
 *     definition="BlockLinksData",
 *     type="object",
 *
 *     @SWG\Property(property="first", type="string", example="http://localhost:8000/api/v1/blocks?page=1"),
 *     @SWG\Property(property="last",  type="string", example="http://localhost:8000/api/v1/blocks?page=2"),
 *     @SWG\Property(property="prev",  type="string", example="null"),
 *     @SWG\Property(property="next",  type="string", example="http://localhost:8000/api/v1/blocks?page=2")
 * )
**/

class BlockCollection extends ResourceCollection
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request): array
    {
        $result = [];

        $prevBlockTime = null;

        foreach (parent::toArray($request) as &$item ){

            $data = [];

            $item['block_time'] = 5;

            foreach ($item as $k => $v ){

                if ($k === 'created_at' || $k === 'updated_at'){
                    continue;
                }

                if ($k === 'block_reward'){
                    $data['reward'] = $v;
                    continue;
                }

                $data[camel_case($k)] = $v;
            }

            $result[] = $data;
        }

        return $result;
    }
}