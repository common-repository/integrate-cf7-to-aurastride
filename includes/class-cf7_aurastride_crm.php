<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://https://profiles.wordpress.org/vsourz1td/
 * @since      1.0.0
 *
 * @package    Cf7_aurastride_crm
 * @subpackage Cf7_aurastride_crm/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Cf7_aurastride_crm
 * @subpackage Cf7_aurastride_crm/includes
 * @author     Vsourz Digital <wp.support@vsourz.com>
 */
class Cf7au_aurastride_crm {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Cf7au_aurastride_crm_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		if ( defined( 'CF7AU_VERSION' ) ) {
			$this->version = CF7AU_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'cf7_aurastride_crm';

		$this->cf7au_load_dependencies();
		$this->cf7au_set_locale();
		$this->cf7au_define_admin_hooks();
		$this->cf7au_define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Cf7au_aurastride_crm_Loader. Orchestrates the hooks of the plugin.
	 * - Cf7au_aurastride_crm_i18n. Defines internationalization functionality.
	 * - Cf7au_aurastride_crm_Admin. Defines all hooks for the admin area.
	 * - Cf7au_aurastride_crm_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function cf7au_load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cf7_aurastride_crm-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cf7_aurastride_crm-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-cf7_aurastride_crm-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-cf7_aurastride_crm-public.php';
		
		/**
		 * The file responsible for defining all core functions of this plugin
		 */

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/cf7-to-aurastride-function.php';
		
		/**
		 * The file responsible for calling aurastride API
		 */

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-aurastride-api.php';
		

		$this->loader = new Cf7au_aurastride_crm_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Cf7_aurastride_crm_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function cf7au_set_locale() {

		$plugin_i18n = new Cf7au_aurastride_crm_i18n();

		$this->loader->cf7au_add_action( 'plugins_loaded', $plugin_i18n, 'cf7au_load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function cf7au_define_admin_hooks() {

		$plugin_admin = new Cf7au_aurastride_crm_Admin( $this->cf7au_get_plugin_name(), $this->cf7au_get_version() );

		$this->loader->cf7au_add_action( 'admin_enqueue_scripts', $plugin_admin, 'cf7au_enqueue_styles' );
		$this->loader->cf7au_add_action( 'admin_enqueue_scripts', $plugin_admin, 'cf7au_enqueue_scripts' );
		
		//Add Menu to the Wordpress
		$this->loader->cf7au_add_action( 'admin_menu',  $plugin_admin, 'cf7au_api_settings_menu');

		//Ajax callback functions
		$this->loader->cf7au_add_action( 'wp_ajax_nopriv_cf7_form_fields_template', $plugin_admin, 'cf7au_form_fields_template_callback' );
		$this->loader->cf7au_add_action( 'wp_ajax_cf7_form_fields_template', $plugin_admin, 'cf7au_form_fields_template_callback' );
		
		//Ajax callback functions for getting the aurastride form fields
		$this->loader->cf7au_add_action( 'wp_ajax_nopriv_vsz_get_af_form_fields_data', $plugin_admin, 'cf7au_vsz_get_af_form_fields_data' );
		$this->loader->cf7au_add_action( 'wp_ajax_vsz_get_af_form_fields_data', $plugin_admin, 'cf7au_vsz_get_af_form_fields_data' );

		//Ajax callback functions for getting the aurastride form saved fields
		$this->loader->cf7au_add_action( 'wp_ajax_nopriv_vsz_get_af_form_saved_fields_data', $plugin_admin, 'cf7au_vsz_get_af_form_saved_fields_data' );
		$this->loader->cf7au_add_action( 'wp_ajax_vsz_get_af_form_saved_fields_data', $plugin_admin, 'cf7au_vsz_get_af_form_saved_fields_data' );
		
		//setup a Cron for send enquiry data to aurastride CRM
		if(CF7AU_ACF7DB_ACTIVE && !CF7AU_SEND_DIRECT ){
			
			$this->loader->cf7au_add_action( 'init', $plugin_admin,'cf7au_aurastride_crm_register_cron_for_api_callback' );
			$this->loader->cf7au_add_filter( 'cron_schedules', $plugin_admin, 'cf7au_aurastride_crm_cron_schedules_callback',10,1);	
			$this->loader->cf7au_add_action( 'cf7au_aurastride_crm_api_enquiry_submission', $plugin_admin,'cf7au_aurastride_crm_api_enquiry_submission_cron_callback',10);
			$this->loader->cf7au_add_action( 'cf7au_aurastride_crm_form_api_enquiry_submission', $plugin_admin,'cf7au_aurastride_crm_form_api_enquiry_submission_cron_callback',10);
		}	
			//Add additional table header here
			$this->loader->cf7au_add_action('vsz_cf7_admin_after_heading_field',$plugin_admin, 'cf7au_aurastride_crm_admin_after_heading_field_callback', 30);
			
			//Display Resend Data button here if the data is not sent beside the edit button
			$this->loader->cf7au_add_action('vsz_cf7_admin_after_body_field',$plugin_admin, 'cf7au_aurastride_crm_admin_after_body_edit_field_func', 30, 3);
			//Ajax submission for sending the data to aurastride
			$this->loader->cf7au_add_action('wp_ajax_vsz_send_pending_aura_data',$plugin_admin, 'cf7au_aurastride_crm_admin_send_pending_aura_data');

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function cf7au_define_public_hooks() {

		$plugin_public = new Cf7au_aurastride_crm_Public( $this->cf7au_get_plugin_name(), $this->cf7au_get_version() );

		//$this->loader->cf7au_add_action( 'wp_enqueue_scripts', $plugin_public, 'cf7au_enqueue_styles' );
		//$this->loader->cf7au_add_action( 'wp_enqueue_scripts', $plugin_public, 'cf7au_enqueue_scripts' );
		
		//Define contact form action which is call before mail is trigger to send the data to CRM
		
		if(CF7AU_ACF7DB_ACTIVE && !CF7AU_SEND_DIRECT){
			$this->loader->cf7au_add_filter('vsz_cf7_modify_form_before_insert_data',$plugin_public,'cf7au_to_aurastride_setup_hidden_fields',10,1);
		}
		else if(CF7AU_ACF7DB_ACTIVE && CF7AU_SEND_DIRECT){
			$this->loader->cf7au_add_filter('vsz_cf7_modify_form_before_insert_data',$plugin_public,'cf7au_to_aurastride_enquiry_send_direct',10,1);
		}
		else{
			$this->loader->cf7au_add_action('wpcf7_before_send_mail',$plugin_public,'cf7au_to_aurastride_enquiry_before_send_email',9,1);
		}
		

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function cf7au_run() {
		$this->loader->cf7au_run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function cf7au_get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Cf7au_aurastride_crm_Loader    Orchestrates the hooks of the plugin.
	 */
	public function cf7au_get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function cf7au_get_version() {
		return $this->version;
	}

}