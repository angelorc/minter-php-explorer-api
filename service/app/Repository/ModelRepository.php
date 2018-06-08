<?php

namespace App\Repository;

use Illuminate\Database\Eloquent\Model;

abstract class ModelRepository implements ModelRepositoryInterface
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->model->query();
    }

    /**
     * Получить список
     *
     * @param string $valueField
     * @param null|string $keyField
     * @return array
     */
    public function getList($valueField = 'name', $keyField = null): array
    {
        $keyField = $keyField ?? $this->model->getKeyName();

        $list = $this->model->query()
            ->pluck($valueField, $keyField)
            ->toArray();

        asort($list);

        return $list;
    }

    /**
     * Получить все записи
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function all()
    {
        return $this->model->all();
    }

    /**
     * @param $id
     * @return Model
     */
    public function findOrFail($id): Model
    {
        return $this->model->query()->findOrFail($id);
    }

    /**
     * Find model with relations
     *
     * @param $id
     * @param $with
     * @return \Illuminate\Database\Eloquent\Collection|Model
     */
    public function findOrFailWith($id, $with)
    {
        return $this->model->query()
            ->with($with)
            ->findOrFail($id);
    }

    /**
     * @param int $id
     * @param array $columns
     * @return Model|null
     */
    public function find($id, array $columns = ['*']): ?Model
    {
        return $this->model->find($id, $columns);
    }

    /**
     * Update model by id
     *
     * @param $id
     * @param array $data
     * @return bool|int
     */
    public function updateById($id, $data)
    {
        $model = $this->findOrFail($id);

        return $model->update($data);
    }

    /**
     * Update model
     *
     * @param Model $model
     * @param array $data
     * @return bool|int
     */
    public function update($model, $data)
    {
        return $model->update($data);
    }

    /**
     * Create model
     *
     * @param array $data
     * @return Model
     */
    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    /**
     * Delete model
     *
     * @param int|Model $id
     * @return bool|null
     * @throws \Exception
     */
    public function delete($id): ?bool
    {
        $model = $id instanceof Model ? $id : $this->findOrFail($id);

        return $model->delete();
    }

}