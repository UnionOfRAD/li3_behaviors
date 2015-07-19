<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2014-2015, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_behaviors\data\model;

use lithium\core\Libraries;
use lithium\util\Set;
use Exception;
use RuntimeException;

/**
 * Trait that adds support for behaviors to the Model class. Add this trait
 * to your model that you plan to use behaviors with, then define all behaviors
 * using the `actsAs` property in your model class.
 *
 * ```
 * // ...
 * class Posts extends \lithium\data\Model {
 *
 *    use li3_behaviors\data\model\Behaviors;
 *
 *    public $actsAs = [
 *        'Sluggable' => ['fields' => ['title']]
 *    ];
 * // ...
 * ```
 *
 * This trait also makes some static methods available in the model,
 * which allows to manage behaviors as follows.
 *
 * ```
 * // Bind the slug behavior with configuration.
 * Posts::bindBehavior('Slug', ['fields' => ['title]]);
 *
 * // Accessing configuration.
 * Posts::behavior('Slug')->config();
 * Posts::behavior('Slug')->config('fields');
 *
 * // Unbinding it again.
 * Posts::unbindBehavior('Slug');
 * ```
 *
 * Behaviors themselves must extend the Behavior class. See the class'
 * docblock for more information on how to implement behaviors.
 *
 * @see li3_behaviors\data\model\Behaviors::bindBehavior()
 * @see li3_behaviors\data\model\Behavior
 */
trait Behaviors {

	/**
	 * Stores all loaded behavior instances,
	 * keyed by model name then by behavior name.
	 *
	 * @var array
	 */
	protected static $_behaviors = [];

	/**
	 * Indicates if we already intialized behaviors
	 * on a model. Prevents recursion as Model's `_initialize()`
	 * seems tricky to overwrite.
	 *
	 * @fixme Refactor this when we have decent tests in place.
	 * @var boolean
	 */
	protected $_initializedBehaviors = false;

	/**
	 * Initializes behaviors from the `$_actsAs` property of the model.
	 *
	 * Overwrites `Model::_initialize()` in order to hook initialization of
	 * behaviors into model initialization phase. Note that `Model::_initialize()`
	 * is still called and its result returned unmodified.
	 *
	 * @param string $class The fully-namespaced model class name to initialize.
	 * @return object Returns the initialized model instance.
	 */
	protected static function _initialize($class) {
		$self = parent::_initialize($class);

		if ($self->_initializedBehaviors) {
			return $self;
		}
		$self->_initializedBehaviors = true;

		if (isset($class::$_actsAs)) {
			foreach (Set::normalize(static::$_actsAs) as $name => $config) {
				static::bindBehavior($name, $config ?: []);
			}
		}
		return $self;
	}

	/**
	 * Transfer static call to the behaviors first. Static behavior
	 * methods will get the name of the model as its first parameter
	 * and the instance of the behavior as a second paramter.
	 *
	 * @fixme Type hint $params, once parent allows.
	 * @param string $method Method name caught by `__callStatic()`.
	 * @param array $params Arguments given to the above `$method` call.
	 * @return mixed
	 */
	public static function __callStatic($method, $params) {
		$model = get_called_class();

		if (!isset(static::$_behaviors[$model])) {
			return parent::__callStatic($method, $params);
		}
		foreach (static::$_behaviors[$model] as $class => $behavior) {
			if (method_exists($class, $method)) {
				array_unshift($params, $behavior);
				array_unshift($params, $model);

				return call_user_func_array([$class, $method], $params);
			}
		}
		return parent::__callStatic($method, $params);
	}

	/**
	 * Transfer call from the entity class to the behaviors. Concrete
	 * behavior methods will receive the following parameters: `$model`
	 * `$behavior` and `$entity`.
	 *
	 * @fixme Type hint $params, once parent allows.
	 * @param string $method Method name caught by `__call()`.
	 * @param array $params Arguments given to the above `$method` call.
	 * @return mixed
	 */
	public function __call($method, $params) {
		$model = get_called_class();

		if (!isset(static::$_behaviors[$model])) {
			return parent::__call($method, $params);
		}
		foreach (static::$_behaviors[$model] as $class => $behavior) {
			if ($behavior->respondsTo($method)) {
				array_unshift($params, $behavior);
				array_unshift($params, $model);

				return $behavior->invokeMethod($method, $params);
			}
		}
		return parent::__call($method, $params);
	}

	/**
	 * Returns a behavior instance. Configuration of
	 * the instance can be accessed as follows.
	 *
	 * ```
	 * Posts::behavior('Slug')->config();
	 * Posts::behavior('Slug')->config('fields');
	 * ```
	 *
	 * @param string $name The name of the behavior.
	 * @return \li3_behaviors\data\model\Behavior Intance of the behavior.
	 */
	public static function behavior($name) {
		list($model, $class) = static::_classesForBehavior($name);

		if (!isset(static::$_behaviors[$model][$class])) {
			throw new RuntimeException("Behavior `{$class}` not bound to model `{$model}`.");
		}
		return static::$_behaviors[$model][$class];
	}

	/**
	 * Binds a new instance of a behavior to the model using given config or
	 * entirely replacing an existing behavior instance with new config.
	 *
	 * @param string $name The name of the behavior.
	 * @param array $config Configuration for the behavior instance.
	 */
	public static function bindBehavior($name, array $config = []) {
		list($model, $class) = static::_classesForBehavior($name);

		static::$_behaviors[$model][$class] = new $class($config + compact('model'));
	}

	/**
	 * Unbinds an instance of a behavior from the model. Will throw
	 * an exception if behavior is not bind.
	 *
	 * @param string $name The name of the behavior.
	 */
	public static function unbindBehavior($name) {
		list($model, $class) = static::_classesForBehavior($name);

		if (!isset(static::$_behaviors[$model][$class])) {
			throw new RuntimeException("Behavior `{$class}` not bound to model `{$model}`.");
		}
		unset(static::$_behaviors[$model][$class]);
	}

	/**
	 * Allows to check if a certain behavior is bound to the model
	 *
	 * @param string $name The name of the behavior.
	 * @return boolean
	 */
	public static function hasBehavior($name) {
		try {
			list($model, $class) = static::_classesForBehavior($name);
		} catch (Exception $e) {
			return false;
		}
		return isset(static::$_behaviors[$model][$class]);
	}

	/**
	 * Helper method to retrieve current model class and behavior class for name.
	 *
	 * @param string $name The name of the behavior.
	 * @return array An array usable with `list()` with the model and behavior classes.
	 */
	protected static function _classesForBehavior($name) {
		$model = get_called_class();

		if (!$class = Libraries::locate('behavior', $name)) {
			throw new RuntimeException("No behavior named `{$name}` found.");
		}
		return [$model, $class];
	}
}

?>