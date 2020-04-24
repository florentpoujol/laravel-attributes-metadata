

idea:

features and "scopes" (Nova, validation, relations) of the attributes metadata classes are brought by "providers"

the idea is to be able to extend the metadata classe without overriding their code
and that a provider + helper thing can be brought into a project via composer


attributes classes are aggregator of providers



how a package can brig a provider ?
ho can the use "extend"" the metadata classes


Etendre le model metadata
- utiliser de simple traits à rajouter sur une classe de l'utilisateur qui étend la classe de base
- fournir sa propre classe qui étend la classe de base (demander à utiliser cette classe, ou à ce que l'utilisateur étende la sienne à partir du packet)
simple traits to extends






le model metadata et les attribut metadata sont des manager qui fonctionnent avec des drivers, contiennent plusieurs autres classes fournissant les fonctionnalités

le seul driver du model metadata est l'aggregateur les metadata d'attribut


```php
class ProviderManager 
{
	public function addProvider()
	public function getProvider()
	public function getProviders()
}


class AttributesMetadataProxyCollection implements ModelMetadataProvider
{
	public function getMethodNames(){}

	public function hasAttribute()
	public function getAttribute($name): AttributeMetadata
}


class AttributeMetadata extends BaseManager 
{

}

interface Provider
{
	/**
	 * @return array<string> Method names
	 */
	public function provides();
}

class ValidationProvider implements AttributeMetadataProvider
{

	public function getMethodNames(){}

	public function getvalidationrules(){}
	public function getvalidationrules(){}

}
```


```php

$model
	->getMetadata()
	->getProvider(AttributeMetadata::class)
	->getAttribute('test')
	->getProvider(ValidationProvider::class)
	->getValidationRules();

trait HasMetadata
{
	public function getMetadata(): ModelMetadata;
}

trait HasAttributeMetadata
{
	public function getAttributeMetadataProvider()
}

trait ProvidesValidationThroughAttributeMetadata
{
	public function getValidationRules()
}

$model->getValidationRules();
```



séparer la classe de base des metadata de l'usage qui est fait des métadata

d'autres pakcage peuvent requerir définir des metadata différent de ceux built-in


ModelMetadata
	1 AttributeMetadataAggregator i ModelMetadataProvider
		* Integer i AttributeMetadata
			* ValidationProvider i ModelMetadataProvider