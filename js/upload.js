jQuery.noConflict();
(function($) {

    jQuery('.stripe_payment_checkout_image_button').on('click',function() {
        tb_show('', 'media-upload.php?type=image&post_id=&TB_iframe=true');

        window.original_send_to_editor = window.send_to_editor;

        window.send_to_editor = function(html) {
            imgurl_match = html.match(/src="(.*?)"/);
            if (imgurl_match[1] != "") {
                jQuery('#inputimageurl').val(imgurl_match[1]);
                jQuery('#inputimageurl_img').html('<img src="'+imgurl_match[1]+'" />');
                tb_remove();
            } else {
                jQuery('#inputimageurl').val('');
                jQuery('#inputimageurl_img').html('');
            }
            window.send_to_editor = window.original_send_to_editor;
        };
        return false;
    });

})(jQuery);

function chkTestMode( elem ) {
    if ( elem.checked == false ) {
        jQuery('#inputpublickey').parent('td').parent('tr').css('display', '');
        jQuery('#inputsecretkey').parent('td').parent('tr').css('display', '');
        jQuery('#inputtestpublickey').parent('td').parent('tr').css('display', 'none');
        jQuery('#inputtestsecretkey').parent('td').parent('tr').css('display', 'none');
    } else {
        jQuery('#inputpublickey').parent('td').parent('tr').css('display', 'none');
        jQuery('#inputsecretkey').parent('td').parent('tr').css('display', 'none');
        jQuery('#inputtestpublickey').parent('td').parent('tr').css('display', '');
        jQuery('#inputtestsecretkey').parent('td').parent('tr').css('display', '');
    }
}