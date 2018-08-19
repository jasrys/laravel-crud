<?php

namespace Jasrys\Crud\Test;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Jasrys\Crud\CrudServiceProvider;

class TestCase extends OrchestraTestCase
{
    /**
     * Load package service provider
     * @param  \Illuminate\Foundation\Application $app
     */
    protected function getPackageProviders($app)
    {
        return [
            CrudServiceProvider::class,
        ];
    }
}
