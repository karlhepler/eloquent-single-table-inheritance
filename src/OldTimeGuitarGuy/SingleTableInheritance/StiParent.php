<?php

namespace OldTimeGuitarGuy\SingleTableInheritance;

use Illuminate\Database\Eloquent\Model;

/**
 * Instead of extending from Laravel's Eloquent Model,
 * you can extend your model from this. It will allow
 * for simple single table inheritance by defining the
 * stiKey (optional) and the stiChildren (required).
 */
abstract class StiParent extends Model
{
    use Traits\StiBootstrap;

    /**
     * The key in the db that
     * defines the child's type
     *
     * @var string
     */
    protected static $stiKey = 'type';

    /**
     * A hash of children.
     * ex: ['stringType' => ActualType::class, ...]
     *
     * @var array
     */
    protected static $stiChildren = [];

    /**
     * !! OVERRIDE ELOQUENT MODEL !!
     * Create a new Eloquent model instance.
     *
     * @param  array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        // Set the type to the given type if possible.
        // Otherwise, get the keyed type from the classname.
        $attributes[static::$stiKey] = array_get(
            $attributes, static::$stiKey,
            static::getKeyedTypeFromClassName()
        );

        // Call the parent onw that the type is set
        parent::__construct($attributes);
    }

    /**
     * !! OVERRIDE ELOQUENT MODEL !!
     * Create a new model instance that is existing.
     *
     * @param  array  $attributes
     * @param  string|null  $connection
     * @return static
     */
    public function newFromBuilder($attributes = [], $connection = null)
    {
        static::stiEnforceChildren();

        // If there isn't a key set, just return the default
        if (! isset($attributes->{static::$stiKey})) {
            return parent::newFromBuilder($attributes, $connection);
        }

        // We create the model based on the keyed type
        $class = static::stiChildren()[$attributes->{static::$stiKey}];
        $model = new $class;
        $model->exists = true;

        /** The rest is copy-and-paste from eloquent */

        $model->setRawAttributes((array) $attributes, true);

        $model->setConnection($connection ?: $this->connection);

        return $model;
    }

    /**
     * Throw an exception if there are no children set
     *
     * @return void
     * @throws SingleTableInheritanceException
     */
    protected static function stiEnforceChildren()
    {
        // If there are no children, throw an exception
        if ( empty(static::stiChildren()) ) {
            throw new Exceptions\StiException('No children defined.');
        }
    }

    /**
     * Get the keyed type from the class name
     *
     * @return string
     */
    protected static function getKeyedTypeFromClassName()
    {
        // Make sure there are children
        static::stiEnforceChildren();

        // Get it from the flipped children,
        // keyed by the current class
        return array_get(
            array_flip(static::stiChildren()), static::class
        );
    }

    /**
     * Get the single table inheritance children
     *
     * @return array
     */
    protected static function stiChildren()
    {
        return static::$stiChildren;
    }
}
