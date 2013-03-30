<?php namespace Jenssegers\Eloquentic\Facades;

use Illuminate\Support\Facades\Facade;

class Eloquentic extends Facade {

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'eloquentic'; }

}