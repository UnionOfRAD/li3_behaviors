<?php
/**
 * li₃ behaviors
 *
 * Copyright 2014, Union of RAD. All rights reserved. This source
 * code is distributed under the terms of the BSD 3-Clause License.
 * The full license text can be found in the LICENSE.txt file.
 */

namespace li3_behaviors\tests\mocks\data\model;

class MockPosts extends \lithium\data\Model {

	use \li3_behaviors\data\model\Behaviors;

	protected $_actsAs = [];

	protected $_meta = [
		'connection' => false,
		'key' => 'id'
	];
}

?>