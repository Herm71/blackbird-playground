<?php
/**
 * BB-05 — every translatable string must use the plugin's declared domain.
 *
 * @package Blackbird_Sandbox
 */

/**
 * Text domain regression.
 */
class TextDomainTest extends WP_UnitTestCase {

	/**
	 * The domain the plugin declares, matching the directory slug so that
	 * WordPress can auto-load translations without load_plugin_textdomain().
	 */
	const DOMAIN = 'blackbird-playground';

	/**
	 * The header declares the expected domain.
	 */
	public function test_header_declares_the_domain() {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		$data = get_plugin_data( dirname( __DIR__ ) . '/plugin.php', false, false );

		$this->assertSame( self::DOMAIN, $data['TextDomain'], 'Plugin header declares a different text domain.' );
	}

	/**
	 * The domain matches the plugin directory.
	 *
	 * WordPress only auto-loads translations when these agree.
	 */
	public function test_domain_matches_the_plugin_directory() {
		$this->assertSame(
			self::DOMAIN,
			basename( dirname( __DIR__ ) ),
			'Text domain no longer matches the plugin directory name.'
		);
	}

	/**
	 * The block variation's strings actually resolve under that domain.
	 *
	 * Intercepts gettext for this domain only and marks anything passing
	 * through it, then triggers the variation filter. A string still declared
	 * against the old domain is never offered to this filter and comes back
	 * unmarked.
	 */
	public function test_block_variation_strings_use_the_domain() {
		$marker = '@@translated@@';

		$intercept = static function ( $translation, $text, $domain ) use ( $marker ) {
			return self::DOMAIN === $domain ? $marker . $text : $translation;
		};

		add_filter( 'gettext', $intercept, 10, 3 );

		$block_type       = new stdClass();
		$block_type->name = 'core/search';
		$variations       = apply_filters( 'get_block_type_variations', array(), $block_type );

		remove_filter( 'gettext', $intercept, 10 );

		$this->assertNotEmpty( $variations, 'The search block variation was not registered.' );

		$variation = $variations[0];

		$this->assertStringStartsWith( $marker, $variation['title'], 'Variation title does not use the plugin text domain.' );
		$this->assertStringStartsWith( $marker, $variation['description'], 'Variation description does not use the plugin text domain.' );
		$this->assertStringStartsWith( $marker, $variation['attributes']['placeholder'], 'Placeholder does not use the plugin text domain.' );
		$this->assertStringStartsWith( $marker, $variation['attributes']['buttonText'], 'Button text does not use the plugin text domain.' );
		$this->assertStringStartsWith( $marker, $variation['attributes']['label'], 'Label does not use the plugin text domain.' );
	}
}
