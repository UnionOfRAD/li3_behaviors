<?php
/**
 * li₃ behaviors
 *
 * Copyright 2014, Union of RAD. All rights reserved. This source
 * code is distributed under the terms of the BSD 3-Clause License.
 * The full license text can be found in the LICENSE.txt file.
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