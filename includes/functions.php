<?php
/**
 * Helper Functions
 *
 * @package     EDD\EDDMailchimpAbandonedCart\Functions
 * @since       1.0.0
 */


// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;


/**
 *  Create EDD Discount
*/
function create_edd_dicount_code($email) {

    global $edd_options;

    $edd_abandoned_discount_status = !empty( $edd_options['edd_abandoned_discount_status'] ) ? $edd_options['edd_abandoned_discount_status'] : 'on';

    if($edd_abandoned_discount_status == 'on'){
        $edd_abandoned_mailchimp_discount_day = !empty( $edd_options['edd_abandoned_mailchimp_discount_day'] ) ? $edd_options['edd_abandoned_mailchimp_discount_day'] : '';
        
        $edd_abandoned_mailchimp_discount_percentage = !empty( $edd_options['edd_abandoned_mailchimp_discount_percentage'] ) ? $edd_options['edd_abandoned_mailchimp_discount_percentage'] : '';

        if(!empty($edd_abandoned_mailchimp_discount_day) && !empty($edd_abandoned_mailchimp_discount_percentage) && !empty($email)){

            $day_in_sec = $edd_abandoned_mailchimp_discount_day * 86400;

            //Create random discount code
            $pool = array_merge(range(0,9), range('a', 'z'),range('A', 'Z'));
            $code = '';
            for($x= 0; $x < 3; $x++) {
                for ( $i = 0; $i < 5; $i ++ ) {
                    $code .= $pool[ mt_rand( 0, count( $pool ) - 1 ) ];
                    }
                $code .= '-';
            }
            $edd_abandoned_mailchimp_discount_code_prefix = !empty( $edd_options['edd_abandoned_mailchimp_discount_code_prefix'] ) ? $edd_options['edd_abandoned_mailchimp_discount_code_prefix'] : EDD_ABD_CODE_PREFIX;
            $code =  $edd_abandoned_mailchimp_discount_code_prefix . rtrim( $code, '-' );

            // Set expiration time 
            $expires = time() + $day_in_sec;
            $details = array(
                'code'              => $code,
                'name'              => $email,
                // 'status'            => 'active',
                'is_single_use'     => 1,
                'amount'            => $edd_abandoned_mailchimp_discount_percentage,
                'start'             => date('Y-m-d' ),
                'expiration'        => date('Y-m-d', $expires ),
                'type'              => 'percent',
                'max_uses'          => 1,
                'uses'              => 1
            );
            $id = edd_store_discount( $details );

             //Check for error
            if( ! is_numeric( $id ) ){
                return '';
            }else{
                return $code;
            }
        }else{
            return '';
        }
    }else{
        return '';
    }
}


add_action( 'admin_menu', 'add_create_user_menu' );
function add_create_user_menu() {
    add_submenu_page(
        '',
        'Create Store',
        'Create Store',
        'manage_options',
        'create-store',
        'add_create_user_page'
    );
}

function add_create_user_page() {
    require EDD_MAILCHIMP_ABANDONED_CART_DIR . 'includes/admin/add_store.php';
}


function action_edd_post_add_to_cart( ) { 

    $cart_contents = edd_get_cart_contents();

    if ( ! empty( $cart_contents ) ) {
        edd_abd_save_cart();
        
        if ( is_user_logged_in() ) {

            $inser_data =    array();

            $unique_id  =    edd_get_cart_token();
            $inser_data['unique_id'] = $unique_id;
            $user_id = get_current_user_id();
            $inser_data['user_id'] = $user_id;
            $user_info = get_userdata($user_id);
                
            $first_name = $user_info->first_name;
            $last_name = $user_info->last_name;
            $edd_email = $user_info->user_email;

            $inser_data['email'] = $edd_email;
            $inser_data['first_name'] = $first_name;
            $inser_data['last_name'] = $last_name;
        
            $inser_data['created_at'] = date('Y-m-d H:i:s');
            
            $inser_data['cart'] = serialize($cart_contents);
            
            global $wpdb;
            $table_carts = $wpdb->prefix . 'edd_mailchimp_abd_carts';
            $row_id = $wpdb->get_var( "SELECT id FROM $table_carts WHERE email='".$inser_data['email']."' OR unique_id='".$inser_data['unique_id']."'" );
            if(isset($row_id) && !empty($row_id)){
                $wpdb->update( $table_carts, $inser_data,array('id'=>$row_id));
            }else{
                $wpdb->insert( $table_carts, $inser_data);    
            }

        }

    }
}

function edd_abd_save_cart(){

    
    $user_id  = get_current_user_id();
    $cart     = EDD()->session->get( 'edd_cart' );
    $token    = edd_generate_cart_token();
    $messages = EDD()->session->get( 'edd_cart_messages' );
    if ( is_user_logged_in() ) {
        update_user_meta( $user_id, 'edd_saved_cart', $cart,  false );
        update_user_meta( $user_id, 'edd_cart_token', $token, false );
    } else {
        $cart = json_encode( $cart );
        global $edd_options;
        
        $days             = is_numeric( $edd_options['edd_abandoned_mailchimp_edd_token_day'] ) ? $edd_options['edd_abandoned_mailchimp_edd_token_day'] : 7;

        setcookie( 'edd_saved_cart', $cart,  time() + 3600 * 24 * $days , COOKIEPATH, COOKIE_DOMAIN );
        setcookie( 'edd_cart_token', $token, time() + 3600 * 24 * $days , COOKIEPATH, COOKIE_DOMAIN );
    }
    if ( $cart ) {
        return true;
    }
    return false;

}

function action_edd_post_remove_from_cart( $key, $item_id ) { 
    $cart_contents = edd_get_cart_contents();

    if ( ! empty( $cart_contents ) ) {
        edd_abd_save_cart();
        
        if ( is_user_logged_in() ) {

            $inser_data =    array();

            $user_id = get_current_user_id();
            $inser_data['user_id'] = $user_id;
            $user_info = get_userdata($user_id);
                
            $first_name = $user_info->first_name;
            $last_name = $user_info->last_name;
            $edd_email = $user_info->user_email;

            if(empty($first_name)){
                $first_name = $_SESSION['EDD_Mailchimp_Abandoned_Session_Last_Name'];
            }
            if(empty($last_name)){
                $last_name = $_SESSION['EDD_Mailchimp_Abandoned_Session_First_Name'];
            }

        }else{
            $edd_email = $_SESSION['EDD_Mailchimp_Abandoned_Session_Email'];
            $first_name = $_SESSION['EDD_Mailchimp_Abandoned_Session_Last_Name'];
            $last_name = $_SESSION['EDD_Mailchimp_Abandoned_Session_First_Name'];
        }

        $inser_data['email'] = $edd_email;
        $inser_data['first_name'] = $first_name;
        $inser_data['last_name'] = $last_name;

        $unique_id  =    edd_get_cart_token();
        $inser_data['unique_id'] = $unique_id;
        $inser_data['created_at'] = date('Y-m-d H:i:s');
            
        $inser_data['cart'] = serialize($cart_contents);
        
        global $wpdb;
        $table_carts = $wpdb->prefix . 'edd_mailchimp_abd_carts';
        $row_id = $wpdb->get_var( "SELECT id FROM $table_carts WHERE email='".$inser_data['email']."' OR unique_id='".$inser_data['unique_id']."' " );
        

        if(isset($row_id) && !empty($row_id)){
            $mailchimp_cart_id = $wpdb->get_var( "SELECT unique_id FROM $table_carts WHERE email='".$row_id."'" );
            if(isset($mailchimp_cart_id) && !empty($mailchimp_cart_id)){
                mailchimp_remove_cart($mailchimp_cart_id);
                $inser_data['status'] = 0;
            }
            $wpdb->update( $table_carts, $inser_data,array('id'=>$row_id));
        }else{
            $wpdb->insert( $table_carts, $inser_data);    
        }
    }
}
         
// remove from cart action 
add_action( 'edd_post_remove_from_cart', 'action_edd_post_remove_from_cart', 10, 2 ); 
         
// add to cart action 
add_action( 'edd_post_add_to_cart', 'action_edd_post_add_to_cart');


// Scheduling 

function edd_abd_cron_schedules($schedules){
    if(!isset($schedules["15min"])){
        $schedules["15min"] = array(
            'interval' => 15*60,
            'display' => __('Once every 15 minutes'));
    }
    if(!isset($schedules["5min"])){
        $schedules["5min"] = array(
            'interval' => 5*60,
            'display' => __('Once every 5 minutes'));
    }
    return $schedules;
}
add_filter('cron_schedules','edd_abd_cron_schedules');

if (!wp_next_scheduled('edd_abd_task_hook')) {
    wp_schedule_event( time(), '15min', 'edd_abd_task_hook' );
}
if (!wp_next_scheduled('edd_abd_order_hook')) {
    wp_schedule_event( time(), '5min', 'edd_abd_order_hook' );
}
add_action ( 'edd_abd_task_hook', 'edd_abd_task_function' );
add_action ( 'edd_abd_order_hook', 'edd_abd_order_function' );

function edd_abd_order_function(){
    
    global $wpdb;
    $table_carts = $wpdb->prefix . 'edd_mailchimp_abd_carts';
    $drafts = $wpdb->get_results("SELECT id,email FROM ".$table_carts." WHERE status = '1' ");
    
    if(isset($drafts) && count($drafts)){
        foreach ( $drafts as $draft )
        {
            $draftid = $draft->id;
            $email = $draft->email;

            $purchases = edd_get_users_purchases( $email,1);

            $payment = new EDD_Payment($purchases[0]->ID);
            $payment_details = $payment->get_meta( '_edd_payment_meta');
            $downloads = $payment_details['downloads'];
            $cart_details = $payment_details['cart_details'];
            $currency = $payment_details['currency'];

            $order_total = $payment->total;
            $customer_id = $payment->customer_id;
            $user_id = $payment->user_id;
            $first_name = $payment->first_name;
            $last_name = $payment->last_name;
            $address = $payment->address;
            $address_line1 = $address['line1'];
            $address_line2 = $address['line2'];
            $address_city = $address['city'];
            $address_country = $address['country'];
            $address_state = $address['state'];
            $address_zip = $address['zip'];

            $customer_array = array();
            $customer_array['id'] = CUSTOMER_PREFIX.md5(strtolower($email));
            $customer_array['email'] = $email;
            $customer_array['first_name'] = $first_name;
            $customer_array['last_name'] = $last_name;


            $customer_array['address1'] = $address_line1;
            $customer_array['address2'] = $address_line2;
            $customer_array['city'] = $address_city;
            $customer_array['country'] = $address_country;
            $customer_array['province'] = $address_state;
            $customer_array['postal_code'] = $address_zip;


            $customer = new EDD_Customer( $customer_id );
            $purchase_count = $customer->purchase_count;
            $total_spent = edd_sanitize_amount( $customer->purchase_value );

            $customer_array['orders_count'] = $purchase_count;
            $customer_array['total_spent'] = $total_spent;

            $order_array['currency_code'] = $currency;
            $order_array['order_total'] = $order_total;


            $product_array = array();
            if(isset($cart_details) && count($cart_details)){
                foreach ($cart_details as $key => $value) {
                    if(isset($value['item_number']['options']['price_id'])){
                        $price_id = $value['item_number']['options']['price_id'];
                    }else{
                        $price_id = '0';
                    }

                    $p_array['id'] = 'LINE_'.$key.time();
                    $p_array['product_id'] = PRODUCT_PREFIX.$value['id'];
                    $p_array['product_variant_id'] = PRODUCT_VARIANTS_PREFIX.$price_id;
                    // $p_array['download_name'] = $value['name'];
                    // $p_array['download_id'] = $value['id'];
                    $p_array['price'] = $value['item_price'];
                    $p_array['quantity'] = $value['quantity'];
                    $p_array['discount'] = $value['discount'];
                    // $p_array['subtotal'] = $value['subtotal'];
                    // $p_array['tax'] = $value['tax'];
                    // $p_array['price'] = $value['price'];
                    $product_array[] = $p_array;
                }
            }
            if($total_spent > 0){
                $api_data = new EDD_Mailchimp_Abandoned_Cart();
                $res = $api_data->mailchimp_create_order($customer_array, $product_array,$order_array);
                if($res){
                    $wpdb->update( $table_carts, array("status"=>'2'),array('id'=>$draftid));
                }
            }
            
        }
    }
}

function edd_abd_task_function() {

    global $wpdb;
    $table_carts = $wpdb->prefix . 'edd_mailchimp_abd_carts';
    $drafts = $wpdb->get_results("SELECT * FROM ".$table_carts." WHERE status = '0' AND unique_id!='' ");
    
    if(isset($drafts) && count($drafts)){
        $api_data = new EDD_Mailchimp_Abandoned_Cart();
        foreach ( $drafts as $draft )
        {
            $draftid = $draft->id;
            $email = $draft->email;
            $cart = $draft->cart;
            $unique_id = $draft->unique_id;
            $first_name = $draft->first_name;
            $last_name = $draft->last_name;
            $created_at = $draft->created_at;
            $user_id = $draft->user_id;

            // Add or Update Product on Mailchimp
            $product_array = array();
                    
            $cart_array = unserialize($cart);
            
            if(isset($cart_array) && count($cart_array)){
                $order_total = 0;
                foreach ($cart_array as $key => $value) {
                    $downloads = $value['id'];
                    if(isset($value['options']['price_id'])){
                    	$price_id = $value['options']['price_id'];
                    	$edd_p = edd_price(  $downloads,  false,  $price_id ); 
	                    
                    }else{
                    	$price_id = '0';
                    	$edd_p = edd_price(  $downloads,  false ); 
                    }

                    
                    
                    $quantity = $value['quantity'];
                    $edd_p = trim(strip_tags($edd_p)); 
                    $currency = edd_get_currency();
                    $symbol = edd_currency_symbol( $currency );
                    
                    $edd_p1 = trim(str_replace($symbol, '', $edd_p));
                    $edd_p2 = floatval($edd_p1);	
                    
                    $product_array[$key]['id'] = 'LINE_'.$key.time();
                    $product_array[$key]['product_id'] = PRODUCT_PREFIX.$downloads;
                    $product_array[$key]['product_variant_id'] = PRODUCT_VARIANTS_PREFIX.$price_id;
                    $product_array[$key]['quantity'] = $quantity;
                    $product_array[$key]['price'] = $edd_p2;
                    
                    $order_total = ($order_total  + ($edd_p2*$quantity));
                    $p_res = $api_data->add_update_product($downloads);
                 //    $wpdb->update( $table_carts, array("status"=>'1','mailchimp_cart_id'=>$p_res),array('id'=>$draftid));
                	// exit;

                }

               
                if($order_total > 0){

                // Check Customer Total Spent
                    $total_spent = edd_purchase_total_of_user( $email );   //User ID or email
                    // update_option('edd_TIME',$total_spent);
                    if($total_spent == 0 || empty($total_spent)){

                        // Create Discount Code for User
                        $discount_code = create_edd_dicount_code($email);
                        // add_action( 'init', array( $email, 'create_edd_dicount_code' ) );

                        

                        // Add Customer as a subscriber
                        $api_data->add_update_subscribe_member($email,$first_name,$last_name,$discount_code);

                        // Add to Cart in Mailchimp
                        $customer_array = array();
                        
                        $customer_array['user_id'] = md5(strtolower($email));
                        $customer_array['email'] = $email;
                        $customer_array['first_name'] = $first_name;
                        $customer_array['last_name'] = $last_name;

                         // exit;
                        $res = $api_data->mailchimp_add_to_cart($customer_array, $product_array,$unique_id,$order_total);
                        
                        // print_r($res);exit();
                        if($res){
                            $wpdb->update( $table_carts, array("status"=>'1'),array('id'=>$draftid));
                        }
                    }
                }

            }
        }
    }

}



//add_action('wp_head','mailchimp_site_connection_code_header');
function mailchimp_site_connection_code_header(){ 
    
    global $edd_options;
    $url             = !empty( $edd_options['edd_abandoned_mailchimp_connected_site_script'] ) ? $edd_options['edd_abandoned_mailchimp_connected_site_script'] : '';

    if ( !empty($url)) { ?>
            <script id="mcjs">!function(c,h,i,m,p){m=c.createElement(h),p=c.getElementsByTagName(h)[0],m.async=1,m.src=i,p.parentNode.insertBefore(m,p)}(document,"script","<?= $url;?>");</script>
        <?php
    } 
} 


?>