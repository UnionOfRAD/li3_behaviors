<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2012, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_behaviors\data\model;

use lithium\core\Libraries;
use RuntimeException;

/**
 * Trait that adds support for behaviors to the Model class. Add this trait
 * to your model that you plan to use behaviors with, then define all behaviors
 * using the `actsAs` property in your model class.
 *
 * {{{
 * // ...
 * class Posts extends \lithium\data\Model {
 *
 *    use li3_behaviors\data\model\Behaviors;
 *
 *    protected $_actsAs = [
 *        'Slug' => ['fields' => ['title']]
 *    ];
 * // ...
 * }}}
 *
 * This trait also makes some static methods available in the model,
 * which allows to manage behaviors as follows.
 *
 * {{{
 * // Bind the slug behavior with configuration.
 * Posts::bindBehavior('Slug', ['fields' => ['title]]);
 *
 * // Accessing configuration.
 * Posts::behavior('Slug')->config();
 * Posts::behavior('Slug')->config('fields');
 *
 * // Unbinding it again.
 * Posts::unbindBehavior('Slug');
 * }}}
 *
 * Behaviors themselves must extend the Behavior class. See the class'
 * docblock for more information on how to implement behaviors.
 *
 * @see li3_behaviors\data\model\Behaviors::actsAs()
 * @see li3_behaviors\data\model\Behavior
 */
trait Behaviors {

	/**
	 * Store all loaded behaviors.
	 *
	 * @var array
	 */
	protected $_behaviors = [];

	/**
	 * Boolean indicates if the `Model::_init()` has been launched at the initialization step.
	 */
	protected $_inited = false;

	/**
	 * Allow the exectution of a kind of `_init()` for the model instance once.
	 *
	 * @param string $class The fully-namespaced class name to initialize.
	 */
	protected static function _initialize($class) {
		$self = parent::_initialize($class);

		if (!$self->_inited) {
			$self->_inited = true;
			$self->_init();
		}
		return $self;
	}

	/**
	 * Initializer function called just after the model instanciation.
	 *
	 * Example to disable the `_init()` call use the following before any access to the model:
	 * {{{
	 * Posts::config(['init' => false]);
	 * }}}
	 */
	protected function _init() {
		$self = static::_object();

		if (!isset($self->_actsAs)) {
			$self->_actsAs = [];
		}
		foreach ($self->_actsAs as $name => $config) {
			if (is_string($config)) {
				$name = $config;
				$config = [];
			}
			static::bindBehavior($name, $config);
		}
	}

	/**
	 * Transfer static call to the behaviors first.
	 *
	 * @param string $method Method name caught by `__callStatic()`.
	 * @param array $params Arguments given to the above `$method` call.
	 * @return mixed
	 */
	public static function __callStatic($method, $params) {
		$self = static::_object();

		foreach ($self->_behaviors as $class => $behavior) {
			if (method_exists($class, $method)) {
				array_unshift($params, get_called_class());
				return call_user_func_array([$class, $method], $params);
			}
		}
		return parent::__callStatic($method, $params);
	}

	/**
	 * Transfer call from the entity class to the behaviors
	 *
	 * @param string $method Method name caught by `__call()`.
	 * @param array $params Arguments given to the above `$method` call.
	 * @return mixed
	 */
	public function __call($method, $params) {
		$self = static::_object();
		foreach ($self->_behaviors as $class => $behavior) {
			if ($behavior->respondsTo($method)) {
				return $behavior->invokeMethod($method, $params);
			}
		}
		parent::__call($method, $params);
	}

	/**
	 * Returns a behavior instance. Configuration of
	 * the instance can be accessed as follows.
	 *
	 * {{{
	 * Posts::behavior('Slug')->config();
	 * Posts::behavior('Slug')->config('fields');
	 * }}}
	 *
	 * @param string $name The name of the behavior.
	 * @return array Configuration of the behavior.
	 */
	public static function behavior($name) {
		$self = static::_object();
		$class = Libraries::locate('behavior', $name);

		if (!isset($self->_behaviors[$class])) {
			throw new RuntimeException("Unexisting Behavior named `{$class}`.");
		}
		return $self->_behaviors[$class];
	}

	/**
	 * Binds a new instance of a behavior to the model using given config or
	 * reconfigures an existing behavior instance.
	 *
	 * @param string $name The name of the behavior.
	 * @param array $config Configuration for the behavior instance.
	 */
	public static function bindBehavior($name, array $config = []) {
		$self = static::_object();
		$config = $config + ['model' => get_called_class()];

		if (isset($self->_behaviors[$class])) {
			$self->_behaviors[$class]->config($config);
		} else {
			$class = Libraries::locate('behavior', $name);
			$self->_behaviors[$class] = new $class($config);
		}
	}

	/**
	 * Unbinds an instance of a behavior from the model. Will throw
	 * an exception if behavior is not bind.
	 *
	 * @param string $name The name of the behavior.
	 */
	public static function unbindBehavior($name) {
		$self = static::_object();
		$class = Libraries::locate('behavior', $name);

		if (!isset($self->_behaviors[$class])) {
			throw new RuntimeException("Unexisting Behavior named `{$class}`.");
		}
		unset($self->_behaviors[$class]);
	}
}

?>