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
			"description"       => "",
			"address"           => "",  // 住所入力するかパラメータ存在したら ON
			"ship_address"      => "",  // 送付先住所入力するかパラメータ存在したら　ON
			"form"              => "",  // 任意のフォームに存在させる場合に form="on" とすると submit ボタンとして動作（独自に<form/>タグを出力しなくなる
			"pay_id"                => "",  // pay_id をつけることにより残カウントを有効化出来る
			"count"             => 0,   // このフォームを表示させる回数 ※id指定がない場合は無効
			"subscription"         => "",  // 定期課金
			"interval"          => "",  // Specifies billing frequency. Either day, week, month or year.
		), $atts );

		// 通貨 （指定ない場合は jpy）
		$currency = $atts['currency'];
		$amount   = $atts['price'];
		$pay_id   = $atts['pay_id'];
		$count    = $atts['count'];

		$subscription = $atts['subscription'];
		$interval  = $atts['interval'];

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
				'pay_id'                           => esc_attr( $pay_id ),
				'count'                            => esc_attr( $count ),
				'subscription'                     => esc_attr( $subscription ),
				'interval'                         => esc_attr( $interval ),
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
		$checkout_btn_text = "";
		$checkout_label_text   = "";
		if ( isset( $atts['checkout_btn_text'] ) && trim( $atts['checkout_btn_text'] ) != "" ) {
			$checkout_btn_text = $atts['checkout_btn_text'];
		}
		if ( isset( $atts['checkout_label_text'] ) && trim( $atts['checkout_label_text'] ) != "" ) {
			$checkout_label_text = $atts['checkout_label_text'];
		}
		$description = "";
		if ( isset( $atts['description'] ) && trim( $atts['description'] ) != "" ) {
			$description = esc_attr( $atts['description'] );
		}
		$loading_gif = get_option( 'stripe_payment_loading_gif', STRIPE_PAYMENT_LOADING_GIF );

		$checkout_btn_text = apply_filters( 'stripe-payment-gti-checkout_btn_text', $checkout_btn_text, $amount );
		$checkout_label_text   = apply_filters( 'stripe-payment-gti-checkout_label_text', $checkout_label_text, $amount );
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
		$pay_id       = $atts['pay_id'];
		$count        = $atts['count'];

		// チェックアウト表示パラメータ
		$checkout_args = array(
			'checkout_label_text'   => $checkout_label_text,
			'checkout_btn_text' => $checkout_btn_text,
			'description'       => $description,
			'price'             => $amount,
			'email'             => $data_email,
			'currency'          => $currency,
			'address'           => $address,
			'ship_address'      => $ship_address,
			'pay_id'            => $pay_id,
			'count'             => $count
		);
		// 通常決済の場合

		$html_str .= $this->get_stripe_html( $checkout_args );


		$html_str .= "
				<input type='hidden' name='nonce' value='" . wp_create_nonce( $amount ) . "'>";
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

		$html_str = "";
		$pay_id = $checkout_args['pay_id'];
		$count  = intval( $checkout_args['count'] ); // 個数 整数でなければNG 数値でないまたはNULLなら0なので利用する

		$result_counts = maybe_unserialize( get_option( 'stripe-payment_result-counts') );
		$zan_count = 0;
		if ( $result_counts ) {
			if ( array_key_exists( $pay_id, $result_counts ) ) {
				if ( isset( $result_counts[$pay_id] ) ) {
					$zan_count = intval( $result_counts[ $pay_id ] );
				}
			}
		} else {
			$zan_count = $count;
		}

		if ( $zan_count > 0 || $count == 0 ) {

		$site_name  = get_bloginfo( 'name' );
		$public_key = get_option( 'stripe_payment_public_key' );

		$checkout_label_text = "";
		$checkout_btn_text   = "";
		$amount               = 0;
		$data_email          = "";
		$currency            = "";
		$address_flg         = "false";
		$ship_address_flg    = "false";
		if ( $checkout_args !== null && is_array( $checkout_args ) ) {
			$description         = $checkout_args['description'];
			$amount               = $checkout_args['price'];
			$checkout_label_text = sprintf( $checkout_args['checkout_label_text'], $amount );
			$checkout_btn_text   = sprintf( $checkout_args['checkout_btn_text'], $amount );
			$data_email          = $checkout_args['email'];
			$currency            = $checkout_args['currency'];
			$address_flg         = ( $checkout_args['address'] !== "" ? "true" : "false" );
			$ship_address_flg    = ( $checkout_args['ship_address'] !== "" ? "true" : "false" );
		}

		if ( $currency == "" ) {
			$currency = get_option( 'stripe_payment_checkout_currency', 'jpy' );
		}

		$stripe_payment_checkout_img = apply_filters( 'stripe_payment_checkout_img', STRIPE_PAYMENT_CHECKOUT_IMG_MARKETPLACE );

		$html_str .= "<script
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
		data-amount='{$amount}'
		data-key='{$public_key}'
		data-label='{$checkout_btn_text}'
		data-panel-label='{$checkout_label_text}' 
		data-image='{$stripe_payment_checkout_img}'
		data-locale='auto' 
		closed='stripe_purchase' 
		data-email='{$data_email}'
		data-allow-remember-me='false'
		data-currency='{$currency}'></script>";
		} else {
			$html_str .= "<p class='stripe-payment-no-item'>".__( 'No Item.', 'stripe-payment-gti' )."</p>";
		}
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
		$pay_id = $args['pay_id'];
		$count  = $args['count'];

		$description = ( isset( $args['description'] ) ? $args['description'] : "" );

		$currency = ( isset( $args['currency'] ) ? $args['currency'] : "jpy" );

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

				$use_interval = array( "day", "week", "month", "year" );
				$status = "";
				$charge_id = "";
				$status_message = "";
				if ( !isset( $args['subscription'] ) && $args['subscription'] == "on" &&
					!isset( $args['interval'] )  && in_array( $args['interval'], $use_interval )
				) {
					$charge = \Stripe\Charge::create(array(
						"amount" => $amount,
						"currency" => $currency,
						"description" => $description,
						"source" => $token,
						));
					$status = $charge->status; // succeeded で成功
					if ( "succeeded" == $status ) {
						$charge_id = $charge->id;
					}

				} else {
					$interval =  $args['interval'];
					// Create Customer
					$customer_info["source"] = $token;
					$customer_info[ 'email' ] = $email;
					$customer_info[ 'description' ] = $description;
					$cus_object = \Stripe\Customer::create( $customer_info );

					if ( $description == "" ) {
						$description = "Stripe Payment: ".$_SERVER['HTTP_REFERER'];
					}

					// Create Plan
					$plan = \Stripe\Plan::create(array(
						"amount" => $amount,
						// Specifies billing frequency. Either day, week, month or year.
						"interval" => $interval,
						"name" => $description,
						"currency" => $currency
					));

					// Create Subscription
					$subscription = \Stripe\Subscription::create(array(
						"customer" =>$cus_object->id,
						"items" => array(
							array(
								"plan" => $plan->id,
							),
						)
					));
					$start = $subscription->current_period_start;
					$start = date( "Y/m/d H:i:s", $start );
					$start_str = $start;
					// active 等
					$status = $subscription->status;

					$interval = $subscription->plan->interval;
					$interval_count = $subscription->plan->interval_count;

					$status_message = "START AT: ".$start_str." \n";
					$status_message .= "STATUS: ".$status." \n";
					$status_message .= "INTERVAL: ".$interval." \n";
					$status_message .= "INTERVAL_COUNT: ".$interval_count." \n";
					$status_message .= "ID: ".$subscription->id." \n";
				}
				// 残数管理の場合マイナスする
				if ( is_numeric( $count ) && intval( $count ) > 0 ) {
					$result_counts = maybe_unserialize( get_option( 'stripe-payment_result-counts') );
					if ( $result_counts ) {
						if ( array_key_exists( $pay_id, $result_counts ) ) {
							$zan_count = $result_counts[$pay_id];
							if ( $zan_count > 0 ) {
								$result_counts[$pay_id] = $zan_count - 1;
							}
						}
					} else {
						$zan_count = intval( $count ) - 1;
						$result_counts = array();
						$result_counts[ $pay_id ] = $zan_count;
					}

				} else {
					$result_counts = maybe_unserialize( get_option( 'stripe-payment_result-counts') );
					if ( $result_counts ) {
						if ( array_key_exists( $pay_id, $result_counts ) ) {
							unset( $result_counts[ $pay_id ] );
						}
					} else {
						$result_counts = array();
					}
				}
				$serial_data = serialize( $result_counts );

				update_option( 'stripe-payment_result-counts', $serial_data );

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
			金額: " . $amount ."\n";
				if ( $status_message != "" ) {
					$email_for_admin .= $status_message;
				} elseif ( $status != "" ) {
					$email_for_admin .= "ID: ".$charge_id."\n";
					$email_for_admin .= "STATUS :".$status;
				}


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
				$error_msg  = "処理に失敗しました。<br>";
				$error_msg .= "------- Exception ------<br>";

				$error_msg .= '捕捉した例外: '.  $e->getMessage(). "<br>";

				echo apply_filters( "stripe-payment-gti-payment-error-message", $error_msg );
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