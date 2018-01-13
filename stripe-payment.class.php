<?php
/**
 * stripe-payment.class
 * @
 * Date: 2017/12/28
 * Time: 9:18
 */

class StripePayment extends Singleton {

	private $new_flg = true;

	/**
	 * 決済ボタン
	 *
	 * @param $atts
	 *
	 * @return string
	 */
	function stripe_payment_form( $atts ) {

		//  $payments, $action_flag, $rand, $purchase_disabled
		$atts = shortcode_atts( array(
			"price"             => 0,
			"currency"          => 'jpy',
			"email"             => '',
			"checkout_btn_text" => "",
			"checkout_label_text"   => "",
			"address"           => "",  // 住所入力するかパラメータ存在したら ON
			"ship_address"      => "",   // 送付先住所入力するかパラメータ存在したら　ON
			"form"              => ""
		), $atts );

		// 通貨 （指定ない場合は jpy）
		$currency = $atts['currency'];
		$amount   = $atts['price'];

		if ( $_REQUEST['stripeToken'] ) {
			/**
			 * array(23) {
			 * ["action"]=> string(6) "stripe"
			 * ["action_return"]=> string(1) "1"
			 * ["result"]=> string(1) "1"
			 * ["amount"]=> string(5) "12000"
			 * ["sub"]=> string(9) "169238135"
			 * ["nonce"]=> string(10) "63252b6bfc"
			 * ["stripeToken"]=> string(28) "tok_1Bb24PFlXz0vpAiv0hhRIdif"
			 * ["stripeTokenType"]=> string(4) "card"
			 * ["stripeEmail"]=> string(14) "info@gti.co.jp"
			 * ["stripeBillingName"]=> string(12) "佐藤　毅"
			 * ["stripeBillingAddressCountry"]=> string(5) "Japan"
			 * ["stripeBillingAddressCountryCode"]=> string(2) "JP"
			 * ["stripeBillingAddressZip"]=> string(8) "810-0041"
			 * ["stripeBillingAddressLine1"]=> string(24) "福岡市中央区大名"
			 * ["stripeBillingAddressCity"]=> string(9) "福岡県"
			 * ["stripeBillingAddressState"]=> string(10) "Fukuokaken"
			 * ["stripeShippingName"]=> string(12) "佐藤　毅"
			 * ["stripeShippingAddressCountry"]=> string(5) "Japan"
			 * ["stripeShippingAddressCountryCode"]=> string(2) "JP"
			 * ["stripeShippingAddressZip"]=> string(8) "810-0041"
			 * ["stripeShippingAddressLine1"]=> string(24) "福岡市中央区大名"
			 * ["stripeShippingAddressCity"]=> string(9) "福岡県"
			 * ["stripeShippingAddressState"]=> string(10) "Fukuokaken"
			 * }
			 */
			$args = array(
				'currency'                         => esc_attr( $currency ),
				'result'                           => esc_attr( $_REQUEST['result'] ),
				'amount'                           => esc_attr( $amount ),
				'stripeToken'                      => esc_attr( $_REQUEST['stripeToken'] ),
				'stripeTokenType'                  => esc_attr( $_REQUEST['stripeTokenType'] ),
				'stripeEmail'                      => esc_attr( $_REQUEST['stripeEmail'] ),
				'stripeBillingName'                => esc_attr( $_REQUEST['stripeBillingName'] ),
				// 支払者名
				'stripeBillingAddressCountry'      => esc_attr( $_REQUEST['stripeBillingAddressCountry'] ),
				// 国
				'stripeBillingAddressCountryCode'  => esc_attr( $_REQUEST['stripeBillingAddressCountryCode'] ),
				// 国コード
				'stripeBillingAddressZip'          => esc_attr( $_REQUEST['stripeBillingAddressZip'] ),
				// 郵便番号
				'stripeBillingAddressLine1'        => esc_attr( $_REQUEST['stripeBillingAddressLine1'] ),
				// 住所1
				'stripeBillingAddressCity'         => esc_attr( $_REQUEST['stripeBillingAddressCity'] ),
				// 住所 市区町村
				'stripeBillingAddressState'        => esc_attr( $_REQUEST['stripeBillingAddressState'] ),
				// 住所 都道府県
				'stripeShippingName'               => esc_attr( $_REQUEST['stripeShippingName'] ),
				// 送付先名
				'stripeShippingAddressCountry'     => esc_attr( $_REQUEST['stripeShippingAddressCountry'] ),
				// 送付先国
				'stripeShippingAddressCountryCode' => esc_attr( $_REQUEST['stripeShippingAddressCountryCode'] ),
				// 送付先国コード
				'stripeShippingAddressZip'         => esc_attr( $_REQUEST['stripeShippingAddressZip'] ),
				// 送付先郵便番号
				'stripeShippingAddressLine1'       => esc_attr( $_REQUEST['stripeShippingAddressLine1'] ),
				// 送付先 住所1
				'stripeShippingAddressCity'        => esc_attr( $_REQUEST['stripeShippingAddressCity'] ),
				// 送付先住所 市区町村
				'stripeShippingAddressState'       => esc_attr( $_REQUEST['stripeShippingAddressState'] )
				// 送付先住所 都道府県
			);
			$this->stripe_order( $args );
			$_REQUEST = null;
		}
		$price             = esc_attr( $atts['price'] );
		$checkout_btn_text = "";
		$checkout_label_text   = "";
		if ( isset( $atts['checkout_btn_text'] ) && trim( $atts['checkout_btn_text'] ) != "" ) {
			$checkout_btn_text = $atts['checkout_btn_text'];
		}
		if ( isset( $atts['checkout_label_text'] ) && trim( $atts['checkout_label_text'] ) != "" ) {
			$checkout_label_text = $atts['checkout_label_text'];
		}
		$loading_gif = get_option( 'stripe_payment_loading_gif', STRIPE_PAYMENT_LOADING_GIF );

		$checkout_btn_text = apply_filters( 'stripe-payment-gti-checkout_btn_text', $checkout_btn_text, $price );
		$checkout_label_text   = apply_filters( 'stripe-payment-gti-checkout_label_text', $checkout_label_text, $price );
		$loading_gif       = apply_filters( 'stripe_payment_loading_gif', $loading_gif );

		$this->stripe_error_log( "purchase: Stripe " );

		$html_str = "";
		if ( $this->new_flg === true ) {
			$html_str .= "<script>function stripe_payment_loading() {
  jQuery('#stripe_payment_loading').css('display', 'block');
  jQuery('body').css('opacity', '0.5');
}

function loading_over() {
  jQuery('#stripe_payment_loading').css('display', 'none');
  jQuery('body').css('opacity', '1');
}

function stripe_purchase() {
  stripe_payment_loading();
}
//--></script>";
			$html_str .= apply_filters( 'stripe_payment_loading_purchase_css', "
<style>
#stripe_payment_loading {
  display: none;
  position:           fixed;
  z-index:            1;
  top:                0;
  left:               0;
  width:              100%;
  height:             100%;
  background-color:   rgba(0,0,0,0.15);
}
#stripe_payment_loading img {
  width: 96px; /* gif画像の幅 */
  height: 96px; /* gif画像の高さ */
  margin: -68px 0 0 -68px; /* gif画像を画面中央に */
  padding: 20px; /* gif画像を大きく */
  background: #BABABA; /* gif画像の背景色 */
  opacity: 0.5; /* 透過させる */
  border-radius: 15px; /* 丸角 */
  position: fixed; /* gif画像をスクロールさせない */
  left: 50%; /* gif画像を画面横中央へ */
  top: 50%; /* gif画像を画面縦中央へ */
}
</style>" );
		}
		if ( $atts['form'] === "" ) {
			$html_str .= "<div style='text-align: center;'><form id='purchase_form' action='' method=POST onKeyDown='if (event.keyCode == 13) {return false;}' >";
		}
		if ( $this->new_flg === true ) {
			$html_str .= "<div id='stripe_payment_loading'><img src='{$loading_gif}' ></div>";
		}
		// Stripeへ送信するメールアドレス
		$data_email = $atts['email'];
		// 住所入力するか
		$address      = $atts['address'];
		$ship_address = $atts['ship_address'];

		// チェックアウト表示パラメータ
		$checkout_args = array(
			'checkout_label_text'   => $checkout_label_text,
			'checkout_btn_text' => $checkout_btn_text,
			'price'             => $price,
			'email'             => $data_email,
			'currency'          => $currency,
			'address'           => $address,
			'ship_address'      => $ship_address
		);

		// 通常決済の場合

		$html_str .= $this->get_stripe_html( $checkout_args );


		$html_str .= "
				<input type='hidden' name='nonce' value='" . wp_create_nonce( $price ) . "'>";
		if ( $atts['form'] === "" ) {
			$html_str .= "</form></div>";
		}
		$html = apply_filters( 'stripe-payment-gti-payment_form', $html_str );

		// 一度起動していたら false にする
		$this->new_flg = false;

		return $html;
	}

	/**
	 * get_stripe_html
	 */
	function get_stripe_html( $checkout_args ) {

		$site_name  = get_bloginfo( 'name' );
		$public_key = get_option( 'stripe_payment_public_key' );

		$checkout_label_text   = "";
		$checkout_btn_text = "";
		$price             = 0;
		$data_email        = "";
		$currency          = "";
		$address_flg       = "false";
		$ship_address_flg  = "false";
		if ( $checkout_args !== null && is_array( $checkout_args ) ) {
			$price             = $checkout_args['price'];
			$checkout_label_text   = sprintf( $checkout_args['checkout_label_text'], $price );
			$checkout_btn_text = sprintf( $checkout_args['checkout_btn_text'], $price );
			$data_email        = $checkout_args['email'];
			$currency          = $checkout_args['currency'];
			$address_flg       = ( $checkout_args['address'] !== "" ? "true" : "false" );
			$ship_address_flg  = ( $checkout_args['ship_address'] !== "" ? "true" : "false" );
		}

		if ( $currency == "" ) {
			$currency = get_option( 'stripe_payment_checkout_currency', 'jpy' );
		}

		$stripe_payment_checkout_img = apply_filters( 'stripe_payment_checkout_img', STRIPE_PAYMENT_CHECKOUT_IMG_MARKETPLACE );

		$html_str = "<script
		src='https://checkout.stripe.com/checkout.js' class='stripe-button' 
		";
		if ( $address_flg !== "false" ) {
			$html_str .= "
		data-billing-address='{$address_flg}'  
		";
		}
		if ( $ship_address_flg !== "false" ) {
			$html_str .= "
		data-shipping-address='{$ship_address_flg}' 
		";
		}
		$html_str .= "
		data-name='{$site_name}'
		data-amount='{$price}'
		data-key='{$public_key}'
		data-label='{$checkout_btn_text}'
		data-description='{$checkout_label_text}'
		data-image='{$stripe_payment_checkout_img}'
		data-locale='auto' 
		closed='stripe_purchase' 
		data-email='{$data_email}'
		data-allow-remember-me='false'
		data-currency='{$currency}'></script>";

		return $html_str;
	}

	/**
	 * 受注時処理
	 *
	 * @param $args Stripe結果パラメータ
	 */
	function stripe_order( $args ) {

		$token = $args['stripeToken'];

		$billingName  = $args['stripeBillingName'];
		$shippingName = $args['stripeShippingName'];

		$billing_address_country       = $args['stripeBillingAddressCountry'];
		$billing_address_country_code  = $args['stripeBillingAddressCountryCode'];
		$billing_address_zip           = $args['stripeBillingAddressZip'];
		$billing_address_line1         = $args['stripeBillingAddressLine1'];
		$billing_address_city          = $args['stripeBillingAddressCity'];
		$billing_address_state         = $args['stripeBillingAddressState'];
		$shipping_address_country      = $args['stripeShippingAddressCountry'];
		$shipping_address_country_code = $args['stripeShippingAddressCountryCode'];
		$shipping_address_zip          = $args['stripeShippingAddressZip'];
		$shipping_address_line1        = $args['stripeShippingAddressLine1'];
		$shipping_address_city         = $args['stripeShippingAddressCity'];
		$shipping_address_state        = $args['stripeShippingAddressState'];

		$amount = $args['amount'];
		$email  = $args['stripeEmail'];

		if ( $token != '' ) {    //Stripe

			// 注文処理
			try {

				// TOKEN ゲット。
				$secret_key = get_option( 'stripe_payment_secret_key' );
				$this->stripe_error_log( "================= Stripe PART 1 =========" );
				$this->stripe_error_log( "SECRET KEY : " . $secret_key );
				\Stripe\Stripe::setApiKey( $secret_key );

				$stripeinfo = \Stripe\Token::retrieve( $token );

				$card_brand = $stripeinfo->card->brand;
				$card_last4 = $stripeinfo->card->last4;

				// メール送信
				// メール

				// テンプレート変換
				$replace_array = array(
					'billing_name'                  => $billingName,
					'shipping_name'                 => $shippingName,
					'email'                         => $email,
					'card_brand'                    => $card_brand,
					'card_last4'                    => $card_last4,
					'amount'                        => number_format( $amount ),
					'price'                         => number_format( $amount ),
					'billing_address_country'       => $billing_address_country,
					'billing_address_country_code'  => $billing_address_country_code,
					'billing_address_zip'           => $billing_address_zip,
					'billing_address_line1'         => $billing_address_line1,
					'billing_address_city'          => $billing_address_city,
					'billing_address_state'         => $billing_address_state,
					'shipping_address_country'      => $shipping_address_country,
					'shipping_address_country_code' => $shipping_address_country_code,
					'shipping_address_zip'          => $shipping_address_zip,
					'shipping_address_line1'        => $shipping_address_line1,
					'shipping_address_city'         => $shipping_address_city,
					'shipping_address_state'        => $shipping_address_state
				);

				// for Customer
				$email_subject_for_customer = get_option( 'stripe_payment_customer_mail_subject' );
				$email_for_customer         = get_option( 'stripe_payment_customer_mail' );

				$email_subject_for_customer = apply_filters( 'stripe-payment-gti-customer-mail-subject', $email_subject_for_customer );
				$email_for_customer         = apply_filters( 'stripe-payment-gti-customer-mail-template', $email_for_customer );

				foreach ( $replace_array as $key => $val ) {
					$email_for_customer = str_replace( "{" . $key . "}", $val, $email_for_customer );
				}
				foreach ( $_REQUEST as $key => $val ) {
					if ( strpos( $email_for_customer, "{" . $key . "}" ) !== false ) {
						$val                = esc_attr( $val );
						$email_for_customer = str_replace( "{" . $key . "}", $val, $email_for_customer );
					}
				}

				$send_mail = $this->stripe_payment_send_mail(
					$email,
					$email_subject_for_customer,
					$email_for_customer
				);

				// for Admin
				$email_subject_for_admin = get_option( 'stripe_payment_admin_mail_subject' );
				$email_for_admin         = get_option( 'stripe_payment_admin_mail' );

				$email_for_admin .= "
			ご請求先 氏名: " . $billingName . "
			送付先 氏名: " . $shippingName . "
			Email: " . $email . "
			カードブランド: " . $card_brand . "
			No: ****-****-****-" . $card_last4 . "
			金額: " . $amount . "";

				$email_subject_for_admin = apply_filters( 'stripe-payment-gti-admin-mail-subject', $email_subject_for_admin );
				$email_for_admin         = apply_filters( 'stripe-payment-gti-admin-mail-template', $email_for_admin );

				foreach ( $replace_array as $key => $val ) {
					$email_for_admin = str_replace( "{" . $key . "}", $val, $email_for_admin );
				}
				foreach ( $_REQUEST as $key => $val ) {
					if ( strpos( $email_for_admin, "{" . $key . "}" ) !== false ) {
						$val             = esc_attr( $val );
						$email_for_admin = str_replace( "{" . $key . "}", $val, $email_for_admin );
					}
				}

				$send_mail = $this->stripe_payment_send_mail(
					get_option( 'admin_email' ),
					$email_subject_for_admin,
					$email_for_admin
				);

				// 画面表示
//			echo "ご請求先 氏名: ".$billingName."<br>";
//			echo "送付先 氏名: ".$shippingName."<br>";
//			echo "Email: ".$email."<br>";
//			echo "カードブランド: ".$card_brand."<br>";
//			echo "No: ****-****-****-".$card_last4."<br>";
//			echo "金額: ".$amount."<br>";

				// サンクス画面
				$thanks_msg = get_option( 'stripe_payment_thanks_message' );
				foreach ( $replace_array as $key => $val ) {
					$thanks_msg = str_replace( "{" . $key . "}", $val, $thanks_msg );
				}
				foreach ( $_REQUEST as $key => $val ) {
					if ( strpos( $thanks_msg, "{" . $key . "}" ) !== false ) {
						$val        = esc_attr( $val );
						$thanks_msg = str_replace( "{" . $key . "}", $val, $thanks_msg );
					}
				}

				// オプション処理
				apply_filters( 'stripe-payment-gti-payment-after', $_REQUEST );
				$thanks_msg = str_replace( "\n", "<br>", $thanks_msg );
				echo $thanks_msg;

				$this->stripe_error_log( 'Stripe RESULT' );

			} catch ( Exception $e ) {

				echo "------- Exception ------<br>";

				// echo '捕捉した例外: ',  $e->getMessage(), "\n";
				$log = array(
					'action' => 'stripe',
					'result' => 'Stripe ERROR:' . $e->getMessage(),
					'data'   => $e
				);

				$this->stripe_error_log( $log );

			}
		}
//	exit;
//	return false;
	}

	/**
	 * メール送信
	 *
	 * @param $to
	 * @param $subject
	 * @param $body
	 *
	 * @return bool
	 */
	function stripe_payment_send_mail( $to, $subject, $body ) {

		$result = wp_mail( $to, $subject, $body );

		return $result;
	}

	/**
	 * エラーログ出力
	 *
	 * @param $msg 表示するメッセージ
	 */
	function stripe_error_log( $msg ) {
		// エラーログ出力は wp_config.php 等で　define('STRIPE_ERROR_LOG_ON', '1'); とすればOK
		if ( defined( 'STRIPE_ERROR_LOG_ON' ) ) {
			error_log( $msg );
		}
	}


}