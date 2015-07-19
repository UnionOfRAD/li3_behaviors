<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2014, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_behaviors\tests\mocks\data\behavior;

class MockFly extends \li3_behaviors\data\model\Behavior {

	protected static $_defaults = [
		'foo' => 'bar',
		'baz' => ['qux']
	];

	public static function staticFly($model, $target) {
		return $target . ' reached in 1h54.';
	}

	public function instanceFly($entity, $target) {
		return $target . ' reached in 1h24.';
	}
}

?>