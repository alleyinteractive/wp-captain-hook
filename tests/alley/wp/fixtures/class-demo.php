<?php
/**
 * Demo class file
 *
 * @package wp-captain-hook
 */

namespace Alley\WP\Fixtures;

/**
 * Demo class.
 */
class Demo {
	public function __construct(
		private string $name,
	) {
		\add_action( 'captain_hook_test_action', [ $this, 'instance_action_callback' ], 50, 3 );
		\add_filter( 'captain_hook_test_filter', [ $this, 'instance_filter_callback' ], 50, 3 );
	}

	public function instance_action_callback() {
		echo $this->name;
	}

	public function instance_filter_callback( $value ) {
		return "{$value} {$this->name}";
	}

	public static function static_action_callback() {
		echo 'This is an action callback';
	}

	public static function static_filter_callback( $value ) {
		return "Filtered {$value}";
	}

	public function get_name() {
		return $this->name;
	}
}
