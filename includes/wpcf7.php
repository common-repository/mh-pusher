<?php
/**
** Message hub
**/

add_action( 'wpcf7_submit', 'wpcf7_message_hub_submit', 10, 2 );

function wpcf7_message_hub_submit( $contact_form, $result ) {
	if ( ! class_exists( 'MessageHub_Handler' )) {
		return;
	}

	if ( $contact_form->in_demo_mode() ) {
		return;
	}

	$cases = (array) apply_filters( 'wpcf7_message_hub_submit_if',
		array( 'spam', 'mail_sent', 'mail_failed' ) );

	if ( empty( $result['status'] )
	|| ! in_array( $result['status'], $cases ) ) {
		return;
	}

	$submission = WPCF7_Submission::get_instance();

	if ( ! $submission || ! $posted_data = $submission->get_posted_data() ) {
		return;
	}

	if ( $submission->get_meta( 'do_not_store' ) ) {
		return;
	}

	$fields_senseless =
		$contact_form->scan_form_tags( array( 'feature' => 'do-not-store' ) );

	$exclude_names = array();

	foreach ( $fields_senseless as $tag ) {
		$exclude_names[] = $tag['name'];
	}

	$exclude_names[] = 'g-recaptcha-response';

	foreach ( $posted_data as $key => $value ) {
		if ( '_' == substr( $key, 0, 1 ) || in_array( $key, $exclude_names ) ) {
			unset( $posted_data[$key] );
		}
	}

	$email = wpcf7_message_hub_get_value( 'email', $contact_form );
	$name = wpcf7_message_hub_get_value( 'name', $contact_form );
  $subject = wpcf7_message_hub_get_value( 'subject', $contact_form );
  $phone = wpcf7_message_hub_get_value( 'phone', $contact_form );

	$meta = array();

	$special_mail_tags = array( 'serial_number', 'remote_ip',
		'user_agent', 'url', 'date', 'time', 'post_id', 'post_name',
		'post_title', 'post_url', 'post_author', 'post_author_email',
		'site_title', 'site_description', 'site_url', 'site_admin_email',
		'user_login', 'user_email', 'user_display_name' );

	foreach ( $special_mail_tags as $smt ) {
		$meta[$smt] = apply_filters( 'wpcf7_special_mail_tags', '',
			sprintf( '_%s', $smt ), false );
	}

	$args = array(
		'message' => array(
      'email' => $email,
      'title' => $subject,
      'phone' => $phone,
      'source' => $contact_form->title(),
      'content' => '',
    ),
		'from' => trim( sprintf( '%s <%s>', $name, $email ) ),
		'from_name' => $name,
		'from_email' => $email,
		'fields' => $posted_data,
		'meta' => $meta,
		'spam' => ( 'spam' == $result['status'] )
  );
  
  $components = MB_WPCF7_Mail::compose_mail($contact_form->prop('mail'), 'mail');
  
  $args['message']['content'] = $components['body'];

	$message = MessageHub_Handler::sendMessage( $args );
}

function wpcf7_message_hub_get_value( $field, $contact_form ) {
	if ( empty( $field ) || empty( $contact_form ) ) {
		return false;
	}

	$value = '';

	if ( in_array( $field, array( 'email', 'name', 'subject' ) ) ) {
		$templates = $contact_form->additional_setting( 'message_hub_' . $field );

		if ( empty( $templates[0] ) ) {
			$template = sprintf( '[your-%s]', $field );
		} else {
			$template = trim( wpcf7_strip_quote( $templates[0] ) );
		}

		$value = wpcf7_mail_replace_tags( $template );
	}

	$value = apply_filters( 'wpcf7_message_hub_get_value', $value,
		$field, $contact_form );

	return $value;
}
