<?php

namespace MarothyZsolt\ViewModel\Facades;

use Illuminate\Support\Facades\Facade;

class ViewModel extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'viewmodel';
    }
}
