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

require_once( __DIR__ . '/inc/slack.php' );

class Coffee_Break {

	const POST_TYPE_HUMAN = 'human';
	const COFFEE_BREAK_OAUTH_TOKEN = 'xoxp-2178342187-92343588914-255718838464-3851199b80f1d8010d96b569e006452f';
	const COFFEE_BREAK_BOT_TOKEN = 'xoxb-257455469751-OdZe7IsybZalrtaTOM9rslkB';

	public function __construct() {
		// Initiate REST Endpoint.
		add_action( 'rest_api_init', [ $this, 'action_register_endpoints' ] );

		// Register Custom Post Type.
		add_action( 'init', [ $this, 'action_register_custom_post_type' ] );

		// Save Meta Box Data.
		add_action( 'save_post_human', [ $this, 'action_save_meta_box_data' ], 10, 2 );
	}

	/**
	 * Action to Register Meta Boxes for Humans CPT.
	 */
	public function action_register_human_meta_boxes() {
		add_meta_box( 'human-timezone', __( 'Human Timezone' ), [
			$this,
			'meta_box_timezone'
		], self::POST_TYPE_HUMAN, 'side', 'default' );
	}

	/**
	 * HTML Output for TimeZone Meta Box.
	 */
	public function meta_box_timezone() {
		$human_timezone = get_post_meta( get_the_ID(), 'human_timezone', true );

		$timezones = DateTimeZone::listIdentifiers( DateTimeZone::ALL );

		wp_nonce_field( basename( __FILE__ ), 'human_meta_box_nonce' );

		echo '<select name="human-timezone">';

		foreach ( $timezones as $timezone ) {
			echo '<option ' . selected( $human_timezone, $timezone ) . '>' . $timezone . '</option>';
		}

		echo '</select>';
	}

	/**
	 * Action for Saving Meta Box data.
	 *
	 * @param $post_id
	 */
	public function action_save_meta_box_data( $post_id ) {
		if ( ! isset( $_POST['human_meta_box_nonce'] ) ||
			 ! wp_verify_nonce( $_POST['human_meta_box_nonce'], basename( __FILE__ ) ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}


		if ( isset( $_POST['human-timezone'] ) ) {
			update_post_meta( $post_id, 'human_timezone', wp_filter_post_kses( $_POST['human-timezone'] ) );
		}
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
		$human_query = new WP_Query( [
			'post_type' => self::POST_TYPE_HUMAN,
		] );

		$humans = [];

		/** @var \WP_Post $human */
		foreach ( $human_query->posts as $human ) {
			$humans[] = [
				'ID'           => $human->ID,
				'username'     => $human->post_name,
				'user_created' => $human->post_date_gmt,
				'timezone'     => get_post_meta( $human->ID, 'human_timezone', true ),
			];
		}

		return $humans;
	}

	public function get_available_hours_for_human( int $human_id ) {

	}

	public function set_available_hours_for_human( int $human_id, array $available_times ) {

	}

	public function get_matching_humans_for_human( int $human_id ) {

	}

	public function set_human_has_met_human( array $human_id ) {

	}

	public function set_human_has_cancelled( int $canceler_human_id, int $canceled_human_id ) {

	}

	/**
	 * Register Action for Human Custom Post Type.
	 */
	public function action_register_custom_post_type() {
		register_post_type(
			self::POST_TYPE_HUMAN,
			[
				'labels'               => [
					'name'          => __( 'Humans' ),
					'singular_name' => __( 'Human' ),
					'add_new_item'  => __( 'Add New Human' ),
					'edit_item'     => __( 'Edit Human' ),
					'new_item'      => __( 'New Human' ),
					'view_item'     => __( 'View Human' ),
					'search_items'  => __( 'Search Humans' ),
					'not_found'     => __( 'No humans found' ),
					'all_items'     => __( 'All Humans' ),
				],
				'public'               => false,
				'show_ui'              => true,
				'rewrite'              => false,
				'menu_icon'            => 'dashicons-smiley',
				'supports'             => [],
				'taxonomies'           => [],
				'register_meta_box_cb' => [ $this, 'action_register_human_meta_boxes' ]
			]
		);
	}
}

$coffee_break = new Coffee_Break();
