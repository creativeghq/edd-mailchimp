jQuery(document).ready(function($){
    
    jQuery(document).on("change","#edd-email-wrap #edd-email,#edd-first-name-wrap #edd-first,#edd-last-name-wrap #edd-last,#edd-user-login-wrap #edd_user_login,#edd-card-address-wrap #card_address,#edd-card-city-wrap #card_city,#edd-card-country-wrap #billing_country,#edd-card-state-wrap #card_state",function(){
        
        var edd_email = jQuery("#edd-email-wrap #edd-email").val();
        var edd_first_name = jQuery("#edd-first-name-wrap #edd-first").val();
        var edd_last_name = jQuery("#edd-last-name-wrap #edd-last").val();    
        var edd_cart_items = [];


        jQuery.ajax({
            url: cart_script_object.ajax_url,
            data: {
                    'action':'action_save_logout_user',
                    'edd_email' : edd_email,
                    'edd_first_name' : edd_first_name,
                    'edd_last_name' : edd_last_name
                },
                dataType:'JSON',
                type : 'post',
                success:function(data) {
                    // console.log(data);   
                },
                error: function(errorThrown){
                    // console.log(errorThrown);
                }
            }); 
        });
});

