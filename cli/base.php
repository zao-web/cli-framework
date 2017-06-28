<?php
namespace Example_WP_CLI\CLI;
use WP_CLI;
use WP_CLI\Utils as Utils;

/**
 * CLI Framework Base class.
 */
class Base {

	/**
	 * Arguments passed to command.
	 *
	 * @var array
	 */
	public $args       = array();

	/**
	 * Parameters passed to command.
	 *
	 * @var array
	 */
	public $assoc_args = array();

	/**
	 * Whether command is requestiong verbose output.
	 * Requires passing --verbose along with the command.
	 *
	 * @var boolean
	 */
	public $verbose = false;

	/**
	 * Whether command is requestiong silent output.
	 * Requires passing --silent along with the command.
	 *
	 * @var boolean
	 */
	public $silent = false;

	/**
	 * The directory for the CLI log files.
	 *
	 * @var string
	 */
	protected static $log_dir = '';

	/**
	 * The file name pattern for the log files.
	 *
	 * @var string
	 */
	protected static $log_file_name = 'cli-log.log';

	/**
	 * Constructor
	 *
	 * @since 0.1.0
	 *
	 * @param array  $args          Arguments passed to command.
	 * @param array  $assoc_args    Parameters passed to command.
	 * @param string $log_dir       The directory for the CLI log files.
	 * @param string $log_file_name The file name pattern for the log files.
	 */
	public function __construct( $args, $assoc_args, $log_dir = null, $log_file_name = null ) {
		$this->set_args( $args, $assoc_args );
		self::set_log_dir( $log_dir );
		self::set_log_file( $log_file_name );
	}

	/**
	 * Message Methods
	 */

	/**
	 * Outputs a WP_CLI success message as well as logs the message.
	 *
	 * @since  0.1.0
	 *
	 * @param  string $msg  The message to relay.
	 * @param  mixed  $data Optional data to print out.
	 *
	 * @return Base for chaining.
	 */
	public function success_message_log( $msg, $data = null ) {
		return $this->success_message( $msg, $data )->write_log( $msg, $data );
	}

	/**
	 * Outputs a WP_CLI success message.
	 *
	 * @since  0.1.0
	 *
	 * @param  string $msg  The message to relay.
	 * @param  mixed  $data Optional data to print out.
	 *
	 * @return Base for chaining.
	 */
	public function success_message( $msg, $data = null ) {
		WP_CLI::success( $data ? $msg . ': ' . print_r( $data, true ) : $msg );

		return $this;
	}

	/**
	 * Outputs a WP_CLI message if the --verbose flag is set, as well as logs the message.
	 *
	 * @since  0.1.0
	 *
	 * @param  string $msg  The message to relay.
	 * @param  mixed  $data Optional data to print out.
	 *
	 * @return Base for chaining.
	 */
	public function verbose_message_log( $msg, $data = null ) {
		return $this->verbose_message( $msg, $data )->write_log( $msg, $data );
	}

	/**
	 * Outputs a WP_CLI message if the --verbose flag is set.
	 *
	 * @since  0.1.0
	 *
	 * @param  string $msg    The message to relay.
	 * @param  mixed  $data   Optional data to print out.
	 * @param  string $method Which WP_CLI method to use for messaging.
	 *
	 * @return Base for chaining.
	 */
	public function verbose_message( $msg, $data = null, $method = 'line' ) {
		if ( $this->verbose ) {
			WP_CLI::{$method}( $data ? $msg . ': ' . print_r( $data, true ) : $msg );
		}

		return $this;
	}

	/**
	 * Outputs a WP_CLI error message if the --silent flag is set, and logs the message either way.
	 *
	 * @since  0.1.0
	 *
	 * @param  string $msg  The message to relay.
	 * @param  mixed  $data Optional data to print out.
	 *
	 * @return Base for chaining.
	 */
	public function silent_error_log( $msg, $data = null ) {
		return $this->silent_log( $msg, $data, 'error' );
	}

	/**
	 * Outputs a WP_CLI message if the --silent flag is set, and logs the message either way.
	 *
	 * @since  0.1.0
	 *
	 * @param  string $msg    The message to relay.
	 * @param  mixed  $data   Optional data to print out.
	 * @param  string $method Which WP_CLI method to use for messaging.
	 *
	 * @return Base for chaining.
	 */
	public function silent_log( $msg, $data = null, $method = 'line' ) {
		if ( $this->silent ) {
			$this->write_log( $msg, $data );
		} else {
			$this->message_log( $msg, $data, $method );
		}

		return $this;
	}

	/**
	 * Outputs a WP_CLI error message as well as logs the error.
	 *
	 * @since  0.1.0
	 *
	 * @param  string $msg    The message to relay.
	 * @param  mixed  $data   Optional data to print out.
	 *
	 * @return Base for chaining.
	 */
	public function error_message_log( $msg, $data = null ) {
		return $this->error_message( $msg, $data, $method )->write_log( $msg, $data );
	}

	/**
	 * Outputs a WP_CLI error message.
	 *
	 * @since  0.1.0
	 *
	 * @param  string $msg  The message to relay.
	 * @param  mixed  $data Optional data to print out.
	 *
	 * @return Base for chaining.
	 */
	public function error_message( $msg, $data = null ) {
		WP_CLI::error( $data ? $msg . ': ' . print_r( $data, true ) : $msg );

		return $this;
	}

	/**
	 * Outputs a WP_CLI message as well as logs the message.
	 *
	 * @since  0.1.0
	 *
	 * @param  string $msg    The message to relay.
	 * @param  mixed  $data   Optional data to print out.
	 * @param  string $method Which WP_CLI method to use for messaging.
	 *
	 * @return Base for chaining.
	 */
	public function message_log( $msg, $data = null, $method = 'line' ) {
		WP_CLI::{$method}( $data ? $msg . ': ' . print_r( $data, true ) : $msg );

		return $this->write_log( $msg, $data );
	}

	/**
	 * Logging Methods
	 */

	/**
	 * Sends a message to the log file.
	 *
	 * @since  0.1.0
	 *
	 * @param  string $msg  The message to relay.
	 * @param  mixed  $data Optional data to print out.
	 *
	 * @return Base for chaining.
	 */
	public function write_log( $msg, $data = null ) {
		$this->check_log();

		$msg .= $data ? ': ' . print_r( $data, true ) : '';
		file_put_contents( $this->log_file, '[' . date( 'd-M-Y h:i:s A', current_time( 'timestamp' ) ) . '] ' . $msg . "\n", FILE_APPEND );

		return $this;
	}

	/**
	 * Empties the wpcli log file.
	 *
	 * @since  0.1.0
	 *
	 * @return Base for chaining.
	 */
	public function empty_log() {
		file_put_contents( $this->log_file, "\n" );
		$this->success_message( 'Log emptied.' );

		return $this;
	}

	/**
	 * Deletes all the wpcli log files.
	 *
	 * @since  0.1.0
	 *
	 * @return Base for chaining.
	 */
	public function delete_logs() {
		$this->confirm( 'Are you sure you want to delete the logs?', $this->assoc_args );

		foreach ( glob( self::$log_dir . '*.log' ) as $log_file ) {
			unlink( $log_file );
		}

		$this->success_message( 'Logs deleted.' );

		return $this;
	}

	/**
	 * Creates a renamed backup of the wpcli log file.
	 *
	 * @since  0.1.0
	 *
	 * @return Base for chaining.
	 */
	public function backup_log() {
		$this->check_log();

		$file = $this->log_file;
		$count = 1;
		while ( file_exists( str_replace( '.log', '-' . $count . '.log', $file ) ) ) {
			$count++;
		}

		$new_file = str_replace( '.log', '-' . $count . '.log', $file );

		if ( ! copy( $file, $new_file ) ) {
			WP_CLI::error( "Failed to copy {$new_file}..." );
		}

		$this->success_message( "Log backed up: {$new_file}" );

		return $this;
	}

	/**
	 * Checks if the log directory/file exists and creates them.
	 *
	 * @since  0.1.0
	 *
	 * @return Base for chaining.
	 */
	public function check_log() {
		if ( ! file_exists( self::$log_dir ) ) {
			mkdir( self::$log_dir );
		}

		if ( ! file_exists( $this->log_file ) ) {
			touch( $this->log_file );
		}

		return $this;
	}

	/**
	 * WPCLI Proxy Methods
	 */

	/**
	 * Progress Bar for WP_CLI
	 *
	 * @param int    $param  Either the count to initiate, or a progress event (tick or finish)
	 * @param string $action Action being performed
	 *
	 * @return \cli\progress\Bar Progress bar object.
	 */
	public function progress_bar( $param = 0, $action = 'Migrating', $items = 'Rows' ) {
		if ( $param && is_numeric( $param ) ) {
			$this->progress_bar = Utils\make_progress_bar( "$action $param $items", $param );
		} elseif ( 'tick' == $param ) {
			$this->progress_bar->tick();
		} elseif ( 'finish' == $param ) {
			$this->progress_bar->finish();
		}

		return $this->progress_bar;
	}

	/**
	 * Pauses execution temporarily, and clears WordPress internal object caches.
	 *
	 * In long-running scripts, the internal caches on `$wp_object_cache` and `$wpdb`
	 * can grow to consume gigabytes of memory. Periodically calling this utility
	 * can help with memory management.
	 *
	 * See WP_CLI\Utils\wp_clear_object_cache() (https://github.com/wp-cli/wp-cli/blob/master/php/utils-wp.php)
	 *
	 * @since  0.1.0
	 *
	 * @param  integer $sleep_time Amount of time to pause execution. Potentially help things cool down.
	 *
	 * @return void
	 */
	public static function stop_the_insanity( $sleep_time = 0 ) {
		if ( $sleep_time ) {
			sleep( $sleep_time );
		}

		wp_clear_object_cache();
	}

	/**
	 * Proxy method for WP_CLI::confirm(). Passes in the $assoc_args property.
	 *
	 * @since  0.1.0
	 *
	 * @param  string  $question The confirmation question.
	 *
	 * @return mixed             Exits excection if "no" is passed.
	 */
	public function confirm( $question ) {
		return WP_CLI::confirm( $question, $this->assoc_args );
	}

	/**
	 * Checks the $assoc_args array for given flag.
	 *
	 * @since  0.1.0
	 *
	 * @param  mixed  $flag    The flag to check.
	 * @param  mixed  $default The default/fallback value for the flag.
	 *
	 * @return mixed           The value for that flag, or the fallback default.
	 */
	public function get_flag_value( $flag, $default = null ) {
		return Utils\get_flag_value( $this->assoc_args, $flag, $default );
	}

	/**
	 * Utilities
	 */

	/**
	 * Sets the $args, $assoc_args, $verbose, and $silent properties.
	 *
	 * @since 0.1.0
	 *
	 * @param array  $args       Arguments passed to command.
	 * @param array  $assoc_args Parameters passed to command.
	 *
	 * @return Base for chaining.
	 */
	public function set_args( $args, $assoc_args = array() ) {
		$this->args       = $args;
		$this->assoc_args = $assoc_args;
		$this->verbose    = $this->get_flag_value( 'verbose', false );
		$this->silent     = $this->get_flag_value( 'silent', true );

		return $this;
	}

	/**
	 * Sets the wpcli log directory
	 *
	 * @since 0.1.0
	 *
	 * @param string  $log_dir The log directory. Defaults to creating a "logs" directory in the current.
	 *
	 * @return $log_dir        The set log directory.
	 */
	public static function set_log_dir( $log_dir = null ) {
		self::$log_dir = trailingslashit( null === $log_dir ? __DIR__ . '/logs' : $log_dir );

		return self::$log_dir;
	}

	/**
	 * Sets the wpcli log file name and location.
	 *
	 * @since 0.1.0
	 *
	 * @param string  $log_file_name The requested log file name. Defaults to 'cli-log.log'
	 * @param string  $log_dir       The requested log directory.
	 *
	 * @return $log_file             The log file location.
	 */
	public static function set_log_file( $log_file_name = null, $log_dir = null ) {
		self::$log_file_name = ! empty( $log_file_name ) ? $log_file_name : 'cli-log.log';

		if ( null !== $log_dir || empty( self::$log_dir ) ) {
			self::set_log_dir( $log_dir );
		}

		self::$log_file = self::$log_dir . self::$log_file_name;

		return self::$log_file;
	}

	/**
	 * Make sure emails are not sent out during CLI commands.
	 *
	 * @since  0.1.0
	 *
	 * @return Base for chaining.
	 */
	protected function disable_emails() {
		add_filter( 'wp_mail', function( $atts ) {
			$atts['to'] = '';
			$atts['subject'] = '';
			$atts['message'] = '';
			$atts['headers'] = 'From: admin@localhost.dev';
			$atts['attachments'] = array();
			return $atts;
		} );
		add_filter( 'wp_mail_from', function( $from_email ) {
			return 'admin@localhost.dev';
		} );

		return $this;
	}
}
