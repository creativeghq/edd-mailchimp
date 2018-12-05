jQuery(document).ready(function($){
    jQuery('#edd_edd_mailchimp_abandoned_cart_wordpress_plugin_license_key-nonce').parent().find('.edd-license-error').hide();
    jQuery('#edd_edd_mailchimp_abandoned_cart_wordpress_plugin_license_key-nonce').parent().find('.license-expires-soon-notice').hide();
    jQuery("input[name='edd_settings[edd_abandoned_mailchimp_api1]']").change(function(){

        var mailChimpKey = jQuery(this).val();
        
        jQuery("select[name='edd_settings[edd_abandoned_mailchimp_merge_field_id]']").html('<option value="false">Please wait..</option>');
        jQuery("select[name='edd_settings[edd_settings[edd_abandoned_mailchimp_merge_field_total_spend_id]]']").html('<option value="false">Please wait..</option>');
        jQuery("select[name='edd_settings[edd_settings[edd_abandoned_mailchimp_merge_field_subscription_expiring_date_id]]']").html('<option value="false">Please wait..</option>');
        jQuery("select[name='edd_settings[edd_settings[edd_abandoned_mailchimp_merge_field_subscription_started_date_id]]']").html('<option value="false">Please wait..</option>');
        jQuery("select[name='edd_settings[edd_settings[edd_abandoned_mailchimp_merge_field_subscription_total_dates_id]]']").html('<option value="false">Please wait..</option>');
        jQuery("select[name='edd_settings[edd_settings[edd_abandoned_mailchimp_merge_field_subscription_status_id]]']").html('<option value="false">Please wait..</option>');
        jQuery("select[name='edd_settings[edd_settings[edd_abandoned_mailchimp_merge_field_subscription_frequency_id]]']").html('<option value="false">Please wait..</option>');
        jQuery.ajax({
            url: abd_admin_script_object.ajax_url,
            data: {
                    'action':'load_mailchimp_stores',
                    'mailChimpKey' : mailChimpKey
                },
            dataType:'JSON',
            type : 'post',
            success:function(res) {
                
                var options = '<p>';
                if(res['data']){
                    var store_name = res.data.store_name;
                    var store_domain = res.data.store_domain;
                    // var site_script = res.data.site_script;
                    // var site_script_url_live = res.data.site_script_url_live;
                    
                    var manage_store = '<p><a class="" href="<?= get_admin_url();?>admin.php?page=create-store" target="_self">Click Here</a> to Manage your Stores</p>';
                    if(store_name && !store_name.false){
                        options += 'Store Name : <b>'+store_name+'</b>';
                        if(store_domain){
                            options += '&nbsp;&nbsp;URL : <a href="'+store_domain+'">'+store_domain+'</a>';
                        }
                        options += manage_store;
                        if(site_script){
                            options += '<p>Following javascript code will be added into your website.</p>';
                            options += '<code>'+site_script+'</code>';
                        }
                    }else{
                        options += '<p><b>No Store found for this site.</b></p>';
                        options += manage_store;
                    }
                    var sel_options = '';
                    var mailChimpStoreId = '0';
                    
                    jQuery.each(res['data']['populate_store_dropdown'], function(index, value ) {
                        sel_options += '<option value="'+index+'">'+value+'</option>';
                        mailChimpStoreId = index;
                    });
                    jQuery("select[name='edd_settings[edd_abandoned_mailchimp_store_id]']").html(sel_options);
                    
                    // if(site_script_url_live === null || site_script_url_live == ''){
                    //     jQuery(".abandoned_mailchimp_connected_site_script input").attr('value','');
                    // }else{
                    //     jQuery(".abandoned_mailchimp_connected_site_script input").attr('value',site_script_url_live);
                    // }
                    

                    update_merge_fields(mailChimpStoreId,mailChimpKey);
                }  
                jQuery(".mc_store_data td").html(options);
            },
            error: function(errorThrown){
                // console.log(errorThrown);
            }
        }); 

    });
    
    jQuery("input[name='edd_settings[edd_abandoned_mailchimp_api]']").change(function(){

        var mailChimpKey = jQuery(this).val();
        
        jQuery("select[name='edd_settings[edd_abandoned_mailchimp_store_id]']").html('<option value="false">Please wait..</option>');
        jQuery.ajax({
            url: abd_admin_script_object.ajax_url,
            data: {
                    'action':'load_mailchimp_stores',
                    'mailChimpKey' : mailChimpKey
                },
            dataType:'JSON',
            type : 'post',
            success:function(res) {
                
                var options = '';
                if(res['data']){
                    var store_name = res.data.store_name;
                    var store_domain = res.data.store_domain;
                    // var site_script = res.data.site_script;
                    // var site_script_url_live = res.data.site_script_url_live;
                    
                    /*var manage_store = '<p><a class="" href="<?= get_admin_url();?>admin.php?page=create-store" target="_self">Click Here</a> to Manage your Stores</p>';
                    if(store_name && !store_name.false){
                        options += 'Store Name : <b>'+store_name+'</b>';
                        if(store_domain){
                            options += '&nbsp;&nbsp;URL : <a href="'+store_domain+'">'+store_domain+'</a>';
                        }
                        options += manage_store;
                        // if(site_script){
                        //     options += '<p>Following javascript code will be added into your website.</p>';
                        //     options += '<code>'+site_script+'</code>';
                        // }
                    }else{
                        options += '<p><b>No Store found for this site.</b></p>';
                        options += manage_store;
                    }
                   
                    var mailChimpStoreId = '0';*/
                     var sel_options = '';
                    jQuery.each(res['data'], function(index, value ) {
                        sel_options += '<option value="'+index+'">'+value+'</option>';
                        // mailChimpStoreId = index;
                    });
                    jQuery("select[name='edd_settings[edd_abandoned_mailchimp_store_id]']").html(sel_options);
                    
                    // if(site_script_url_live === null || site_script_url_live == ''){
                    //     jQuery(".abandoned_mailchimp_connected_site_script input").attr('value','');
                    // }else{
                    //     jQuery(".abandoned_mailchimp_connected_site_script input").attr('value',site_script_url_live);
                    // }
                    

                    // update_merge_fields(mailChimpStoreId,mailChimpKey);
                }  
                jQuery(".mc_store_data td").html(options);
            },
            error: function(errorThrown){
                // console.log(errorThrown);
            }
        }); 
    });




    function update_merge_fields(mailChimpStoreId,mailChimpKey){
        jQuery("select[name='edd_settings[edd_abandoned_mailchimp_merge_field_id]']").html('<option value="false">Please wait..</option>');
        jQuery.ajax({
            url: abd_admin_script_object.ajax_url,
            data: {
                    'action':'load_mailchimp_merge_fields',
                    'mailChimpKey' : mailChimpKey,
                    'mailChimpStoreId' : mailChimpStoreId,
                },
            dataType:'JSON',
            type : 'post',
            success:function(res) {
                
                var options = '';
                if(res['data']){

                    jQuery.each(res['data'], function(index, value ) {
                        options += '<option value="'+index+'">'+value+'</option>';
                    });
                }  
                jQuery("select[name='edd_settings[edd_abandoned_mailchimp_merge_field_id]']").html(options);
                // if(res.list_name){
                //     var list_using = '<p><small>Selected Store use <b>'+res.list_name +'</b> List.</small></p>';
                //     jQuery("select[name='edd_settings[edd_abandoned_mailchimp_store_id]']").parent().append(list_using);
                // }
            },
            error: function(errorThrown){
                // console.log(errorThrown);
            }
        }); 
    }

    function update_merge_fields_total_spend(mailChimpStoreId,mailChimpKey){
        jQuery("select[name='edd_settings[edd_abandoned_mailchimp_merge_field_total_spend_id]']").html('<option value="false">Please wait..</option>');
        jQuery.ajax({
            url: abd_admin_script_object.ajax_url,
            data: {
                    'action':'load_mailchimp_merge_fields',
                    'mailChimpKey' : mailChimpKey,
                    'mailChimpStoreId' : mailChimpStoreId,
                },
            dataType:'JSON',
            type : 'post',
            success:function(res) {
                
                var options = '';
                if(res['data']){

                    jQuery.each(res['data'], function(index, value ) {
                        options += '<option value="'+index+'">'+value+'</option>';
                    });
                }  
                jQuery("select[name='edd_settings[edd_abandoned_mailchimp_merge_field_total_spend_id]']").html(options);
             },
            error: function(errorThrown){
                // console.log(errorThrown);
            }
        }); 
    }

    function update_merge_fields_subscription_expiring(mailChimpStoreId,mailChimpKey){
        jQuery("select[name='edd_settings[edd_abandoned_mailchimp_merge_field_subscription_expiring_date_id]']").html('<option value="false">Please wait..</option>');
        jQuery.ajax({
            url: abd_admin_script_object.ajax_url,
            data: {
                    'action':'load_mailchimp_merge_fields',
                    'mailChimpKey' : mailChimpKey,
                    'mailChimpStoreId' : mailChimpStoreId,
                },
            dataType:'JSON',
            type : 'post',
            success:function(res) {
                
                var options = '';
                if(res['data']){

                    jQuery.each(res['data'], function(index, value ) {
                        options += '<option value="'+index+'">'+value+'</option>';
                    });
                }  
                jQuery("select[name='edd_settings[edd_abandoned_mailchimp_merge_field_subscription_expiring_date_id]']").html(options);
                // if(res.list_name){
                //     var list_using = '<p><small>Selected Store use <b>'+res.list_name +'</b> List.</small></p>';
                //     jQuery("select[name='edd_settings[edd_abandoned_mailchimp_store_id]']").parent().append(list_using);
                // }
            },
            error: function(errorThrown){
                // console.log(errorThrown);
            }
        }); 
    }


    function update_merge_fields_subscription_started(mailChimpStoreId,mailChimpKey){
        jQuery("select[name='edd_settings[edd_abandoned_mailchimp_merge_field_subscription_started_date_id]']").html('<option value="false">Please wait..</option>');
        jQuery.ajax({
            url: abd_admin_script_object.ajax_url,
            data: {
                    'action':'load_mailchimp_merge_fields',
                    'mailChimpKey' : mailChimpKey,
                    'mailChimpStoreId' : mailChimpStoreId,
                },
            dataType:'JSON',
            type : 'post',
            success:function(res) {
                
                var options = '';
                if(res['data']){

                    jQuery.each(res['data'], function(index, value ) {
                        options += '<option value="'+index+'">'+value+'</option>';
                    });
                }  
                jQuery("select[name='edd_settings[edd_abandoned_mailchimp_merge_field_subscription_started_date_id]']").html(options);
                // if(res.list_name){
                //     var list_using = '<p><small>Selected Store use <b>'+res.list_name +'</b> List.</small></p>';
                //     jQuery("select[name='edd_settings[edd_abandoned_mailchimp_store_id]']").parent().append(list_using);
                // }
            },
            error: function(errorThrown){
                // console.log(errorThrown);
            }
        }); 
    }
    
     function update_merge_fields_subscription_total(mailChimpStoreId,mailChimpKey){
        jQuery("select[name='edd_settings[edd_abandoned_mailchimp_merge_field_subscription_total_dates_id]']").html('<option value="false">Please wait..</option>');
        jQuery.ajax({
            url: abd_admin_script_object.ajax_url,
            data: {
                    'action':'load_mailchimp_merge_fields',
                    'mailChimpKey' : mailChimpKey,
                    'mailChimpStoreId' : mailChimpStoreId,
                },
            dataType:'JSON',
            type : 'post',
            success:function(res) {
                
                var options = '';
                if(res['data']){

                    jQuery.each(res['data'], function(index, value ) {
                        options += '<option value="'+index+'">'+value+'</option>';
                    });
                }  
                jQuery("select[name='edd_settings[edd_abandoned_mailchimp_merge_field_subscription_total_dates_id]']").html(options);
                // if(res.list_name){
                //     var list_using = '<p><small>Selected Store use <b>'+res.list_name +'</b> List.</small></p>';
                //     jQuery("select[name='edd_settings[edd_abandoned_mailchimp_store_id]']").parent().append(list_using);
                // }
            },
            error: function(errorThrown){
                // console.log(errorThrown);
            }
        }); 
    }
    function update_merge_fields_subscription_frequency(mailChimpStoreId,mailChimpKey){
        jQuery("select[name='edd_settings[edd_abandoned_mailchimp_merge_field_subscription_frequency_id]']").html('<option value="false">Please wait..</option>');
        jQuery.ajax({
            url: abd_admin_script_object.ajax_url,
            data: {
                    'action':'load_mailchimp_merge_fields',
                    'mailChimpKey' : mailChimpKey,
                    'mailChimpStoreId' : mailChimpStoreId,
                },
            dataType:'JSON',
            type : 'post',
            success:function(res) {
                
                var options = '';
                if(res['data']){

                    jQuery.each(res['data'], function(index, value ) {
                        options += '<option value="'+index+'">'+value+'</option>';
                    });
                }  
                jQuery("select[name='edd_settings[edd_abandoned_mailchimp_merge_field_subscription_total_dates_id]']").html(options);
                // if(res.list_name){
                //     var list_using = '<p><small>Selected Store use <b>'+res.list_name +'</b> List.</small></p>';
                //     jQuery("select[name='edd_settings[edd_abandoned_mailchimp_store_id]']").parent().append(list_using);
                // }
            },
            error: function(errorThrown){
                // console.log(errorThrown);
            }
        }); 
    }

    function update_merge_fields_subscription_status(mailChimpStoreId,mailChimpKey){
        jQuery("select[name='edd_settings[edd_abandoned_mailchimp_merge_field_subscription_status_id]']").html('<option value="false">Please wait..</option>');
        jQuery.ajax({
            url: abd_admin_script_object.ajax_url,
            data: {
                    'action':'load_mailchimp_merge_fields',
                    'mailChimpKey' : mailChimpKey,
                    'mailChimpStoreId' : mailChimpStoreId,
                },
            dataType:'JSON',
            type : 'post',
            success:function(res) {
                
                var options = '';
                if(res['data']){

                    jQuery.each(res['data'], function(index, value ) {
                        options += '<option value="'+index+'">'+value+'</option>';
                    });
                }  
                jQuery("select[name='edd_settings[edd_abandoned_mailchimp_merge_field_subscription_status_id]']").html(options);
                // if(res.list_name){
                //     var list_using = '<p><small>Selected Store use <b>'+res.list_name +'</b> List.</small></p>';
                //     jQuery("select[name='edd_settings[edd_abandoned_mailchimp_store_id]']").parent().append(list_using);
                // }
            },
            error: function(errorThrown){
                // console.log(errorThrown);
            }
        }); 
    }


    jQuery("select[name='edd_settings[edd_abandoned_mailchimp_store_id]']").change(function(){

        var mailChimpKey = jQuery("input[name='edd_settings[edd_abandoned_mailchimp_api]']").val();
        var mailChimpStoreId = jQuery(this).val();
        

        update_merge_fields(mailChimpStoreId,mailChimpKey);
        update_merge_fields_total_spend(mailChimpStoreId,mailChimpKey);
        update_merge_fields_subscription_expiring(mailChimpStoreId,mailChimpKey);
        update_merge_fields_subscription_started(mailChimpStoreId,mailChimpKey);
        update_merge_fields_subscription_total(mailChimpStoreId,mailChimpKey);
        update_merge_fields_subscription_status(mailChimpStoreId,mailChimpKey);
        update_merge_fields_subscription_frequency(mailChimpStoreId,mailChimpKey);
    });



}); 