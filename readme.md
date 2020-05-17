

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
Since they are simple classes, they are easily extendable, composable, and overridable at runtime.

Along code generation tools like the `make` Artisan command or [Blueprint](https://github.com/laravel-shift/blueprint), reusing the same preset across projects is a great way to drastically reduce the boilerplate needed to start a new one !


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
            // when the preset do not need any runtime configuration, you can set its class name as the value
            // the preset will only be instantiated if the attribute is used
            // this will typically happen for preset that match a usage of attribute, like here
            'id' => PrimaryId::class,
            'slug' => Slug::class

            // when preset needs runtime configuration to match their exact usage for that particular attribute/model,
            // an array can be passed to the constructor
            'content' => new Text(['required', 'validation' => 'max:1000']),
            'created_at' => new DateTime([
                'cast' => 'datetime:H:i:s.u',
                'dbColumn' => ['timestamp', 'precision' => 2],
            ]),

            // runtime configuration can also be done in a fluent way
            'user_id' => (new BelongsTo())
                ->name('publisher')
                ->related(User::class)
                ->foreign('foreign_key'),
            'comments' => (new HasMany())->related(Comment::class),

            // but beside the default implementation and the example you are free to built
            // your preset however you want, here the constructor arguments are overriden
            'is_published' => new Boolean(false),
            'meta' => new Json('{}', 'array'),
        ];
    }
}
```


## Mapping presets to attributes of a model

Once you have preset defined, it is as easy as adding the `HasAttributePresets` trait on the model, and implementing a static `getRawAttributePresets()` method that returns an associative array of attribute names as keys and their presets instances or Fqcn as values.


## Usage

Once the `HasAttributePresets` trait is added on a model, you have access to several public static methods on the model, including 
- `getAttributePresetCollection(): PresetCollection` that returns a custom collection to easilly work with all the preset of a model
- `getAttributePreset(string $name): ?Preset` to get individual preset instance


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

### Setting up models from their attribute presets

Model behaviour is configurable by a set of properties like the ones below:
- `$primaryKey`, `$incrementing` and `$keyType`
- `$guarded`, `$fillable` and `$hidden`
- `$attributes` (default values)
- `$dates`
- `$casts`

If attribute presets contain the right information, it is possible to altogether forget about any of them and just add the `SetupModelFromAttributePresets` trait on the model.  

The trait do not populate the properties, it overrides the corresponding methods of the base model that usually returns the value of the property.  

**Any values existing on the properties takes precedence over the values defined in the presets.** 

Note that when using this trait all presets instance are resolved on model boot (the first time an instance of that model is created).


### Dynamic relations

If you want relations to be handled via the presets, add the `HandlesRelationsFromAttributePresets` trait on your models.

Note that it overrides the `__call` magic methods, so be careful with conflicts with other traits that may do the same.

If relations are defined in the presets, you do not need anymore to define them as actual method on the model itself.
But you still can, and those hardcoded in the model class will naturally take precedence.    

Also note that relations that have a field in the database, like the `BelongsTo` relation of the example and thus actually represents 2 attributes only need to be defined in a single preset.

Of course you can still access the relation
- as a method -that return a Relationship instance- 
- as property -that returns the result of the DB query-
- and to the attribute from the database


## Example presets

The `Examples` folder contain already-built and flexible preset for a variety of common use cases.

You *may* built your own preset by extending these one, but we do not recommand it.
They are here solely as example, for use in the example project and for tests, we make not promise to not introduce a breaking change, even during a pakcage minor version bump.

If you want to base you preset on these, **copy/paste them**.  
Or if you extends them (fine for a short or non evolutive projet), at least target a specific minor version of this package in your Composer file.


## Building presets

Presets are simple classes that implements the `Preset` interface which only accounts for how the data is extracted from a preset (mostly by the `PresetCollection`).

This package provide one implementation that allows to configure a preset either via array or fluently.

Beside the interface or even the base preset class, you are free to organize your presets classes however you want.  
Typically each classes represent either a **type** of attribute (that usually follow a type of field in the database), or a **usage** like a VAT attribute, or relations.  

The simplest way to build a preset is to fill the `$baseDefinitions` static property in the class body, or override the static `getBaseDefinitions(): array` static method.

Examples:
```php
Datetime extends BasePreset
{
    protected static $baseDefinitions = [
        'dbColumn' => ['timestamp', 'useCurrent'],
        'validation' => ['required', 'date'],
        'novaField' => ['boolean', 'sortable', 'format' => 'Y-M-d'],
        
        'cast' => 'datetime:Y-m-d H:i',
        'fillable',
        'date',
    ];
}

BelongsTo extends BasePreset
{
    protected static $baseDefinitions = [
        'dbColumn' => ['unsignedInteger', 'nullable', 'index'],
        'validation' => ['nullable', 'integer'],
        'novaField' => ['belongsTo', 'searchable', 'nullable'],
        'relation' => ['belongsTo' => [User::class], 'name' => 'user'],
        'guarded',
    ];
}
```

The `dbColumn`, `validation`, `novaField` and `relation` key/values contains the definitions specific to their scope.

Other top-level keys/values are the attribute's model metadata like cast, fillable, guarded, hidden, date or its default value.


### Passing definitions at runtime

Another way to define the attribute is to pass the same kind of array, but to the constructor.

Of course the main point of passing definitions through the constructor is to override or extends base definitions.

```php
BelongsTo extends BasePreset
{
    protected static $baseDefinitions = [
        'dbColumn' => ['integer', 'unsigned', 'nullable', 'index'],
        'validation' => ['nullable', 'integer'],
        'novaField' => ['belongsTo', 'searchable', 'nullable'],
        'relation' => ['belongsTo' => [User::class], 'name' => 'user'],
        'fillable',
    ];
}

new BelongsTo([
    'dbColumn' => ['-nullable'],
    'relation' => ['name' => 'unguessableRelationName', 'related' => User::class],
    'fillable' => false,
]);
```

In this example the attribute is marked as not fillable, and the definitions of the relation are extended to include values specific to that particular attribute, in that case the name of the relation since it is different than the DB field name that follows.

Also the base column definitions are nullable but we do not want this DB field to be nullable, so we have to remove that definition, that is why there is a hyphen in front of the key.
We could have used that same notation to remove the `fillable` definitions, and vice-versa.


### DB columns definitions

For attributes that have a corresponding database column, it type and other column definitions can be set via the `dbColumn` key.

The type can be set as the value of the `type` key, or any numerical key.  
The supported types are any method names from the `\Illuminate\Database\Schema\Blueprint` object that directly adds a column to the table (`string`, `integer`, `datetime`, ...).  
Shorcuts that like `timestamps` are not supported.

All theses types have been regrouped in the PHPDocs of the `\FlorentPoujol\LaravelAttributePresets\Definitions\BlueprintFieldTypePHPDocs` trait.

All the modifiers from the `\Illuminate\Database\Schema\ColumnDefinition` fluent class are also supported (`nullable`, `index`, ...)

When the type has arguments (like string length or timestamp precision)
```php
'dbColumn' => ['timestamp' => 2],
'dbColumn' => ['type' => ['timestamp', 2]],
// that second notation is the only one possible when there is more than one argument
'dbColumn' => ['type' => ['integer', true, true]],
```

### Validation definitions

The `validation` key/values contains the validation rules.    
The validation message can be set with a `message` key.

All built-in validation rules as well ass object rules are supported.  
Rules that have values (like `min`) can be set as a a key/value pair, or as a single string with the rule and value separated by a semicolon.

```php
'validation' => ['nullable', 'min:5', new MyRule()],
// is the same as
'validation' => ['nullable', 'min' => 5, new MyRule()],
```

When you need to remove a rule from an existing preset, you just need to reference the rule name (not its value), or its class name in case of object rules.

```php
'validation' => ['-nullable', '-min', '-\App\Rules\MyRule'],
```

### Nova field definitions


The `novaField` key value contains the definitions for the Nova field.  
As for the column definition the first value is the type of field, which can also be set as the value of the `type` key.
The field nice name can be set via the `name` key, or if not set will be guessed for the attribute name.


### Relation definitions


### Metadata 

Other top-level keys/values are the attribute's model metadata like cast, fillable, guarded, hidden, date or its default value.






## Future scope and PHP8

PHP 8 will introduce [attributes](https://stitcher.io/blog/attributes-in-php-8), often called annotations in another languages.  
These are classes that are basically metadata for functions, methods, properties, $arguments and other classes.

This is great because it means the preset can be defined as annotations instead of having to map them to model attribute in a method.  
But in that case you can only configure the preset via constructor arguments. This is where the fact that presets can be configured fully via simple arrays shine.

The first example could be rewritten as follow

```php
<<Int('id', ['primary'])>>
<<Text('content', [
    'dbColumn' => ['type' => ['text', 500]],
    'validation' => ['required', 'max:500'],
])>>
<<Boolean('is_published', ['default' => false])>>
<<Json('meta', [
    'dbColumn' => ['-default'], // the default value does not affect the DB Column
    'default:{}', 'cast:array',
])>>
<<BelongsTo('user_id', ['relation' => ['belongsTo', User::class])>>
<<HasMany('comments', ['relation' => ['hasMany', CommentModel::class, 'post_foreign']])>>
<<Relation('comments', ['HasMany' => [CommentModel::class, 'post_foreign']])>>
<<DateTime('created_at', [
    'dbColumn' => ['type' => ['timestamp', 2]],
    'cast' => 'd H:i:s.u',
    'date',
])>>
<<Slug('slug')>>
class PostModel extends LaravelBaseModel
{
    use HasAttributePresets;
}
```
