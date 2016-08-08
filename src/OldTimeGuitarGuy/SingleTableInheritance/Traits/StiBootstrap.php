<?php

namespace OldTimeGuitarGuy\SingleTableInheritance\Traits;

use Illuminate\Database\Eloquent\Builder;

trait StiBootstrap
{
    /**
     * I'm choosing to do this here instead of overriding Eloquent's
     * boot method. The way I see it, the less overrides the better.
     *
     * @return void
     */
    protected static function bootStiBootstrap()
    {
        // Assign local variables
        $type = static::getKeyedTypeFromClassName();
        $key = static::$stiKey;

        // Doing this allows us to get all children in one request
        // if we do that request from the parent
        if ( is_null($type) ) {
            return;
        }

        // Only return queries for models of the same type
        static::addGlobalScope($key, function (Builder $builder) use ($key, $type) {
            $builder->where($key, $type);
        });
    }
}
