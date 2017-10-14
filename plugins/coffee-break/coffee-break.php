<?php
/**
 * Plugin Name: CoffeeBreak
 * Plugin URI: https://github.com/humanmade/hm-coffee-break
 * Description: Human Made Roulette
 * Version: 0.1.0
 * Author: Human Made
 *
 * @package hm-coffee-break
 */

class Coffee_Break {

	const POST_TYPE_HUMAN = 'human';
	const COFFEE_BREAK_OAUTH_TOKEN = 'xoxp-2178342187-92343588914-255718838464-3851199b80f1d8010d96b569e006452f';
	const COFFEE_BREAK_BOT_TOKEN = 'xoxb-257455469751-OdZe7IsybZalrtaTOM9rslkB';

	public function __construct() {

		// Initiate REST Endpoint.
		add_action( 'rest_api_init', [ $this, 'action_register_endpoints' ] );

		// Register Custom Post Type.
		add_action( 'init', [ $this, 'action_register_custom_post_type' ] );
	}

	/**
	 * Action to Register API endpoints.
	 */
	public function action_register_endpoints() {
		register_rest_route( 'coffeebreak', '/humans/', [
			'methods'  => WP_REST_SERVER::READABLE,
			'callback' => [ $this, 'get_humans' ]
		] );
	}

	/**
	 * API callback for /coffeebreak/humans/
	 *
	 * @return array
	 */
	public function get_humans() {
		$human_query  = new WP_Query( [
			'post_type' => self::POST_TYPE_HUMAN,
		] );

		$humans = [];

		/** @var \WP_Post $human */
		foreach ( $human_query->posts as $human ) {
			$humans[] = [
				'ID'           => $human->ID,
				'username'     => $human->post_name,
				'user_created' => $human->post_date_gmt
			];
		}

		return $humans;
	}

	/**
	 * Register Action for Human Custom Post Type.
	 */
	public function action_register_custom_post_type() {
		register_post_type(
			self::POST_TYPE_HUMAN,
			[
				'labels'     => [
					'name'          => __( 'Humans', 'coffee' ),
					'singular_name' => __( 'Human', 'coffee' ),
					'add_new_item'  => __( 'Add New Human', 'coffee' ),
					'edit_item'     => __( 'Edit Human', 'coffee' ),
					'new_item'      => __( 'New Human', 'coffee' ),
					'view_item'     => __( 'View Human', 'coffee' ),
					'search_items'  => __( 'Search Humans', 'coffee' ),
					'not_found'     => __( 'No humans found', 'coffee' ),
					'all_items'     => __( 'All Humans', 'coffee' ),
				],
				'public'     => false,
				'show_ui'    => true,
				'rewrite'    => false,
				'supports'   => [],
				'taxonomies' => [],
			]
		);
	}
}

$coffee_break = new Coffee_Break();
