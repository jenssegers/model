<?php namespace Jenssegers\Eloquentic;

use \ArrayObject;

abstract class Eloquentic extends ArrayObject {

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
     * @return Eloquentic
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

    /**
     * Convert the model instance to JSON.
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this, $options);
    }

    /**
     * Handle dynamic static method calls into the method.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        $instance = new static;

        return call_user_func_array(array($instance, $method), $parameters);
    }

    /**
     * Convert the model to its string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }

}