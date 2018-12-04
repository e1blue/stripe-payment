<?php
require_once( STRIPE_PAYMENT_PLUGIN_DIR . '/stripe-php/init.php' );

class StripePaymentSetting {   // 管理画面
	function __construct() {
		// language
		load_plugin_textdomain( 'stripe-payment-gti', false, basename( dirname( __FILE__ ) ) . '/languages' );

		add_action( 'admin_menu', array( $this, 'add_pages' ) );

		/**
		 * autoloading modules
		 */
		$includes = array(
		        '/vendor',
    			'/module',
		);
		foreach ( $includes as $include ) {
			foreach ( glob( __DIR__ . $include . '/*.php' ) as $file ) {
				require_once( $file );
			}
		}

		add_action( 'wp_ajax_reset_count', array( $this, 'reset_item_counter' ) );
		add_action( 'wp_ajax_nopriv_reset_count', array( $this, 'reset_item_counter' ) );


		/**
		 * 管理画面の「Wordpressのご利用ありがとうございます。」の文言を削除
		 */
		add_filter('admin_footer_text', '__return_empty_string');
	}

	function admin_item_init() {
		wp_register_script( 'cf--my-upload', plugins_url( '/js/upload.js', __FILE__ ) );
		wp_enqueue_script( 'cf--my-upload' );
		wp_enqueue_script('thickbox');

		// Load thickbox CSS
		wp_enqueue_style('thickbox');
	}

	function add_pages() {
		$mincap = 'manage_options';
		if ( function_exists( 'add_submenu_page' ) ) {
			add_menu_page( __( 'Stripe Payment', 'stripe-payment-gti' ), __( 'Stripe Setting', 'stripe-payment-gti' ), $mincap, __FILE__, array(
				$this,
				'stripe_setting_page'
			) );
		}
	}

	public function data_update() {
		//$_POST['stripe_payment_public_key'])があったら保存
		if ( isset( $_POST['stripe_payment_public_key'] ) && isset( $_POST['stripe_payment_secret_key'] ) ) {

			check_admin_referer( 'stripe2017' );

			$stripe_payment_checkout_open = esc_attr( $_POST['stripe_payment_checkout_open'] );
			update_option( 'stripe_payment_checkout_open', $stripe_payment_checkout_open );

			$stripe_payment_checkout_img = esc_attr( $_POST['stripe_payment_checkout_img'] );
			update_option( 'stripe_payment_checkout_img', $stripe_payment_checkout_img );

			$stripe_payment_test_flg = esc_attr( $_POST['stripe_payment_test_flg'] );
			update_option( 'stripe_payment_test_flg', $stripe_payment_test_flg );

			$stripe_payment_public_key = esc_attr( $_POST['stripe_payment_public_key'] );
			update_option( 'stripe_payment_public_key', $stripe_payment_public_key );
			$stripe_payment_secret_key = esc_attr( $_POST['stripe_payment_secret_key'] );
			update_option( 'stripe_payment_secret_key', $stripe_payment_secret_key );

			$stripe_payment_test_public_key = esc_attr( $_POST['stripe_payment_test_public_key'] );
			update_option( 'stripe_payment_test_public_key', $stripe_payment_test_public_key );
			$stripe_payment_test_secret_key = esc_attr( $_POST['stripe_payment_test_secret_key'] );
			update_option( 'stripe_payment_test_secret_key', $stripe_payment_test_secret_key );

			$stripe_payment_checkout_currency = esc_attr( $_POST['stripe_payment_checkout_currency'] );
			update_option( 'stripe_payment_checkout_currency', $stripe_payment_checkout_currency );

			$stripe_payment_checkout_btn_text = esc_attr( $_POST['stripe_payment_checkout_btn_text'] );
			update_option( 'stripe_payment_checkout_btn_text', $stripe_payment_checkout_btn_text );
			$stripe_payment_checkout_label_text = esc_attr( $_POST['stripe_payment_checkout_label_text'] );
			update_option( 'stripe_payment_checkout_label_text', $stripe_payment_checkout_label_text );

			$stripe_payment_save_customer = esc_attr( $_POST['stripe_payment_save_customer'] );
			update_option( 'stripe_payment_save_customer', $stripe_payment_save_customer );
			$stripe_payment_use_card_info_flg = esc_attr( $_POST['stripe_payment_use_card_info_flg'] );
			update_option( 'stripe_payment_use_card_info_flg', $stripe_payment_use_card_info_flg );

			$stripe_payment_thanks_message = esc_attr( $_POST['stripe_payment_thanks_message'] );
			update_option( 'stripe_payment_thanks_message', $stripe_payment_thanks_message );

			$stripe_payment_customer_mail_subject = esc_attr( $_POST['stripe_payment_customer_mail_subject'] );
			update_option( 'stripe_payment_customer_mail_subject', $stripe_payment_customer_mail_subject );
			$stripe_payment_customer_mail = esc_attr( $_POST['stripe_payment_customer_mail'] );
			update_option( 'stripe_payment_customer_mail', $stripe_payment_customer_mail );
			$stripe_payment_admin_mail_subject = esc_attr( $_POST['stripe_payment_admin_mail_subject'] );
			update_option( 'stripe_payment_admin_mail_subject', $stripe_payment_admin_mail_subject );
			$stripe_payment_admin_mail = esc_attr( $_POST['stripe_payment_admin_mail'] );
			update_option( 'stripe_payment_admin_mail', $stripe_payment_admin_mail );

			$stripe_payment_from_email_name = esc_attr( $_POST['stripe_payment_from_email_name'] );
			update_option( 'stripe_payment_from_email_name', $stripe_payment_from_email_name );
			$stripe_payment_from_email_address = esc_attr( $_POST['stripe_payment_from_email_address'] );
			update_option( 'stripe_payment_from_email_address', $stripe_payment_from_email_address );
			$stripe_payment_replyto_email_name = esc_attr( $_POST['stripe_payment_replyto_email_name'] );
			update_option( 'stripe_payment_replyto_email_name', $stripe_payment_replyto_email_name );
			$stripe_payment_replyto_email_address = esc_attr( $_POST['stripe_payment_replyto_email_address'] );
			update_option( 'stripe_payment_replyto_email_address', $stripe_payment_replyto_email_address );

			$stripe_payment_no_item_html = esc_attr( $_POST['stripe_payment_no_item_html'] );
			update_option( 'stripe_payment_no_item_html', $stripe_payment_no_item_html );

			// CheckoutHelperからのsubmit時ローディング画像
			$stripe_payment_loading_gif = esc_attr( $_POST[ 'stripe_payment_loading_gif' ] );
			update_option( 'stripe_payment_loading_gif', $stripe_payment_loading_gif );

			echo( '<div class="updated fade"><p><strong>' . __( 'Saved.', 'stripe-payment-gti' ) . '</strong></p></div>' );
		}

	}

	/**
	 * Stripe Setting Page
	 */
	function stripe_setting_page() {
		$this->data_update();
		?>
        <h2><?php _e( 'Stripe Settings', 'stripe-payment-gti' ); ?></h2>
        <form action="" method="post">
			<?php
			wp_nonce_field( 'stripe2017' );

			$stripe_payment_checkout_img = get_option( 'stripe_payment_checkout_img', STRIPE_PAYMENT_CHECKOUT_IMG_MARKETPLACE );

			$stripe_payment_test_flg = get_option( 'stripe_payment_test_flg' );

			$stripe_payment_public_key = get_option( 'stripe_payment_public_key' );
			$stripe_payment_secret_key = get_option( 'stripe_payment_secret_key' );
			$stripe_payment_test_public_key = get_option( 'stripe_payment_test_public_key' );
			$stripe_payment_test_secret_key = get_option( 'stripe_payment_test_secret_key' );

			$stripe_payment_checkout_currency = get_option( 'stripe_payment_checkout_currency', 'jpy' );

			$stripe_payment_checkout_btn_text = get_option( 'stripe_payment_checkout_btn_text' );
			$stripe_payment_checkout_label_text   = get_option( 'stripe_payment_checkout_label_text' );

			$stripe_payment_thanks_message = get_option( 'stripe_payment_thanks_message' );
			$stripe_payment_customer_mail_subject = get_option( 'stripe_payment_customer_mail_subject' );
			$stripe_payment_customer_mail  = get_option( 'stripe_payment_customer_mail' );
			$stripe_payment_admin_mail_subject = get_option( 'stripe_payment_admin_mail_subject' );
			$stripe_payment_admin_mail     = get_option( 'stripe_payment_admin_mail' );

			$stripe_payment_from_email_name = get_option( 'stripe_payment_from_email_name' );
			$stripe_payment_from_email_address = get_option( 'stripe_payment_from_email_address' );
			$stripe_payment_replyto_email_name = get_option( 'stripe_payment_replyto_email_name' );
			$stripe_payment_replyto_email_address = get_option( 'stripe_payment_replyto_email_address' );

			$stripe_payment_no_item_html = get_option( 'stripe_payment_no_item_html' );

			$stripe_payment_loading_gif = get_option( 'stripe_payment_loading_gif' );
			?>
            <style>
                textarea {
                    width: 500px;
                    max-width: 100%;
                    resize: auto !important;
                }
            </style>
            <table class="settle_table">
                <tr>
                    <th scope="row"><label
                                for="inputimageurl"><?php _e( 'Image of Stripe Paid', 'stripe-payment-gti' ); ?></label>
                    </th>
                    <td>
                        <div id="inputimageurl_img"><?php
	                        if ( isset( $stripe_payment_checkout_img ) ) {
		                        $response = @file_get_contents( $stripe_payment_checkout_img );
		                        if ( $response !== false ) {
?><img src="<?php echo $stripe_payment_checkout_img; ?>" width="150px" ><?php
		                        }
	                        }
                        ?></div>
                        <input name="stripe_payment_checkout_img" type="text" id="inputimageurl"
                               value="<?php echo $stripe_payment_checkout_img; ?>" class="regular-text">
                        <input type="button" class="stripe_payment_checkout_image_button" value="<?php _e( 'Select Image.', 'stripe-payment-gti' ); ?>"><br>
                        Default Value: <?php echo STRIPE_PAYMENT_CHECKOUT_IMG_MARKETPLACE; ?>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label
                                for="inputtestmode"><?php _e( 'Test Mode', 'stripe-payment-gti' ); ?></label>
                    </th>
                    <td>
                        <?php
                        $test_mrk = "";
                        if ( isset( $stripe_payment_test_flg ) && $stripe_payment_test_flg == "1" ) {
                            $test_mrk = " checked='checked'";
                        }
                        ?>
                        <input name="stripe_payment_test_flg" type="checkbox" id="inputtestmode"
                               value="1"<?php echo $test_mrk; ?>" class="regular-text" onclick="chkTestMode(this)"></td>
                </tr>

                <tr <?php if ( $stripe_payment_test_flg == "1" ) { ?> style="display:none;"<?php } ?>>
                    <th scope="row"><label
                                for="inputpublickey"><?php _e( 'Public Key', 'stripe-payment-gti' ); ?></label>
                    </th>
                    <td><input name="stripe_payment_public_key" type="text" id="inputpublickey"
                               value="<?php echo $stripe_payment_public_key; ?>" class="regular-text"></td>
                </tr>
                <tr <?php if ( $stripe_payment_test_flg == "1" ) { ?> style="display:none;"<?php } ?>>
                    <th scope="row"><label
                                for="inputsecretkey"><?php _e( 'Secret Key', 'stripe-payment-gti' ); ?></label>
                    </th>
                    <td><input name="stripe_payment_secret_key" type="text" id="inputsecretkey"
                               value="<?php echo $stripe_payment_secret_key; ?>" class="regular-text"></td>
                </tr>

                <tr <?php if ( $stripe_payment_test_flg != "1" ) { ?> style="display:none;"<?php } ?>>
                    <th scope="row"><label
                                for="inputtestpublickey"><?php _e( 'Test Public Key', 'stripe-payment-gti' ); ?></label>
                    </th>
                    <td><input name="stripe_payment_test_public_key" type="text" id="inputtestpublickey"
                               value="<?php echo $stripe_payment_test_public_key; ?>" class="regular-text"></td>
                </tr>
                <tr <?php if ( $stripe_payment_test_flg != "1" ) { ?> style="display:none;"<?php } ?>>
                    <th scope="row"><label
                                for="inputtestsecretkey"><?php _e( 'Test Secret Key', 'stripe-payment-gti' ); ?></label>
                    </th>
                    <td><input name="stripe_payment_test_secret_key" type="text" id="inputtestsecretkey"
                               value="<?php echo $stripe_payment_test_secret_key; ?>" class="regular-text"></td>
                </tr>


                <tr>
                    <th scope="row"><label for="inputcurrency"><?php _e( 'currency', 'stripe-payment-gti' ); ?></label></th>
                    <td><input name="stripe_payment_checkout_currency" type="text" id="inputcurrency"
                               value="<?php echo $stripe_payment_checkout_currency; ?>"
                               class="regular-text"><br><?php _e( '3-letter
                            ISO code for currency. 例： jpy, usd etc. <br>Attempting to settle in multiple currencies against a single customer results in an error. (It will result in an unexpected error on the screen)', 'stripe-payment-gti' ); ?>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label
                                for="inputsubmitbtntext"><?php _e( 'Settlement Start Button Text', 'stripe-payment-gti' ); ?></label>
                    </th>
                    <td><input name="stripe_payment_checkout_label_text" type="text" id="inputsubmitbtntext"
                               value="<?php echo $stripe_payment_checkout_label_text; ?>"
                               class="regular-text"><br><?php _e( 'The invoice amount will be included in the part described as %s', 'stripe-payment-gti' ); ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label
                                for="inputcheckoutbtntext"><?php _e( 'Settlement information transmission button text', 'stripe-payment-gti' ); ?></label>
                    </th>
                    <td><input name="stripe_payment_checkout_btn_text" type="text" id="inputcheckoutbtntext"
                               value="<?php echo $stripe_payment_checkout_btn_text; ?>" class="regular-text">
                        <br><?php _e( 'The invoice amount will be included in the part described as %s', 'stripe-payment-gti' ); ?>
                    </td>
                </tr>
            </table>
            <table class="settle_table">
                <tr>
                    <th scope="row"><label
                                for="input_thanks_msg"><?php _e( 'Thank You Message', 'stripe-payment-gti' ); ?></label>
                    </th>
                    <td><textarea name="stripe_payment_thanks_message"
                                  id="input_thanks_msg" rows="10" cols="50"><?php echo $stripe_payment_thanks_message;
							?></textarea>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label
                                for="input_custom_mail_subject"><?php _e( 'EMail Subject For Customer', 'stripe-payment-gti' ); ?></label>
                    </th>
                    <td><input type="text" name="stripe_payment_customer_mail_subject"
                                  id="input_custom_mail_subject" value="<?php echo $stripe_payment_customer_mail_subject; ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label
                                for="input_custom_mail"><?php _e( 'Template Of EMail For Customer', 'stripe-payment-gti' ); ?></label>
                    </th>
                    <td><textarea name="stripe_payment_customer_mail"
                                  id="input_custom_mail" rows="10" cols="50"><?php echo $stripe_payment_customer_mail;
							?></textarea>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label
                                for="input_admin_mail_subject"><?php _e( 'EMail Subject For Administrator', 'stripe-payment-gti' ); ?></label>
                    </th>
                    <td><input type="text" name="stripe_payment_admin_mail_subject"
                               id="input_admin_mail_subject" value="<?php echo $stripe_payment_admin_mail_subject; ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label
                                for="input_admin_mail"><?php _e( 'Template Of EMail For Administrator', 'stripe-payment-gti' ); ?></label>
                    </th>
                    <td><textarea name="stripe_payment_admin_mail"
                                  id="input_admin_mail" rows="10" cols="50"><?php echo $stripe_payment_admin_mail;
							?></textarea>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label
                                for="input_from_email_name"><?php _e( 'From Email Name', 'stripe-payment-gti' ); ?></label>
                    </th>
                    <td><input type="text" name="stripe_payment_from_email_name"
                                  id="input_from_email_name" value="<?php echo $stripe_payment_from_email_name;
				            ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label
                                for="input_from_email_address"><?php _e( 'From Email Address', 'stripe-payment-gti' ); ?></label>
                    </th>
                    <td><input type="email" name="stripe_payment_from_email_address"
                               id="input_from_email_address" value="<?php echo $stripe_payment_from_email_address;
		                ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label
                                for="input_replyto_email_name"><?php _e( 'Reply to Email Name', 'stripe-payment-gti' ); ?></label>
                    </th>
                    <td><input type="text" name="stripe_payment_replyto_email_name"
                               id="input_replyto_email_name" value="<?php echo $stripe_payment_replyto_email_name;
			            ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label
                                for="input_replyto_email_address"><?php _e( 'Reply to Email Address', 'stripe-payment-gti' ); ?></label>
                    </th>
                    <td><input type="email" name="stripe_payment_replyto_email_address"
                               id="input_replyto_email_address" value="<?php echo $stripe_payment_replyto_email_address;
			            ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label
                                for="input_no_item_html"><?php _e( 'No Item HTML', 'stripe-payment-gti' ); ?></label>
                    </th>
                    <td><textarea name="stripe_payment_no_item_html"
                                  id="input_no_item_html" rows="10" cols="50"><?php echo $stripe_payment_no_item_html;
			                ?></textarea>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label
                                for="input_stripe_payment_loading_gif"><?php _e( 'Stripe\'s processing wait after inputting credit card Loading image', 'stripe-payment-gti' ); ?></label>
                    </th>
                    <td><input name="stripe_payment_loading_gif" type="text"
                               id="input_stripe_payment_loading_gif"
                               value="<?php echo $stripe_payment_loading_gif; ?>"/></td>
                </tr>

            </table>

            <input name="usces_option_update" type="submit" class="button button-primary"
                   value="<?php _e( 'Update settings', 'stripe-payment-gti' ); ?>"/>
			<?php wp_nonce_field( 'admin_settlement', 'wc_nonce' ); ?>
        </form>
        <table>
            <tr>
                <th scope="row"><label for="input_stripe_payment_item_count_by_payid"><?php _e( 'Stripe Item Result Count', 'stripe-payment-gti' ); ?></label></th>
                <td>
			        <?php
			        $result_counts = maybe_unserialize( get_option( 'stripe-payment_result-counts', "" ) );
			        if ( $result_counts && is_array( $result_counts ) && count( $result_counts ) > 0 ) {
				        ?>
                        <table>
                            <tr>
                                <th>項目名</th>
                                <th>残数</th>
                                <th></th>
                            </tr>
					        <?php
					        foreach ( $result_counts as $key=>$val ) {
						        ?>
                                <tr>
                                    <th><?php echo $key; ?><input type="hidden" name="key" value="<?php echo $key; ?>"></th>
                                    <td><?php echo $val; ?></td>
                                    <td><input type="number" id="set_result_count_<?php echo $key; ?>" value=""><button id="result_count_<?php echo $key; ?>">残数設定</button></td>
                                </tr>
						        <?php
					        }
					        ?>
                        </table>
				        <?php
			        }

			        ?>
                </td>
            </tr>

        </table>
        <div class="settle_exp">
            <a href="https://stripe.com/"
               target="_blank"><?php _e( 'Details of Stripe service are here', 'stripe-payment-gti' ); ?> 》</a>
        </div>
        </div><!-- /uscestabs_stripe -->
        <script>
            var ajaxurl = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
            jQuery( function () {
                jQuery("[id^=result_count_]").on("click",function(){
                    reset_key = jQuery(this).attr("id");
                    reset_cnt = jQuery("#set_"+reset_key).val();
                    jQuery.ajax({
                        type: 'POST',
                        url: ajaxurl,
                        data: {
                            'action' : 'reset_count',
                            'mes' : reset_key,
                            'cnt' : reset_cnt,
                        },
                        success: function( response ){
                            if ( response.length > 1 ) {
                                response = response.substring(0, response.length - 1);
                            }
                            alert( response );
                        }
                    });
                    return false;
                });
            });
        </script>
		<?php
	}

	/**
     * 在庫数調整
     */
	function reset_item_counter(){
	    // POSTデータからキー取得
        $reset_key = esc_attr( $_POST['mes'] );
        if ( strlen( $reset_key ) > 0 ) {
            $reset_key = substr( $reset_key, strlen( "result_count_" ), strlen( $reset_key ) );
        }
        $reset_cnt = esc_attr( $_POST['cnt'] );

        // カウンターリセット
        $reset_array = array();
		$result_counts = maybe_unserialize( get_option( 'stripe-payment_result-counts', "" ) );
		$change_flg = false;
		if ( $result_counts && is_array( $result_counts ) && count( $result_counts ) > 0 ) {
            foreach ( $result_counts as $key=>$val ) {
                if ( $key === $reset_key ) {
                    $reset_array[ $key ] = $reset_cnt;
                    $change_flg = true;
                } else {
                    $reset_array[ $key ] = $val;
                }
            }
		}
		if ( $change_flg === true ) {
			$serial_data = serialize( $reset_array );

			update_option( 'stripe-payment_result-counts', $serial_data );
			echo "RESET : ".$reset_key." => ".$reset_cnt." Saved.";
		} else {
			echo "Not Save.".$reset_key." ---- ".get_option( 'stripe-payment_result-counts', "" );
		}
	}

}

if ( is_admin() ) {
	$showtext = new StripePaymentSetting;
	add_action( 'admin_head', array( $showtext, 'admin_item_init' ) );
}

