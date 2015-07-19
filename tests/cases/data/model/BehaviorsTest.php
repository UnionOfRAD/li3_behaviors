<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2014, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_behaviors\tests\cases\data\model;

use li3_behaviors\tests\mocks\data\model\MockPosts;
use li3_behaviors\tests\mocks\data\behavior\MockFly;
use lithium\core\Libraries;

class BehaviorsTest extends \lithium\test\Unit {

	protected $_backup;

	public function setUp() {
		$this->_backup = Libraries::paths();

		Libraries::paths([
			'behavior' => [
				'{:library}\tests\mocks\data\behavior\{:name}'
			]
		]);
	}

	public function tearDown() {
		Libraries::paths($this->_backup);
		MockPosts::reset();
	}

	public function testBindUnbindBehavior() {
		$behavior = new MockFly();

		$result = MockPosts::hasBehavior('MockFly');
		$this->assertFalse($result);

		MockPosts::bindBehavior($behavior);

		$result = MockPosts::hasBehavior('MockFly');
		$this->assertTrue($result);

		MockPosts::unbindBehavior('MockFly');

		$result = MockPosts::hasBehavior('MockFly');
		$this->assertFalse($result);
	}

	public function testCallStatic() {
		MockPosts::bindBehavior('MockFly');

		$result = MockPosts::staticFly('New York');
		$this->assertEqual('New York reached in 1h54.', $result);
	}

	public function testCall() {
		MockPosts::bindBehavior('MockFly');

		$entity = MockPosts::create();

		$result = $entity->instanceFly('Las Vegas');
		$this->assertEqual('Las Vegas reached in 1h24.', $result);
	}
}

?>