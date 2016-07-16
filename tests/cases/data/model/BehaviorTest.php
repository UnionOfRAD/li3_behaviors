<?php
/**
 * li₃ behaviors
 *
 * Copyright 2014, Union of RAD. All rights reserved. This source
 * code is distributed under the terms of the BSD 3-Clause License.
 * The full license text can be found in the LICENSE.txt file.
 */

namespace li3_behaviors\tests\cases\data\model;

use li3_behaviors\data\model\Behavior;

class BehaviorTest extends \lithium\test\Unit {

	public function testConfigRead() {
		$behavior = new Behavior([
			'model' => 'li3_behaviors\tests\mocks\data\model\MockPost',
			'test' => 'case'
		]);

		$expected = [
			'model' => 'li3_behaviors\tests\mocks\data\model\MockPost',
			'test' => 'case'
		];
		$result = $behavior->config();
		$this->assertEqual($expected, $result);

		$expected = 'case';
		$result = $behavior->config('test');
		$this->assertEqual($expected, $result);
	}

	public function testConfigSet() {
		$behavior = new Behavior([
			'model' => 'li3_behaviors\tests\mocks\data\model\MockPost',
			'test' => 'case'
		]);

		$behavior->config('test', 'phase');

		$expected = 'phase';
		$result = $behavior->config('test');
		$this->assertEqual($expected, $result);
	}
}

?>