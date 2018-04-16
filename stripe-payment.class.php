<?php
/**
 * stripe-payment.class
 * @
 * Date: 2017/12/28
 * Time: 9:18
 *
 * Update: 2018/3/19
 */
define( 'STRIPE_PAYMENT_RESULT_ID', "stripe-payment-result-gti" );

class StripePayment extends Singleton {

	private $new_flg = true;
	// 結果 HTML
	private $result_html = "";

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
			"price"               => 0,
			"currency"            => 'jpy',
			"email"               => '',
			"checkout_btn_text"   => "",
			"checkout_label_text" => "",
			"description"         => "",
			"address"             => "",  // 住所入力するかパラメータ存在したら ON
			"ship_address"        => "",  // 送付先住所入力するかパラメータ存在したら　ON
			"form"                => "",  // 任意のフォームに存在させる場合に form="on" とすると submit ボタンとして動作（独自に<form/>タグを出力しなくなる
			"pay_id"              => "",  // pay_id をつけることにより残カウントを有効化出来る
			"count"               => 0,   // このフォームを表示させる回数 ※id指定がない場合は無効
			// subscription
			"subscription"        => "",  // 定期課金
			"coupon"              => "",  // Stripe で作っているクーポン指定
			"trial_end"           => "",  // タイムスタンプ指定のため秒割
			"trial_period_days"   => "",  // 何日間の指定"
			// plan
			"interval"            => "",  // Specifies billing frequency. Either day, week, month or year.
			"interval_count"      => 1,
			"plan_id"             => "",  // planの ID 入力で Stripe 登録済みのプランとなる （ subscription, interval は無視される ）
			"checkout_id"         => "",  // checkout_id は同画面で複数チェックアウトするときは必須です。
			"finish_post_id"      => "",  // サンクスページを表示するpost_id（固定ページ推奨）
			"finish_param"        => "",  // サンクスページに表示するパラメータ finish_param="xxx|yyy,zzz|aaa"のように複数可 {xxx}をyyy に置換する
		), $atts );

		// 通貨 （指定ない場合は jpy）
		$currency = $atts['currency'];
		$amount   = $atts['price'];

//		$pay_id = $atts['pay_id'];
//		$count  = $atts['count'];

		// 定期購買フラグ "on" で定期となる
//		$subscription      = $atts['subscription'];
		$coupon = $atts['coupon'];
//		$trial_end         = $atts['trial_end'];
//		$trial_period_days = $atts['trial_period_days'];
//
//		// プラン指定時は必要ないが新規プランを作成する場合は必須（エラーとなってしまう）
//		$interval       = $atts['interval'];
//		$interval_count = $atts['interval_count'];  // 無指定時には1
//
//		// プラン指定時には必須（新規作成時には interval[, interval_count=1] を指定すること
//		$plan_id = $atts['plan_id'];
//
//		// 概要（nameとして記録される）
//		$description = $atts['description'];

		$checkout_id = $atts['checkout_id'];

		$this->stripe_payment_result( $atts );

		$checkout_btn_text   = "";
		$checkout_label_text = "";
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

		$checkout_btn_text   = apply_filters( 'stripe-payment-gti-checkout_btn_text', $checkout_btn_text, $amount );
		$checkout_label_text = apply_filters( 'stripe-payment-gti-checkout_label_text', $checkout_label_text, $amount );
		$loading_gif         = apply_filters( 'stripe_payment_loading_gif', $loading_gif );

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
			$html_str .= "<div style='text-align: center;'><form id='" . $checkout_id . "' action='' method=POST onKeyDown='if (event.keyCode == 13) {return false;}' >";
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
		$checkout_id  = $atts['checkout_id'];

		// チェックアウト表示パラメータ
		$checkout_args = array(
			'checkout_label_text' => $checkout_label_text,
			'checkout_btn_text'   => $checkout_btn_text,
			'description'         => $description,
			'price'               => $amount,
			'email'               => $data_email,
			'currency'            => $currency,
			'address'             => $address,
			'ship_address'        => $ship_address,
			'pay_id'              => $pay_id,
			'count'               => $count,
			'checkout_id'         => $checkout_id
		);
		// 通常決済の場合
		if ( $coupon == "on" ) {
			$coupon_input_text = apply_filters( 'stripe-payment-gti-coupon_input_label', __( "Coupon Code", 'stripe-payment-gti' ) );
			$html_str          .= "
			{$coupon_input_text}: <input type='text' name='stripe-coupon' value=''><br>
			";
		}
		$html_str .= $this->get_stripe_html( $checkout_args );
		$html_str .= "
				<input type='hidden' name='checkout_id' value='" . $checkout_id . "'>
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
		$pay_id   = $checkout_args['pay_id'];
		$count    = intval( $checkout_args['count'] ); // 個数 整数でなければNG 数値でないまたはNULLなら0なので利用する

		$result_counts = maybe_unserialize( get_option( 'stripe-payment_result-counts' ) );
		$zan_count     = 0;
		if ( $result_counts ) {
			if ( array_key_exists( $pay_id, $result_counts ) ) {
				if ( isset( $result_counts[ $pay_id ] ) ) {
					$zan_count = intval( $result_counts[ $pay_id ] );
				}
			} else {
				$zan_count = $count;
			}
		} else {
			$zan_count = $count;
		}
		$this->stripe_error_log( "==================== COUNT: " . $count . " ZAN_COUNT: " . $zan_count );
		if ( $zan_count > 0 || $count == 0 ) {

			$site_name  = get_bloginfo( 'name' );
			$public_key = get_option( 'stripe_payment_public_key' );

			$checkout_label_text = "";
			$checkout_btn_text   = "";
			$amount              = 0;
			$data_email          = "";
			$currency            = "";
			$address_flg         = "false";
			$ship_address_flg    = "false";
			$checkout_id         = "";
			if ( $checkout_args !== null && is_array( $checkout_args ) ) {
				$checkout_id         = $checkout_args['checkout_id'];
				$description         = $checkout_args['description'];
				$amount              = $checkout_args['price'];
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

			$stripe_payment_checkout_img = get_option( 'stripe_payment_checkout_img', STRIPE_PAYMENT_CHECKOUT_IMG_MARKETPLACE );

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
			$html_str .= " data-name='{$site_name}' ";
			$html_str .= " data-amount=\"{$amount}\" ";
			$html_str .= " data-key='{$public_key}' ";
			$html_str .= " data-label='{$checkout_btn_text}' ";
			$html_str .= " data-description='{$description}' ";
			$html_str .= " data-panel-label='{$checkout_label_text}' ";
			$html_str .= " data-image='{$stripe_payment_checkout_img}' ";
			$html_str .= " data-locale='auto' ";
			$html_str .= " closed='stripe_purchase' ";
			$html_str .= " data-email='{$data_email}' ";
			$html_str .= " data-allow-remember-me='false' ";
			$html_str .= " data-currency='{$currency}'></script>";
		} else {
			$html_str .= "<p class='stripe-payment-no-item'>" . __( 'No Item.', 'stripe-payment-gti' ) . "</p>";
		}

		return $html_str;
	}

	/**
	 * token から情報取得
	 */
	function get_token_info( $token = "" ) {
		$this->stripe_error_log( "================= get_token_info =========" );
		$stripeinfo = null;
		if ( $token != '' ) {    //Stripe

			// 注文処理
			try {

				// TOKEN ゲット。
				$secret_key = get_option( 'stripe_payment_secret_key' );
				$this->stripe_error_log( "================= Stripe Info =========" );
				$this->stripe_error_log( "SECRET KEY : " . $secret_key );
				\Stripe\Stripe::setApiKey( $secret_key );

				$stripeinfo = \Stripe\Token::retrieve( $token );

			} catch ( Exception $e ) {
				$error_msg = "処理に失敗しました。<br>";
				$error_msg .= "------- Exception ------<br>";

				$error_msg .= '捕捉した例外: ' . $e->getMessage() . "<br>";

				return apply_filters( "stripe-payment-gti-payment-error-message", $error_msg );
				$log = array(
					'action' => 'stripe',
					'result' => 'Stripe ERROR:' . $e->getMessage(),
					'data'   => $e
				);

				$this->stripe_error_log( $log );
			}
		}

		return $stripeinfo;
	}

	/**
	 * 受注処理結果表示ショートコード
	 */
	function stripe_payment_result( $atts ) {
		// 返却値初期化
		$ret_html    = "";
		// 完了時メッセージの投稿ID初期化
		$finish_post_id = null;
		// 完了時メッセージキーバリュー初期化
		$finish_param      = null;
		$checkout_id = $atts['checkout_id'];
		$this->stripe_error_log( "================= stripe_payment_result_display =========:" . $checkout_id );
		if ( isset( $_REQUEST['stripeToken'] ) &&
		     ( empty( $_REQUEST['checkout_id'] ) ||
		       $checkout_id === $_REQUEST['checkout_id'] ) ) {
			$this->stripe_error_log( "================= PROCESS =========:" . $checkout_id );
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
			$stripeToken = ( isset( $_REQUEST['stripeToken'] ) ? esc_attr( $_REQUEST['stripeToken'] ) : '' );
			$stripeInfo  = $this->get_token_info( $stripeToken );

			$currency = $atts['currency'];

			$amount       = $atts['price'];   //　ここまでは price で引きずってる
			$pay_id       = $atts['pay_id'];
			$count        = $atts['count'];
			$subscription = $atts['subscription'];
			$coupon       = $atts['coupon'];
			$coupon_code  = "";
			if ( $coupon == "on" ) {
				$coupon_code = esc_attr( $_REQUEST['stripe-coupon'] );
			}
			$trial_end         = $atts['trial_end'];
			$trial_period_days = $atts['trial_period_days'];
			$interval          = $atts['interval'];
			$interval_count    = $atts['interval_count'];
			$plan_id           = $atts['plan_id'];
			$description       = $atts['description'];
			$finish_post_id    = $atts['finish_post_id'];
			$finish_param      = $atts['finish_param'];

			$args     = array(
				'checkout_id'                      => esc_attr( $_REQUEST['checkout_id'] ),
				'currency'                         => esc_attr( $currency ),
				'result'                           => ( isset( $_REQUEST['result'] ) ? esc_attr( $_REQUEST['result'] ) : '' ),
				'amount'                           => esc_attr( $amount ),
				'pay_id'                           => esc_attr( $pay_id ),
				'count'                            => esc_attr( $count ),
				'subscription'                     => esc_attr( $subscription ),
				'coupon_code'                      => esc_attr( $coupon_code ),
				'trial_end'                        => esc_attr( $trial_end ),
				'trial_period_days'                => esc_attr( $trial_period_days ),
				'interval'                         => esc_attr( $interval ),
				'interval_count'                   => esc_attr( $interval_count ),
				'plan_id'                          => esc_attr( $plan_id ),
				'description'                      => esc_attr( $description ),
				'stripeToken'                      => $stripeToken,
				'stripeTokenType'                  => ( isset( $_REQUEST['stripeTokenType'] ) ? esc_attr( $_REQUEST['stripeTokenType'] ) : '' ),
				'stripeEmail'                      => ( isset( $_REQUEST['stripeEmail'] ) ? esc_attr( $_REQUEST['stripeEmail'] ) : '' ),
				'stripeBillingName'                => ( isset( $_REQUEST['stripeBillingName'] ) ? esc_attr( $_REQUEST['stripeBillingName'] ) : '' ),
				// 支払者名
				'stripeBillingAddressCountry'      => ( isset( $_REQUEST['stripeBillingAddressCountry'] ) ? esc_attr( $_REQUEST['stripeBillingAddressCountry'] ) : '' ),
				// 国
				'stripeBillingAddressCountryCode'  => ( isset( $_REQUEST['stripeBillingAddressCountryCode'] ) ? esc_attr( $_REQUEST['stripeBillingAddressCountryCode'] ) : '' ),
				// 国コード
				'stripeBillingAddressZip'          => ( isset( $_REQUEST['stripeBillingAddressZip'] ) ? esc_attr( $_REQUEST['stripeBillingAddressZip'] ) : '' ),
				// 郵便番号
				'stripeBillingAddressLine1'        => ( isset( $_REQUEST['stripeBillingAddressLine1'] ) ? esc_attr( $_REQUEST['stripeBillingAddressLine1'] ) : '' ),
				// 住所1
				'stripeBillingAddressCity'         => ( isset( $_REQUEST['stripeBillingAddressCity'] ) ? esc_attr( $_REQUEST['stripeBillingAddressCity'] ) : '' ),
				// 住所 市区町村
				'stripeBillingAddressState'        => ( isset( $_REQUEST['stripeBillingAddressState'] ) ? esc_attr( $_REQUEST['stripeBillingAddressState'] ) : '' ),
				// 住所 都道府県
				'stripeShippingName'               => ( isset( $_REQUEST['stripeShippingName'] ) ? esc_attr( $_REQUEST['stripeShippingName'] ) : '' ),
				// 送付先名
				'stripeShippingAddressCountry'     => ( isset( $_REQUEST['stripeShippingAddressCountry'] ) ? esc_attr( $_REQUEST['stripeShippingAddressCountry'] ) : '' ),
				// 送付先国
				'stripeShippingAddressCountryCode' => ( isset( $_REQUEST['stripeShippingAddressCountryCode'] ) ? esc_attr( $_REQUEST['stripeShippingAddressCountryCode'] ) : '' ),
				// 送付先国コード
				'stripeShippingAddressZip'         => ( isset( $_REQUEST['stripeShippingAddressZip'] ) ? esc_attr( $_REQUEST['stripeShippingAddressZip'] ) : '' ),
				// 送付先郵便番号
				'stripeShippingAddressLine1'       => ( isset( $_REQUEST['stripeShippingAddressLine1'] ) ? esc_attr( $_REQUEST['stripeShippingAddressLine1'] ) : '' ),
				// 送付先 住所1
				'stripeShippingAddressCity'        => ( isset( $_REQUEST['stripeShippingAddressCity'] ) ? esc_attr( $_REQUEST['stripeShippingAddressCity'] ) : '' ),
				// 送付先住所 市区町村
				'stripeShippingAddressState'       => ( isset( $_REQUEST['stripeShippingAddressState'] ) ? esc_attr( $_REQUEST['stripeShippingAddressState'] ) : '' )
				// 送付先住所 都道府県
			);
			$ret_html = $this->stripe_order( $stripeInfo, $args );
		}

		$this->result_html = $ret_html;
		if ( $ret_html != "" ) {

			if ( $finish_post_id != "" && is_numeric( $finish_post_id ) && get_post_status( (int) $finish_post_id ) != false ) {
				$post_id = (int) $finish_post_id;
				// サンクスページ指定時は内容取得表示
				$post        = get_post( $post_id );
				$result_html = $post->post_content;
				// ショートコード
				$result_html = do_shortcode( $result_html );
				// wpautop
				$result_html = wpautop( $result_html );

				// finish_param が設定されている場合は文字列置換を行う
				$result_html = $this->param_replace( $result_html, $finish_param );

				echo $result_html;
			} else {

				$result_html = str_replace( array( "\r\n", "\r", "\n" ), '', $this->result_html );
				$result_html = str_replace( "&lt;", "<", $result_html );
				$result_html = str_replace( "&gt;", ">", $result_html );

				// finish_param が設定されている場合は文字列置換を行う
				$result_html = $this->param_replace( $result_html, $finish_param );
				// 返却HTMLの生成フック
				$result_html = apply_filters( "stripe-payment-gti-result_html", $result_html, $atts );

				if ( empty( $_REQUEST['checkout_id'] ) ) {
					echo $result_html;
				} else {
					echo "
					<script>
					jQuery( function() {
				        jQuery('#{STRIPE_PAYMENT_RESULT_ID}').html('{$result_html}');
				        location.href = \"#{STRIPE_PAYMENT_RESULT_ID}\";
					} );
					</script>
					";
				}
			}
		}
		$_REQUEST = null;

	}

	/**
	 * ページの文字列をリクエストパラメータに置換する
	 */
	function param_replace( $result_html, $finish_param ) {
		if ( ! empty( $finish_param ) ) {
			$finish_param_list = explode( ",", $finish_param );
			foreach ( $finish_param_list as $param ) {
				$params = explode( "|", $param );
				if ( count( $params ) == 2 ) {
					$rep_str     = esc_attr( $_REQUEST[ $params[1] ] );
					$result_html = str_replace( "{" . $params[0] . "}", $rep_str, $result_html );
				}
			}
		}

		return $result_html;
	}

	/**
	 * 受注時処理
	 *
	 * @param $args Stripe結果パラメータ
	 */
	function stripe_order( $stripeInfo = null, $args ) {

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

		$token  = $args['stripeToken'];
		$amount = $args['amount'];
		$email  = $args['stripeEmail'];
		$pay_id = $args['pay_id'];
		$count  = $args['count'];

		$description = ( isset( $args['description'] ) ? $args['description'] : $args['name'] );

		$currency = ( isset( $args['currency'] ) ? $args['currency'] : "jpy" );
		// クーポン結果メッセージ
		$coupon_result = "";

		if ( ! empty( $stripeInfo ) ) {    //Stripe

			// 注文処理
			try {

				$card_brand = $stripeInfo->card->brand;
				$card_last4 = $stripeInfo->card->last4;

				$use_interval   = array( "day", "week", "month", "year" );
				$status         = "";
				$charge_id      = "";
				$status_message = "";
				if ( ! isset( $args['subscription'] ) ||
				     $args['subscription'] != "on" ||
				     ! isset( $args['interval'] ) ||
				     ! in_array( $args['interval'], $use_interval ) ||
				     ! isset( $args['plan_id'] )
				) {
					// メタデータ（格納用）
					$metadata_list = array( "email" => $email );
					// 格納データを追加・削除したい場合は Hook [stripe-payment-gti-save-metadata] を作る
					$metadata_list = apply_filters( "stripe-payment-gti-save-metadata", $metadata_list, $args, $stripeInfo );
					try {
						$charge = \Stripe\Charge::create( array(
							"amount"        => $amount,
							"currency"      => $currency,
							"description"   => $description,
							"receipt_email" => $email,
							"metadata"      => $metadata_list,
							"source"        => $token,
						) );
						$status = $charge->status; // succeeded で成功
						if ( "succeeded" == $status ) {
							$charge_id = $charge->id;
						}
					} catch ( Exctption $e1 ) {
						$error_msg = "Charge 処理に失敗しました。<br>";
						$error_msg .= "------- Exception ------<br>";

						$error_msg .= '捕捉した例外: ' . $e1->getMessage() . "<br>";
						$log       = array(
							'action' => 'stripe',
							'result' => 'Stripe ERROR:' . $e1->getMessage(),
							'data'   => $e1
						);

						$this->stripe_error_log( $log );

						return apply_filters( "stripe-payment-gti-payment-error-message", $error_msg );
					}
				} else {
					$interval       = $args['interval'];
					$interval_count = intval( $args['interval_count'] ) == 0 ? 1 : intval( $args['interval_count'] );

					$trial_end         = $args['trial_end'];
					$trial_period_days = $args['trial_period_days'];
					$plan_id           = $args['plan_id'];

					$coupon_code = $args['coupon_code'];

					if ( empty( $plan_id ) ) {
						$plan_id = ( $description != "" ? $description : $billingName );
					}

					// Create Customer
					$customer_info["source"] = $token;
					$customer_info['email']  = $email;

					try {
						$cus_object = \Stripe\Customer::create( $customer_info );
					} catch ( Exctption $e1 ) {
						$error_msg = "Customer 処理に失敗しました。<br>";
						$error_msg .= "------- Exception ------<br>";

						$error_msg .= '捕捉した例外: ' . $e1->getMessage() . "<br>";
						$log       = array(
							'action' => 'stripe',
							'result' => 'Stripe ERROR:' . $e1->getMessage(),
							'data'   => $e1
						);

						$this->stripe_error_log( $log );

						return apply_filters( "stripe-payment-gti-payment-error-message", $error_msg );
					}
					if ( $description == "" ) {
						$description = "Stripe Payment: " . $_SERVER['HTTP_REFERER'];
					}

					// Create Plan
					$plan         = null;
					$subscription = null;

					// Retrieve or Create Plan
					try {
						$plan = \Stripe\Plan::retrieve( $plan_id );
					} catch ( Exception $pe ) {
						if ( $pe->getCode() == 0 ) {
							$plan_args           = array();
							$plan_args['id']     = $plan_id;
							$plan_args['amount'] = $amount;
							// Specifies billing frequency. Either day, week, month or year.
							$plan_args['interval'] = $interval;
							if ( isset( $interval_count ) ) {
								$plan_args['interval_count'] = intval( $interval_count );
							}
							$plan_args['product']  = array(
								'name' => $description
							);
							$plan_args['currency'] = $currency;
							$plan                  = \Stripe\Plan::create( $plan_args );
						} else {
							$this->stripe_error_log( "--- Exception: Stripe Plan ----" );
							throw $pe;
						}
					}
					// Create Subscription
					$sub_args['customer'] = $cus_object->id;
					$sub_args['items']    = array(
						array(
							"plan" => $plan->id,
						),
					);
					// トライアル期間はあれば設定
					if ( ! empty( $trial_end ) ) {
						$sub_args['trial_end'] = $trial_end;
					} elseif ( ! empty( $trial_period_days ) ) {
						$sub_args['trial_period_days'] = $trial_period_days;
					}

					// クーポンがあれば設定（ Stripe で設定済みのもの ）
					if ( ! empty( $coupon_code ) ) {
						$sub_args['coupon'] = $coupon_code;
					}

					// メタデータ（格納用）
					$metadata_list = array( "email" => $email );
					// 格納データを追加・削除したい場合は Hook [stripe-payment-gti-save-metadata] を作る
					$metadata_list        = apply_filters( "stripe-payment-gti-save-metadata", $metadata_list, $args, $stripeInfo );
					$sub_args['metadata'] = $metadata_list;

					$subscription = \Stripe\Subscription::create( $sub_args );

					$start     = $subscription->current_period_start;
					$start     = date( "Y/m/d H:i:s", $start );
					$start_str = $start;
					// active 等
					$status = $subscription->status;

					$interval       = $subscription->plan->interval;
					$interval_count = $subscription->plan->interval_count;

					if ( ! empty( $subscription->discount ) ) {
						$coupon_result .= "COUPON: " . $subscription->discount->coupon->id . " \n";
						$amount_off    = $subscription->discount->coupon->amount_off;
						$percent_off   = $subscription->discount->coupon->percent_off;
						// 有効期間・期限・回数   repeating or forever or once
						$duration = $subscription->discount->coupon->duration;
						// 回数の場合
						$duration_in_months = $subscription->discount->coupon->duration_in_months;
						switch ( $duration ) {
							case "repeating":
								$coupon_result .= "クーポン割引月数:" . $duration_in_months . "\n";
								break;
							case "forever":
								$coupon_result .= "クーポン割引: 永久 \n";
								break;
							case "once":
								$coupon_result .= "クーポン割引: 1回目のみ \n";

						}
						if ( null != $amount_off ) {
							$coupon_result .= "AMOUNT OFF: " . $amount_off . " \n";
						} elseif ( null != $percent_off ) {
							$coupon_result .= "PERCENT OFF: " . $percent_off . " \n";
						}

					}

					$status_message = "START AT: " . $start_str . " \n";
					$status_message .= "STATUS: " . $status . " \n";
					$status_message .= "INTERVAL: " . $interval . " \n";
					$status_message .= "INTERVAL_COUNT: " . $interval_count . " \n";
					$status_message .= "ID: " . $subscription->id . " \n";
					$status_message .= $coupon_result;
				}
				// 残数管理の場合マイナスする
				$this->stripe_error_log( "==================== COUNT: " . $count );
				if ( is_numeric( $count ) && intval( $count ) > 0 ) {
					$result_counts = maybe_unserialize( get_option( 'stripe-payment_result-counts' ) );
					if ( $result_counts ) {
						if ( array_key_exists( $pay_id, $result_counts ) ) {
							$zan_count = $result_counts[ $pay_id ];
							if ( $zan_count > 0 ) {
								$result_counts[ $pay_id ] = $zan_count - 1;
							}
						} else {
							$zan_count                = intval( $count ) - 1;
							$result_counts[ $pay_id ] = $zan_count;
						}
					} else {
						$zan_count                = intval( $count ) - 1;
						$result_counts            = array();
						$result_counts[ $pay_id ] = $zan_count;
					}

				} else {
					$result_counts = maybe_unserialize( get_option( 'stripe-payment_result-counts' ) );
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
				// テンプレート変換
				$replace_array = array(
					'description'                   => $description,
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
				if ( $send_mail ) {
					error_log( "SEND_MAIL result: TRUE ? " );
				} else {
					error_log(" SEND MAIL RESULT FAILED");
				}
				// for Admin
				$email_subject_for_admin = get_option( 'stripe_payment_admin_mail_subject' );
				$email_for_admin         = get_option( 'stripe_payment_admin_mail' );

				$email_for_admin .= "
			" . __( 'Billing Name', 'stripe-payment-gti' ) . ": " . $billingName /** ご請求先 氏名 */ . "
			" . __( 'Shipping Name', 'stripe-payment-gti' ) . ": " . $shippingName /** 送付先 氏名 */ . "
			" . __( 'Email', 'stripe-payment-gti' ) . ": " . $email /** メール */ . "
			" . __( 'Card Brand', 'stripe-payment-gti' ) . ": " . $card_brand /** カードブランド */ . "
			" . __( 'Card No', 'stripe-payment-gti' ) . ": ****-****-****-" . $card_last4 /** カード番号 */ . "
			" . __( 'Amount', 'stripe-payment-gti' ) . ": " . $amount /** 金額 */ . "
			";
				if ( $status_message != "" ) {
					$email_for_admin .= $status_message;
				} elseif ( $status != "" ) {
					$email_for_admin .= "ID: " . $charge_id . "\n";
					$email_for_admin .= "STATUS :" . $status;
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
				if ( $send_mail ) {
					error_log( "ADMIN:SEND_MAIL result: TRUE ? " );
				} else {
					error_log(" ADMIN:SEND MAIL RESULT FAILED");
				}

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
				if ( ! empty( $coupon_result ) ) {
					$thanks_msg .= str_replace( "\n", "<br>", $coupon_result );
				}

				// オプション処理
				apply_filters( 'stripe-payment-gti-payment-after', $_REQUEST );

				$thanks_msg = str_replace( "\n", "<br>", $thanks_msg );

				return $thanks_msg;

				$this->stripe_error_log( 'Stripe RESULT' );

			} catch ( Exception $e ) {
				$error_msg = "stripe_order 処理に失敗しました。<br>";
				$error_msg .= "------- Exception ------<br>";

				$error_msg .= '捕捉した例外: ' . $e->getMessage() . "<br>";

				return apply_filters( "stripe-payment-gti-payment-error-message", $error_msg );
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
	 * 結果表示
	 */
	function stripe_payment_result_display() {
		return "<span id='{STRIPE_PAYMENT_RESULT_ID}'></span>";
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