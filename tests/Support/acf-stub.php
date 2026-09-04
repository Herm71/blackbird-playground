<?php
/**
 * Minimal stand-in for the ACF repeater row API.
 *
 * ACF is not installed in the test environment and cannot be: the
 * `style_definitions` field is a Repeater, which is an ACF PRO feature and is
 * not downloadable from wordpress.org.
 *
 * Stubbing is the right boundary regardless. These tests are about what the
 * plugin does with the values ACF hands back — escaping them, and cleaning up
 * after the loop — not about ACF's own correctness. Feeding the values
 * directly is more precise than round-tripping them through a real repeater.
 *
 * Guarded by function_exists() so a real ACF install always wins.
 *
 * @package Blackbird_Sandbox
 */

/**
 * Holds the rows the stubbed loop walks.
 */
class Blackbird_ACF_Stub {

	/**
	 * Rows to serve, each a map of sub-field name => value.
	 *
	 * @var array
	 */
	public static $rows = array();

	/**
	 * Cursor into $rows. -1 means the loop has not started.
	 *
	 * @var int
	 */
	public static $index = -1;

	/**
	 * Load rows and rewind.
	 *
	 * @param array $rows Rows to serve.
	 */
	public static function set_rows( array $rows ) {
		self::$rows  = $rows;
		self::$index = -1;
	}

	/**
	 * Clear all state. Call between tests.
	 */
	public static function reset() {
		self::$rows  = array();
		self::$index = -1;
	}
}

if ( ! function_exists( 'have_rows' ) ) {
	/**
	 * Whether a row remains to be consumed.
	 *
	 * @param string $selector Field name. Ignored; the stub serves one set.
	 * @return bool
	 */
	function have_rows( $selector = '' ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
		return ( Blackbird_ACF_Stub::$index + 1 ) < count( Blackbird_ACF_Stub::$rows );
	}
}

if ( ! function_exists( 'the_row' ) ) {
	/**
	 * Advance to the next row.
	 */
	function the_row() {
		++Blackbird_ACF_Stub::$index;
	}
}

if ( ! function_exists( 'get_sub_field' ) ) {
	/**
	 * Read a sub-field from the current row.
	 *
	 * @param string $selector Sub-field name.
	 * @return mixed Value, or null when absent.
	 */
	function get_sub_field( $selector = '' ) {
		$row = Blackbird_ACF_Stub::$rows[ Blackbird_ACF_Stub::$index ] ?? array();
		return $row[ $selector ] ?? null;
	}
}
