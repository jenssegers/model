<?php namespace Jenssegers\Eloquentic;

use \ArrayObject;

abstract class Eloquentic extends ArrayObject {

    /**
     * Create a new Eloquentic model instance.
     *
     * @param  array  $attributes
     * @return void
     */
    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
    }

    /**
     * Write all properties to internal array
     *
     * @param  string $name
     * @param  mixed  $value
     */
    public function __set($name, $value)
    {
        $this[$name] = $value;
    }

    /**
     * Get properties to internal array
     *
     * @param  string  $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this[$name];
    }

    /**
     * Save a new model and return the instance.
     *
     * @param  array  $attributes
     * @return \Jenssegers\Eloquentic\Eloquentic
     */
    static public function create($attributes = array())
    {
        $model = new static($attributes);

        if (method_exists($model, 'save'))
        {
            $model->save();
        }

        return $model;
    }

}