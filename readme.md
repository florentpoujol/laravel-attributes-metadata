# Laravel Attributes Metadata

This package proposes a way and some facilities to define all metadata about model attributes in a single place.

The idea is to have a single place to edit when you want to add an attribute to a model, instead of having to edit all these:
- the migrations
- the PHPDoc and cast on the model
- the validation rules in the form requests/controllers,
- the Nova resource
- etc...

Better yet, if several models use the same kind of attribute that has always the same properties (*eg*: a VAT field that is always a `decimal(4,1)` in the DB, has always the same `decimal:1` cast, the same Nova field, etc...), changing these
properties in that one place would effect everywhere they are used automatically.

Attribute metadata are typically defined in dedicated classes that you map to your model attributes.


## Quick Example

Here is an example of how metadata can be defined, for a traditional `Post` model :

```php
class PostModel extends LaravelBaseModel
{
    use HasAttributesMetadata;

    /** 
     * @return array<string, string|AttributeMetadata> 
     */
    public static function getRawAttributeMetadata(): array
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
            // 2) wrap the instantiation in a closure or any callable
            'comments' => function () { return new HasMany([CommentModel::class, 'post_foreign']); },
            // or with PHP7.4+ arrow function
            'comments' => fn() => new HasMany([CommentModel::class, 'post_foreign']),
        ];
    }
}
```


## Define attribute metadata

As you can see from the example, defining attribute is done via instances of dedicated classes.

You are free to organize the classes however you want.  
Typically each classes represent either a **type** of attribute (that usually follow a type of field in the database), or a **usage** like a VAT attribute, or relations.  
Each class is free to provide whatever means makes sense in their context to configure them.  

This package provide some defaults, that you can directly use, or build upon.
You typically need to extend theses to match the exact way you use them.  

### Mapping attributes to a model

Each models having attribute metadata shall poccess the `HasAttributeMetadata` trait, and implement a static `getRawAttributeMetadata()` method that returns an associative array of attribute names as keys and their metadata instance as values.

Other ways to define the meta classes:
- if the instance takes no parameter and should just be intanciated, you can set just the class name, the instance will only be created if really needed
- wrap the instance creation in a callable, for instance a closure


## Usage

Attribute metadata classes are juste collections of metadata, they do not do anything, they only describe the fields.

To be usefull, they are handled by separate classes (called *handlers*), that reads the metadata and build things like validation rules, Nova fields, etc...

This package provide some default handlers for
- migrations
- validation
- relations
- casts
- setting up models properties (like `$guarded`, default values, etc...)

### Validation handler

Once you added the `ProvidesValidationFromAttributeMetadata` trait on your model, you can call the static `getValidationRules()` and `getValidationMessages()` methods.

Example usage :
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

Validation messages can also be extracted from or defined in the metadata.

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

### Laravel Nova handler

Once you added the `ProvidesNovaFieldsFromAttributeMetadata` trait on your model, you can call the static `getNovaFields()` method.

```php
public function fields(Request $request)
{
    return Post::getNovaFields(); // Field[]
}
```

The returned fields uses proper type and validation rules, and may depends on the context (if the request is for an index, a details or a creation or update form).

### Migrations handler

For very simple cases, or projects that do not evolve or for which it is ok to run `php artisan migrate:fresh` for every attribute change, you can generate migrations via the migration handler.

Add the `ProvidesMigrationsFromAttributeMetadata` trait on your model to be able to call `addColumnsToTable()` static method.

```php
public function up()
{
    Schema::create('posts', function (Blueprint $table) {
        Post::addColumnsToTable($table);

        // or with a limited set of attributes
        Post::addColumnsToTable($table, ['column1', 'column2']);
    });
}

public function down()
{
    Post::dropColumnsIfExists();

    // or with a limited set of attributes
    Post::dropColumnsIfExists(['column1']);
}
```

## Setting-up model handler

Model behavior is configurable by a bunch of properties on them like the ones below:
- `$primaryKey`, `$incrementing` and `$keyType`
- `$guarded`, `$fillable` and `$hidden`
- `$attributes` (default values)
- `$dates`
- `$casts`

If attribute metadata contain the right information, it is possible to altogether forget about any of them and just add the `SetupModelFromAttributeMetadata` trait on the model.  
The trait do not populate the properties, it overrides the corresponding methods of the base model that usually returns the value of the property.
So as always with traits be careful if you have overridden one of these methods, yourself or any package, there may be conflict.
   
**Any values existing on the properties takes precedence over the values defined in the metadata.** 

Note that when using this trait all metadata instance are resolved on model boot.


## Relations handler

If you want relations to be handled via the metadata, you can add the `ProvidesRelationsFromAttributeMetadata` trait on your models.

Note that it override the `__call` magic methods, so be careful with conflicts with other traits that may do the same.

If relations are defined in the metadata, you do not need anymore to define them as actual method on the model itself.
But you still can, and those hardcoded in the model class will take precedence.    
Of course you can still access them both as a method -that return a Relationship instance- and as property -that returns the result of the DB query-.


