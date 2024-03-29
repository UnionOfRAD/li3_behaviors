# li₃ Behaviors

This library provides base classes for implementing model behaviors.
Model behaviors provide a simple way to extend models. This pattern allow 
common logic to be encapsulated inside behaviors for keeping models light
and composed only by its own business logic.

## Installation

The preferred installation method is via composer. You can add
the library as a dependency via:

```
composer require unionofrad/li3_behaviors
```

li₃ plugins must be registered within your application bootstrap phase 
as they use a different (faster) autoloader. 

```php
Libraries::add('li3_behaviors')
```

The official manual has more information on 
[how to register the plugin](http://li3.me/docs/book/manual/1.x/installation/plugins) 
with the app or use alternative installation methods (i.e. via Git).

## Usage

### Managing and Loading Behaviors

First to add the ability of using behaviors in a model, use
the behaviors trait in your model. After that define all behaviors you
plan to use in the `$_actsAs` property of the model class.

```php
// ...
class Posts extends \lithium\data\Model {

   use li3_behaviors\data\model\Behaviors;

   protected $_actsAs = [
       'Sluggable' => ['field' => 'slug', 'label' => 'title']
   ];
	
   // ...
```

The `Behaviors` trait also makes some static methods available in the model,
which allows to manage behaviors as follows.

```php
// Bind the sluggable behavior with configuration.
Posts::bindBehavior('Sluggable', ['field' => 'slug', 'label' => 'title']);

// Accessing configuration.
Posts::behavior('Sluggable')->config();
Posts::behavior('Sluggable')->config('field');

// Updating configuration.
Posts::behavior('Sluggable')->config('field', 'alt');

// Unbinding it again.
Posts::unbindBehavior('Sluggable');
```

### Creating a Behavior

Now that we are able to load and manage behaviors we can create our own
behavior which must extend the `Behavior` base class. In the following example
we create a `Sluggable` behavior in `extensions/data/behavior/Sluggable.php`.

```php

namespace app\extensions\data\behavior;

use lithium\util\Inflector;

class Sluggable extends \li3_behaviors\data\model\Behavior {

	protected static $_defaults = [
		'field' => 'slug',
		'label' => 'title'
	];

	protected static function _filters($model, $behavior) {
		Filters::apply($model, 'save', function($params, $next) use ($behavior) {
			$params['data'][$behavior->config('field')] = static::_generate(
				$params['data'][$behavior->config('label')]
			);
			return $next($params);
		});
	}

	protected static function _generate($value) {
		return strtolower(Inflector::slug($value));
	}
}
```

### Behavior Configuration

The configuration of each behavior can be accessed from within the 
behavior via `config()`. By default configuration for the behavior will
be set automatically using the user provided configuration from the `$_actsAs`
property of the model and the defaults provided in the behavior as `$_defaults`.

The defaults are merged with the provided configuration using simple array
addition (`$config += $defaults`). If you want to change the way configuration
is merged read further.

#### Providing Custom Configuration Logic

Behaviors often come with different requirements towards configuration.
In some cases just a 1-dimensional array needs to be merged in other cases 
nested multi-dimensional arrays must be merged
or even normalized in a custom way.

That's why merging the defaults with the provided configuration can be
controlled easily by yourself - the implementer. By default we do a simple
one-dimensional merge adding defaults and configuration to eachother. To
control configuration merging overwrite the `_config()` method of the base 
class.

In the following example we will normalize certain configuration options
while merging with the behavior's defaults.

```php
// ...

class Serializable extends \li3_behaviors\data\model\Behavior {

	protected static $_defaults = [
		'fields' => []
	];

	protected static function _config($model, $behavior, $config, $defaults) {
		$config += $defaults;
		$config['fields'] = Set::normalize($config['fields']);

		foreach ($config['fields'] as $field => &$pass) {
			if (!$pass) {
				$pass = 'json';
			}
		}
		return $config;
	}
	
	// ...
```

The `_config()` method gets the configuration and the defaults as defined in `$_defaults` as
the 3rd and 4th parameters. The method must finally return the configuration that should 
be used for the behavior instance.

### Exposing Static Methods to the Model

Any public static method present in the behavior is automically exposed on the model. This 
allows for adding methods to the model easily. Each static method that is exposed gets
the name of the current model class as its first and the instance of the behavior as a second
parameter.

This is useful if you i.e. need to query the model for results or when you want to retrieve
configuration from the behavior.

The example below shows how we expose a token generation method to the model.

```php
// ...

class TokenGenerator extends \li3_behaviors\data\model\Behavior {

	protected static $_defaults = [
		'short' => false
	];

	// Generates a random token either short (8 chars) or long (16 chars) and
	// returns it. Default expiration is one year.
	public static function token($model, $behavior) {
		$token = substr(md5(Random::generate(32)), 0, $behavior->config('short') ? 8 : 16);
		$expires = date('Y-m-d H:i:s', strtotime('+1 year'));

		return compact('token', 'expires');
	}
	
	// ...
```

### Exposing Instance Methods to the Model

Analog to exposing static methods, instance methods can also be exposed to the model easily. Any concrete
method implemented in the behavior will be exposed. Each behavior method will in addition to the `$entity`
parameter also receive the name of the model and an instance of the behavior as a second parameter i.e. to access configuration.

```php
// ...

class Publishable extends \li3_behaviors\data\model\Behavior {

	protected static $_defaults = [
		'field' => 'is_published'
	];

	public function publish($model, $behavior, $entity) {
		$field = $behavior->config('field');
		$entity->{$field} = true;
	}

	// ...
```

#### Dynamically Adding Model Instance Methods

Sometimes you need to dynamically add methods to a model instance. I.e. when a field 
name of a behavior is user configurable and needs to be added as a method on the entity.

This can be achived by overwriting the `_methods()` method and returning an array
of methods keyed by their alias on the model instance.

```php
// ...

class Taggable extends \li3_behaviors\data\model\Behavior {
    // ...

	protected static function _methods($model, $behavior) {
		return [
			$behavior->config('field') => function() { /* ... */  }
		]
	}

	// ...
```

The above exemplaric behavior would then enable the following
methods on each entity returned from the model.

```php
Posts::bindBehavior('Taggable', ['field' => 'taxonomy']);
$item = Posts::create();
$item->taxonomy();
```

### Attaching Filters to the Model

To modify existing model methods, filters should be used. If your behavior 
needs to use filters a good place to attach them is the behavior's `_filters()`
method. Overwrite it to attach filters to the model during initialization phase.

```php
// ...

class Timestamp extends \li3_behaviors\data\model\Behavior {
	// ...

	protected static function _filters($model, $behavior) {
		Filters::apply($model, 'save', function($params, $next) use ($behavior) {
			$params['data'] = static::_timestamp($behavior, $params['entity'], $params['data']);

			return $next($params);
		});
	}

	protected static function _timestamp($behavior, $entity, $data) {
		// ...
	}

	// ...
```

### Attaching Finders to the Model

To add finders to the model's find method, finders should be used. If your behavior 
needs to use finders a good place to attach them is the behavior's `_finders()`
method. Overwrite it to add finders to the model during initialization phase.

```php
// ...

class Taggable extends \li3_behaviors\data\model\Behavior {
	// ...

	protected static function _finders($model, $behavior) {
		$model::finder('tag', function($params, $next) use ($behavior) {
			// ...
			return $next($params);
		});
	}

	// ...
```

## Copyright & License

Copyright 2010 Union of RAD. All rights reserved. This library
is distributed under the terms of the BSD 3-Clause License. The
full license text can be found in the LICENSE.txt file.

## Credits for previous Implementations

* David Persson, https://github.com/davidpersson/li3_behaviors
* Nate Abele, https://github.com/nateabele/li3_behaviors
* Simon Jaillet, https://github.com/jails/li3_behaviors
