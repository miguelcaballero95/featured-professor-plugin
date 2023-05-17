<?php

/*
  Plugin Name: Featured Professor Block Type
  Version: 1.0
  Author: Mike Caballero
  Text Domain: featured-professor
  Domain Path: /languages
*/

if (!defined('ABSPATH')) exit; // Exit if accessed directly

require_once plugin_dir_path(__FILE__) . "inc/render.php";
require_once plugin_dir_path(__FILE__) . "inc/relatedPostsHTML.php";

class FeaturedProfessor
{
    function __construct()
    {
        add_action('init', [$this, 'onInit']);
        add_action('rest_api_init', [$this, 'professorHTML']);
        add_filter('the_content', [$this, 'addRelatedPosts']);
    }

    function addRelatedPosts($content)
    {
        if (is_singular('professor') && in_the_loop() && is_main_query()) {
            return $content . relatedPostsHTML(get_the_ID());
        }

        return $content;
    }

    function professorHTML()
    {
        register_rest_route('featuredProfessor/v1', 'getHTML', [
            'methods'   => WP_REST_Server::READABLE,
            'callback'  => [$this, 'getProfessorHTML']
        ]);
    }

    function getProfessorHTML($data)
    {
        return generateProfessorHTML($data['professorId']);
    }

    function onInit()
    {
        load_plugin_textdomain('featured-professor', false, dirname(plugin_basename(__FILE__)) . '/languages');
        register_meta('post', 'featuredProfessor', [
            'show_in_rest'  => true,
            'type'          => 'number',
            'single'        => false
        ]);
        wp_register_script('featuredProfessorScript', plugin_dir_url(__FILE__) . 'build/index.js', array('wp-blocks', 'wp-i18n', 'wp-editor'));
        wp_register_style('featuredProfessorStyle', plugin_dir_url(__FILE__) . 'build/index.css');

        wp_set_script_translations('featuredProfessorScript', 'featured-professor', plugin_dir_path(__FILE__) . '/languages');
        register_block_type('ourplugin/featured-professor', array(
            'render_callback' => [$this, 'renderCallback'],
            'editor_script' => 'featuredProfessorScript',
            'editor_style' => 'featuredProfessorStyle'
        ));
    }

    function renderCallback($attributes)
    {
        if ($attributes['profId']) {
            wp_enqueue_style("featuredProfessorStyle");
            return generateProfessorHTML($attributes['profId']);
        } else {
            return null;
        }
    }
}

$featuredProfessor = new FeaturedProfessor();
