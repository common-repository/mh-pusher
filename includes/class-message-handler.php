<?php

/**
 * Message submission.
 *
 * @link       travisnguyen.me
 * @since      1.0.0
 *
 * @package    Message_Hub
 * @subpackage Message_Hub/includes
 */

/**
 * Message submission.
 *
 * This class defines all code necessary to submit message to message server.
 *
 * @since      1.0.0
 * @package    Message_Hub
 * @subpackage Message_Hub/includes
 * @author     Faba Technonogy <travisnguyen.me@gmail.com>
 */
class MessageHub_Handler {

	/**
	 * Send message
	 *
	 * Send message
	 *
	 * @since    1.0.0
	 */
	public static function sendMessage($args) {
    ob_start();
    $token = get_option('message_hub_api_key', '');
    $url = MESSAGE_HUB_REMOTE_URL . '/message';
    $response = wp_remote_post( $url, array(
      'method' => 'POST',
      'timeout' => 45,
      'headers' => array(
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer ' . $token
      ),
      'body' => json_encode($args['message']),
      'cookies' => array()
        )
    );

    if ( is_wp_error( $response ) ) {
      $error_message = $response->get_error_message();
      error_log("Fail to send notification" . $error_message);
   }
   ob_end_clean();
	}
}
