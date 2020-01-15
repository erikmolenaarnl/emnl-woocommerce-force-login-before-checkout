<?php 
/**
 * Plugin Name: WooCommerce Force Login before Checkout
 * Description: This plugin redirects users to the Woocommerce register/login page if they try to access the checkout while not logged in. After logging in succesfully, it redirects to the checkout. Please note: only works if Guest Checkout is disabled in the Woocommerce Settings!
 * Author: Erik Molenaar
 * Author URI: https://erikmolenaar.nl
 * Version: 1.2
 */


// Exit if accessed directly
if ( ! defined ( 'ABSPATH' ) ) exit;


// Redirects users from 'checkout' to 'my-account' if NOT logged in. A URL parameter is added.
add_action ( 'template_redirect', 'emnl_wflbc_checkout_redirect' );
function emnl_wflbc_checkout_redirect() {

    if ( is_checkout() && ! is_user_logged_in() && get_option ( 'woocommerce_enable_guest_checkout' ) !==  'yes' ) {

        // Get Checkout URL
        global $woocommerce;
        $checkout_url = $woocommerce->cart->get_checkout_url();
        
        // Get 'My Account' URL
        $myaccount_page_id = get_option ( 'woocommerce_myaccount_page_id' );
        if ( $myaccount_page_id ) {
            $myaccount_page_url = get_permalink ( $myaccount_page_id );
        }

        $redirect_to_url = add_query_arg ( 'after_login_redirect_to', $checkout_url, $myaccount_page_url );

        wp_redirect ( $redirect_to_url );

        // A redirect should always end with an exit
        exit;

    }

}


// Redirects users from 'my-account' to 'checkout' if user is logged in AND URL parameter was applied
add_action ( 'template_redirect', 'emnl_wflbc_myaccount_redirect' );
function emnl_wflbc_myaccount_redirect() {

    if ( is_account_page() && is_user_logged_in() && get_option ( 'woocommerce_enable_guest_checkout' ) !==  'yes' ) {

        if ( isset ( $_GET['after_login_redirect_to'] ) && strpos ( $_GET['after_login_redirect_to'], 'http') !== false ) {
            
            wp_redirect ( $_GET['after_login_redirect_to'] );
            
            // A redirect should always end with an exit
            exit;

        }
        
    }

}


// Conditional function for string renames
add_filter ( 'gettext', 'emnl_wflbc_string_renames', 20, 3 );
function emnl_wflbc_string_renames ( $translated_text, $text, $domain ) {
    
    // Check if URL parameter is detected
    if ( isset ( $_GET['after_login_redirect_to'] ) && strpos ( $_GET['after_login_redirect_to'], 'http') !== false ) {

        switch ( $translated_text ) {

            // Add translations below
            case 'Gebruikersnaam of e-mailadres' :
                $translated_text = __( 'E-mailadres', 'woocommerce' );
                break;

            case 'Registreren' :
                $translated_text = __( 'Doorgaan zonder inloggen', 'woocommerce' );
                break;

            case 'Er wordt een wachtwoord verzonden naar je e-mailadres.' :
                $translated_text = __( 'U ontvangt per e-mail een wachtwoord om later in te kunnen loggen op uw account', 'woocommerce' );
                break;

        }
    
    }

    return $translated_text;
    
    
}


// Conditional function to apply CSS
add_action ( 'wp_enqueue_scripts', 'emnl_wflbc_css' );
function emnl_wflbc_css() {

    // Check if URL parameter is detected
    if ( isset ( $_GET['after_login_redirect_to'] ) && strpos ( $_GET['after_login_redirect_to'], 'http') !== false ) {

        wp_enqueue_style ( 'emnl-woocommerce-force-login-before-checkout', plugins_url ( 'emnl-woocommerce-force-login-before-checkout.css', __FILE__ ) );

    }

}