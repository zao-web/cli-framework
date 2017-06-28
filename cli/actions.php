<?php
namespace Example_WP_CLI\CLI;
use Exception;
use WP_CLI;

/**
 * Meal Planning Plugin Actions object
 */
class Actions extends Base {

	public function is_post_author() {
		if ( empty( $this->args[0] ) ) {
			$this->error_message( 'Please provide the post ID to check.' );
		}

		$this->disable_emails();

		$user_id = $this->get_flag_value( 'user_id', get_current_user_id() );
		$post    = get_post( absint( $this->args[0] ) );

		$is_post_author = $post && absint( $post->post_author ) === absint( $user_id );

		$this->success_message( 'Can edit? ' . ( $is_post_author ? 'Yes' : 'No' ) );
	}

	public function delete_post_if_mine() {
		if ( empty( $this->args[0] ) ) {
			$this->error_message( 'Please provide the post ID to check.' );
		}

		$this->disable_emails();

		$user_id = absint( $this->get_flag_value( 'user_id', get_current_user_id() ) );
		$post_id = absint( $this->args[0] );

		$this->confirm( sprintf( 'Are you sure you want to delete the post (%d) for user %d?', $post_id, $user_id ) );
		$this->silent_log( sprintf( 'Maybe deleting post (%d) for user (%d)', $post_id, $user_id ) );

		$post = get_post( $post_id );

		$is_post_author = isset( $post->post_author ) && absint( $post->post_author ) === $user_id;

		if ( ! $is_post_author ) {
			$this->error_log( sprintf( 'Sorry, post (%d) author does not match the user id (%d), and cannot be deleted.', $plan_id, $member->user_id ) );
		}

		$this->success_message( 'Can edit? ' . ( $is_post_author ? 'Yes' : 'No' ) );
	}
}
