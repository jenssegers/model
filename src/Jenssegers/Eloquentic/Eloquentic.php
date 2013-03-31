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
        // construct ArrayObject, object properties have precedence
        parent::__construct($attributes, ArrayObject::STD_PROP_LIST);
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
     * Handle dynamic method calls into the method.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        // Create a query
        $query = $this->newQuery();

        return call_user_func_array(array($query, $method), $parameters);
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