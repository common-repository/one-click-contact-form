<?php
/**
 * Plugin Name:       One Click Contact Form
 * Description:       With "One Click Contact Form" it's never been easier to add a contact form to your website, and with its lightweight design, you won't have to worry about it slowing down your site. So download the plugin now and start receiving feedback from your visitors today!
 * Version:           1.0
 * Requires at least: 6.1.1
 * Requires PHP:      7.2
 * Author:            Ajay malik
 * Author URI:        https://theonlined.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

add_action( 'admin_menu', 'lcfp_contact_form__add_options_page' );

function lcfp_contact_form__add_options_page() {
	add_options_page( 'Contact Form Options', 'Contact Form', 'manage_options', 'lcfp_contact_form_', 'lcfp_contact_form__options_page' );
}

function lcfp_contact_form__options_page() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

	$options = get_option( 'lcfp_contact_form__options' );
	if ( !is_array( $options ) ) {
		$options = array();
	}

	echo '<div class="wrap">';
	echo '<h1>Contact Form Options</h1>';
	echo '<form method="post" action="options.php">';
	settings_fields( 'lcfp_contact_form__options_group' );
	do_settings_sections( 'lcfp_contact_form_' );
	submit_button();
	echo '</form>';
	echo '</div>';
}

add_action( 'admin_init', 'lcfp_contact_form__settings_init' );

function lcfp_contact_form__settings_init() {
	register_setting( 'lcfp_contact_form__options_group', 'lcfp_contact_form__options', 'lcfp_contact_form__options_sanitize' );
	add_settings_section( 'lcfp_contact_form__section', '', '', 'lcfp_contact_form_' );
	add_settings_field( 'lcfp_contact_form__email', 'Email address', 'lcfp_contact_form__email_render', 'lcfp_contact_form_', 'lcfp_contact_form__section' );
}

function lcfp_contact_form__email_render() {
	$options = get_option( 'lcfp_contact_form__options' );
	if ( is_array( $options ) && !empty( $options['email'] ) ) {
		$email = $options['email'];
	} else {
		$email = '';
	}
	echo '<p>Simply place the [light_contact_form] shortcode on any page or post and the contact form will automatically appear</p>';
	echo '<input type="email" name="lcfp_contact_form__options[email]" value="' . esc_html( $email ) . '" />';

}


function lcfp_contact_form__options_sanitize( $input ) {
	$new_input = array();
	if ( isset( $input['email'] ) ) {
		$new_input['email'] = sanitize_email( $input['email'] );
	}
	return $new_input;
}

add_shortcode( 'light_contact_form', 'lcfp_contact_form__contact_form_shortcode' );

function lcfp_contact_form__contact_form_shortcode() {
	$options = get_option( 'lcfp_contact_form__options' );
if ( is_array( $options ) && !empty( $options['email'] ) ) {
	$admin_email = $options['email'];
} else {
	return '<p>Error: Please specify an email address in the plugin options.</p>';
}
$form = '<form method="post" action="' . sanitize_url( $_SERVER['REQUEST_URI'] ) . '">';
$form .= '<p>';
$form .= 'Your Name (required) <br/>';
$form .= '<input type="text" name="cf-name" value="' . ( isset( $_POST["cf-name"] ) ? sanitize_text_field( $_POST["cf-name"] ) : '' ) . '" size="40" />';
$form .= '</p>';
$form .= '<p>';
$form .= 'Your Email (required) <br/>';
$form .= '<input type="email" name="cf-email" value="' . ( isset( $_POST["cf-email"] ) ? sanitize_email( $_POST["cf-email"] ) : '' ) . '" size="40" />';
$form .= '</p>';
$form .= '<p>';
$form .= 'Subject (required) <br/>';
$form .= '<input type="text" name="cf-subject" value="' . ( isset( $_POST["cf-subject"] ) ? sanitize_text_field( $_POST["cf-subject"] ) : '' ) . '" size="40" />';
$form .= '</p>';
$form .= '<p>';
$form .= 'Your Message (required) <br/>';
$form .= '<textarea rows="10" cols="35" name="cf-message">' . ( isset( $_POST["cf-message"] ) ? sanitize_textarea_field( $_POST["cf-message"] ) : '' ) . '</textarea>';
$form .= '</p>';
$form .= '<p><input type="submit" name="cf-submitted" value="Send"/></p>';
$form .= '</form>';

	if ( isset( $_POST['cf-submitted'] ) ) {
		$name = sanitize_text_field( $_POST["cf-name"] );
		$email = sanitize_email( $_POST["cf-email"] );
		$subject = sanitize_text_field( $_POST["cf-subject"] );
		$message = sanitize_textarea_field( $_POST["cf-message"] );
		$headers = array('Content-Type: text/html; charset=UTF-8');

		$body = "You received a message\n\n";
		$body .= "Subject: $subject\n\n";
		$body .= "From: $name\n\n";
		$body .= "Email: $email\n\n";
		$body .= "Message:\n$message";

		if ( wp_mail( $admin_email, 'You received a message', $body, $headers ) ) {
			$form = '<p>Thanks for contacting us, expect a response soon.</p>';
		} else {
			$form = '<p>An error occurred while trying to send the email. Please try again.</p>';
		}
	}

	return $form;
}

