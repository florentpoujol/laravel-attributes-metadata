

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
    public function getAttributeMetadata(): array
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

As you can see from the example, defining attribute is done via instances of dedicated classes (not unlike how the fields in Laravel Nova are defined).

You are free to organize the classes however you want.  
Typically each classe represent either a **type** of attribute (usually mapped to a type of field in the database), or a **usage** like a VAT attribute, or relations.  
Beside implementing the `AttributeMetadata` interface, each is free to provide whatever means makes sens in their context to configure them.  

This package provide some defaults, you can directly use, or build upon.

You typically need to extend theses to match the exact way you use them.  
If that makes sense for your project, feel free to have classes that represents a single attribute.  

After all the goal of this package is to provide a single place to edit all metadatas about an attribute.  
If you have to update all definitions of these metadatas, like it may be the case if the `created_at` attribute of the example above do change, it is no good.


### Attributing attributes to a model

Each models having attribute metadata shall pocess the HasAttributeMetadata trait, and implement a `getAttributeMetadata()` method that returns an associative array of attribute names as keys and their metadata instance as key.



Other ways to define the meta classe


if the instance takes no parameter and should just be intanciated, you can set just the class name, the instance will only be created if really needed
use the static make() method as factory, it will map the associative array to the setters of the class












## Usage

Once defined, you have access to a public static `getMetadata()` method on your models which returns an instance of `ModelMetadata`

### In migrations

```php
public function up()
{
    Schema::create('posts', function (Blueprint $table) {
        Post::addColumnsToTable($table);

        // or for an update
        Post::setChangedColumnsToTable($table, ['column1', 'column2']);
    });
}

public function down()
{
    Post::getMetadata()->dropColumnsIfExists();
}
```

Currently the package does not do anything more than that regarding migrations, you have to handle reverts and changes yourself, manually.


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


### With casts, relations and dynamic attributes

By default, casts and relations must still be defined as before. If you want both to be handled via the metadata, you can add the `HandlesCastsAndRelationsFromMetadata` trait on your models.

Note that it override the `__get`, `__set` and `__call` magic methods, so be careful with conflicts with other traits that may do the same.

Once this is done, you do not need to define casts via the traditionnal `$cast` property (but still can). Moreover you may slightly increase performance by specifying the method that ends up being called, instead of having to resolve that every times. Read more about that below.

For relations, if they are defined in the metadata, you can altogether delete the methods that define them on the model itself.

If an attribute has a getter and/or setter, you can mark it as so to immediately access it without having to resolve it everytimes.


## Defining properties

Let's reshow the example
```php
class PostModel
{
    use HasAttributesMetadata;

    /** @var array<string, array<int|string, mixed>> */
    protected static $rawAttributesMetadata = [
        'id' => [
            'guarded',
            'column_definitions' => ['increments', 'primary'],
            'validations' => ['rules' => ['unique:']],
            'cast' => 'int',
        ],
        'content' => (new TextAttrMetadata)
            ->addValidationRule('max', 500),
        'is_published' => new BooleanAttrMetadata,
        'status' => (new EnumAttrMetadata)
            ->setValues([])
            ->setDefaultValue(''),
        'created_at' =>   [
            'column_definitions' => ['timestamp' => 2, 'useCurrent'],
            , 'datetime' => 'd H:i:s.u'
        ],
        'user' =>         [BelongTo::class => [UserModel::class]],
        'comments' =>     [HasMany::class => [CommentModel::class, 'post_foreign']],
        'dynamic_attr' => ['getter']
        'meta' =>         ['object', 'default' => '{}'],
    ];
}
```

Add the `HasAttributeMetadata` trait on your models and define a static `$rawAttributesMetadata` property, or implement a static `getRawAttributesMetadata(): array` method if you need/want to set things up inside a method instead of the body of the model.

The value of the property (or returned by the method) must be an associative array.  
The attributes names are the keys, the values are arrays which contain the metadata.

Your models can have attributes and relations that are not listed in the metadata, they are completely mandatory.

The content of the metadata actually depends on what you will use them for. If for instance you do not care about migrations, relations and Nova, the above example could be rewritten like so
```php
protected static $rawAttributesMetadata = [
    'content' => ['required', 'max:500'],
    'is_published' => ['boolean'],
    'created_at' => ['datetime' => 'd H:i:s.u'],
    'metadata' => ['object'],
];
```

The metadata should actually match any of the following:
- any methods on the `\Illuminate\Database\Schema\Blueprint` or `\Illuminate\Database\Schema\ColumnDefinition` fields, and their expected arguments as value (an array when several arguments)
- a validation rule, and their value for those which have some
- a cast, and their value for those which have some
- a relation class name, and its method arguments

When a metadata has no value, it can either be a string value (with a numerical key), or set as a key with `null` as value.  
This is the same as in the example above: `'is_published' => ['boolean' => null],`.

The order of the properties also define their order in Nova fields.

To mark an attribute has non existant in the DB, set the special value `_dynamic` as its first metadata.

For relations, the key are the relation class name, the values the relation method arguments.
When defining relations that way you can access both the corresponding method that return the relation instance and
 the attribute that return the result of the relation query.
 
For relations, the key are the relation class name, the values the relation method arguments.



#### Purpose-specific cases



### List of all possible metadata values (keys)

- `_dynamic`: mark the field as not existing in the database
- Any that match a public `Blueprint` methods but you only shall set one of them since the call to the first one return an instance of `ColumnDefinition`
- Any that match a method that you could call on a `ColumnDefinition` instance
- `unsignedInteger`:
    - column definition: unsigned integer
    - validation: integer, min 0





## Organisational pro-tip

Instead of defining the attributes, and the corresponding PHPDocs directly on the model, you can create a dedicated
 trait, which will do all that so that you only have to "use" YourModelAttribute trait in you base model.








------------------------------------------------
Advanced


Upon resolving, the definitions as array are actually turned into an instance of the `AttributeMetadata` class.

When you need more complex thing than what the array allows, you can replace the array by the Fqcn of a class
 that extends `AttributeMetadata`.


## Define attribute metadata

One way to define metadata about a model attribute est to create its own class that extends `AttributeMetadata`.

This base class define several properties which most have a matching getter. In the example below, we will most
 demonstrate modifying properties, but if some values are too complex, you may leave the property null and
  override the method instead. 

For simple cases, you may define the caracteristics of the attribute in the metadata property.

The name fo the definitions here match the name of the method usable on Column definition objects.
When they shall have values the the name is a key, with its value

```php
class IsPublishedMetadata extends AttributeMetadata
{
    protected $name = 'is_published';

    protected $metadata = [
        'boolean', 'default' => true,
    ];
}
```

The name of the attribute, if not set is inferred from the class name.

All usage keys :
- all that match a method on the column definition object
- all that mast a built-in cast

To clear up ambiguitise and case where not everything can be resolved, you need add more information, some of which
 may looking redundant.
 For instance if you define `'datetime' => 'Y-m-d H:i:s.u'`, the system can not known on it own if you want a
  datetime or timestamp column and can not know the precision that you want.
  So in that case, you shall add a key/value the precise that : `'timestamp' => 2,`


#### When the retreived type of the attribute does not match the database type

This is the case for datetime, or when the column is Text(json) and the value casted as array or object 

```php
protected $metadata = [
    'timestamp' => 2, // this is the column type 
    'datetime' => 'Y-m-d H:i:s.u', // this is a cast
];
```

```php
protected $metadata = [
    'object', 'nullable', 
];
```

## Define model metadata



## Caching

All these metadata can be cached easily by any cache driver. Note that the classes are serialized, not be carefull not to store any non cachebale things (closure or complex classes) on the metadata classes themselves.

BaseModelMetadata::cacheWith($cacheDriver, [$key], [$models])
BaseModelMetadata::loadFromcache($cacheDriver, [$key])

php artisan florentpoujol:model-metadata:cache







#### PHPDoc

Since models do not have real attributes properties, the attributes of the models are defined via PHPDocs over the
 model class.
 
Thanks to the `@mixin` tag, you can also deport that to the ModelMetadata class, which itself may deport each
 attribute definition over the AttributeMetadata class
  
````php
/**
 * @property string $content The content of the post 
 */
class ContentMetadata extends AttributeMetadata
{
	//
}

/**
 * @property bool $is_published 
 */
class IsPublishedMetadata extends AttributeMetadata
{
	//
}

/**
 * @mixin ContentMetadata
 * @mixin PublishedMetadata
 */
class UserMetadata extends AttributeMetadata
{
	//
}

/**
 * @ mixin UserMetadata
 */
class User extends Model
{
    //
}
````


