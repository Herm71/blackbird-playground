<?php
/**
 * BB-01 — shortcode output must escape ACF values.
 *
 * @package Blackbird_Sandbox
 */

/**
 * Escaping regressions for [style-definition] and [style-archive].
 */
class ShortcodeEscapingTest extends Blackbird_TestCase {

	/**
	 * A script payload an editor could save into the text sub-field.
	 */
	const PAYLOAD = '<script>alert("xss")</script>';

	/**
	 * The item name is an ACF text field and must be escaped outright.
	 */
	public function test_style_definition_escapes_item_name() {
		Blackbird_ACF_Stub::set_rows(
			array(
				array(
					'editorial_style_item'       => self::PAYLOAD,
					'editorial_style_definition' => '<p>A definition.</p>',
				),
			)
		);

		$output = do_shortcode( '[style-definition]' );

		$this->assertStringNotContainsString( '<script>', $output, 'Raw <script> reached the output.' );
		$this->assertStringContainsString( '&lt;script&gt;', $output, 'Payload was not HTML-escaped.' );
	}

	/**
	 * The definition is an ACF wysiwyg field.
	 *
	 * It must keep safe markup — escaping it outright would render every
	 * definition as visible tag soup — while still losing script tags.
	 */
	public function test_style_definition_filters_wysiwyg_but_keeps_markup() {
		Blackbird_ACF_Stub::set_rows(
			array(
				array(
					'editorial_style_item'       => 'Oxford comma',
					'editorial_style_definition' => '<p>Use <strong>always</strong>. <a href="https://example.com">Ref</a>.</p>'
						. '<script>alert("xss")</script>',
				),
			)
		);

		$output = do_shortcode( '[style-definition]' );

		$this->assertStringNotContainsString( '<script>', $output, 'Script survived wp_kses_post().' );
		$this->assertStringContainsString( '<strong>always</strong>', $output, 'Legitimate markup was stripped.' );
		$this->assertStringContainsString( 'href="https://example.com"', $output, 'Links were stripped.' );
	}

	/**
	 * The archive shortcode has the same defect at its own call site.
	 */
	public function test_style_archive_escapes_item_name() {
		self::factory()->post->create(
			array(
				'post_type'   => self::POST_TYPE,
				'post_title'  => 'Entry',
				'post_status' => 'publish',
			)
		);

		Blackbird_ACF_Stub::set_rows(
			array(
				array(
					'editorial_style_item'       => self::PAYLOAD,
					'editorial_style_definition' => '<p>A definition.</p>',
				),
			)
		);

		$output = do_shortcode( '[style-archive]' );

		$this->assertStringNotContainsString( '<script>', $output, 'Raw <script> reached the archive output.' );
		$this->assertStringContainsString( '&lt;script&gt;', $output, 'Payload was not HTML-escaped in the archive.' );
	}
}
