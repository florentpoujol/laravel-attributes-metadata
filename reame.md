

# Laravel attribute presets

The idea of this package is to provide a single place (a class) where to define all information about your model attributes :
- DB column definitions
- validations rules
- cast
- corresponding Nova fields
- kind of relation
- but also other metadata like its default value, if it is fillable, guarded, hidden, a date


Setting up a new attribute on a model is as simple as declaring to the model its name and preset (and maybe adjusting its instance to the specific needs of that attribute/model).  
Editing one of the preset reflects immediately everywhere the info you changed is used.  

Presets are just simple classes that are usually modelled after a database field type, or usage of attribute that has the same characteristics every times you use one (like a relation, a VAT, a slug, ...).
Since hey are simple classes, they are easilly extendable, composable, and overidable at runtime.


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
            'id' => (new Int())->primary(),
            'content' => (new Text())->setMaxLength(500)->markRequired(),
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


## Mapping presets to attributes of a model

As you can see from the example, defining attribute is done via instances of dedicated classes.

You are free to organize the classes however you want.  
Typically each classes represent either a **type** of attribute (that usually follow a type of field in the database), or a **usage** like a VAT attribute, or relations.  
Beside extending the `AttributePresets` base class, each class is free to provide whatever means makes sense in their context to configure them.  

This package provide some sensible examples, that you can directly use, or build upon.  
You typically need to extend theses to match the exact way you use them.  

Each models that use presets for at least some of their attributes shall poccess the `HasAttributePresets` trait, and implement a static `getAttributePresets()` method that returns an associative array of attribute names as keys and their presets instances (or factory callable) as values.


## Usage of `HasAttributePresets` trait

Once the `HasAttributePresets` trait is added on a model, you have access to a bunch of static methods and to a public static `getAttributePresetCollection()` method on your models which returns an instance of `PresetCollection`


### In validation

```php
/**
 * Route: POST /posts
 */
public function store(Request $request)
{
    $request->validate(Post::getValidationRules());

    Post::getValidator($request->all())->validates();

    // ...
}
```

Validation messages can also be defined in the presets.

In form requests:

```php
public function rules()
{
    return Post::getValidationRules(); // array<string, array<string|object>>
}

public function messages()
{
    return Post::getValidationMessages(); // array<string, string>
}
```

Or restrict the considered fields : `Post::getValidationRules(['slug', 'content'])`


### In Laravel Nova

```php
public function fields(Request $request)
{
    return Post::getNovaFields(); // Field[]
}
```


### In migrations

For very simple cases, or projects that do not evolve or for which it is ok to run `php artisan migrate:fresh` for every attribute change, you can use these methods.

```php
public function up()
{
    Schema::create('posts', function (Blueprint $table) {
        Post::addColumnsToTable($table);

        // or with a limited set of attributes
        Post::addColumnsToTable($table, ['column1', 'column2']);
    });


    // or for an update
    Schema::table('posts', function (Blueprint $table) {
        Post::updateColumnsFromTable($table, ['column1', 'column2']);
    });
}

public function down()
{
    Post::dropColumnsIfExists();

    // or with a limited set of attributes
    Post::dropColumnsIfExists(['column1']);
}
```

## Setting up models from their attribute presets

Model behavior is configurable by a bunch of properties on them like the ones below:
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

If you want relations to be handled via the presets, you can add the `HandlesRelationsFromAttributePresets` trait on your models.

Note that it overrides the `__call` magic methods, so be careful with conflicts with other traits that may do the same.

If relations are defined in the presets, you do not need anymore to define them as actual method on the model itself.
But you still can, and those hardcoded in the model class will take precedence.    

Of course you can still access them both as a method -that return a Relationship instance- and as property -that returns the result of the DB query-.


## Advanced

### Getting the model presets instance

Beside the methods shown in the Usage section above, the `HasAttributePresets` trait provide the static `getAttributePresetCollection(): \FlorentPoujol\LaravelAttributePresets\AttributePresetCollection` method which is a custom collection, based on Laravel base one.

Among others, that instance provides the following public methods:
- `hasAttribute(string $name): bool`
- `getNames(): string[]`
- `get(string $name): AttributeMetadata`

If you want to use your own class instead of the base one, you may set its FQCN as the value of the static property `$modelMetadataFqcn` on your model.
