<?php

namespace app\Repository;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

interface ModelRepositoryInterface
{

    /**
     * Прлучить запрос
     * @return Builder
     */
    public function query(): Builder;

    /**
     * Получить список
     *
     * @param string $valueField
     * @param null|string $keyField
     * @return array
     */
    public function getList($valueField = 'name', $keyField = null): array;
    /**
     * Получить все записи
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
    public function create(array $data);

    /**
     * Delete model
     *
     * @param int|Model $id
     * @return bool|null
     * @throws \Exception
     */
    public function delete($id): ?bool;

}