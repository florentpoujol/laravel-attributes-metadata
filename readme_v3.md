

# Laravel Model Metadata

More than providing actual features, this package proposes a way and some facilities to define in a single place all metadatas about model attributes.

The idea is to have a single place to edit when you want to add an attribute to a model, instead of having to edit the PHPDoc and cast on the model, the migrations, the validation rules in the form requests/controllers, the Nova resource, etc...

Better yet, if several models use the same kind of attribute that has always the same properties (say a VAT field that is always a decimal(5,2) in the DB, has always the same cdecimal:1 cast, the same Nova field, etc...), changing these properties in that one place would effect on all those affected automatically.


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
            'id' => function() { return (new Int())->primary(); },
            'content' => (new Text())->addRule('max', 500),
            'is_published' => new Boolean(false),

            // the configuration of such "custom" field especially if used multiple times throughout the application is a good candidate to be put in its own class that would propably extend the base DateTime
            'created_at' => (new DateTime)->isTimestamp()->withPrecision(2)->castsTo('datetime', 'd H:i:s.u'),
            'user' => new BelongsTo(User::class),

            // configuring instances can also be done via a static factory if it's more you style
            'comments' => HasMany::make(['params' => [CommentModel::class, 'post_foreign']]),
            'meta' => JsonObject::make(['default' => '{}']),
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
- use the static make() method as factory, it will map the associative array to the setters of the class
- callable factory the return the instance












## Usage

Once the `HasAttributeMetadata` trait is added on a model, you have access to a bunch of static methods to a public static `getMetadata()` method on your models which returns an instance of `ModelMetadata`

### In migrations

```php
public function up()
{
    Schema::create('posts', function (Blueprint $table) {
        Post::addColumnsToTable($table);

        // or with a limited set of attributes
        Post::addColumnsToTable($table, ['column1', 'column2']);
    });

    // or as a callable
    Schema::create('posts', [Post::class, 'addColumnsToTable']);


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

Currently the package does not do anything more than that regarding migrations, you have to handle more complex reverts and changes yourself, manually.


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



### In Laravel Nova

```php
public function fields(Request $request)
{
    return Post::getNovaFields($request); // Field[]
}
```

The returned fields uses proper type and validation rules, and may depends on the context (if the requet is for an index, a details or a creation or update form).



### Setting up models from their attribute metadata

It is possible to populate most properties used to setup model behavior based on its attributes metadata.

Properties include
- primaryKey, increments and keyType
- guarded, fillable and hidden
- attributes (default values)
- dates
- casts

You can enable this by adding the `SetupModelFromAttributeMetadata` trait, and you can turn off for each properties individually by setting the corresponding static property to false. Eg : `protected static $setupGuardedFromAttributesMetadata = false;`

Note that this require to eagerly resolve all attributes metadatas on model boot.

Note that casts may also be handled dynamically, which has one advantage, read on below.


### Dynamic casts and relations

Casts and relation If you want both to be handled via the metadata, you can add the `HandlesCastsAndRelationsFromAttributeMetadata` trait on your models.

Note that it override the `__get`, `__set` and `__call` magic methods, so be careful with conflicts with other traits that may do the same.

Once this is done, you do not need to define casts via the traditionnal `$cast` property (but still can).  
Moreover, when dealing with the built-in casts, you may slightly increase performance by specifying the **target** of the cast which is the method that ends up being called, instead of having to resolve it every times.  
Note that not all metadata classes allows you to specify this.

Similarly, attributes can be flagged for having a setter and/or getter which will be called immediately.

For relations, if they are defined in the metadata, you do not need anymore to define them as actualy method on the model itself.  
Of course you can still both access them as method, that return a Relationship instance and as property which returns the result of the DB query.



