<?php

namespace Ferri\LaravelSettings;

class Facade extends \Illuminate\Support\Facades\Facade
{
    /**
         * {@inheritDoc}
         */
        protected static function getFacadeAccessor()
        {
            return 'settings';
        }
}
