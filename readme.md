

# Laravel attribute presets

The idea of this package is to provide a single place (a class) where to define all information about your model attributes :
- DB column definitions
- validations rules and message
- cast
- Nova field
- kind of relation
- and other metadata like its default value, if it is fillable, guarded, hidden, a date, etc...

Setting up a new attribute on a model is as simple as declaring to the model its name and preset (and maybe adjusting its instance to the specific needs of that attribute/model).  
Editing one of the preset reflects immediately everywhere the info you changed is used.  

Presets are just simple classes that are usually modelled after a database field type, or usage of attribute that has the same characteristics every times you use one (like a relation, a VAT, a slug, ...).
Since hey are simple classes, they are easilly extendable, composable, and overidable at runtime.

Allong code generation tools like the `make` Artisan command or [Blueprint](https://github.com/laravel-shift/blueprint), reusing the same preset accross projects is a great way to drastically reduce the boilerplate needed to start a new one.


## Quick Example

Here is an example of how presets can be mapped to attributes, for a traditional `Post` model :

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
            'id' => PrimaryId::class,
            'content' => (new Text())->setMaxlength(500)->markRequired(),
            'is_published' => new Boolean(false),
            'meta' => (new Json())->setDefault('{}')->setCast('array'),

            // relations can also be defined this way
            'user' => new BelongsTo(User::class, true),

            'comments' => new HasMany([Comment::class, 'post_foreign']),

            // the configuration of such "custom" field especially if used multiple times throughout the application 
            // is a good candidate to be put in its own class that would probably extend the base DateTime
            'created_at' => (new DateTime('timestamp', 'H:i:s.u'))->precision(2),

            // instead of creating instances right away you can also : 
            // 1) set the attribute class Fqcn, if the instance do not need to be configured at all after instantiation
            'slug' => Slug::class,
            // 2) use the static make() "factory" that return the class Fqcn, but that will configure the instance of the class as soon as it is created (see below for caveats)
            'meta' => Json::make(['default:{}', 'setCast' => 'array']),
            // 3) wrap the instantiation in a closure or any callable
            'comments' => function () { return new HasMany([CommentModel::class, 'post_foreign']); },
            // or with PHP7.4+ arrow function
            'comments' => fn() => new HasMany([CommentModel::class, 'post_foreign']),
        ];
    }
}
```


## Mapping presets to attributes of a model

Once you have preset defined, it is as easy as adding the `HasAttributePresets` trait on the model, and implementing a static `getAttributePresets()` method that returns an associative array of attribute names as keys and their presets instances (or factory callable) as values.


## Usage

Once the `HasAttributePresets` trait is added on a model, you have access to several public static methods on the model, including 
- `getAttributePresetCollection(): PresetCollection` method that returns an instance of `PresetCollection`.
- `getAttributePreset(string $name): ?Preset`


### In validation

```php
/**
 * Route: POST /posts
 */
public function store(Request $request)
{
    $request->validate(Post::getValidationRules());

    // ...
}
```

In form requests:

```php
public function rules()
{
    return Post::getValidationRules(); // array<string, array<string|\Illuminate\Validation\Rule>>
}

public function messages()
{
    return Post::getValidationMessages(); // array<string, null|string>
}
```

For both methods, you can limit the returned attributes : `Post::getValidationRules(['slug', 'content'])`.


### In Laravel Nova

```php
public function fields(Request $request)
{
    return Post::getNovaFields(); // Field[]
}
```

Or limit the considered fields : `Post::geteNovaFields(['slug', 'content'])`.  

The fields will be ordered as in the passed array, or as the attributes are defined on the model.


### In migrations

For very simple cases, or projects that do not evolve, or for which it is ok to run `php artisan migrate:fresh` for every attribute change, you can use the `addColumnsToTable()` method :

```php
public function up()
{
    Schema::create('posts', function (Blueprint $table) {
        Post::addColumnsToTable($table);

        // or with a limited set of attributes
        Post::addColumnsToTable($table, ['column1', 'column2']);
    });
}
```

## Setting up models from their attribute presets

Model behavior is configurable by a set of properties like the ones below:
- `$primaryKey`, `$incrementing` and `$keyType`
- `$guarded`, `$fillable` and `$hidden`
- `$attributes` (default values)
- `$dates`
- `$casts`

If attribute presets contain the right information, it is possible to altogether forget about any of them and just add the `SetupModelFromAttributePresets` trait on the model.  

The trait do not populate the properties, it overrides the corresponding methods of the base model that usually returns the value of the property.  

So as always with traits be careful if you have overridden one of these methods, yourself or any package, there may be conflict.
   
**Any values existing on the properties takes precedence over the values defined in the presets.** 

Note that when using this trait all presets instance are resolved on model boot (the first time an instance of that model is created).


## Dynamic relations

If you want relations to be handled via the presets, add the `HandlesRelationsFromAttributePresets` trait on your models.

Note that it overrides the `__call` magic methods, so be careful with conflicts with other traits that may do the same.

If relations are defined in the presets, you do not need anymore to define them as actual method on the model itself.
But you still can, and those hardcoded in the model class will take precedence.    

Also note that relations that have a field in the database, like the `BelongsTo` relation of the example needs to be defined as 2 attributes, one for the DB field, one for the relation.

Of course you can still access them both as a method -that return a Relationship instance- and as property -that returns the result of the DB query-.


## Example presets

The `Examples` folder contain already-built and flexible preset for a variety of common use cases.

You *may* built your own preset by extending these one, but we do not recommand it.
They are here solely as example, for use in the example project and for tests, we make not promise to not introduce a breaking change, even during a pakcage minor version bump.

If you want to base you preset on these, **copy/paste them**, and keep the licence at the top.  
Or if you extends them (fine for a short or non evolutive projet), at least target a specific minor version of this package in your Composer file.


## Building presets

Presets are simple classes that implements the `Preset` interface.  
The interface only accounts for how the data is extracted from a preset.

How the data is defined is up to the implementation, which this package provides one.

Beside the interface or even the base preset class, you are free to organize your presets classes however you want.  
Typically each classes represent either a **type** of attribute (that usually follow a type of field in the database), or a **usage** like a VAT attribute, or relations.  


### Fluent class

The package makes havy use a Fluent class that extends Laravel's base one.

You can see the class as a practical way to work with an associative array, or to "store" method calls and their arguments to be reapplied later to an instance of another class.

The Preset implementation itself, as well as the definition classes for the DB column, validation rules and Nova field all extend that class.


### Column definitions

On a preset instance, calling `getColumnDefinitions(): DbColumn` return an instance of `DbColumn` which you can use pretty much as you would for Laravel's built-in `ColumnDefinitions` in the migrations.

But you can also set the type of the field by calling on it the same methods you would on a `Blueprint` instance, but without the attribute name.

Examples:
```php
Post extends BasePreset
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

In a similar fashion as for the column definitions, calling `getValidationDefinitions(): Validation` return a fluent instance on which each method calls are turned into a validation rule.

Examples:
```php
Post extends BasePreset
{
    public function __construct()
    {
        $this->getValidationDefinitions()

            // all validations rules are supported but only the few "main" ones have PHPDocs and will autocomplete in PHPStorm
            ->required()
            ->alpha_dash()
            ->in(['foo', 'bar'])
            
            // rules objects can be added via the add method
            ->add(new Rule())

            // validation message can be set via the message method
            ->message('The :attribute is wrong !'); 

            // rules can also be added via the set() or add() method
            // the 3 following calls are have the same effect
            ->min(5)
            ->set('min', 5)
            ->add('min:5');

            // and if needed you can remove alredy set rules
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
Post extends BasePreset
{
    public function __construct()
    {
        $this->getNovaFieldDefinitions()

            // the type of field can be set via common shorthand
            // or by passing the Fqcn of a field
            ->type('json') // Code
            ->type(Select::class)

            // if not set, the "nice" name will be guessed from the attribute name (that itself is set automatically when the field is resolved from the definitions)
            ->name('Is requied')

            // for realtionship field
            ->resource(PostResource::class)

            // + all the usual methods
            ->sortable()
            ->searchable()
            ->rules('required', 'min:5')
            ->hideFromIndex();

        // The definitions class has several static factories that return an instance with the type already set and the PHPDoc that includes the definitions of that type of field

        $definitions = NovaField::date() // @return static&\Laravel\Nova\Fields\Date
                            ->format('...')
    }
}
```

/!\ The package currently do not support setting several fields for the same attribute.


## Advanced

### Getting the model presets instance

Beside the methods shown in the Usage section above, the `HasAttributePresets` trait provide the static `getAttributePresetCollection(): \FlorentPoujol\LaravelAttributePresets\AttributePresetCollection` method which is a custom collection, based on Laravel base one.

Among others, that instance provides the following public methods:
- `hasAttribute(string $name): bool`
- `getNames(): string[]`
- `get(string $name): AttributeMetadata`

If you want to use your own class instead of the base one, you may set its FQCN as the value of the static property `$modelMetadataFqcn` on your model.



## Fluent class

The base fluent class extends Laravel's one with many convenience methods and features.

Example :
```php
$attributes = [
    'required',
    'max:5',
    'string' => null,
    'min' => 5,
];

$fluent = new Fluent();

$fluent->fill($attributes);

$fluent->add('max:5');
$fluent->set('max', 5);
// this is the same as 
$fluent->max(5)

$max = $fluent->getMax(); // returns 5

$fluent->has($key);
$bool = $fluent->hasMax(); // returns true

$fluent->remove($key);
$fluent->clear();
$fluent->clear([$key1, $ke2]);

$fluent->applyTo($instance);

```

When a Fluent instance is filled, either via the constructor or the fill method, the passed array can be associative with string keys and arbitrary values.  
numerical keys are ignore, there values are considered the kkey, with null as value.

Values that have arguments can be set as a single string, using a colon for separator (like validation rules).
```php
[
    'required',
    // is the same as
    'required' => null,

    'min:5',
    // is the same as
    'min' => 5,
]
```

One of the main usage of the fluent class is to actually store method calls and their arguments, to be later applied to another instance, in the `applyTo()` method.  
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





## Future scope and PHP8

PHP8 will have Attributes, or Anotation.
These are classes that are basically metadata for function, methods, properties and other classes.

This is great because it means the preset can be defined as annotations instead of having to map them to model attribute in a method.  
But in that case you can only configure the preset with via constructor arguments. This is where the fact that presets and their definitions class extends our fluent will shine.

Because you can fully configure everything them just by passing an array.

The first example could be rewritten as follow

```php
<<Int('id', ['primary'])>>
<<Text('content', [
    'db_column' => ['type' => ['text', 500]],
    'validation' => ['required', 'max:500'],
])>>
<<Boolean('is_published', ['default' => false])>>
<<Json('meta', [
    'default:{}', 'cast:array',
    'db_column' => ['-default'], // the default value does not affect the DB Column
])>>
<<BelongsTo('user', ['relation' => ['belongsTo', User::class])>>
<<HasMany('comments', ['relation' => ['hasMany', CommentModel::class, 'post_foreign']])>>
<<Relation('comments', ['HasMany' => [CommentModel::class, 'post_foreign']])>>
<<DateTime('created_at', [
    'date',
    'cast' => 'd H:i:s.u',
    'db_column' => ['type' => ['timestamp', 2]],
])>>
<<Slug('slug')>>
class PostModel extends LaravelBaseModel
{
    use HasAttributePresets;
}
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


