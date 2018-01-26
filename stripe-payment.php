<?php
/*
Plugin Name: Stripe Payment By GTI
Plugin URI: https://gti.co.jp/
Description: Stripe Payment.
Version: 1.1
Author: Takeshi Satoh@ GTI Inc.
Author URI: https://gti.co.jp/
Text Domain: stripe-payment-gti
Domain Path: /languages/
 */
/**
 * 更新履歴：
 * 2018/1/26 Charge を作るようにして、Subscribe 指定したら・・・っていう分岐だけ作った。
 */
define( 'STRIPE_PAYMENT_CONTENT_DIR', ABSPATH . 'wp-content' );
define( 'STRIPE_PAYMENT_PLUGIN_PARENT_DIR', STRIPE_PAYMENT_CONTENT_DIR . '/plugins' );
define( 'STRIPE_PAYMENT_PLUGIN_DIR', STRIPE_PAYMENT_PLUGIN_PARENT_DIR . '/' . plugin_basename( dirname( __FILE__ ) ) );
define( 'STRIPE_PAYMENT_CHECKOUT_IMG_MARKETPLACE', 'https://stripe.com/img/documentation/checkout/marketplace.png' );
define( 'STRIPE_PAYMENT_LOADING_GIF', plugin_dir_url( __FILE__ ) . 'img/gif-load.gif' );
require_once( STRIPE_PAYMENT_PLUGIN_DIR . '/stripe-php/init.php' );
require_once( STRIPE_PAYMENT_PLUGIN_DIR . '/Singleton.php' );
require_once( STRIPE_PAYMENT_PLUGIN_DIR . '/stripe-payment.class.php' );

if ( is_admin() ) {
	require_once( STRIPE_PAYMENT_PLUGIN_DIR . "/stripe-payment-admin.php" );
}

$stripe_payment = StripePayment::getInstance();

add_shortcode( 'stripe_payment', array( $stripe_payment, 'stripe_payment_form' ) );


