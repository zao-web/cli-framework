<?php
namespace Example_WP_CLI\CLI;
use WP_CLI;
use WP_CLI\Utils as Utils;

/**
 * Meal Planning Plugin CLI Base
 */
class Base {

	public $args       = array();
	public $assoc_args = array();
	public $verbose    = false;
	public $silent     = false;
	public $logfile = 'cli-log.log';

	public function __construct( $args, $assoc_args, $log_dir = null ) {
		$this->set_args( $args, $assoc_args );
		$this->log_dir = trailingslashit( null === $log_dir ? __DIR__ . '/logs' : $log_dir );
		$this->log_file = $this->log_dir . $this->logfile;
	}

	public function silent_log( $msg, $data = null ) {
		return $this->silent_error( $msg, 'line', $data );
	}

	public function error( $msg, $method = 'error', $data = null ) {
		WP_CLI::{$method}( $data ? $msg . ': ' . print_r( $data, 1 ) : $msg );
		$this->write_log( $msg, $data );
	}

	public function silent_error( $msg, $method = 'error', $data = null ) {
		if ( $this->silent ) {
			return $this->log( $msg, $data );
		} else {
			$this->error( $msg, $method, $data );
		}

		return $this->silent;
	}

	public function success_log( $title, $data = null ) {
		$title .= $data ? ': ' . print_r( $data, true ) : '';
		WP_CLI::success( $title );
	}

	public function verbose_log( $title, $data = null ) {
		$this->verbose_log_only( $title, $data );
		$this->write_log( $title, $data );
	}

	public function verbose_log_only( $title, $data = null ) {
		if ( $this->verbose ) {
			$title .= $data ? ': ' . print_r( $data, 1 ) : '';
			WP_CLI::line( $title );
		}
	}

	public function log( $title, $data = null ) {
		$title .= $data ? ': ' . print_r( $data, 1 ) : '';
		WP_CLI::line( $title );

		$this->write_log( $title, $data );
	}

	public function delete_log() {
		file_put_contents( $this->log_file, "\n" );
		WP_CLI::success( 'Log deleted.' );
	}

	public function delete_logs() {
		WP_CLI::confirm( 'Are you sure you want to delete the logs?', $this->assoc_args );

		foreach ( glob( "{$this->log_dir}*.log" ) as $log_file ) {
			unlink( $log_file );
		}

		WP_CLI::success( 'Logs deleted.' );
	}

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

		WP_CLI::success( "Log backed up: {$new_file}" );
	}

	public function check_log() {
		if ( ! file_exists( $this->log_dir ) ) {
			mkdir( $this->log_dir );
		}

		if ( ! file_exists( $this->log_file ) ) {
			touch( $this->log_file );
		}
	}

	public function write_log( $title, $data = null ) {
		$this->check_log();

		$title .= $data ? ': ' . print_r( $data, true ) : '';
		file_put_contents( $this->log_file, '[' . date( 'd-M-Y h:i:s A', current_time( 'timestamp' ) ) . '] ' . $title . "\n", FILE_APPEND );
	}

	/**
	 * Progress Bar for WP_CLI
	 *
	 * @param int    $param  Either the count to initiate, or a progress event (tick or finish)
	 * @param string $action Action being performed
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

	public function confirm( $question ) {
		return WP_CLI::confirm( $question, $this->assoc_args );
	}

	public function get_flag_value( $flag, $default = null ) {
		return Utils\get_flag_value( $this->assoc_args, $flag, $default );
	}

	public function set_args( $args, $assoc_args = array() ) {
		$this->args       = $args;
		$this->assoc_args = $assoc_args;
		$this->verbose    = isset( $assoc_args['verbose'] ) && $assoc_args['verbose'];
		$this->silent     = ! isset( $assoc_args['silent'] ) || $assoc_args['silent'];
	}

	/**
	 * Make sure emails are not sent out during CLI commands.
	 *
	 * @since  0.1.0
	 *
	 * @return void
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
	}
}
