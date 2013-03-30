<?php namespace Jenssegers\Eloquentic;

use Illuminate\Support\ServiceProvider;

class EloquenticServiceProvider extends ServiceProvider {

	/**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->package('jenssegers/chef');
    }

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