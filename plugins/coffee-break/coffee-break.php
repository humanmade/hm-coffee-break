<?php
/**
 * Plugin Name: CoffeeBreak
 * Plugin URI: https://github.com/humanmade/hm-coffee-break
 * Description: Human Made Roulette
 * Version: 0.1.0
 * Author: Human Made
 * Text Domain: hm-coffee-break
 *
 * @package hm-coffee-break
 */

class Coffee_Break {

	public function __construct() {
		// Initiate REST Endpoint.
		add_action( 'rest_api_init', [ $this, 'action_register_endpoints' ] );

		// Register Custom Post Type.
		add_action( 'init', [ $this, 'action_register_custom_post_type' ] );

		// Save Meta Box Data.
		add_action( 'save_post_human', [ $this, 'action_save_meta_box_data' ], 10, 2 );

		// Include required files.
		$this->includes();
	}

	private function includes() {
		require_once __DIR__ . '/inc/slack.php' ;
		require_once __DIR__ . '/vendor/cmb2/init.php';
	}

	/**
	 * Action to Register Meta Boxes for Humans CPT.
	 */
	public function action_register_human_meta_boxes() {
		add_meta_box( 'human-timezone', __( 'Human Timezone', 'hm-coffee-break' ), [
			$this,
			'meta_box_timezone'
		], self::POST_TYPE_HUMAN, 'normal', 'default' );

		add_meta_box( 'human-availability', __( 'Human Availability', 'hm-coffee-break' ), [
			$this,
			'meta_box_availability'
		], self::POST_TYPE_HUMAN, 'normal', 'default' );
	}

	/**
	 * HTML Output for TimeZone Meta Box.
	 */
	public function meta_box_timezone() {
		$human_timezone = $this->get_timezone_for_human( get_the_ID() );

		$timezones = DateTimeZone::listIdentifiers( DateTimeZone::ALL );

		wp_nonce_field( basename( __FILE__ ), 'human_meta_box_nonce' );

		echo '<select name="human-timezone">';

		foreach ( $timezones as $timezone ) {
			echo '<option ' . selected( $human_timezone, $timezone ) . '>' . $timezone . '</option>';
		}

		echo '</select>';
	}

	/**
	 * HTML Output for Availability Meta Box.
	 */
	public function meta_box_availability() {
		$human_availability = $this->get_available_hours_for_human( get_the_ID() );

		wp_nonce_field( basename( __FILE__ ), 'human_meta_box_nonce' );

		echo '<textarea name="human-availability">' . implode( "\n", $human_availability ) . '</textarea>';
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
			$this->set_timezone_for_human( $post_id, $_POST['human-timezone'] );
		}

		if ( isset( $_POST['human-availability'] ) ) {
			// Create array from plaintext input (expected separation by carriage return).
			$available_times = explode( "\n", wp_filter_post_kses( $_POST['human-availability'] ) );

			// Trim each entry in the array.
			$available_times = array_map( 'trim', $available_times );

			$this->set_available_hours_for_human( $post_id, $available_times );
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
	 * Create human endpoint.
	 *
	 * @TODO: Create Human post.
	 */
	public function create_human() {

	}

	/**
	 * API callback for GET /coffeebreak/humans/
	 *
	 * @return array
	 */
	public function get_humans() {
		$human_query  = new WP_Query( [
			'post_type' => 'human',
		] );

		$humans = [];

		/** @var \WP_Post $human */
		foreach ( $human_query->posts as $human ) {
			$humans[] = [
				'ID'           => $human->ID,
				'username'     => $human->post_name,
				'user_created' => $human->post_date_gmt,
				'timezone'     => $this->get_timezone_for_human( $human->ID ),
				'availability' => $this->get_available_hours_for_human( $human->ID ),
			];
		}

		return $humans;
	}

	/**
	 * Update Human.
	 *
	 * @TODO: Update Human exposed fields (timezone, available hours, etc.)
	 */
	public function update_human() {

	}

	/**
	 * Delete Human.
	 *
	 * @TODO: Delete Human (no auth for now).
	 */
	public function delete_human() {

	}

	/**
	 * Get TimeZone for Human.
	 *
	 * @param int $human_id
	 *
	 * @return array
	 */
	public function get_timezone_for_human( int $human_id ) {
		$timezone = get_post_meta( $human_id, 'human_timezone', true );

		return $timezone;
	}

	/**
	 * Set TimeZone for Human.
	 *
	 * @param int $human_id
	 * @param string $timezone
	 */
	public function set_timezone_for_human( int $human_id, string $timezone ) {
		update_post_meta( $human_id, 'human_timezone', wp_filter_post_kses( $timezone ) );
	}

	/**
	 * Get available hours for Human.
	 *
	 * @param int $human_id
	 *
	 * @return array
	 */
	public function get_available_hours_for_human( int $human_id ) {
		$available_hours = (array) get_post_meta( $human_id, 'human_availability', true );

		return $available_hours;
	}

	/**
	 * Set available hours for Human.
	 *
	 * @param int $human_id
	 * @param array $available_times
	 */
	public function set_available_hours_for_human( int $human_id, array $available_times ) {
		update_post_meta( $human_id, 'human_availability', $available_times );
	}

	/**
	 * Get matching Humans for Human ID.
	 *
	 * @TODO: Build matching algorithm here.
	 *
	 * @param int $human_id
	 */
	public function get_matching_humans_for_human( int $human_id ) {

	}

	/**
	 * Set whether a Human has met another Human.
	 *
	 * @TODO: Set post meta with an array of post IDs (human IDs) that a Human has met. [ 97, 68 ]
	 *
	 * @param array $human_id
	 */
	public function set_human_has_met_human( array $human_id ) {

	}

	/**
	 * Set Human has Cancelled.
	 *
	 * @TODO: Set post meta with an array of user IDs mapped to a count for each user the Human has cancelled against. [ 97 => 7, 68 => 1 ]
	 *
	 * @param int $canceler_human_id
	 * @param int $canceled_human_id
	 */
	public function set_human_has_cancelled( int $canceler_human_id, int $canceled_human_id ) {

	}

	/**
	 * Register Action for Human Custom Post Type.
	 */
	public function action_register_custom_post_type() {
		register_post_type(
			'human',
			[
				'labels'               => [
					'name'          => __( 'Humans', 'hm-coffee-break' ),
					'singular_name' => __( 'Human', 'hm-coffee-break' ),
					'add_new_item'  => __( 'Add New Human', 'hm-coffee-break' ),
					'edit_item'     => __( 'Edit Human', 'hm-coffee-break' ),
					'new_item'      => __( 'New Human', 'hm-coffee-break' ),
					'view_item'     => __( 'View Human', 'hm-coffee-break' ),
					'search_items'  => __( 'Search Humans', 'hm-coffee-break' ),
					'not_found'     => __( 'No humans found', 'hm-coffee-break' ),
					'all_items'     => __( 'All Humans', 'hm-coffee-break' ),
				],
				'public'               => false,
				'show_ui'              => true,
				'rewrite'              => false,
				'menu_icon'            => 'dashicons-smiley',
				'supports'             => [ 'title' ],
				'taxonomies'           => [],
				'register_meta_box_cb' => [ $this, 'action_register_human_meta_boxes' ]
			]
		);
	}
}

$coffee_break = new Coffee_Break();
