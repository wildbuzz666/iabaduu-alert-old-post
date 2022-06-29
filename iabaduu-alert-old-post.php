<?php

/*
 Plugin Name: iabaduu alert old post
 Plugin URI:
 Description: Display a notification when a post is old
 Author: iabaduu srl
 Version: 1.0
 Author URI: http://iabaduu.com/
 Credits: https://code.tutsplus.com/tutorials/how-to-build-a-wordpress-plugin-to-identify-old-posts--cms-20642
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

		// add the plugin stylesheet to header
		add_action('wp_head', array($this, 'stylesheet'));

		// display notification above post
		add_filter('the_content', array($this, 'displayNotification'));

	}

	public function iaop_settings_default_values() {
		$iaop_plugin_options = array(
		'notification' => 'This post hasn\'t been updated in over 12 month.',
		'months' => 12
		);
		update_option('apo_alert_old_post', $iaop_plugin_options);
	}

	// get option value from the database
	public function databaseValues() {
		$options = get_option('apo_alert_old_post');
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
		echo '<h2>Alert Post is Old</h2>';
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
		register_setting('iaop_settings_group', 'apo_alert_old_post');
	}

	// ------------------------------------------------------------------
	// Settings section callback function
	// ------------------------------------------------------------------

	public function iaop_notification() {
		// call database values just like global in procedural
		$this -> databaseValues();
		echo '<textarea id="notification" cols="50" rows="3" name="apo_alert_old_post[notification]">';
		echo esc_attr($this -> _notification);
		echo '</textarea>';

	}

	public function iaop_months() {
		// call database values
		$this -> databaseValues();
		echo '<input type="number" id="months" name="apo_alert_old_post[months]" value="' . esc_attr($this -> _months) . '">';

	}

	// ------------------------------------------------------------------
	// Plugin functions
	// ------------------------------------------------------------------

	// plugin Stylesheet
	public function stylesheet() {
		echo <<<TREE
		<!-- Alert post is old (author: http://iabaduu.com) -->
	<style type="text/css">
	.oldPost {
		padding-top: 8px;
		padding-bottom: 8px;
		background-color: white;
		color: red;
		border: 1px solid;
		padding: 4px 12px 12px;
		margin-bottom: 20px;
		border-radius: 6px;
		}

		span.oldtext  {
		padding-top: 0px;
		color: red;
		margin-left: 20px;
		}
	</style>
	<!-- /Alert post is old -->

TREE;

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
		$month = date('Y') - get_post_time('Y', true, $post -> ID);

		// show notification only on post
		if (is_single()) :
			if ($month > $setMonth) {
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
