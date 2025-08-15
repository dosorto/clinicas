<?php

namespace App\Models\Traits;

use App\Scopes\CentroScope;

trait HasCentroScope
{
    protected static function bootHasCentroScope()
    {
        static::addGlobalScope(new CentroScope);
    }
}
