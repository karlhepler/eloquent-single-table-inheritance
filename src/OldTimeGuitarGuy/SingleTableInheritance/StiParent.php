<?php

namespace OldTimeGuitarGuy\SingleTableInheritance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

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
     * This variable is a quick reference
     * to tell us if the sti children were
     * already checked
     *
     * @var boolean
     */
    protected static $stiChildrenChecked = false;

    /**
     * Once we figure out the sti keyed type,
     * we save it here for quick reference
     *
     * @var string
     */
    protected static $stiKeyedType;

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
        // Return early if children have already been check
        if ( static::$stiChildrenChecked ) {
            return;
        }

        // If there are no children, throw an exception
        if ( empty(static::stiChildren()) ) {
            throw new Exceptions\StiException('No children defined.');
        }

        // We have just checked the children!
        static::$stiChildrenChecked = true;
    }

    /**
     * Get the keyed type from the class name
     *
     * @return string
     */
    protected static function getKeyedTypeFromClassName()
    {
        // If we've already done this, then just return what we've done
        if ( isset(static::$stiKeyedType) ) {
            return static::$stiKeyedType;
        }

        // Make sure there are children
        static::stiEnforceChildren();

        // Get it, set it, & return it
        return static::$stiKeyedType = array_get(
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
