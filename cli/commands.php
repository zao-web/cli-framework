<?php
namespace Example_WP_CLI\CLI;

/**
 * Example CLI commands.
 */
class Commands {

	/**
	 * The CLI logs directory.
	 *
	 * @var string
	 */
	protected static $log_dir = __DIR__;

	public static function set_log_dir( $log_dir ) {
		self::$log_dir = $log_dir;
	}

	// Todo: add documentation for available commands.
	public function is_post_author( $args, $assoc_args ) {
		$cli = new Actions( $args, $assoc_args, self::$log_dir );
		$cli->backup_log();
		$cli->delete_log();
		$cli->is_post_author();
	}

	// Todo: add documentation for available commands.
	public function delete_post_if_mine( $args, $assoc_args ) {
		$cli = new Actions( $args, $assoc_args, self::$log_dir );
		$cli->backup_log();
		$cli->delete_log();
		$cli->delete_post_if_mine();
	}

	/**
	 * Delete the log
	 */
	public function delete_log( $args, $assoc_args ) {
		$cli = new Base( $args, $assoc_args, self::$log_dir );
		$cli->delete_log();
	}

	/**
	 * Delete all CLI logs
	 */
	public function delete_logs( $args, $assoc_args ) {
		$cli = new Base( $args, $assoc_args, self::$log_dir );
		$cli->delete_logs();
	}

	/**
	 * Backup the log
	 */
	public function backup_log( $args, $assoc_args ) {
		$cli = new Base( $args, $assoc_args, self::$log_dir );
		$cli->backup_log();
	}

}
