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

	public function testConfig() {
		$behavior = new Behavior(['config' => ['test1' => 'value1']]);

		$expected = ['test1' => 'value1'];
		$result = $behavior->config();
		$this->assertEqual($expected, $result);

		$expected = 'value1';
		$result = $behavior->config('test1');
		$this->assertEqual($expected, $result);
	}
}

?>