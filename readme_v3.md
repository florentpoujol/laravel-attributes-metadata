

# Laravel Model Metadata

More than providing actual features, this package proposes a way and some facilities to define all metadata about model attributes in a single place.

The idea is to have a single place to edit when you want to add an attribute to a model, instead of having to edit the PHPDoc and cast on the model, the migrations, the validation rules in the form requests/controllers, the Nova resource, etc...

Better yet, if several models use the same kind of attribute that has always the same properties (say a VAT field that is always a decimal(5,2) in the DB, has always the same decimal:1 cast, the same Nova field, etc...), changing these properties in that one place would effect everywhere they are used automatically.


## Example

Here is an example of how metadata can be defined, for a traditionnal Post model :

```php
class PostModel
{
    use HasAttributesMetadata;

    /** 
     * @return array<string, AttributeMetadata> 
     */
    public static function getAttributeMetadata(): array
    {
        return [
            'id' => (new Int())->primary(),
            'content' => (new Text())->addValidationRule('max', 500),
            'is_published' => new Boolean(false),
            'user' => new BelongsTo(User::class),

            // the configuration of such "custom" field especially if used multiple times throughout the application 
            // 1is a good candidate to be put in its own class that would propably extend the base DateTime
            'created_at' => (new DateTime('timestamp', 'd H:i:s.u'))->setPrecision(2),

            // configuring instances can also be done via a static factory if it's more you style
            'comments' => new HasMany([CommentModel::class, 'post_foreign']),
            'meta' => (new Json())->setDefault('{}'),

            // instead of instanciating right away you can use the make
            'meta' => Json::make(['setDefault' => '{}']),

            // you can also wrap the instanciation in a closure or any callable
            'comments' => function () { return new HasMany(CommentModel::class, 'post_foreign'); },
            // or with PHP7.4+ arrow function
            'meta' => fn() => (new JsonObject())->setDefault('{}'),
        ];
    }
    
}
```


## Define attribute

As you can see from the example, defining attribute is done via instances of dedicated classes.

You are free to organize the classes however you want.  
Typically each classes represent either a **type** of attribute (that usually follow a type of field in the database), or a **usage** like a VAT attribute, or relations.  
Beside implementing the `AttributeMetadata` interface, each is free to provide whatever means makes sense in their context to configure them.  

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






## Usage

Once the `HasAttributeMetadata` trait is added on a model, you have access to a bunch of static methods to a public static `getMetadata()` method on your models which returns an instance of `ModelMetadata`

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

Or restrict the fields considered : `Post::getValidationRules(['slug', 'content'])`


### In Laravel Nova

```php
public function fields(Request $request)
{
    return Post::getNovaFields(); // Field[]
}
```

The returned fields uses proper type and validation rules, and may depends on the context (if the requet is for an index, a details or a creation or update form).



### Setting up models from their attribute metadata

Model behavior is configurable by a bunch of properties on them like the ones below:
- `$primaryKey`, `$incrementing` and `$keyType`
- `$guarded`, `$fillable` and `$hidden`
- `$attributes` (default values)
- `$dates`
- `$casts`

If attribute metadata contain the right information, it is possible to altogether forget about any of them
and just throw the `SetupModelFromAttributeMetadata` trait on the model.  
The trait do not populate the properties, it overrides the corresponding method of the base model that usually returns the value of the property.
Any values existing on the properties takes precedence over the values defined in the metadata. 
 and you can turn off for each properties individually by setting the corresponding static property to false. Eg : `protected static $setupGuardedFromAttributesMetadata = false;`


### Dynamic casts and relations

If you want both casts and relations to be handled via the metadata, you can add the `HandlesCastsAndRelationsFromAttributeMetadata` trait on your models.

Note that it override the `__get`, `__set` and `__call` magic methods, so be careful with conflicts with other traits that may do the same.

Once this is done, you do not need to define casts via the traditionnal `$casts` property (but still can).  
Moreover, when dealing with the built-in casts, you may slightly increase performance by specifying the **target** of the cast which is the method that ends up being called, instead of having to resolve it every times.  
Note that not all metadata classes allows you to specify this.

Similarly, attributes can be flagged for having a setter and/or getter which will be called immediately.

For relations, if they are defined in the metadata, you do not need anymore to define them as actualy method on the model itself, but again you still cann, and those hardcoded in the model class will take precedence.  
Of course you can still access them both as a method -that return a Relationship instance- and as property -that returns the result of the DB query-.



