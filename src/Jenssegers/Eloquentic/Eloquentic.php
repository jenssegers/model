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