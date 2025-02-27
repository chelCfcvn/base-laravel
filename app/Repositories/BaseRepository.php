<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use Exception;

abstract class BaseRepository
{
    /**
     * The model instance for the repo.
     */
    protected $model;

    /**
     * BaseRepository constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->makeModel();
    }

    /**
     * Specify Model class name.
     */
    abstract public function model();

    /**
     * Make model
     *
     * @return Model|mixed
     * @throws \Exception
     */
    public function makeModel()
    {
        $model = app()->make($this->model());

        if (!$model instanceof Model) {
            throw new Exception("Class {$this->model()} must be an instance of " . Model::class);
        }

        return $this->model = $model;
    }

    public function all()
    {
        return $this->model->paginate(10);
    }

    /**
     * Get the first specified model record from the database.
     */
    public function first()
    {
        return $this->model->first();
    }

    public function findOrFail($id)
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Get all the specified model records in the database.
     */
    public function get()
    {
        return $this->model->get();
    }

    /**
     * Create a new model record in the database.
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Insert a new model record in the database.
     */
    public function insert(array $data)
    {
        try {
            return $this->model->insert($data);
        } catch (\Exception $e) {
            logger($e->getMessage());
            return false;
        }
    }

    /**
     * Update
     *
     * @param array   $attributes Attributes
     *
     */
    public function update(array $data)
    {
        try {
            return $this->model->update($data);
        } catch (\Exception $e) {
            logger($e->getMessage());
            dd($e->getMessage());
        }
    }

    /**
     * Delete one or more model records from the database
     */
    public function delete($id)
    {
        $result = $this->model->find($id);

        if ($result) {
            $result->delete();

            return true;
        }

        return false;
    }

    /**
     * Add a basic where clause to the query.
     */
    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        return $this->model->where($column, $operator, $value, $boolean);
    }

    /**
     * Get instance
     *
     * @param integer|array $id ID
     * @param bool          $withTrashed  with Trash ? (true/false)
     *
     * @return mixed
     */
    public function find($id, $withTrashed = false)
    {
        return $this->model
            ->when($withTrashed, function ($query) {
                $query->withTrashed();
            })
            ->find($id);
    }

    public function updateOrCreate(array $attributes, array $values = [])
    {
        return $this->model->updateOrCreate($attributes, $values);
    }
}
