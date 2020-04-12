

# Laravel Model Metadata

This package proposes a way and some facilities to define all metadata about model attributes in a single place.

The idea is to have a single place to edit when you want to add an attribute to a model, instead of having to edit the 
PHPDoc and cast on the model, the migrations, the validation rules in the form requests/controllers, the Nova resource, etc...

Better yet, if several models use the same kind of attribute that has always the same properties (say a VAT field that 
is always a `decimal(4,1)` in the DB, has always the same `decimal:1` cast, the same Nova field, etc...), changing these
properties in that one place would effect everywhere they are used automatically.

Attribute metadata are defined in dedicated classes that you map to your model attributes 

## Quick Example

Here is an example of how metadata can be defined, for a traditional `Post` model :

```php
class PostModel extends LaravelBaseModel
{
    use HasAttributesMetadata;

    /** 
     * @return array<string, string|AttributeMetadata> 
     */
    public static function getAttributesMetadata(): array
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


## Define attribute

As you can see from the example, defining attribute is done via instances of dedicated classes.

You are free to organize the classes however you want.  
Typically each classes represent either a **type** of attribute (that usually follow a type of field in the database), or a **usage** like a VAT attribute, or relations.  
Beside implementing the `AttributeMetadata` interface, each class is free to provide whatever means makes sense in their context to configure them.  

This package provide some defaults, that you can directly use, or build upon.

You typically need to extend theses to match the exact way you use them.  
If that makes sense for your project, feel free to have classes that represents a single attribute.  

After all the goal of this package is to provide a single place to edit all metadatas about an attribute.  
If you have to update all definitions of these metadatas, like it may be the case if the `created_at` attribute of the example above do change, it is no good.



### Attributing attributes to a model

Each models having attribute metadata shall poccess the `HasAttributeMetadata` trait, and implement a static `getAttributeMetadata()` method that returns an associative array of attribute names as keys and their metadata instance as key.

Other ways to define the meta classes:
- if the instance takes no parameter and should just be intanciated, you can set just the class name, the instance will only be created if really needed
- wrap the instance creation in a callable, for instance a closure






## Usage of `HasAttributeMetadata` trait

Once the `HasAttributeMetadata` trait is added on a model, you have access to a bunch of static methods and to a public static `getMetadata()` method on your models which returns an instance of `ModelMetadata`


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


### In Laravel Nova

```php
public function fields(Request $request)
{
    return Post::getNovaFields(); // Field[]
}
```

The returned fields uses proper type and validation rules, and may depends on the context (if the request is for an index, a details or a creation or update form).

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

For other cases it is better to generate new migration file(s) from a artisan command every times your attributes changes.  

To do so, you may use the `fpoujol:attr-meta:migration` command.

Example that will generate an update migration for attributes `slug` and `content` of the `Post` model.
```bash
php artisan fpoujol:attr-meta:make:migration "\App\Post" slug content
```

It will be an update migration if we can find a file name that contain `CreatePostsTable` in the migration directory.

Make sure to inspect the file afterward, to fix the `down()` method that is never handled automaticaly for updates and make sure the content of the `up()` is also actually ok.


## Setting up models from their attribute metadata

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


## Dynamic relations

If you want relations to be handled via the metadata, you can add the `HandlesRelationsFromAttributeMetadata` trait on your models.

Note that it override the `__call` magic methods, so be careful with conflicts with other traits that may do the same.

If relations are defined in the metadata, you do not need anymore to define them as actual method on the model itself.
But you still can, and those hardcoded in the model class will take precedence.    
Of course you can still access them both as a method -that return a Relationship instance- and as property -that returns the result of the DB query-.






## Advanced

### Configuring metadata with the static make() method 

From the example above, these two lines are similar in setting-up the default value of a Json field.

```php
(new Json())->setDefault('{}');

Json::make(['setDefault' => '{}']);
```
There is two important differences though
- the first one already create an instance of the Json class, so the default value only apply for that model's attribute, and several attributes may have a different default value (or any other property)
- the `make()` method does not return an instance, but the Fqcn of the class and saves the definitions to be applied only when and instance of the class will need to be created

The definitions should be an array which can be a mix of associative and regular array. 
String keys or values with int key match method names. Values for string keys are the method argument, as a single value, or an array of values, if there is several arguments.
When the single argument is an array, you have to wrap the argument in an array. 


### Getting the model metadata instance

Beside the methods shown in the Usage section above, the `HasAttributeMetadata` trait provide the static `getModelMetadata()` method, 
which returns and instance of `\FlorentPoujol\LaravelModelMetadata\ModelMetadata`.

This class holds the instance of the model's metadata class and is essentially a proxy for them.  
For instance the trait's `getValidationRules()` actually calls this same method on the instance of the models metadata, 
which call the same method on all attribute metadata instances. 

Among others, that instance provides the following public methods:
- `hasAttribute(string $name): bool`
- `getAttributeNames(): string[]`
- `getAttributeMetadata(string $name): AttributeMetadata`
- `getAttrCollection(string[] $names = null): Collection<AttributeMetadata>`

If you want to use your own class instead of the base one, you may set its FQCN as the value of the static property `$modelMetadataFqcn` on your model.

### Column definitions

Columns definitions are expressed as you would in a migration file.

The type of the field is defined by a method and its arguments if any.
st
Then it can be further customized by calling method of the SchemaDefinition object






## Relations


The provider Relation class assume you use an integer field, if you use a string field, you have to create your own "StringRelation" class.


## Defin

using closure makes the model not serializable 