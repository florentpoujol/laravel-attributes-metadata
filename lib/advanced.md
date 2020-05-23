
### Fluent class

For those more into a fluent way to describe things, you are in luck, because the array syntax shown above is actually an (optional) shorthand to fill fluent instances.

The package makes heavy use a Fluent class that extends Laravel's base one.

You can see the class as a practical way to work with an associative array, or to "store" method calls and their arguments to be reapplied later to an instance of another class.

The Preset implementation itself, as well as the definition classes for the DB column, validation rules and Nova field are all fluent.


### Column definitions

On a preset instance, calling `getColumnDefinitions(): DbColumn` return an instance of `DbColumn` which you can use pretty much as you would for Laravel's built-in `ColumnDefinitions` in the migrations.

But you can also set the type of the field by calling on it the same methods you would on a `Blueprint` instance, but without the attribute name.

Examples:
```php
SomeAttribute extends BasePreset
{
    public function __construct()
    {
        $this->getColumnDefinitions()
            ->string(50)
            ->nullable()
            ->index();

        $this->getColumnDefinitions()
            ->unsignedInteger()
            ->autoIncrement();

        $this->getColumnDefinitions()
            ->timestamp()
            ->precision(2)
            ->useCurrent();
    }
}
```

Note that you can only call the methods from the Blueprint class that denote a type of field.
Helpers like `timestamps()`, that actually sets two datetimes do not work.


### Validation rules and message

In a similar fashion than for the column definitions, calling `getValidationDefinitions(): Validation` return a fluent instance on which each method calls are turned into a validation rule.

Examples:
```php
SomeAttribute extends BasePreset
{
    public function __construct()
    {
        $this->getValidationDefinitions()

            // all validations rules are supported
            ->required()
            ->alpha_dash()
            ->in(['foo', 'bar'])
            
            // rules objects are also supported via the add method
            ->add(new Rule())

            // validation message can be set via the message method
            ->message('The :attribute is wrong !'); 

            // rules can also be added via the set() or add() method
            // the 3 following calls are have the same effect
            ->min(5)
            ->set('min', 5)
            ->add('min:5');

            // and if needed you can remove already set rules
            ->remove('min')
            ->remove(new Rule())
            ->remove(Rule::class);
    }
}
```


### Nova field definitions

For Nova field you can directly set and work with an instance of an actual Nova field, via the `setNovaField()` / `getNovaField()` methods.

But as before, calling `getNovaFieldDefinitions(): NofaField` return a fluent instance that you can setup exactly like an actual field.

Examples:
```php
SomeAttribute extends BasePreset
{
    public function __construct()
    {
        $this->getNovaFieldDefinitions()

            // the type of field can be set via common shorthand
            // or by passing the Fqcn of a field
            ->type('json') // Code field
            ->type(Select::class)

            // if not set, the "nice" name will be guessed from the attribute name (that itself is set automatically when the field is resolved from the definitions)
            ->name('Is required')

            // if needed for relationship field
            ->resource(PostResource::class)

            // + all the usual methods
            ->sortable()
            ->searchable()
            ->rules('required', 'min:5')
            ->hideFromIndex(); // and so on

        // The definitions class has several static factories that return an instance with the type already set and the PHPDoc that includes the definitions of that type of field

        $definitions = NovaField::datetime() // @return static&\Laravel\Nova\Fields\DateTime
                            ->format('...')
    }
}
```

/!\ The package currently do not support setting several fields for the same attribute.


### Metadata definitions

The attributes metadata used to configure model behavior can also be set, this time directly on the preset instance which is also fluent.

```php
Post extends BasePreset
{
    public function __construct()
    {
        $this
            ->cast('string')
            ->cast('datetime:Y-m-d')
            ->cast('datetime', 'Y-m-d')
            
            ->cast(null)
            // same as 
            ->remove('cast')

            ->fillable()
            ->fillable(false)

            ->guarded()
            ->guarded(false)

            ->hidden()
            ->hidden(false)

            ->date()
            ->date(false)

            // default value (the initial value in the model's $attribute array)
            ->default($defaultValue);
        
        // the corresponding getters are also availble
        $this->hasCast();
        $this->getCast();
        $this->isFillable();
        $this->isGuarded();
        $this->isHidden();
        $this->isDate();
        $this->hasDefault();
        $this->getDefault();
    }
}
```






## Advanced

### Getting the model presets instance

Beside the methods shown in the Usage section above, the `HasAttributePresets` trait provide the static `getAttributePresetCollection(): \FlorentPoujol\LaravelAttributePresets\AttributePresetCollection` method which is a custom collection, based on Laravel base one.

Among others, that instance provides the following public methods:
- `hasAttribute(string $name): bool`
- `getNames(): string[]`
- `get(string $name): AttributeMetadata`

If you want to use your own class instead of the base one, you may set its FQCN as the value of the static property `$modelMetadataFqcn` on your model.



## Fluent class

Al the definitions classes, and the base preset itself extends the `\FlorentPoujol\LaravelAttributePresets\Definitions\Fluent` class that itself extends Laravel's one with many convenience methods and features.

The point of a fluent class is to allow to fluently call any methods on it.

The interesting bit of course is that each method call (when the method doesn't actually exist on the class) is actually recorded and the method name and its argument are stored in an associative array.

So the fluent class can be seens as a practical way to work with an associative array.
The implementation of this package offers a lot more conveniences methods (all themselves fluent except for the getters), over the basic fluency.

When building an instance, it can be seeded with an array.  
Despite being stored as an associative array, you can have values with a numerical key.

Also key/value pairs can be expressed as a single string, separated by a colon (like a validation rule).

Example :
```php
$attributes = [
    'required',
    'max:5',
    'string' => null,
    'min' => 5,
];

$fluent = new Fluent($attributes);

// you can also at any point fill an existing instance this way
$fluent->fill($attributes);

// in this example, the array will be turned in the instance into
[
    'required' => null,
    'max' => '5',
    'string' => null,
    'min' => 5,
]
```

The class also has the traditional `tap(callable $callback)` method that accept a callable, that gets passed an instance of the class.  

The values in the array are 
- `null` when no argument was passed to the method call, or the value was set with a numerical index.
- the single value when the method call had a single argument
- or the array of 

Adding or setting a value can be done with the methods of the same name :
```php
// all theses calls are the same effect (and are all fluent)
$fluent->add('max:5');

$fluent->set('max', 5);
$fluent->set('max:5', null);

$fluent->max(5);
```

Removing one key/value can be done by calling the `remove($key)` method or by filling a value where the key is prepended by a minus sign :
```php
// these two calls have the same effect, are fluent, and are safe if the value doesn't exist.
$fluent->remove('min');
$fluent->fill(['-min']);

// use the clear() method to clear() part or all of the instances values
$fluent->clear();
$fluent->clear([$key1, $ke2]);
```

The existence of a key and its value(s) can be fetched with the `get()` and `has/is()` methods but also with dynamic methods, prefixed with get/has/is.

```php
$max = $fluent->get('max');
$max = $fluent->getMax(); // returns null|mixed|array<mixed>

$fluent->has('max'); // array_key_exists() is used, so it returns true for whatever value has the key as long as it exists
$fluent->hasMax(); // returns true / false

$fluent->is('fillable');
$fluent->isFillable(); // returns true if the key exists and has any truthy value
```

As said above, one usage of the fluent class is to actually store method calls and their arguments, to be later applied (as method calls) to another instance, via in the `applyTo(object $instance)` method.  

When applying stored key/values to an instance, array values are considered to contains several arguments. If a method has a single argument that is an array, wrap it inside another array.
```php
[
    'no_argument' => null,
    'one_argument' => 5,
    'one_argument' => [5],
    'two_arguments' => ['my_table', 'id'],
    'one_array_argument' => [['arg']],
]
```






```php
class PostModel extends LaravelBaseModel
{
    use HasAttributePresets;

    /** 
     * @return array<string, string|AttributePreset> 
     */
    public static function getRawAttributePresets(): array
    {
        return [
            'id' => (new Int())->primary(),
            'content' => (new Text())
                ->required()
                ->dbColumn(fn $def => $def->remove('nullable'))
                ->validation(function ($def) {
                    $def
                        ->max(500)
                        ->message('this is the validation message');
                })
                ->validation(['max:500']),
            'is_published' => new Boolean(false),
            'meta' => (new Json())->setDefault('{}')->setCast('array'),

            // relations can also be defined this way
            'user' => new BelongsTo(User::class),
            'comments' => new HasMany([CommentModel::class, 'post_foreign']),

            // the configuration of such "custom" field especially if used multiple times throughout the application 
            // is a good candidate to be put in its own class that would probably extend the base DateTime
            'created_at' => (new DateTime('timestamp', 'd H:i:s.u'))->setPrecision(2),

            // instead of creating instances right away you can also : 
            // 1) set the attribute class Fqcn, if the instance do not need to be configured at all after instantiation
            'slug' => Slug::class,
            // 2) use the static make() "factory" that return the class Fqcn, but that will configure the instance of the class as soon as it is created (see below for caveats)
            'meta' => Json::make(['setDefault' => '{}', 'setCast' => 'array']),
            // 3) wrap the instantiation in a closure or any callable
            'comments' => function () { return new HasMany([CommentModel::class, 'post_foreign']); },
            // or with PHP7.4+ arrow function
            'comments' => fn() => new HasMany([CommentModel::class, 'post_foreign']),
        ];
    }
}
```


```php

$def = new DbDefintions()

$def->type('string')->length(50)
$def->string(50)

```




TODO

- only one preset for relations that also have a db field, must set both db field name and relation name
- support setting foreign key constraints on column definitions
- for validation rules with a list of arguments, support passing them as array
- add full autocomplete for validation rules via PHPDoc on a trait or interface
- artisan command to synchronise definitions of model metadata and relations with the models


