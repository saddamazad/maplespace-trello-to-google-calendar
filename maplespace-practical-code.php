<?php
/**
 * Plugin Name:       Maplespace Practical Code Plugin
 * Plugin URI:        N/A
 * Description:       This plugin allows a user to create an automation that will insert events on their Google Calendar when they add a new card to a column in a Trello board.
 * Version:           1.0.0
 * Requires at least: 5.6
 * Requires PHP:      7.2
 * Author:            Saddam Hossain Azad
 * Author URI:        N/A
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        N/A
 * Text Domain:       maplespace-practical-code
 * Domain Path:       /languages
 */
 
defined('ABSPATH') or die("you do not have access to this page!");

class Maplespace_Practical_Code {

	/* Constructor for the class */
	function __construct() {
		add_action('plugins_loaded', array(&$this, 'define_constants'));
		add_action('plugins_loaded', array(&$this, 'mpc_load_textdomain'));
		add_action('admin_init', array(&$this, 'mpc_load_functions'));

		add_action('admin_menu', array(&$this, 'mpc_settings_page'));
		add_action('admin_enqueue_scripts', array(&$this, 'mpc_enqueue_scripts'), 10);

		add_action('admin_init', array(&$this, 'mpc_create_webhook_file'));
		add_filter('plugin_action_links_' . plugin_basename(__FILE__), array(&$this,'mpc_settings_page_link'));
		
		register_deactivation_hook( __FILE__, array(&$this, 'mpc_remove_webhook_file') );
	}


	/**
     * Define plugin constants
     */
	public function define_constants() {	
		// Define contants
		define('MPC_ROOT', dirname(__FILE__));
		define('MPC_URL', plugins_url( 'maplespace-practical-code/' ));
	}

	/**
     * Load plugin text domain
     */
	function mpc_load_textdomain() {
		load_plugin_textdomain( 'mpc_textdomain', false, MPC_ROOT . '/languages' );
	}

	/**
     * Register plugin settings page
     */
	public function mpc_settings_page() {
		add_submenu_page( 'options-general.php', __( 'MPC Settings', 'maplespace-practical-code' ), __( 'MPC Settings', 'maplespace-practical-code' ), 'manage_options', 'mpc-settings', array(&$this, 'mpc_settings_plug_page'));
	}

	/**
     * Include plugin settings page
     */
	public function mpc_settings_plug_page() {
		require_once( MPC_ROOT . '/inc/mpc-settings.php');
	}			

	/**
     * Include all the required functions
     *
     */
	public function mpc_load_functions() {			
		$include_path = MPC_ROOT . '/inc/';		
		include_once($include_path . 'mpc-functions.php');
	}

	/**
     * MPC ajax script load.
     */	
	public function mpc_enqueue_scripts() {
		wp_enqueue_script('jquery');

		$ajaxurl = admin_url( 'admin-ajax.php' );
		$ajax_nonce = wp_create_nonce( 'Maplespace_Practical_Code' );

		wp_localize_script( 'jquery', 'mpcObj', array( 'ajaxurl' => $ajaxurl, 'ajax_nonce' => $ajax_nonce ) );	

		wp_enqueue_style( 'mpc-style', plugins_url('/css/style.css', __FILE__ ) );
		wp_enqueue_script( 'mpc-script', plugins_url('/js/script.js', __FILE__ ) );
	}

	/**
     * Add pluign settings page link
     */	
	public function mpc_settings_page_link( $links ) {
		$url = admin_url("options-general.php?page=mpc-settings");
		$settings_link = '<a href="' . $url . '">' . __('Settings', 'maplespace-practical-code') . '</a>';
		$links[] = $settings_link;
		return $links;
	}

	/**
     * Create webhook file
     *
     */
	public function mpc_create_webhook_file() {			
		$include_path = MPC_ROOT . '/inc/';		
		include_once($include_path . 'mpc-webhook.php');
	}
	
	/**
     * Delete webhook file
     *
     */
	public function mpc_remove_webhook_file() {
		$filename = trailingslashit( ABSPATH )."wp-trello-webhook.php";
		if( file_exists($filename) ) {
			unlink($filename);
		}
	}
}

global $Maplespace_Practical_Code;
$Maplespace_Practical_Code = new Maplespace_Practical_Code();