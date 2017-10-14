<?php
/**
 * Plugin Name: CoffeeBreak
 * Plugin URI: https://github.com/humanmade/hm-coffee-break
 * Description: Human Made Roulette
 * Version: 0.0.1
 * Author: Human Made
 *
 * @package hm-coffee-break
 */

class Coffee_Break {

	const POST_TYPE_HUMAN = 'human';

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

		$query_args = [
			'post_type' => self::POST_TYPE_HUMAN,
		];

		$posts_query  = new WP_Query();
		$query_result = $posts_query->query( $query_args );

		$humans = [];

		/** @var \WP_Post $human */
		foreach ( $query_result as $human ) {
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
					'name'          => __( 'Humans', 'usat' ),
					'singular_name' => __( 'Human', 'usat' ),
					'add_new_item'  => __( 'Add New Human', 'usat' ),
					'edit_item'     => __( 'Edit Human', 'usat' ),
					'new_item'      => __( 'New Human', 'usat' ),
					'view_item'     => __( 'View Human', 'usat' ),
					'search_items'  => __( 'Search Humans', 'usat' ),
					'not_found'     => __( 'No humans found', 'usat' ),
					'all_items'     => __( 'All Humans', 'usat' ),
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