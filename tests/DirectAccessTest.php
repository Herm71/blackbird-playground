<?php
/**
 * BB-07 — plugin.php must not execute on a direct HTTP request.
 *
 * @package Blackbird_Sandbox
 */

/**
 * ABSPATH guard regression.
 */
class DirectAccessTest extends WP_UnitTestCase {

	/**
	 * Running the file outside WordPress must exit quietly.
	 *
	 * Executed in a separate PHP process with no WordPress loaded, which is
	 * what a direct web request to the file amounts to. Without the guard the
	 * first WordPress call in the file raises a fatal.
	 */
	public function test_plugin_file_exits_without_wordpress() {
		$plugin = dirname( __DIR__ ) . '/plugin.php';
		$output = array();
		$status = 0;

		exec( 'php ' . escapeshellarg( $plugin ) . ' 2>&1', $output, $status );

		$joined = implode( "\n", $output );

		$this->assertSame( 0, $status, "Direct execution did not exit cleanly:\n" . $joined );
		$this->assertSame( '', trim( $joined ), "Direct execution produced output:\n" . $joined );
	}
}
