# Laravel Model Metadata

This package proposes to create classes that act as the single source of truth for or models and their attributes metadata.


It means that instead of littering information about our attributes all over the place (migration, PHPDoc, casts, form requests/controller), they are centralized, in a place that is discoverable, cacheable, parsable.

This pattern is the basis of Symfony applications where the entity properties themselves define their metadata via annotations, and that information is used almost with zero config to 
- generate migrations
- generate full CRUD admin panels
- build forms and validate them
- define APIs, again with explicit validation
- etc...

In addition to defining the attributes metadata themselves this package aims to provide facilities to use these data to setup these same things like migrations, validation, Nova, model relations, and more.



## Installation

composer yadi yada


## Simple usage

Add the HasAttributeMetadata trait on your models and define a `$attributeMetadata` property. That property, as array
 contain de definitions of each attributes.
 
```php
/**
 * @var array<string, array<int|string, mixed>> 
 */
protected static $attributesMetadata = [
    'id' => ['increments', 'primary'],
    'content' => ['text', 'required', 'max:500'],
    'is_published' => ['boolean', 'default' => false],
    'created_at' => ['timestamp' => 2, 'useCurrent', 'datetime' => 'd H:i:s.u'],
    'user' => [BelongTo => [UserModel]],
    'comments' => [HasMany => [CommentModel, 'post_foreign']],
    'metadata' => ['object', 'default' => '[]'],
];
```

This is already enough to infer many things
- the id attribute is an unsigned integer, autoincrement and primary key. It will thus be casted to int. Since it
 is the primary key, the ID nova field will be used.
- the content attribute is a mandatory text column in the database, where validation make it mandatory but not more
 than 500 characters. Since it is a text column, the Textarea Nova field will be used
- the is_published attribute is a nullable boolean with a default value of false. corresponding column attributes
 will be used for migration and nova fields.
- the created at column is a nullable datetime that is casted to the specified format. Here expressly setting the
 type of field was necessary because we couldn't have guessed if the user whanted a datetime or timestamp field and
  with how much precisios
- the user property is a classic belongsTo relationship to the User model
- the comments property is a HasMany relationship to the Comments models, but for some reason, the foreign key has
 been nammed 'post_foreign' 
 - the metadata attribute is a json column that casts to object

The order of the properties also define their order in Nova fields.

For simple properties, the expected key/values are
- follow any methods on the ColumnDefinition fields, and their expected arguments
- a validation rule. A class name is supposed to be a Rule
- a cast, and their corresponding values  

Casts and validation rules that have parameters, can either be specified as usual (in the same string with the
 : delimiter) or as an actual separated key/value pair.

Note that to be able to define casts or relations this way you also must add another trait: MetadataInterceptsCastsAndRelations.
Note that it override the `__get`, `__set` and `__call` magic methods, so be careful with conflict with other traits that may do the same.
Without it, you can define casts and relations the usual way.

Once all this is setup, you may make use of the getMetadata() method provided in any of the 4 way possible

getMetadata() > instance of ModelMetadata
getMetadata('attribute1') > instance of AttributeMetadata
getMetadata('attribute1', 'attribute2') > assoc collection of AttributeMetadata
getMetadata(['attribute1', 'attribute2']) > assoc collection of AttributeMetadata

The ModelMetadata instance holds some but is mostly a proxy for the underlying AttributeMetdata instances

when calling getMetadata(), the $attribute is resolved 

--------------

Attributes metadata are defined via dedicated classes, one class per attribute.

These can then be aggregated in ModelMetadata classes, accessible on the model.

These look like this:

```php

class PostIsPublishedMetadata extends AttributeMetadata
{
	public $name = 'is_published'; // guessable from class name

	protected $preset => 'boolean'; // string, boolean, enum:{values}, set:{values}
	
	public $metadata = [
		'boolean', 'default' => false,
	]; //
	// the same info are used both to infer column
}

class PostContentMetadata extends AttributeMetadata
{
	public $propertyName = 'content';

	public function getColumnDefinition(Blueprint $table): ColumnDefinition
	{
		return $table->text('content')->default(false);
	}
}

/**
 *
 *
 */
class PostUserMetadata extends AttributeMetadata
{
	public $metadata = [
		RelationFqcn => ['relation constructor args'],
	];
}
```

```php

/** 
 * @mixin IsPublishedMetadata
 * @mixin ContentMetadata
 * @mixin UserMetadata
 */
class PostMetadata extends ModelMetadata
{
	protected $attributes = [
		'content', 'is_proublished',
	]
}

```

- Use the metadata throughout the application
- Define the metadata

### Use the metadata

- [Setup on the model]()
- [In migrations]()
- [In validation]()
- [In Laravel Nova]()


#### Setup on the model

You can add the HasAttributeMetadata trait on your models.

It provide a main method getMetadata() that would return an instance of PostMetadata in our exemple, from which you can get one or many attributes metadata class via the getAttributeMetadata($attributes) method.

Or you can use the getAttributeMetadata(...$attributes) convenience method, which arguments can be one attribute, many attribute as array or a variable list of attributes.


##### In casts

When getting atttributes, they can be casted, which ends-up calling one specific method (like asDatetime())  or a setter.
Metadata can cache which methods should be called so that the whole cast resolution is not done everytime be we go straight from `__get()` to the desired method.

Also relationship may be defined solely on metadata, allowing the model to be clean.

For both these features to work you need to add another trait: MetadataInterceptsCastsAndRelations.
Note that it override the `__get`, `__set` and `__call` magic methods, so be careful with conflict with other traits that may do the same.

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


#### In migrations

```php
	public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            (new PostModel)->getMetadata()->getColumnsDefinitions($table);
        });
    }

    public function down()
    {
        (new PostModel)->getMetadata()->dropColumnsIfExists($attributes = ['*']);
    }
```

Currently the package does not do anything more than that regarding migrations, you have to handle reverts and changes yourself, but nothing prevents you to define more methods


### In validation

```php
	/**
	 * Route: POST /posts
	 */
	public function store(Request $request)
    {
    	$request->validate((new Post)->getMetadata()->getCreationValidationRules())
    }

```

Validation messages are also extracted from the metadata.

If your controller is strictly CRUD/resource-full. All you have to do is create an empty controller, with the trait. Define the related model class via the controller, and that's it.  
Of course you can override any default behavior by defining the controller actions.

```php
class PostController
{
	use HandlesResourcefulllControllerActionsFromMetadata;

	public function __construct()
	{
		$this->metadataModelFqcn = Post::class;
	}
}
```

In formrequest :

```php
public function rules()
{
    return (new Post)->getMetadata()->getValidationRules();
}

public function messages()
{
    return Post::getMetadata()->getValidationMessages();
}
```


### In Laravel Nova


You might guess the drill by now :

```php
public function fields(Request $request)
{
    return (new Post)->getMetadata()->getNovaFields($request);
}
```

The returned fields uses proper type and validation rules, and may depends on the context (if the requet is for an index, a details or a creation or update form).

If all you do in your resource is that, you can replace the method by the InfersNovaFieldsFromModelMetadata trait




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
 For instance if you define `'datetime' => 'Y-m-d H:i:s.u'`, the system can not known on it own if you whant a
  datetime or timestamp column and can not know the precision that you whant.
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



