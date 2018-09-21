<?php

namespace App\Repository;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class ModelRepository implements ModelRepositoryInterface
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(array $filters = []): \Illuminate\Database\Eloquent\Builder
    {
        $query = $this->model->query();

        if (\count($filters)) {
            $this->applyFilters($filters, $query);
        }

        return $query;
    }

    /**
     * Get list
     *
     * @param array $filters
     * @return Collection
     */
    public function getList(array $filters = []): Collection
    {
        return $this->query($filters)->get();
    }

    /**
     * Get all records
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


    /**
     * @param array $filters
     * @param $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applyFilters(array $filters, &$query): \Illuminate\Database\Eloquent\Builder
    {
        foreach ($filters as $filter) {
            if (strpos($filter['field'], 'start_') !== false || strpos($filter['field'], 'end_') !== false) {
                $field = explode('_', $filter['field'])[1];
                $query->where($field, $filter['sign'], $filter['value']);
            } else {
                $query->where($filter['field'], $filter['sign'], $filter['value']);
            }
        }

        return $query;
    }

}