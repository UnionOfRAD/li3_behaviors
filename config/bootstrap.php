<?php
/**
 * li₃ behaviors
 *
 * Copyright 2010, Union of RAD. All rights reserved. This source
 * code is distributed under the terms of the BSD 3-Clause License.
 * The full license text can be found in the LICENSE.txt file.
 */

use lithium\core\Libraries;

/**
 * This adds the `'behavior'` type to the list of recognized class types. You can look up the
 * behaviors available to your application by running `Libraries::locate('behavior')`.
 */
Libraries::paths([
	'behavior' => [
		'{:library}\data\model\behavior\{:name}',
		'{:library}\extensions\data\behavior\{:name}'
	]
]);

?>