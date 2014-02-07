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
 * characteristics that should help building them. A behaviors must
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
 *     you to expose methods as if they were implemented on the model
 *
 *  4. Instance class to the model are transferred to behavior first.
 *     This allows you to expose methods as if the were implement on
 *     the model.
 *
 *  5. Each behavior can be instantiated multiple times for each
 *     model once. There is no need to key configurations by
 *     model for example.
 *
 *  6. Entity methods can be created dynamically. This allows
 *     you to i.e. provide a `tags()` or `taxonomy()` method on
 *     the entity dependent of the configuration provided.
 *
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
	protected $_defaults = [];

	/**
	 * Holding the initialized configuration array of the behavior possibly merged from
	 * the `$_defaults` property and any configuration via the model's `$_actsAs` property.
	 *
	 * @see li3_behaviors\data\model\Behavior::_config()
	 * @var array
	 */
	protected $_config = [];

	/**
	 * Holds the fully namespaced class name of the model this
	 * behavior is bound to. Good when you need to call
	 * static methods on the model.
	 *
	 * @var string
	 */
	protected $_model = null;

	/**
	 * Automatically makes model property available from config.
	 *
	 * @see lithium\core\Object::_autoConfig
	 * @var array
	 */
	protected $_autoConfig = ['model'];

	protected function _init() {
		parent::_init();
		$this->_config($this->_config, $defaults);
		$this->_filters($this->_model, __CLASS__);
	}

	/**
	 * Initializes configuration into `$_config`.
	 *
	 * - Overwrite to implement your own custom configuration merge strategies. -
	 *
	 * Behaviors often come with different requirements towards configuration.
	 * In some cases just a 1-dimensional array needs to be merged (`$config +
	 * $defaults`) in other cases nested multi-dimensional arrays must be merged
	 * or even normalized in a custom way.
	 *
	 * That's why merging the defaults with the provided configuration can be
	 * controlled easily by yourself - the implementor. By default we do a simple
	 * one-dimensional merge adding defaults and configuration to eachother. To
	 * control configuration merging overwrite this method.
	 *
	 * @see lithium\util\Set::normalize()
	 * @see lithium\util\Set::merge()
	 * @param array $config The configuration supplied by the user.
	 * @param array $defaults The default configuration for this behavior.
	 */
	protected function _config($config, $defaults) {
		$this->_config = $defaults + $config;
	}

	/**
	 * Applies filters on $model. Automatically called during initialization
	 * of behavior and model.
	 *
	 *  - Overwrite to apply your own filters. -
	 *
	 * @param $model Class name of the model.
	 * @param $model Class name of the behavior.
	 */
	protected function _filters($model, $behavior) {}

	/**
	 * Gets the configuration, allows for introspecting behavior configuration.
	 *
	 * @param string $config A configuration key or if `null` (default) returns whole configuration.
	 * @return array|string Configuration array or configuration option value if $key was string.
	 */
	public function config($key = null) {
		if (!$key) {
			return $this->_config;
		}
		return isset($this->_config[$key]) ? $this->_config[$key] : null;
	}
}

?>