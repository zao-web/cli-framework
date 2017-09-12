<?php
/**
 * Plugin Name: Example WP-CLI Plugin
 * Plugin URI:  https://github.com/zao-web/cli-framework
 * Description: An example plugin built on top of the CLI Framework
 * Version:     0.1.0
 * Author:      Zao
 * Author URI:  https://zao.is
 * Donate link: https://zao.is
 * License:     GPLv2
 *
 * @link https://zao.is
 */

/**
 * Copyright (c) 2016 Zao (email : hello@zao.is)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/**
 * Autoloads files with classes when needed
 *
 * @since  3.0.0
 * @param  string $class_name Name of the class being requested.
 * @return void
 */
function example_wp_cli_autoload_classes( $class_name ) {

	// project-specific namespace prefix
	$prefix = 'Example_WP_CLI\\';

	// does the class use the namespace prefix?
	$len = strlen( $prefix );

	if ( 0 !== strncmp( $prefix, $class_name, $len ) ) {
		// no, move to the next registered autoloader
		return;
	}

	// base directory for the namespace prefix
	$base_dir = trailingslashit( example_wp_cli()->path );

	// get the relative class name
	$relative_class = substr( $class_name, $len );

	/*
	 * replace the namespace prefix with the base directory, replace namespace
	 * separators with directory separators in the relative class name, replace
	 * underscores with dashes, and append with .php
	 */
	$path = strtolower( str_replace( array( '\\', '_' ), array( '/', '-' ), $relative_class ) );
	$file = $base_dir . $path . '.php';

	// if the file exists, require it
	if ( file_exists( $file ) ) {
		require $file;
	}
}

spl_autoload_register( 'example_wp_cli_autoload_classes' );

/**
 * Main initiation class
 *
 * @since  0.1.0
 */
final class Example_WP_CLI_Plugin {

	/**
	 * Current version
	 *
	 * @var  string
	 * @since  0.1.0
	 */
	const VERSION = '0.1.0';

	/**
	 * URL of plugin directory
	 *
	 * @var string
	 * @since  0.1.0
	 */
	protected $url = '';

	/**
	 * Path of plugin directory
	 *
	 * @var string
	 * @since  0.1.0
	 */
	protected $path = '';

	/**
	 * Plugin basename
	 *
	 * @var string
	 * @since  0.1.0
	 */
	protected $basename = '';

	/**
	 * Singleton instance of plugin
	 *
	 * @var Example_WP_CLI_Plugin
	 * @since  0.1.0
	 */
	protected static $single_instance = null;

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @since  0.1.0
	 * @return Example_WP_CLI_Plugin A single instance of this class.
	 */
	public static function get_instance() {
		if ( null === self::$single_instance ) {
			self::$single_instance = new self();
		}

		return self::$single_instance;
	}

	/**
	 * Sets up our plugin
	 *
	 * @since  0.1.0
	 */
	protected function __construct() {
		$this->basename = plugin_basename( __FILE__ );
		$this->url      = plugin_dir_url( __FILE__ );
		$this->path     = plugin_dir_path( __FILE__ );
	}

	/**
	 * Attach other plugin classes to the base plugin class.
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function plugin_classes() {
		if ( defined( 'WP_CLI' ) && WP_CLI ) {

			Example_WP_CLI\CLI\Commands::set_log_dir( $this->path . 'logs' );

			/*
			 * To see commands, `wp example_wp_cli`.
			 * Then, for more information on a specific command, 'wp help example_wp_cli <command>'.
			 */
			WP_CLI::add_command( 'example_wp_cli', 'Example_WP_CLI\\CLI\\Commands' );
		}
	}

	/**
	 * Add hooks and filters
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function hooks() {
		add_action( 'init', array( $this, 'init' ), 1 );
	}

	/**
	 * Init hooks
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function init() {

		// initialize plugin classes
		$this->plugin_classes();
	}

	/**
	 * Magic getter for our object.
	 *
	 * @since  0.1.0
	 * @param string $field Field to get.
	 * @throws Exception Throws an exception if the field is invalid.
	 * @return mixed
	 */
	public function __get( $field ) {
		if ( 'version' === $field ) {
			return self::VERSION;
		}

		return $this->$field;
	}
}


/**
 * Grab the Example_WP_CLI_Plugin object and return it.
 * Wrapper for Example_WP_CLI_Plugin::get_instance()
 *
 * @since  0.1.0
 * @return Example_WP_CLI_Plugin  Singleton instance of plugin class.
 */
function example_wp_cli() {
	return Example_WP_CLI_Plugin::get_instance();
}

// Kick it off.
add_action( 'plugins_loaded', array( example_wp_cli(), 'hooks' ) );

register_activation_hook( __FILE__, array( 'Example_WP_CLI_Plugin', '_activate' ) );
register_deactivation_hook( __FILE__, array( 'Example_WP_CLI_Plugin', '_deactivate' ) );
