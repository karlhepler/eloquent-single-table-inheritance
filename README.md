Eloquent Single Table Inheritance
==================================

This is a very simple implementation of single table inheritance for Eloquent (Laravel & Lumen).

There are no attribute restrictions or anything that could be considered robust.
There are other libraries that do those things quite well.

This simply allows you to have a single table that, when fetched, is thrown into
an Eloquent model of your choice, defined by a column in that table
(typically "type", which is the default keyed type column. You can customize this).

For instance, imagine you have the following table (called `people`):

| id | name | sex  |
| ---|------|------|
| 1  | Fred | boy  |
| 2  | Jill | girl |

... and imagine you had the following classes:

```php
class Person extends StiParent
{
    protected $table = 'people';
    protected static $stiKey = 'sex';
    protected static $stiChildren = [
        'boy' => Boy::class,
        'girl' => Girl::class,
    ];
}

class Boy extends Person
{
    //
}

class Girl extends Person
{
    //
}
```

When you fetch all people, you will get a mix of boys & girls in the collection.

When you fetch just boys, you will get just boys.

When you fetch just girls, you will get just girls.

UNFORTUNATELY
-------------

1. You cannot make your `Person` class abstract.
2. You must specify the table on the parent
