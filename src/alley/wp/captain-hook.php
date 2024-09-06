<?php
/**
 * Captain Hook functions
 *
 * (c) Alley <info@alley.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package wp-captain-hook
 */

namespace Alley\WP;

/**
 * Compare two callbacks to see if they are the same.
 *
 * @param callable $callback_1 The first callback.
 * @param callable $callback_2 The second callback.
 * @return bool Whether the callbacks are the same.
 */
function compare_callbacks( $callback_1, $callback_2 ) {
	if ( is_array( $callback_1 ) && is_array( $callback_2 ) ) {
		// If the method names don't match, we can bail early.
		if ( $callback_1[1] !== $callback_2[1] ) {
			return false;
		}

		// Knowing the method names match, now compare the classes.
		$class_0 = is_object( $callback_1[0] ) ? get_class( $callback_1[0] ) : $callback_1[0];
		$class_1 = is_object( $callback_2[0] ) ? get_class( $callback_2[0] ) : $callback_2[0];
		return $class_0 === $class_1;
	}

	return $callback_1 === $callback_2;
}

/**
 * Find a hooked callback.
 *
 * @param string   $hook The hook name.
 * @param callable $callback The callback to find.
 * @param mixed    $priority The priority of the callback.
 * @return array|null The callback array if found, otherwise null.
 */
function find_hooked_callback( string $hook, $callback, $priority = 10 ) {
	global $wp_filter;
	foreach ( $wp_filter[ $hook ][ $priority ] ?? [] as $filter ) {
		if ( compare_callbacks( $filter['function'], $callback ) ) {
			return $filter;
		}
	}
	return null;
}

/**
 * Remove a filter by force.
 *
 * @param string   $hook     The hook name.
 * @param callable $callback The callback to remove.
 * @param mixed    $priority The priority of the callback.
 * @return bool Whether the filter was removed.
 */
function remove_filter_by_force( string $hook, $callback, $priority = 10 ) {
	$filter = find_hooked_callback( $hook, $callback, $priority );
	if ( $filter ) {
		return \remove_filter( $hook, $filter['function'], $priority );
	}

	return false;
}

/**
 * Remove an action by force.
 *
 * @param string   $hook     The hook name.
 * @param callable $callback The callback to remove.
 * @param mixed    $priority The priority of the callback.
 * @return bool Whether the action was removed.
 */
function remove_action_by_force( string $hook, $callback, $priority = 10 ) {
	return remove_filter_by_force( $hook, $callback, $priority );
}

/**
 * Reprioritize a filter.
 *
 * @param string   $hook         The hook name.
 * @param callable $callback     The callback to reprioritize.
 * @param mixed    $old_priority The old priority of the callback.
 * @param mixed    $new_priority The new priority of the callback.
 * @return bool Whether the filter was reprioritized.
 */
function reprioritize_filter( string $hook, $callback, $old_priority, $new_priority ) {
	global $wp_filter;
	$filter = find_hooked_callback( $hook, $callback, $old_priority );
	if ( $filter ) {
		$removed = \remove_filter( $hook, $filter['function'], $old_priority );
		if ( $removed ) {
			return \add_filter( $hook, $filter['function'], $new_priority, $filter['accepted_args'] );
		}
	}

	return false;
}

/**
 * Reprioritize an action.
 *
 * @param string   $hook         The hook name.
 * @param callable $callback     The callback to reprioritize.
 * @param mixed    $old_priority The old priority of the callback.
 * @param mixed    $new_priority The new priority of the callback.
 * @return bool Whether the action was reprioritized.
 */
function reprioritize_action( string $hook, $callback, $old_priority, $new_priority ) {
	return reprioritize_filter( $hook, $callback, $old_priority, $new_priority );
}

/**
 * Get the object for a hooked callback.
 *
 * This function allows developers to access out-of-scope objects that are hooked to a filter or
 * action.
 *
 * @param string   $hook     The hook name.
 * @param callable $callback The callback to find.
 * @param mixed    $priority The priority of the callback.
 * @return object|null The object that the callback is hooked to, or null if not found.
 */
function get_hooked_object( string $hook, $callback, $priority = 10 ) {
	$filter = find_hooked_callback( $hook, $callback, $priority );
	if ( $filter && isset( $filter['function'][0] ) && is_object( $filter['function'][0] ) ) {
		return $filter['function'][0];
	}

	return null;
}
