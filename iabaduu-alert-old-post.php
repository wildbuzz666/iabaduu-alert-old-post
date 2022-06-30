<?php

/*
 Plugin Name: iabaduu alert old post
 Plugin URI:
 Description: Display a notification when a post is old
 Author: iabaduu srl
 Version: 1.0
 Author URI: http://iabaduu.com/
 */

/**
 *
 */
class AlertOldPost {

	protected $_notification;
	protected $_months;

	// hook all plugin action and filter
	function __construct() {
		// Initialize setting options on activation
		register_activation_hook(__FILE__, array($this, 'iaop_settings_default_values'));

		// register Menu
		add_action('admin_menu', array($this, 'iaop_settings_menu'));

		// hook plugin section and field to admin_init
		add_action('admin_init', array($this, 'pluginOption'));

		// register the plugin stylesheet
		add_action( 'wp_enqueue_scripts', array($this, 'load_iaop_stylesheet') );

		// display notification above post
		add_filter('the_content', array($this, 'displayNotification'));

	}

	public function iaop_settings_default_values() {
		$iaop_plugin_options = array(
		'notification' => 'This post hasn\'t been updated in over 12 month.',
		'months' => 12
		);
		update_option('iaop_alert_old_post', $iaop_plugin_options);
	}

	// get option value from the database
	public function databaseValues() {
		$options = get_option('iaop_alert_old_post');
		$this -> _notification = $options['notification'];
		$this -> _months = $options['months'];
	}

	// Adding Submenu to settings
	public function iaop_settings_menu() {
		add_options_page('Alert Old Post',
		'Alert Old Post',
		'manage_options',
		'iaop-alert-post-old',
		array($this, 'alert_post_old_function')
		);
	}

	// setttings form
	public function alert_post_old_function() {
		echo '<div class="wrap">';
		screen_icon();
		echo '<h2>Alert Old Post</h2>';
		echo '<form action="options.php" method="post">';
		do_settings_sections('iaop-alert-post-old');
		settings_fields('iaop_settings_group');
		submit_button();

	}

	// plugin field and sections
	public function pluginOption() {
		add_settings_section('iaop_settings_section',
		'Plugin Options',
		null,
		'iaop-alert-post-old'
		);

		add_settings_field('notification',
		'<label for="notification">Notification to display when post is old</label>',
		array($this, 'iaop_notification'),
		'iaop-alert-post-old',
		'iaop_settings_section'
		);

		add_settings_field('months',
		'<label for="months">Number of months for a post to be considered old</label>',
		array($this, 'iaop_months'),
		'iaop-alert-post-old',
		'iaop_settings_section'
		);

		// register settings
		register_setting('iaop_settings_group', 'iaop_alert_old_post');
	}

	// ------------------------------------------------------------------
	// Settings section callback function
	// ------------------------------------------------------------------

	public function iaop_notification() {
		// call database values just like global in procedural
		$this -> databaseValues();
		echo '<textarea id="notification" cols="50" rows="3" name="iaop_alert_old_post[notification]">';
		echo esc_attr($this -> _notification);
		echo '</textarea>';

	}

	public function iaop_months() {
		// call database values
		$this -> databaseValues();
		echo '<input type="number" id="months" name="iaop_alert_old_post[months]" value="' . esc_attr($this -> _months) . '">';

	}

	// ------------------------------------------------------------------
	// Plugin functions
	// ------------------------------------------------------------------

	// plugin Stylesheet
	public function load_iaop_stylesheet(){
		  wp_register_style('iaop', plugin_dir_url( __FILE__ ).'iaop.css');
			wp_enqueue_style('iaop');
	}


	// display notification above post
	public function displayNotification($content) {
		global $post;
		// call database values
		$this -> databaseValues();

		// get settings month
		$setMonth = $this -> _months;

		// get notification text
		$notification = $this -> _notification;
		// calculate post age

		$unixdate = get_post_time('U', true, $post -> ID);
		$postdate = date('Y-m-d', $unixdate);
		$today = date('Y-m-d');
		$date1 = strtotime($postdate);
  	$date2 = strtotime($today);
		$diff = abs($date2 - $date1);
		$months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));

		// show notification only on post
		if (is_single()) :
			if ($months >= $setMonth) {
				echo '<div class="oldPost">';
				echo '<i class="fa fa-exclamation-circle" aria-hidden="true" role="img"></i>';
				echo "<span class='oldtext'>$notification</span>";
				echo '</div>';
			}
		endif;

		return $content;
	}

}

// instantiate the class
new AlertOldPost;
