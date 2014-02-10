## About

This library provides base classes for implementing model behaviors.
Model behaviors provide a simple way to extend models. This pattern allow 
common logic to be encapsulated inside behaviors for keeping models light
and composed only by its own business logic.

The goal of this project is to work out and finalize a model behavior
implementation that in the future can be integrated into Lithium. We
are depending on PHP >= 5.4 here as is assumed that this effort will
at least take a bit longer. So that in the meantime Lithium will already 
depend on 5.4.

## Requirements

Lithium and PHP 5.4.

## Usage

### Managing and Loading Behaviors

First to add the ability of using behaviors in a model, use
the behaviors trait in your model. After that define all behaviors you
plan to use in the `$_actsAs` property of the model class.

```php
// ...
class Posts extends \lithium\data\Model {

   use li3_behaviors\data\model\Behaviors;

   protected static $_actsAs = [
       'Sluggable' => ['field' => 'slug', 'label' => 'title']
   ];
// ...
```

The behaviors trait also makes some static methods available in the model,
which allows to manage behaviors as follows.

```php
// Bind the sluggable behavior with configuration.
Posts::bindBehavior('Sluggable', ['field' => 'slug', 'label' => 'title']);

// Accessing configuration.
Posts::behavior('Sluggable')->config();
Posts::behavior('Sluggable')->config('field');

// Unbinding it again.
Posts::unbindBehavior('Sluggable');
```

### Creating a Behavior

Now that we are able to load and manage behaviors we can create our own
behavior which must extend the `Behavior` class. In the following example
we create a `Sluggable` behavior in `extensions/data/behavior/Sluggable.php`.

```php
<?php

namespace app\extensions\data\behavior;

use lithium\util\Inflector;

class Sluggable extends \li3_behaviors\data\model\Behavior {

	protected $_defaults = array(
		'field' => 'slug',
		'label' => 'title'
	);

	protected function _filters($model, $behavior) {
		$model::applyFilter('save', function($self, $params, $chain) use ($behavior) {
			$params['data'][$behavior->config('field')] = static::_generateSlug(
				$params['data'][$behavior->config('label')]
			);
			return $chain->next($self, $params, $chain);
		});
	}

	protected static function _generateSlug($value) {
		return strtolower(Inflector::slug($value));
	}
}

?>
```

### Dynamically Adding Methods

Sometimes you need to dynamically add methods to a model instance. I.e. when a field 
name of a behavior is user configurable and needs to be added as a method on the entity.

This can be achived by leveraging existing model functionality. Following an example
that adds a method using a configured name.

```php
// $model  = '\app\models\Posts'
// $config = ['field' => 'tags']

$model::instanceMethods([
	$config['field'] => function($entity) {
		return $entity->taxonomy;
	}
]);

$post = Posts::create(['taxonomy' => 'foo,bar,baz']);
$post->tags();
```

## Credits for previous Implementations

* Nate Abele, https://github.com/nateabele/li3_behaviors
* Simon Jaillet, https://github.com/jails/li3_behaviors
