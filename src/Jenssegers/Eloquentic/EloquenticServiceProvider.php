<?php namespace Jenssegers\Eloquentic;

use Illuminate\Support\ServiceProvider;

class EloquenticServiceProvider extends ServiceProvider {

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('Eloquentic');
    }

}