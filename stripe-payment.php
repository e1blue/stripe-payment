<?php
/*
Plugin Name: Stripe Payment By GTI
Plugin URI: https://gti.co.jp/
Description: Stripe Payment.
Version: 1.5.1
Author: Takeshi Satoh@ GTI Inc.
Author URI: https://gti.co.jp/
Text Domain: stripe-payment-gti
Domain Path: /languages/
 */
/**
 * 更新履歴：
 * 2018/6/14 1.5.1 初期設定のボタンテキスト・ラベルテキストが聴いていないため修正
 * 2018/6/6  1.5   フォームのイメージをメディアライブラリから選べるようにした テストモードを導入した
 * 2018/4/18 フォームの名称部分を site_name="" で指定出来るようにした デフォルト値はサイト名
 * 2018/4/16 フォームのイメージ変更出来るようにした・・・修正
 * 2018/3/19 完了ページに投稿が使えるようにした。 finish_post_id 及び finish_param 追加
 * 2018/3/8  metadataに任意パラメータが入れられるフック作成
 * 2018/3/2  クーポンコード実装（定期課金） Stripeにて設定ずみのもの
 * 2018/2/22 決済ボタン複数対応　及び　決済結果表示場所を指定出来るショートコード追加（必須で貼る必要あり）
 *           通常購入の場合に領収書用メールとは別に保管しないため metadata に入れるようにした（後に機能化する）
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
add_shortcode( 'stripe_payment_result', array( $stripe_payment, 'stripe_payment_result_display' ) );


