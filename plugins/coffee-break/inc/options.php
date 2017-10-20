<?php
/**
 * Coffee Break Options
 *
 * Options page to store tokens, et al.
 *
 * @since 0.1.0
 *
 * @package Coffee_Break
 */

class Coffee_Break_Options {
	public function __construct() {
		add_action( 'cmb2_admin_init', [ $this, 'register_options_metabox' ] );
	}

	public function register_options_metabox() {
		$prefix = '_cb_';

		$options = new_cmb2_box( [
			'id'           => $prefix . 'metabox',
			'title'        => esc_html__( 'Coffee Break Options', 'hm-coffee-break' ),
			'object_types' => [ 'options-page' ],
			'option_key'   => $prefix . '_options',
			'menu_title'   => esc_html__( 'Options', 'hm-coffee-break' ),
			'parent_slug'  => 'edit.php?post_type=human',
			'capability'   => 'manage_options',
			'save_button'  => esc_html__( 'Save Options', 'hm-coffee-break' ),
		] );

		$options->add_field( [
			'name'       => __( 'Oauth Token', 'hm-coffee-break' ),
			'id'         => $prefix . 'oauth_token',
			'type'       => 'text',
			'desc'       => __( 'Oauth token for the Slackbot', 'hm-coffee-break' ),
		] );

		$options->add_field( [
			'name'       => __( 'Bot Token', 'hm-coffee-break' ),
			'id'         => $prefix . 'oauth',
			'type'       => 'text',
			'desc'       => __( 'Bot token for the Slackbot', 'hm-coffee-break' ),
		] );
	}

}

$coffee_break_options = new Coffee_Break_Options;
