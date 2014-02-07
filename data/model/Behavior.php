<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2012, Union of RAD (http://union-of-rad.org)
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
 *  2. Static calls to the model are transferred to the behavior first
 *     and get the model class name as its first parameter.
 *
 *  3. Instance class to the model are transferred to behavior first.
 *
 *  4. Each behavior can be instantiated multiple times for each
 *     model once. There is no need to key configurations by
 *     model for example.
 *
 * {{{
 * // ...
 * class Slug extends \li3_behaviors\data\model\Behavior {
 *
 * // ...
 * }}}
 *
 * @see li3_behaviors\data\model\Behaviors
 */
class Behavior extends \lithium\core\Object {

	/**
	 * Holding the configuration array of the behavior
	 *
	 * @var array
	 */
	protected $_config = [];

	/**
	 * @see lithium\core\Object::_autoConfig
	 * @var array
	 */
	protected $_autoConfig = ['model', 'config'];

	/**
	 * Hold the fully namespaced class name of the model
	 *
	 * @var string
	 */
	protected $_model = null;

	/**
	 * Bind
	 *
	 * Applies Behaviour to the Model and configures its use
	 *
	 * @param \lithium\data\Model $self The Model using this behaviour
	 */
	public function __construct($config = []) {
		parent::__construct($config);
	}

	/**
	 * Sets/Gets the configuration for this behavior
	 *
	 * @param array $config The new configuration.
	 * @return array of configurations.
	 */
	public function config($config = null) {
		if ($config) {
			if (!is_array($config)) {
				return isset($this->_config[$config]) ? $this->_config[$config] : null;
			}
			$this->_config = $config + $this->_config;
		}
		return $this->_config;
	}
}