<?php
require_once( STRIPE_PAYMENT_PLUGIN_DIR . '/stripe-php/init.php' );

class StripePaymentSetting {   // 管理画面
	function __construct() {
		// language
		load_plugin_textdomain( 'stripe-payment-gti', false, basename( dirname( __FILE__ ) ) . '/languages' );

		add_action( 'admin_menu', array( $this, 'add_pages' ) );


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

			$stripe_payment_public_key = esc_attr( $_POST['stripe_payment_public_key'] );
			update_option( 'stripe_payment_public_key', $stripe_payment_public_key );
			$stripe_payment_secret_key = esc_attr( $_POST['stripe_payment_secret_key'] );
			update_option( 'stripe_payment_secret_key', $stripe_payment_secret_key );

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

			$stripe_payment_public_key = get_option( 'stripe_payment_public_key' );
			$stripe_payment_secret_key = get_option( 'stripe_payment_secret_key' );

			$stripe_payment_checkout_currency = get_option( 'stripe_payment_checkout_currency', 'jpy' );

			$stripe_payment_checkout_btn_text = get_option( 'stripe_payment_checkout_btn_text' );
			$stripe_payment_checkout_label_text   = get_option( 'stripe_payment_checkout_label_text' );

			$stripe_payment_thanks_message = get_option( 'stripe_payment_thanks_message' );
			$stripe_payment_customer_mail_subject = get_option( 'stripe_payment_customer_mail_subject' );
			$stripe_payment_customer_mail  = get_option( 'stripe_payment_customer_mail' );
			$stripe_payment_admin_mail_subject = get_option( 'stripe_payment_admin_mail_subject' );
			$stripe_payment_admin_mail     = get_option( 'stripe_payment_admin_mail' );

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
                        <input name="stripe_payment_checkout_img" type="text" id="inputimageurl"
                               value="<?php echo $stripe_payment_checkout_img; ?>" class="regular-text"><br>
                        Default Value: <?php echo STRIPE_PAYMENT_CHECKOUT_IMG_MARKETPLACE; ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label
                                for="inputpublickey"><?php _e( 'Public Key', 'stripe-payment-gti' ); ?></label>
                    </th>
                    <td><input name="stripe_payment_public_key" type="text" id="inputpublickey"
                               value="<?php echo $stripe_payment_public_key; ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label
                                for="inputsecretkey"><?php _e( 'Secret Key', 'stripe-payment-gti' ); ?></label>
                    </th>
                    <td><input name="stripe_payment_secret_key" type="text" id="inputsecretkey"
                               value="<?php echo $stripe_payment_secret_key; ?>" class="regular-text"></td>
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
                                  id="input_thanks_msg"><?php echo $stripe_payment_thanks_message;
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
                                  id="input_custom_mail"><?php echo $stripe_payment_customer_mail;
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
                                  id="input_admin_mail"><?php echo $stripe_payment_admin_mail;
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

                <tr>
                    <th scope="row"><label for="input_stripe_payment_item_count_by_payid"><?php _e( 'Stripe Item Result Count', 'stripe-payment-gti' ); ?></label></th>
                    <td>
                        <?php
                        $result_counts = maybe_unserialize( get_option( 'stripe-payment_result-counts', "" ) );
                        if ( $result_counts && is_array( $result_counts ) && count( $result_counts ) > 0 ) {
                            foreach ( $result_counts as $key=>$val ) {
                                echo $key." = ".$val."<br>";
                            }
                        }

                        ?>
                    </td>
                </tr>
            </table>

            <input name="usces_option_update" type="submit" class="button button-primary"
                   value="<?php _e( 'Update settings', 'stripe-payment-gti' ); ?>"/>
			<?php wp_nonce_field( 'admin_settlement', 'wc_nonce' ); ?>
        </form>
        <div class="settle_exp">
            <a href="https://stripe.com/"
               target="_blank"><?php _e( 'Details of Stripe service are here', 'stripe-payment-gti' ); ?> 》</a>
        </div>
        </div><!-- /uscestabs_stripe -->
		<?php
	}

}

if ( is_admin() ) {
	$showtext = new StripePaymentSetting;

}

function stripe_admin_add_my_ajaxurl() {
	?>
    <script>
        var ajaxurl = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
    </script>
	<?php
}

add_action( 'wp_head', 'stripe_admin_add_my_ajaxurl', 1 );
