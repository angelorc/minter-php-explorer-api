<?php

namespace app\Repository;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface ModelRepositoryInterface
{

    /**
     * Get query
     * @param array $filters
     * @return Builder
     */
    public function query(array $filters): Builder;

    /**
     * Get list
     *
     * @param array $filters
     * @return Collection
     */
    public function getList(array $filters): Collection;

    /**
     * Get all records
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function all();

    /**
     * @param $id
     * @return Model
     */
    public function findOrFail($id): Model;

    /**
     * Find model with relations
     *
     * @param $id
     * @param $with
     * @return \Illuminate\Database\Eloquent\Collection|Model
     */
    public function findOrFailWith($id, $with);

    /**
     * @param int $id
     * @param array $columns
     * @return Model|null
     */
    public function find($id, array $columns = ['*']): ?Model;

    /**
     * Update model by id
     *
     * @param $id
     * @param array $data
     * @return bool|int
     */
    public function updateById($id, $data);

    /**
     * Update model
     *
     * @param Model $model
     * @param array $data
     * @return bool|int
     */
    public function update($model, $data);

    /**
     * Create model
     *
     * @param array $data
     * @return Model
     */
    public function create(array $data): Model;

    /**
     * Delete model
     *
     * @param int|Model $id
     * @return bool|null
     * @throws \Exception
     */
    public function delete($id): ?bool;

}