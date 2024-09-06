<?php
/**
 * Class file for Test_Captain_Hook
 *
 * (c) Alley <info@alley.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package wp-captain-hook
 */

namespace Alley\WP;

use Alley\WP\Fixtures\Demo;
use Mantle\Testing\Utils;
use Mantle\Testkit\Test_Case;

use function Mantle\Support\Helpers\add_filter;

/**
 * Tests for captain hook utilities.
 */
final class Captain_Hook_Test extends Test_Case {
	public function test_remove_class_instance_callback_filter_by_force() {
		new Demo( 'Test' );

		// Confirm that firing the filter returns the expected value.
		$this->assertSame( 'Hello Test', \apply_filters( 'captain_hook_test_filter', 'Hello', null, null ) );

		// Remove the filter by force.
		remove_filter_by_force( 'captain_hook_test_filter', [ Demo::class, 'instance_filter_callback' ], 50 );

		// Confirm that the filter was removed.
		$this->assertSame( 'Hello', \apply_filters( 'captain_hook_test_filter', 'Hello', null, null ) );
	}

	public function test_remove_class_instance_callback_action_by_force() {
		new Demo( 'Test' );

		// Confirm that firing the action returns the expected value.
		$this->assertSame( 'Test', Utils::get_echo( fn () => \do_action( 'captain_hook_test_action' ) ) );

		// Remove the action by force.
		remove_action_by_force( 'captain_hook_test_action', [ Demo::class, 'instance_action_callback' ], 50 );

		// Confirm that the action was removed.
		$this->assertSame( '', Utils::get_echo( fn () => \do_action( 'captain_hook_test_action' ) ) );
	}

	public function test_get_hooked_object() {
		new Demo( 'Test Instance' );

		// Confirm that the hooked object is returned.
		$instance = get_hooked_object( 'captain_hook_test_filter', [ Demo::class, 'instance_filter_callback' ], 50 );
		$this->assertInstanceOf( Demo::class, $instance );
		$this->assertSame( 'Test Instance', $instance->get_name() );
	}

	public function test_remove_filter_callback_by_force_with_distractions() {
		add_filter( 'captain_hook_test_filter', [ Demo::class, 'static_filter_callback' ], 50 );
		new Demo( 'Test' );
		add_filter( 'captain_hook_test_filter', [ Demo::class, 'static_filter_callback' ], 50 );

		// Confirm that firing the filter returns the expected value.
		$this->assertSame( 'Filtered Filtered Hello Test', \apply_filters( 'captain_hook_test_filter', 'Hello' ) );

		// Remove the filter by force.
		remove_filter_by_force( 'captain_hook_test_filter', [ Demo::class, 'instance_filter_callback' ], 50 );

		// Confirm that the filter was removed.
		$this->assertSame( 'Filtered Filtered Hello', \apply_filters( 'captain_hook_test_filter', 'Hello' ) );
	}

	public function test_reprioritize_filter_callback() {
		global $wp_filter;

		// Add the filter and confirm it gets added.
		$this->assertArrayNotHasKey( 'captain_hook_test_filter', $wp_filter );
		new Demo( 'Test' );
		$this->assertCount( 1, $wp_filter['captain_hook_test_filter'][50] );

		// Reprioritize the filter.
		reprioritize_filter( 'captain_hook_test_filter', [ Demo::class, 'instance_filter_callback' ], 50, 7 );

		// Confirm that the filter was reprioritized.
		$this->assertCount( 1, $wp_filter['captain_hook_test_filter'][7] );
		$this->assertArrayNotHasKey( 50, $wp_filter['captain_hook_test_filter'] );
		$filter_key = array_keys( $wp_filter['captain_hook_test_filter'][7] )[0];
		$this->assertSame( 3, $wp_filter['captain_hook_test_filter'][7][ $filter_key ]['accepted_args'] );

		// Assert the filter still works.
		$this->assertSame( 'Hello Test', \apply_filters( 'captain_hook_test_filter', 'Hello', null, null ) );
	}
}
