<?php

/*
Clone from contact form 7, add new static method compose_mail
*/

class MB_WPCF7_Mail {

	private static $current = null;

	private $name = '';
	private $locale = '';
	private $template = array();
	private $use_html = false;
	private $exclude_blank = false;

	public static function get_current() {
		return self::$current;
	}

	public static function send( $template, $name = '') {
		self::$current = new self( $name, $template );
		return self::$current->compose($send);
  }
  
  public static function compose_mail( $template, $name = '') {
		self::$current = new self( $name, $template );
		return self::$current->compose(false);
	}

	private function __construct( $name, $template ) {
		$this->name = trim( $name );
		$this->use_html = ! empty( $template['use_html'] );
		$this->exclude_blank = ! empty( $template['exclude_blank'] );

		$this->template = wp_parse_args( $template, array(
			'subject' => '',
			'sender' => '',
			'body' => '',
			'recipient' => '',
			'additional_headers' => '',
			'attachments' => '',
		) );

		if ( $submission = WPCF7_Submission::get_instance() ) {
			$contact_form = $submission->get_contact_form();
			$this->locale = $contact_form->locale();
		}
	}

	public function name() {
		return $this->name;
	}

	public function get( $component, $replace_tags = false ) {
		$use_html = ( $this->use_html && 'body' == $component );
		$exclude_blank = ( $this->exclude_blank && 'body' == $component );

		$template = $this->template;
		$component = isset( $template[$component] ) ? $template[$component] : '';

		if ( $replace_tags ) {
			$component = $this->replace_tags( $component, array(
				'html' => $use_html,
				'exclude_blank' => $exclude_blank,
			) );

			if ( $use_html
			&& ! preg_match( '%<html[>\s].*</html>%is', $component ) ) {
				$component = $this->htmlize( $component );
			}
		}

		return $component;
	}

	private function htmlize( $body ) {
		if ( $this->locale ) {
			$lang_atts = sprintf( ' %s',
				wpcf7_format_atts( array(
					'dir' => wpcf7_is_rtl( $this->locale ) ? 'rtl' : 'ltr',
					'lang' => str_replace( '_', '-', $this->locale ),
				) )
			);
		} else {
			$lang_atts = '';
		}

		$header = apply_filters( 'wpcf7_mail_html_header',
			'<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml"' . $lang_atts . '>
<head>
<title>' . esc_html( $this->get( 'subject', true ) ) . '</title>
</head>
<body>
', $this );

		$footer = apply_filters( 'wpcf7_mail_html_footer',
			'</body>
</html>', $this );

		$html = $header . wpautop( $body ) . $footer;
		return $html;
	}

	private function compose( $send = true ) {
		$components = array(
			'subject' => $this->get( 'subject', true ),
			'sender' => $this->get( 'sender', true ),
			'body' => $this->get( 'body', true ),
			'recipient' => $this->get( 'recipient', true ),
			'additional_headers' => $this->get( 'additional_headers', true ),
			'attachments' => $this->attachments(),
		);

		$components = apply_filters( 'wpcf7_mail_components',
			$components, wpcf7_get_current_contact_form(), $this );

		if ( ! $send ) {
			return $components;
		}

		$subject = wpcf7_strip_newline( $components['subject'] );
		$sender = wpcf7_strip_newline( $components['sender'] );
		$recipient = wpcf7_strip_newline( $components['recipient'] );
		$body = $components['body'];
		$additional_headers = trim( $components['additional_headers'] );
		$attachments = $components['attachments'];

		$headers = "From: $sender\n";

		if ( $this->use_html ) {
			$headers .= "Content-Type: text/html\n";
			$headers .= "X-WPCF7-Content-Type: text/html\n";
		} else {
			$headers .= "X-WPCF7-Content-Type: text/plain\n";
		}

		if ( $additional_headers ) {
			$headers .= $additional_headers . "\n";
		}

		return wp_mail( $recipient, $subject, $body, $headers, $attachments );
	}

	public function replace_tags( $content, $args = '' ) {
		if ( true === $args ) {
			$args = array( 'html' => true );
		}

		$args = wp_parse_args( $args, array(
			'html' => false,
			'exclude_blank' => false,
		) );

		return wpcf7_mail_replace_tags( $content, $args );
	}

	private function attachments( $template = null ) {
		if ( ! $template ) {
			$template = $this->get( 'attachments' );
		}

		$attachments = array();

		if ( $submission = WPCF7_Submission::get_instance() ) {
			$uploaded_files = $submission->uploaded_files();

			foreach ( (array) $uploaded_files as $name => $path ) {
				if ( false !== strpos( $template, "[${name}]" )
				&& ! empty( $path ) ) {
					$attachments[] = $path;
				}
			}
		}

		foreach ( explode( "\n", $template ) as $line ) {
			$line = trim( $line );

			if ( '[' == substr( $line, 0, 1 ) ) {
				continue;
			}

			$path = path_join( WP_CONTENT_DIR, $line );

			if ( is_readable( $path ) && is_file( $path ) ) {
				$attachments[] = $path;
			}
		}

		return $attachments;
	}
}