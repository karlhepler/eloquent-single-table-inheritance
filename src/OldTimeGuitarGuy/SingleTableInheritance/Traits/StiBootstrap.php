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
        // Doing this allows us to get all children in one request
        // if we do that request from the parent
        if ( is_null(static::getKeyedTypeFromClassName()) ) {
            return;
        }

        // Only return queries for models of the same type
        static::addGlobalScope(static::$stiKey, function (Builder $builder) {
            $builder->where(static::$stiKey, static::getKeyedTypeFromClassName());
        });
    }
}