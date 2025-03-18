<?php

/*
  Plugin Name: Featured Professor Block Type
  Version: 1.0
  Author: Miguel Caballero
  Text Domain: featured-professor
  Domain Path: /languages
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

require_once plugin_dir_path( __FILE__ ) . "inc/render.php";
require_once plugin_dir_path( __FILE__ ) . "inc/related-posts-html.php";

class Featured_Professor {

	/**
	 * Initializes the plugin by adding hooks and filters.
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'init_plugin' ] );
		add_action( 'rest_api_init', [ $this, 'featured_professor_route' ] );
		add_filter( 'the_content', [ $this, 'add_related_posts' ] );
	}

	/**
	 * Adds related posts to the content of a professor post.
	 *
	 * @param string $content The content of the post.
	 * @return string The content of the post with related posts appended.
	 */
	public function add_related_posts( string $content ): string {
		if ( is_singular( 'professor' ) && in_the_loop() && is_main_query() ) {
			return $content . related_posts_html( get_the_ID() );
		}

		return $content;
	}

	/**
	 * Registers the REST route for the plugin.
	 */
	public function featured_professor_route(): void {
		register_rest_route( 'featuredProfessor/v1', 'getHTML', [ 
			'methods' => WP_REST_Server::READABLE,
			'callback' => [ $this, 'get_professor_html' ],
			'permission_callback' => '__return_true'
		] );
	}

	/**
	 * Returns the HTML for a professor post.
	 *
	 * @param WP_REST_Request $data The request data.
	 * @return string The HTML for the professor post.
	 */
	public function get_professor_html( WP_REST_Request $data ): string {
		return generate_professor_html( $data['professorId'] );
	}

	/**
	 * Initializes the plugin by registering the block type.
	 */
	public function init_plugin(): void {

		// Load the plugin text domain
		load_plugin_textdomain( 'featured-professor', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

		// Register the featuredProfessor meta field
		register_meta( 'post', 'featuredProfessor', [ 
			'show_in_rest' => true,
			'type' => 'number',
			'single' => false
		] );

		wp_register_script( 'featured-professor-script', plugin_dir_url( __FILE__ ) . 'build/index.js', [ 'wp-blocks', 'wp-i18n', 'wp-editor' ] );
		wp_register_style( 'featured-professor-style', plugin_dir_url( __FILE__ ) . 'build/index.css' );

		wp_set_script_translations( 'featured-professor-script', 'featured-professor', plugin_dir_path( __FILE__ ) . '/languages' );

		// Register the block type
		register_block_type( 'my-plugin/featured-professor', [ 
			'render_callback' => [ $this, 'render_callback' ],
			'editor_script' => 'featured-professor-script',
			'editor_style' => 'featured-professor-style'
		] );
	}

	/**
	 * Renders the block type.
	 *
	 * @param array $attributes The block attributes.
	 * @return string The HTML for the block.
	 */
	public function render_callback( array $attributes ): string|null {
		if ( $attributes['profId'] ) {
			wp_enqueue_style( "featured-professor-style" );
			return generate_professor_html( $attributes['profId'] );
		} else {
			return null;
		}
	}
}

$featured_professor = new Featured_Professor();
