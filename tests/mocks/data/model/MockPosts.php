<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2014, Union of RAD(http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
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