<?php
/**
 * Register Settings
 *
 * @package     Mailchimp Vendor Email Trigger
 * @since       1.0.0
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add settings section
 *
 * @param       array $sections The existing extensions sections
 * @return      array The modified extensions settings
 * @package 	Mailchimp Vendor Email Trigger
 * @since 		1.0.0
 */
function edd_mailchimp_abd_add_settings_section( $sections ) {
	$sections['mailchimp_abandoned_cart'] = __( 'EDD Mailchimp Abandoned Cart', 'edd-mailchimp-abandoned-cart' );

	return $sections;
}

add_filter( 'edd_settings_sections_extensions', 'edd_mailchimp_abd_add_settings_section' );


/**
 * Add settings
 *
 * @param       array $settings The existing plugin settings
 * @return      array The modified plugin settings
 * @package 	Mailchimp Vendor Email Trigger
 * @since 		1.0.0
 */
function edd_mailchimp_abd_add_settings( $settings ) {

	$api_data = new EDD_Mailchimp_Abandoned_Cart();

	if( EDD_VERSION >= '2.5' ) {
		$mergeFields = $api_data->mailchimp_get_all_merge_fields('','');
		$new_settings = array(
			'mailchimp_abandoned_cart' => apply_filters( 'edd_mailchimp_abandoned_cart_settings', array(
				array(
					'id'   => 'edd_mailchimp_api_config',
					'name' => '<h1>' . __( 'EDD Mailchimp Abandoned Cart', 'edd-mailchimp-abandoned-cart' ) . '</h1>',
					'desc' => 'For detailed documentation please visit the plugin homepage <a class="update-nag" href="https://www.pluginsandsnippets.com/documentation/edd-mailchimp-abandoned-cart-plugin-documentation/" target="_blank">here.</a>',
					'type' => 'header'
				),
				array(
					'id'   => 'edd_mailchimp_api_setting',
					'name' => '<strong>' . __( 'Mailchimp Settings', 'edd-mailchimp-abandoned-cart' ) . '</strong>',
					'desc' => '',
					'type' => 'header'
				),
				array(
					'id'   => 'edd_abandoned_mailchimp_api',
					'name' => __( 'MailChimp API Key', 'edd-mailchimp-abandoned-cart' ),
					'desc' => __( 'Enter your <a class="" href="https://kb.mailchimp.com/integrations/api-integrations/about-api-keys" target="_blank">MailChimp API key</a>', 'edd-mailchimp-abandoned-cart' ),
					'type' => 'text',
					'size' => 'regular'
				),
				array(
					'id'      => 'edd_abandoned_mailchimp_store_id',
					'name'    => __( 'Choose a Store', 'edd-mailchimp-abandoned-cart' ),
					'desc' => __( '<a class="" href="'.get_admin_url().'admin.php?page=create-store" target="_self">Click Here</a> to Manage your Stores', 'edd-mailchimp-abandoned-cart' ),
					'type'    => 'select',
					'class'   => 'edd_abandoned_mailchimp_store_id',
					'options' => $api_data->get_stores('','')
				),
				

				array(
					'id'      => 'edd_abandoned_mailchimp_add_subscriber_status',
					'name'    => __( 'Add Subscriber Status', 'edd-mailchimp-abandoned-cart' ),
					'desc' => __( 'Select True as default value so that you can send other email campaigns to this user if needed. <a class="" href="http://developer.mailchimp.com/documentation/mailchimp/guides/getting-started-with-ecommerce/#about-subscribers-and-customers" target="_blank">Click Here</a> to Know More about it.', 'edd-mailchimp-abandoned-cart' ),
					'type'    => 'select',
					'class'   => 'edd_abandoned_mailchimp_add_subscriber_status',
					'options' => $api_data->get_subscriber_status()
				),
				array(
					'id'   => 'edd_abandoned_mailchimp_edd_token_day',
					'name' => __( 'Set EDD Cart Token Expiry Time (Day)', 'edd-mailchimp-abandoned-cart' ),
					'desc' => __( "The link to the shopping cart id the prospect will receive will only work as long as the cookie in the user's browser is valid. Therefore we recommend using 3-7 days to be on the safe side. Default value is 7 days.", 'edd-mailchimp-abandoned-cart' ),
					'type' => 'number',
					'max' => '365',
					'size' => 'regular'
				),
				array(
					'id'   => 'sync_best_pro_settings_additional',
					'name' => '',
					'desc' => '',
					'type' => 'hook'
				),
				array(
					'id'   => 'edd_mailchimp_discount_setting',
					'name' => '<strong>' . __( 'Discount Settings', 'edd-mailchimp-abandoned-cart' ) . '</strong>',
					'desc' => '',
					'type' => 'header'
				),
				array(
					'id'      => 'edd_abandoned_discount_status',
					'name'    => __( 'Create Discount Code?', 'edd-mailchimp-abandoned-cart' ),
					'desc' => '',
					'type'    => 'select',
					'options' => $api_data->get_discountCode_status()
				),
				array(
					'id'      => 'edd_abandoned_mailchimp_merge_field_id',
					'name'    => __( 'Choose a Merge Field to store Discount Code', 'edd-mailchimp-abandoned-cart' ),
					'desc' => __( 'Discount codes will be saved in an additional field in your customer list in Mailchimp. Go to your List Page in Mailchimp, Settings, and add/edit your List fields and *|MERGE|* tags as needed. <a class="" href="https://kb.mailchimp.com/lists/manage-contacts/manage-list-and-signup-form-fields" target="_blank">MailChimp Doc</a>', 'edd-mailchimp-abandoned-cart' ),
					'type'    => 'select',
					'class'   => 'edd_abandoned_mailchimp_merge_field_id',
					'options' => $mergeFields
				),
				array(
					'id'      => 'edd_abandoned_mailchimp_merge_field_total_spend_id',
					'name'    => __( 'Choose a Merge Field to store Total Spend', 'edd-mailchimp-abandoned-cart' ),
					'desc' => __( 'Total Spend . Go to your List Page in Mailchimp, Settings, and add/edit your List fields and *|MERGE|* tags as needed. <a class="" href="https://kb.mailchimp.com/lists/manage-contacts/manage-list-and-signup-form-fields" target="_blank">MailChimp Doc</a>', 'edd-mailchimp-abandoned-cart' ),
					'type'    => 'select',
					'class'   => 'edd_abandoned_mailchimp_merge_field_id',
					'options' => $mergeFields
				),
				array(
					'id'      => 'edd_abandoned_mailchimp_merge_field_subscription_expiring_date_id',
					'name'    => __( 'Choose a Merge Field to store Subscription Expiring Date', 'edd-mailchimp-abandoned-cart' ),
					'desc' => __( 'Subscription Expiring Date . Go to your List Page in Mailchimp, Settings, and add/edit your List fields and *|MERGE|* tags as needed. <a class="" href="https://kb.mailchimp.com/lists/manage-contacts/manage-list-and-signup-form-fields" target="_blank">MailChimp Doc</a>', 'edd-mailchimp-abandoned-cart' ),
					'type'    => 'select',
					'class'   => 'edd_abandoned_mailchimp_merge_field_id',
					'options' => $mergeFields
				),
				array(
					'id'      => 'edd_abandoned_mailchimp_merge_field_subscription_started_date_id',
					'name'    => __( 'Choose a Merge Field to store Subscription Started Date', 'edd-mailchimp-abandoned-cart' ),
					'desc' => __( 'Subscription Started Date . Go to your List Page in Mailchimp, Settings, and add/edit your List fields and *|MERGE|* tags as needed. <a class="" href="https://kb.mailchimp.com/lists/manage-contacts/manage-list-and-signup-form-fields" target="_blank">MailChimp Doc</a>', 'edd-mailchimp-abandoned-cart' ),
					'type'    => 'select',
					'class'   => 'edd_abandoned_mailchimp_merge_field_id',
					'options' => $mergeFields
				),
				array(
					'id'      => 'edd_abandoned_mailchimp_merge_field_subscription_total_dates_id',
					'name'    => __( 'Choose a Merge Field to store Subscription Total Days', 'edd-mailchimp-abandoned-cart' ),
					'desc' => __( 'Subscription Total Days . Go to your List Page in Mailchimp, Settings, and add/edit your List fields and *|MERGE|* tags as needed. <a class="" href="https://kb.mailchimp.com/lists/manage-contacts/manage-list-and-signup-form-fields" target="_blank">MailChimp Doc</a>', 'edd-mailchimp-abandoned-cart' ),
					'type'    => 'select',
					'class'   => 'edd_abandoned_mailchimp_merge_field_id',
					'options' => $mergeFields
				),
				array(
					'id'      => 'edd_abandoned_mailchimp_merge_field_subscription_frequency_id',
					'name'    => __( 'Choose a Merge Field to store Subscription Frequency', 'edd-mailchimp-abandoned-cart' ),
					'desc' => __( 'Subscription Frequency . Go to your List Page in Mailchimp, Settings, and add/edit your List fields and *|MERGE|* tags as needed. <a class="" href="https://kb.mailchimp.com/lists/manage-contacts/manage-list-and-signup-form-fields" target="_blank">MailChimp Doc</a>', 'edd-mailchimp-abandoned-cart' ),
					'type'    => 'select',
					'class'   => 'edd_abandoned_mailchimp_merge_field_id',
					'options' => $mergeFields
				),
				array(
					'id'      => 'edd_abandoned_mailchimp_merge_field_subscription_status_id',
					'name'    => __( 'Choose a Merge Field to store Subscription Status', 'edd-mailchimp-abandoned-cart' ),
					'desc' => __( 'Subscription Status . Go to your List Page in Mailchimp, Settings, and add/edit your List fields and *|MERGE|* tags as needed. <a class="" href="https://kb.mailchimp.com/lists/manage-contacts/manage-list-and-signup-form-fields" target="_blank">MailChimp Doc</a>', 'edd-mailchimp-abandoned-cart' ),
					'type'    => 'select',
					'class'   => 'edd_abandoned_mailchimp_merge_field_id',
					'options' => $mergeFields
				),
				array(
					'id'   => 'edd_abandoned_mailchimp_discount_code_prefix',
					'name' => __( 'Set Discount Code Prefix', 'edd-mailchimp-abandoned-cart' ),
					'desc' => __( 'Set Discount Code Prefix', 'edd-mailchimp-abandoned-cart' ),
					'type' => 'text',
					'size' => 'regular'
				),
				array(
					'id'   => 'edd_abandoned_mailchimp_discount_percentage',
					'name' => __( 'Set Discount (%)', 'edd-mailchimp-abandoned-cart' ),
					'desc' => '',
					'type' => 'number',
					'max' => '100',
					'size' => 'regular'
				),
				array(
					'id'   => 'edd_abandoned_mailchimp_discount_day',
					'name' => __( 'Set Discount Time (Day)', 'edd-mailchimp-abandoned-cart' ),
					'desc' => __( '', 'edd-mailchimp-abandoned-cart' ),
					'type' => 'number',
					'max' => '365',
					'size' => 'regular'
				),
				array(
					'id'   => 'delete_plugin_generated_codes',
					'name' => '',
					'desc' => '',
					'type' => 'hook'
				),
			) )
		);

		$settings = array_merge( $settings, $new_settings );
	}

	return $settings;
}

add_filter( 'edd_settings_extensions', 'edd_mailchimp_abd_add_settings' );


/**
 * Add settings (pre-2.5)
 *
 * @param       array $settings The existing plugin settings
 * @return      array The modified plugin settings
 * @package 	Mailchimp Vendor Email Trigger
 * @since 		1.0.0
 */
function edd_mailchimp_abd_add_settings_pre25( $settings ) {

	$api_data = new EDD_Mailchimp_Abandoned_Cart();

	if( EDD_VERSION < '2.5' ) {
		$new_settings = apply_filters( 'edd_mailchimp_settings', array(
			array(
				'id'   => 'edd_mailchimp_api_config',
				'name' => '<h1>' . __( 'EDD Mailchimp Abandoned Cart', 'edd-mailchimp-abandoned-cart' ) . '</h1>',
				'desc' => '',
				'type' => 'header'
			),
			array(
				'id'   => 'edd_mailchimp_api_setting',
				'name' => '<strong>' . __( 'Mailchimp Settings', 'edd-mailchimp-abandoned-cart' ) . '</strong>',
				'desc' => '',
				'type' => 'header'
			),
			array(
				'id'   => 'edd_abandoned_mailchimp_api',
				'name' => __( 'MailChimp API Key', 'edd-mailchimp-abandoned-cart' ),
				'desc' => __( 'Enter your <a class="" href="https://kb.mailchimp.com/integrations/api-integrations/about-api-keys" target="_blank">MailChimp API key</a>', 'edd-mailchimp-abandoned-cart' ),
				'type' => 'text',
				'size' => 'regular'
			),
			array(
				'id'   => 'edd_abandoned_mailchimp_connected_store_name',
				'name' => 'Connected Store',
				'desc' => '',
				'type' => 'hook'
			),
			array(
				'id'      => 'edd_abandoned_mailchimp_store_id',
				'name'    => __( 'Choose A Store', 'edd-mailchimp-abandoned-cart' ),
				'desc' => __( '<a class="" href="'.get_admin_url().'admin.php?page=create-store" target="_self">Click Here</a> to Manage your Stores', 'edd-mailchimp-abandoned-cart' ),
				'type'    => 'select',
				'class'      => 'edd_abandoned_mailchimp_store_id',
				'options' => $api_data->get_stores('','')
			),
			array(
				'id'   => 'edd_abandoned_mailchimp_connected_site_script',
				'name' => __( '', 'edd-mailchimp-abandoned-cart' ),
				'desc' => '',
				'type' => 'text',
				'class' => 'abandoned_mailchimp_connected_site_script',
				'size' => 'regular'
			),
			
			array(
				'id'      => 'edd_abandoned_mailchimp_add_subscriber_status',
				'name'    => __( 'Add Subscriber Status', 'edd-mailchimp-abandoned-cart' ),
				'desc' => __( 'Select True as default value so that you can send other email campaigns to this user if needed.  <a class="" href="http://developer.mailchimp.com/documentation/mailchimp/guides/getting-started-with-ecommerce/#about-subscribers-and-customers" target="_blank">Click Here</a> to Know More about it.', 'edd-mailchimp-abandoned-cart' ),
				'type'    => 'select',
				'options' => $api_data->get_subscriber_status()
			),
			array(
					'id'   => 'edd_abandoned_mailchimp_edd_token_day',
					'name' => __( 'Set EDD Cart Token Expiry Time (Day)', 'edd-mailchimp-abandoned-cart' ),
					'desc' => __( "The link to the shopping cart id the prospect will receive will only work as long as the cookie in the user's browser is valid. Therefore we recommend using 3-7 days to be on the safe side. Default value is 7 days.", 'edd-mailchimp-abandoned-cart' ),
					'type' => 'number',
					'max' => '365',
					'size' => 'regular'
				),
			array(
				'id'   => 'sync_best_pro_settings_additional',
				'name' => '',
				'desc' => '',
				'type' => 'hook'
			),
			array(
				'id'   => 'edd_mailchimp_discount_setting',
				'name' => '<strong>' . __( 'Discount Settings', 'edd-mailchimp-abandoned-cart' ) . '</strong>',
				'desc' => '',
				'type' => 'header'
			),
			array(
				'id'      => 'edd_abandoned_discount_status',
				'name'    => __( 'Create Discount Code?', 'edd-mailchimp-abandoned-cart' ),
				'desc' => '',
				'type'    => 'select',
				'options' => $api_data->get_discountCode_status()
			),

			array(
				'id'   => 'edd_abandoned_mailchimp_discount_code_prefix',
				'name' => __( 'Set Discount Code Prefix', 'edd-mailchimp-abandoned-cart' ),
				'desc' => __( 'Set Discount Code Prefix', 'edd-mailchimp-abandoned-cart' ),
				'type' => 'text',
				'size' => 'regular',
			),
			array(
				'id'   => 'edd_abandoned_mailchimp_discount_percentage',
				'name' => __( 'Set Discount (%)', 'edd-mailchimp-abandoned-cart' ),
				'desc' => '',
				'type' => 'number',
				'max' => '100',
				'size' => 'regular'
			),
			array(
				'id'   => 'edd_abandoned_mailchimp_discount_day',
				'name' => __( 'Set Discount Time (Day)', 'edd-mailchimp-abandoned-cart' ),
				'desc' => __( '', 'edd-mailchimp-abandoned-cart' ),
				'type' => 'number',
				'max' => '365',
				'size' => 'regular'
			),
			array(
				'id'   => 'delete_plugin_generated_codes',
				'name' => '',
				'desc' => '',
				'type' => 'hook'
			),
			
		) );

		$settings = array_merge( $settings, $new_settings );
	}

	return $settings;
}

add_filter( 'edd_settings_extensions', 'edd_mailchimp_abd_add_settings_pre25' );


function edd_best_seller_sync_button() {
	echo '<style type="text/css">
			.abandoned_mailchimp_connected_site_script{
				display: none;
			}
			select[name="edd_settings[edd_abandoned_discount_status]"],select[name="edd_settings[edd_abandoned_mailchimp_add_subscriber_status]"]{width: 145px;}
			.download_page_edd-settings .form-table label{display:inline;float:left;width:100%;font-size: smaller;}
		</style><a href="' . wp_nonce_url( add_query_arg( array( 'edd_action' => 'sync_edd_best_pro' ) ), 'edd-abd-sync' ) . '" class="button-secondary">' . __( 'Sync Best Sellers', 'edd-mailchimp-abandoned-cart' ) . '</a>  <br> <small>You will need some product data in Mailchimp to recommend similar products. Push the button to ensure your best selling products are up to date on Mailchimp. For more information, <a target="_blank" href="https://kb.mailchimp.com/campaigns/design/about-product-recommendations">click here.</a></small>' ;
}
add_action( 'edd_sync_best_pro_settings_additional', 'edd_best_seller_sync_button' );


function sync_edd_best_pro( $data ) {
	if ( ! wp_verify_nonce( $data['_wpnonce'], 'edd-abd-sync' ) ) {
		return;
	}

	// Sync Products
	$api_data = new EDD_Mailchimp_Abandoned_Cart();
	$api_data->edd_sync_top_seller_items();
	add_action( 'admin_notices', 'edd_pro_sync_notice' );
}
add_action( 'edd_sync_edd_best_pro', 'sync_edd_best_pro' );

function edd_pro_sync_notice() {
	printf( '<div class="updated settings-error"> <p> %s </p> </div>', esc_html__( 'Products Updated.', 'edd-mailchimp-abandoned-cart' ) );
}


function edd_deleted_coupon_botton() {
	echo '<a href="' . wp_nonce_url( add_query_arg( array( 'edd_action' => 'delete_abd_coupon' ) ), 'edd-abd-delete-code' ) . '" class="button-secondary">' . __( 'Delete Discount Codes', 'edd-mailchimp-abandoned-cart' ) . '</a>  <br> <small>Pressing Delete Discount Codes will delete all inactive, expired or used discount codes with the same Prefix as stored in the discount codes in the database <a target="_blank" href="'.get_admin_url().'edit.php?post_type=download&page=edd-discounts">here</a>. Please ensure there are no spelling mistakes with the Prefix saved above. Delete any space, character after the prefix if needed.</small>' ;
}
add_action( 'edd_delete_plugin_generated_codes', 'edd_deleted_coupon_botton' );


function delete_abd_coupon( $data ) {
	if ( ! wp_verify_nonce( $data['_wpnonce'], 'edd-abd-delete-code' ) ) {
		return;
	}

	// Sync Products
	$api_data = new EDD_Mailchimp_Abandoned_Cart();
	$api_data->delete_edd_cp();
	add_action( 'admin_notices', 'edd_delete_code_notice' );
}
add_action( 'edd_delete_abd_coupon', 'delete_abd_coupon' );

function edd_delete_code_notice() {
	printf( '<div class="updated settings-error"> <p> %s </p> </div>', esc_html__( 'Discount codes deleted.', 'edd-mailchimp-abandoned-cart' ) );
}




function abandoned_mailchimp_connected_store_name() {
	$api_data = new EDD_Mailchimp_Abandoned_Cart();
	$store_name = $api_data->get_site_stores('','name');
	$store_domain = $api_data->get_site_stores('','domain');
	$echos = '<p>';
	if(!empty($store_name)){
		$echos .= 'Store Name : <b>'.$store_name.'</b>';
		if(!empty($store_domain)){
			$echos .= '&nbsp;&nbsp;URL : <a href="'.$store_domain.'">'.$store_domain.'</a>';
		}
	$echos .= '</p>';
	}else{
		$echos .= '<p><b>No Store found for this site.</b></p>';
	}
	$echos .=  __( '<p><a class="" href="'.get_admin_url().'admin.php?page=create-store" target="_self">Click Here</a> to Manage your Stores</p>', 'edd-mailchimp-abandoned-cart' );
	


	// global $edd_options;
 //    $site_script = !empty( $edd_options['edd_abandoned_mailchimp_connected_site_script'] ) ? $edd_options['edd_abandoned_mailchimp_connected_site_script'] : '';

	
	echo $echos;
}
add_action( 'edd_edd_abandoned_mailchimp_connected_store_name', 'abandoned_mailchimp_connected_store_name' );

/**
 * Add debug option if the S214 Debug plugin is enabled
 *
 * @param       array $settings The current settings
 * @return      array $settings The updated settings
 * @package 	Mailchimp Vendor Email Trigger
 * @since 		1.0.0
 */
function edd_mailchimp_abd_add_debug( $settings ) {
	if( class_exists( 'S214_Debug' ) ) {
		$debug_setting[] = array(
			'id'   => 'edd_mailchimp_debugging',
			'name' => '<strong>' . __( 'Debugging', 'edd-mailchimp-abandoned-cart' ) . '</strong>',
			'desc' => '',
			'type' => 'header'
		);

		$debug_setting[] = array(
			'id'   => 'edd_mailchimp_enable_debug',
			'name' => __( 'Enable Debug', 'edd-mailchimp-abandoned-cart' ),
			'desc' => sprintf( __( 'Log plugin errors. You can view errors %s.', 'edd-mailchimp-abandoned-cart' ), '<a href="' . admin_url( 'tools.php?page=s214-debug-logs' ) . '">' . __( 'here', 'edd-mailchimp-abandoned-cart' ) . '</a>' ),
			'type' => 'checkbox'
		);

		$settings = array_merge( $settings, $debug_setting );
	}

	return $settings;
}

add_filter( 'edd_mailchimp_abandoned_cart_settings', 'edd_mailchimp_abd_add_debug' );