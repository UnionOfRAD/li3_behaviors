<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2014, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
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