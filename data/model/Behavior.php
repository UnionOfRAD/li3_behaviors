<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2014, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_behaviors\data\model;

/**
 * Base class that all behaviors must extend. Behaviors can be applied
 * to a model via its `actsAs` property. Behaviors have some special
 * characteristics that should help building them. A behavior must
 * be created as `extensions/data/behavior/<name>.php` from where
 * it can be loaded automatically.
 *
 *  1. Default configuration in `$_defaults` will automatically be
 *     merged with configuration specified in the `actsAs` poperty
 *     of the model and be made available in its entirety in the
 *     behaviors `$_config` property.
 *
 *  2. Configuratin merge strategies can be controlled by overwriting
 *     the `_config()` method.
 *
 *  3. Static calls to the model are transferred to the behavior first
 *     and get the model class name as its first parameter. This allows
 *     you to expose methods as if they were implemented on the model.
 *     Each static method implemented in the behavior receives
 *     two parameters by default: The name of the model class,
 *     an instance of the behavior.
 *
 *  4. Instance class to the model are transferred to behavior first.
 *     This allows you to expose methods as if the were implement on
 *     the model. Each concrete public method implemented on the
 *     behavior receives three parameters by default: The name of the
 *     model class, an instance of the behavior and the entity.
 *
 *  5. Each behavior can be instantiated multiple times for each
 *     model once. There is no need to key configurations by
 *     model for example.
 *
 *  6. Entity methods can be created dynamically. This allows
 *     you to i.e. provide a `tags()` or `taxonomy()` method on
 *     the entity dependent of the configuration provided.
 *
 *     This can be achieved by implementing the following method
 *     in the behavior which must return an array of model instance
 *     methods to be added to the model.
 *
 * @see li3_behaviors\data\model\Behavior::_config()
 * @see li3_behaviors\data\model\Behavior::_methods()
 * @see li3_behaviors\data\model\Behaviors
 */
class Behavior extends \lithium\core\Object {

	/**
	 * Allows to specify default configuration for the behavior.
	 *
	 * - Overwrite in sublasses where needed. -
	 *
	 * @see li3_behaviors\data\model\Behavior::_config()
	 * @var array
	 */
	protected static $_defaults = [];

	/**
	 * Holding the initialized configuration array of the behavior possibly merged from
	 * the `$_defaults` property and any configuration via the model's `$_actsAs` property.
	 *
	 * Always at least holds the fully namespaced class name of the
	 * model this behavior is bound to.
	 *
	 * @see li3_behaviors\data\model\Behavior::_config()
	 * @var array
	 */
	protected $_config = [];

	protected function _init() {
		parent::_init();

		$model = $this->_config['model'];
		$behavior = $this;

		$this->_config = static::_config($model, $behavior, $this->_config, static::$_defaults);
		static::_filters($model, $behavior);

		if ($methods = static::_methods($model, $behavior)) {
			$model::instanceMethods($methods);
		}
	}

	/**
	 * Initializes configuration into `$_config` using `config()`.
	 *
	 * - Overwrite to implement your own custom configuration merge strategies. -
	 *
	 * Behaviors often come with different requirements towards configuration.
	 * In some cases just a 1-dimensional array needs to be merged (`$config +
	 * $defaults`) in other cases nested multi-dimensional arrays must be merged
	 * or even normalized in a custom way.
	 *
	 * That's why merging the defaults with the provided configuration can be
	 * controlled easily by yourself - the implementer. By default we do a simple
	 * one-dimensional merge adding defaults and configuration to eachother. To
	 * control configuration merging overwrite this method.
	 *
	 * @see lithium\util\Set::normalize()
	 * @see lithium\util\Set::merge()
	 * @param string $model Class name of the model.
	 * @param object $behavior Instance of the behavior.
	 * @param array $config The configuration supplied by the user.
	 * @param array $defaults The default configuration for this behavior.
	 * @param array The final configuration which should be set for this behavior.
	 */
	protected static function _config($model, $behavior, $config, $defaults) {
		return $config + $defaults;
	}

	/**
	 * Applies filters on $model. Automatically called during initialization
	 * of behavior and model.
	 *
	 *  - Overwrite to apply your own filters. -
	 *
	 * @param string $model Class name of the model.
	 * @param object $behavior Instance of the behavior.
	 */
	protected static function _filters($model, $behavior) {}

	/**
	 * Allows for dyamically adding instance methods to the model. The
	 * methods to be added must be returned as an array, where the key
	 * is the name of the concrete method on the model and the value
	 * an anonymous function.
	 *
	 *  - Overwrite to add your own methods. -
	 *
	 * @param string $model Class name of the model.
	 * @param object $behavior Instance of the behavior.
	 * @return array Methods to be added to the model instance.
	 */
	protected static function _methods($model, $behavior) {
		return [];
	}

	/**
	 * Gets/sets the configuration, allows for introspecting and changing behavior configuration.
	 *
	 * @param string|array $key A configuration key or if `null` (default) returns whole
	 *        configuration. If array will merge config values with existing.
	 * @param mixed $value Configuration value if `null` (default) will return $key.
	 * @return array|string Configuration array or configuration option value if $key was string.
	 */
	public function config($key = null, $value = null) {
		if (is_array($key)) {
			return $this->_config = $key + $this->_config;
		}
		if ($key === null) {
			return $this->_config;
		}
		if ($value !== null) {
			return $this->_config[$key] = $value;
		}
		return isset($this->_config[$key]) ? $this->_config[$key] : null;
	}
}

?>