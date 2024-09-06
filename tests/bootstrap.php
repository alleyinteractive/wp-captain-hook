<?php
/**
 * PHPUnit bootstrap
 *
 * (c) Alley <info@alley.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package wp-captain-hook
 */

\Mantle\Testing\install();

// Callback functions for testing.
function alley_wp_captain_hook_action_callback() {
	echo 'This is an action callback';
}

function alley_wp_captain_hook_filter_callback() {
	return 'This is a filter callback';
}
