<?php
/**
 * Plugin Name:     EDD Mailchimp Abandoned Cart Wordpress Plugin
 * Plugin URI:      https://www.pluginsandsnippets.com/documentation/edd-mailchimp-abandoned-cart-plugin-documentation/
 * Description:     The plugin connects your Easy Digital Download Store with the Abandoned Cart Automation Process of Mailchimp.
 * Version:         1.0.9
 * Author:          PluginsandSnippets.com
 * Author URI:      https://pluginsandsnippets.com/
 * Text Domain:     edd-mailchimp-abandoned-cart
 * Requires at least:   3.9
 * Tested up to:        4.9.8
 *
 * @package         EDD\EDDMailchimpAbandonedCartWordpressPlugin
 * @author          PluginsandSnippets.com
 * @copyright       All rights reserved Copyright (c) 2018, PluginsandSnippets.com
 *
 */


// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

if( !class_exists( 'EDD_Mailchimp_Abandoned_Cart' ) ) {

    /**
     * Main EDD_Mailchimp_Abandoned_Cart class
     *
     * @since       1.0.0
     */
    class EDD_Mailchimp_Abandoned_Cart {

        /**
         * @var         EDD_Mailchimp_Abandoned_Cart $instance The one true EDD_Mailchimp_Abandoned_Cart
         * @since       1.0.0
         */
        private static $instance;
        private  $apiKey;
        private  $listID;
        private  $storeID;
        private $message = '';
        private $messageError = FALSE;
        

        public function __construct()
        {
            global $edd_options;
            
            $this->apiKey  = !empty( $edd_options['edd_abandoned_mailchimp_api'] ) ? $edd_options['edd_abandoned_mailchimp_api'] : '';
            $this->storeID  = !empty( $edd_options['edd_abandoned_mailchimp_store_id'] ) ? $edd_options['edd_abandoned_mailchimp_store_id'] : '';

        }


        /**
         * Get active instance
         *
         * @access      public
         * @since       1.0.0
         * @return      object self::$instance The one true EDD_Mailchimp_Abandoned_Cart
         */
        public static function instance() {
            if( !self::$instance ) {
                self::$instance = new EDD_Mailchimp_Abandoned_Cart();
                self::$instance->setup_constants();
                self::$instance->includes();
                self::$instance->load_table();
                self::$instance->load_textdomain();
                self::$instance->hooks();
                self::$instance->mailchimp_site_connection_code();
            }

            return self::$instance;
        }

        /**
         * Setup plugin constants
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function setup_constants() {

            // Plugin version
            define( 'EDD_MAILCHIMP_ABANDONED_CART_VER', '1.0.9' );

            // Plugin name
            define( 'EDD_MAILCHIMP_ABANDONED_CART_NAME', 'EDD Mailchimp Abandoned Cart Wordpress Plugin' );

            // Plugin path
            define( 'EDD_MAILCHIMP_ABANDONED_CART_DIR', plugin_dir_path( __FILE__ ) );

            // Plugin URL
            define( 'EDD_MAILCHIMP_ABANDONED_CART_URL', plugin_dir_url(__FILE__));

            // Product Prefix for Mailchimp Product id (Foreign Key)
            define("PRODUCT_PREFIX","product_");

            // Product Variants Prefix for Mailchimp Product Variant id (Foreign Key)
            define("PRODUCT_VARIANTS_PREFIX","variants_");

            // Cart Prefix for Mailchimp Cart id (Foreign Key)
            define("CART_PREFIX","cart_");

            // Customer Prefix for Mailchimp Store (Foreign Key)
            define("CUSTOMER_PREFIX","customer_");

            // Default Discount Code Prefix
            define("EDD_ABD_CODE_PREFIX","EDD-ABD-");

            // Auto save EDD cart Saving for customer
            edd_update_option( 'enable_cart_saving', true );

            define( 'EDD_ABD_STORE_API_URL', 'https://www.pluginsandsnippets.com/' );

            define( 'EDD_ABD_STORE_PRODUCT_ID', 169);
        }

        /**
         * Load necessary files
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function load_table() {
            global $wpdb;
            $table_carts = $wpdb->prefix . 'edd_mailchimp_abd_carts';
         
            // create database table
            if($wpdb->get_var("show tables like '$table_carts'") != $table_carts) 
            {
                $sql = "CREATE TABLE " . $table_carts . " (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `email` mediumtext NOT NULL,
                `first_name` mediumtext NOT NULL,
                `last_name` mediumtext NOT NULL,
                `unique_id` mediumtext NOT NULL,
                `user_id` int(11) NOT NULL,
                `cart` text NOT NULL,
                `status` int(11) NOT NULL,
                `created_at` datetime NOT NULL,
                 PRIMARY KEY (id)
                );";
         
                require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                dbDelta($sql);
            }
        }

        /**
         * Include necessary tables
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function includes() {
            // Include scripts
            require_once EDD_MAILCHIMP_ABANDONED_CART_DIR . 'includes/libraries/MailChimpAPI3.php';
            if( is_admin() ) {
                require_once EDD_MAILCHIMP_ABANDONED_CART_DIR . 'includes/admin/register.php';
            }
        }

        /**
         * Run action and filter hooks
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         *
         */
        private function hooks() {
             
            // Handle licensing
            // if(!class_exists( 'EDD_SL_Plugin_Updater' ) ) {
            //     require_once EDD_MAILCHIMP_ABANDONED_CART_DIR . 'includes/EDD_SL_Plugin_Updater.php';
            // }
            // if(!class_exists( 'EDD_License' ) ) {
            //     require_once EDD_MAILCHIMP_ABANDONED_CART_DIR . 'includes/EDD_License_Handler.php';
            // }
            // if(is_admin()) {
            //     $license = new EDD_License( __FILE__, EDD_MAILCHIMP_ABANDONED_CART_NAME, EDD_MAILCHIMP_ABANDONED_CART_VER, 'PluginsandSnippets.com' ,null,EDD_ABD_STORE_API_URL,EDD_ABD_STORE_PRODUCT_ID);
            // }

            // retrieve our license key from the DB
            global $edd_options;
            // $license_key             = !empty( $edd_options['edd_edd_mailchimp_abandoned_cart_wordpress_plugin_license_key'] ) ? $edd_options['edd_edd_mailchimp_abandoned_cart_wordpress_plugin_license_key'] : '';
            // setup the updater

            // $edd_updater = new EDD_SL_Plugin_Updater( EDD_ABD_STORE_API_URL, __FILE__, array( 
            //         'version'   => EDD_MAILCHIMP_ABANDONED_CART_VER,       // current version number
            //         'license'   => $license_key,    // license key (used get_option above to retrieve from DB)
            //         'item_name'   => EDD_MAILCHIMP_ABANDONED_CART_NAME,  // name of this product in EDD
            //         'item_id'   => EDD_ABD_STORE_PRODUCT_ID,  // id of this product in EDD
            //         //'author'    => 'PluginsandSnippets.com',  // author of this plugin
            //         'url'       => home_url(),
            //         'slug'      => 'edd-mailchimp-abandoned-cart-wordpress-plugin',
            //         'beta'      => false
            //     )
            // );

            add_action('wp_enqueue_scripts', array($this, 'load_js_scripts'));
            

            // Frontend Ajax
            add_action('wp_ajax_action_save_logout_user',array($this,'action_save_logout_user_callback'));
            add_action('wp_ajax_nopriv_action_save_logout_user', array($this,'action_save_logout_user_callback'));

            //Ajax For Admin
            if( is_admin() ) {
                add_action('admin_enqueue_scripts', array($this, 'load_admin_js_scripts'));

                add_action('wp_ajax_load_mailchimp_stores',array($this,'load_mailchimp_stores_callback'));
                add_action('wp_ajax_nopriv_load_mailchimp_stores', array($this,'load_mailchimp_stores_callback'));

                add_action('wp_ajax_load_mailchimp_merge_fields',array($this,'load_mailchimp_merge_fields_callback'));
                add_action('wp_ajax_nopriv_load_mailchimp_merge_fields', array($this,'load_mailchimp_merge_fields_callback'));
            }
            // add_action ( 'admin_notices', array($this,'showLicenseMessage') );
        }

        /**
         * Internationalization
         *
         * @access      public
         * @since       1.0.0
         * @return      void
         */
        public function load_textdomain() {
            // Set filter for language directory
            $lang_dir = EDD_MAILCHIMP_ABANDONED_CART_DIR . '/languages/';
            $lang_dir = apply_filters( 'edd_mailchimp_abandoned_cart_languages_directory', $lang_dir );

            // Traditional WordPress plugin locale filter
            $locale = apply_filters( 'plugin_locale', get_locale(), 'edd-mailchimp-abandoned-cart' );
            $mofile = sprintf( '%1$s-%2$s.mo', 'edd-mailchimp-abandoned-cart', $locale );

            // Setup paths to current locale file
            $mofile_local   = $lang_dir . $mofile;
            $mofile_global  = WP_LANG_DIR . '/edd-mailchimp-abandoned-cart/' . $mofile;

            if( file_exists( $mofile_global ) ) {
                // Look in global /wp-content/languages/edd-mailchimp-abandoned-cart/ folder
                load_textdomain( 'edd-mailchimp-abandoned-cart', $mofile_global );
            } elseif( file_exists( $mofile_local ) ) {
                // Look in local /wp-content/plugins/edd-mailchimp-abandoned-cart/languages/ folder
                load_textdomain( 'edd-mailchimp-abandoned-cart', $mofile_local );
            } else {
                // Load the default language files
                load_plugin_textdomain( 'edd-mailchimp-abandoned-cart', false, $lang_dir );
            }
        }

        /**
         * Add settings
         *
         * @access      public
         * @since       1.0.0
         * @param       array $settings The existing EDD settings array
         * @return      array The modified EDD settings array
         */
        public function settings( $settings ) {
           
            $new_settings = array(
                array(
                    'id'    => 'edd_mailchimp_abandoned_cart_settings',
                    'name'  => '<strong>' . __( EDD_MAILCHIMP_ABANDONED_CART_NAME.' Settings', 'edd-mailchimp-abandoned-cart' ) . '</strong>',
                    'desc'  => __( 'Configure '.EDD_MAILCHIMP_ABANDONED_CART_NAME.' Settings', 'edd-mailchimp-abandoned-cart' ),
                    'type'  => 'header',
                )
            );

            return array_merge( $settings, $new_settings );
        }

        /**
         *  Get List from MailChimp
         */
        public function edd_get_mailchimp_lists($_apiKey='') {
            
            if(!empty($_apiKey)){
                $apikey = $_apiKey;
            }else{
                $apikey = $this->apiKey;
            }
            
            if (!empty( $apikey ) ) {

                $mailchimp_lists = array();

                require EDD_MAILCHIMP_ABANDONED_CART_DIR . 'includes/libraries/MailChimpAPI3.php';
                $MailChimp = new MailChimpAPI3($apikey);
                $retval = $MailChimp->get('lists');
                   
                if (empty($retval)) {
                    $mailchimp_lists['false'] = __( "Unable to load MailChimp lists, check your API Key.", 'edd-mailchimp-abandoned-cart' );
                } else {

                    if ( $retval['total_items'] == 0 ) {
                        $mailchimp_lists['false'] = __( "You have not created any lists at MailChimp", 'edd-mailchimp-abandoned-cart' );
                        return $mailchimp_lists;
                    }
                    $mailchimp_lists[0] = __( "Select List", 'edd-mailchimp-abandoned-cart' );
                    foreach ( $retval['lists'] as $list ) {
                        $mailchimp_lists[$list['id']] = $list['name'];
                    }
                }
            }

            $mailchimp_lists = !empty( $mailchimp_lists ) ? $mailchimp_lists : array();

            return $mailchimp_lists;
        }

        /**
         *  Get Automations from MailChimp
         */
        public function edd_get_mailchimp_automations() {
            
            $apikey = $this->apiKey;
            
            if (!empty( $apikey ) ) {

                $mailchimp_lists = array();

                require EDD_MAILCHIMP_ABANDONED_CART_DIR . 'includes/libraries/MailChimpAPI3.php';
                $MailChimp = new MailChimpAPI3($apikey);
                return $retval = $MailChimp->get('/automations');
                  
            }

        }

        /**
         *  Get List from MailChimp
         */
        public function edd_get_mailchimp_list_detail($list_id,$_apiKey='') {
            
            if(!empty($_apiKey)){
                $apikey = $_apiKey;
            }else{
                $apikey = $this->apiKey;
            }      
            $mailchimp_list_detail =array();
            
            if (!empty( $apikey ) && !empty($list_id)) {
                require EDD_MAILCHIMP_ABANDONED_CART_DIR . 'includes/libraries/MailChimpAPI3.php';
                $MailChimp = new MailChimpAPI3($apikey);         
                $retval = $MailChimp->get("/lists/$list_id");
                
                if (empty($retval)) {
                    $mailchimp_list_detail['false'] = __( "Unable to load list , check your API Key.", 'edd-mailchimp-abandoned-cart' );
                } else {
                    $mailchimp_list_detail = $retval;
                }
            }

            $mailchimp_list_detail = !empty( $mailchimp_list_detail ) ? $mailchimp_list_detail : array();

            return $mailchimp_list_detail;
        }

        /**
         *  Get Store List from MailChimp
         */
        public function get_stores($is_detail=false,$_apiKey='')
        {
            if(!empty($_apiKey)){
                $apikey = $_apiKey;
            }else{
                $apikey = $this->apiKey;
            }
            if (!empty( $apikey ) ) {

                $mailchimp_stores =array();

                require EDD_MAILCHIMP_ABANDONED_CART_DIR . 'includes/libraries/MailChimpAPI3.php';
                $MailChimp = new MailChimpAPI3($apikey);
                $retval = $MailChimp->get("/ecommerce/stores");

                if (empty($retval)) {
                    $mailchimp_stores['false'] = __( "Unable to load MailChimp store, check your API Key.", 'edd-mailchimp-abandoned-cart' );
                } else {
                    if($is_detail){
                        return $retval;
                    }
                    if ( $retval['total_items'] == 0 ) {
                        $mailchimp_stores['false'] = __( "You have not created any store at MailChimp", 'edd-mailchimp-abandoned-cart' );
                        return $mailchimp_stores;
                    }
                    $mailchimp_stores[0] = __( "Select Store", 'edd-mailchimp-abandoned-cart' );
                    foreach ( $retval['stores'] as $list ) {
                        $mailchimp_stores[$list['id']] = $list['name'];
                    }
                }
            }

            $mailchimp_stores = !empty( $mailchimp_stores ) ? $mailchimp_stores : array();

            return $mailchimp_stores;
        }

        /**
         *  Create a new Store into MailChimp
         */
        public function add_new_store($request){
            if(isset($request) && count($request)){
                
                $apikey = $this->apiKey;
            
                $list_id = $request['edd_mailchimp_list_id'];
                $name = $request['edd_mailchimp_store_name'];
                
                if(!empty($list_id) && !empty($name) && !empty( $apikey )){
                    $currency_code = edd_get_currency();

                    require EDD_MAILCHIMP_ABANDONED_CART_DIR . 'includes/libraries/MailChimpAPI3.php';
                    $MailChimp = new MailChimpAPI3($apikey);   
                    
                    $result = $MailChimp->post("/ecommerce/stores", [
                                        "id"=>'store000_'.time(),
                                        'list_id'=>$list_id,
                                        'name'=> $name,
                                        'domain' =>home_url(),
                                        // 'is_syncing' =>true,
                                        'platform' =>'Easy Digital Downloads',
                                        'currency_code'=> $currency_code,
                                    ]);
                    return $result;
                }else{
                    return false;
                }
            }else{
                return false;
            }
        }

        /**
         *  Get Current site store from MailChimp
         */
        public function get_site_stores($_apiKey='',$return_param=''){
            if(!empty($_apiKey)){
                $apikey = $_apiKey;
            }else{
                $apikey = $this->apiKey;
            }
            if (!empty( $apikey ) ) {

                $mailchimp_stores =array();

                require EDD_MAILCHIMP_ABANDONED_CART_DIR . 'includes/libraries/MailChimpAPI3.php';
                $MailChimp = new MailChimpAPI3($apikey);
                $retval = $MailChimp->get("/ecommerce/stores");

                if (empty($retval)) {
                    $mailchimp_stores['false'] = __( "Unable to load MailChimp store, check your API Key.", 'edd-mailchimp-abandoned-cart' );
                } else {
                    
                    if ( $retval['total_items'] == 0 ) {
                        $mailchimp_stores['false'] = __( "You have not created any store at MailChimp", 'edd-mailchimp-abandoned-cart' );
                        return $mailchimp_stores;
                    }
                    
                    $mailchimp_stores[0] = __( "Select Store", 'edd-mailchimp-abandoned-cart' );
                    foreach ( $retval['stores'] as $list ) {
                        if($list['domain'] == home_url()){
                            if($return_param == 'name'){
                                return $list['name'];
                            }
                            elseif($return_param == 'domain'){
                                return $list['domain'];
                            }
                            elseif($return_param == 'store_id'){
                                return $list['id'];
                            }
                            elseif($return_param == 'site_script'){
                                return $list['connected_site']['site_script']['url'];
                            }
                            else{
                                $mailchimp_stores =array();
                                if ( $retval['total_items'] == 0 ) {
                                    $mailchimp_stores['false'] = __( "You have not created any store at MailChimp", 'edd-mailchimp-abandoned-cart' );
                                    return $mailchimp_stores;
                                }
                                foreach ( $retval['stores'] as $list ) {
                                    if($list['domain'] == home_url()){
                                        $mailchimp_stores[$list['id']] = $list['name'];    
                                    }
                                    
                                }
                                $mailchimp_stores = !empty( $mailchimp_stores ) ? $mailchimp_stores : array();

                                return $mailchimp_stores;
                            }
                        }
                    }
                }
            }

            $mailchimp_stores = !empty( $mailchimp_stores ) ? $mailchimp_stores : array();

            return $mailchimp_stores;
        }

        /**
         *  Get a current Store Details
         */
        public function get_store_detail($_apiKey,$_store_id='')
        {
            
            if(!empty($_store_id)){
                $store_id = $_store_id;
            }else{
                $store_id = $this->storeID;
            }

            if(!empty($_apiKey)){
                $apikey = $_apiKey;
            }else{
                $apikey = $this->apiKey;
            }
            
            $mailchimp_store_detail =array();
            
            if (!empty( $apikey ) && !empty($store_id)) {
                require EDD_MAILCHIMP_ABANDONED_CART_DIR . 'includes/libraries/MailChimpAPI3.php';
                $MailChimp = new MailChimpAPI3($apikey);         
                $retval = $MailChimp->get("/ecommerce/stores/$store_id");
                
                if (empty($retval)) {
                    $mailchimp_store_detail['false'] = __( "Unable to load store , check your API Key.", 'edd-mailchimp-abandoned-cart' );
                } else {
                    $mailchimp_store_detail = $retval;
                }
            }

            $mailchimp_store_detail = !empty( $mailchimp_store_detail ) ? $mailchimp_store_detail : array();

            return $mailchimp_store_detail;
            
        }

        /**
         *  Delete Store from MailChimp
         */
        public function delete_mailchimp_store($store_id)
        {
            $apikey = $this->apiKey;
            
            if (!empty( $apikey ) && !empty($store_id)) {
                require EDD_MAILCHIMP_ABANDONED_CART_DIR . 'includes/libraries/MailChimpAPI3.php';
                $MailChimp = new MailChimpAPI3($apikey);         
                return $retval = $MailChimp->delete("/ecommerce/stores/$store_id");
            }
            
        }

        /**
         *  Create OR Update Subscriber into Mailchimp List (with Discount Code)
         */
        public function add_update_subscribe_member($emailid,$fName,$lName,$discount_code)
        {   
            if(!empty($emailid)){
                global $edd_options;
                
                $apikey = $this->apiKey;
                
                $list_id = $this->get_store_list_id('','');
                
                $merge_id             = !empty( $edd_options['edd_abandoned_mailchimp_merge_field_id'] ) ? $edd_options['edd_abandoned_mailchimp_merge_field_id'] : '';

                $total_spend             = !empty( $edd_options['edd_abandoned_mailchimp_merge_field_total_spend_id'] ) ? $edd_options['edd_abandoned_mailchimp_merge_field_total_spend_id'] : '';

                $subscription_expiring             = !empty( $edd_options['edd_abandoned_mailchimp_merge_field_subscription_expiring_date_id'] ) ? $edd_options['edd_abandoned_mailchimp_merge_field_subscription_expiring_date_id'] : '';

                $subscription_started             = !empty( $edd_options['edd_abandoned_mailchimp_merge_field_subscription_started_date_id'] ) ? $edd_options['edd_abandoned_mailchimp_merge_field_subscription_started_date_id'] : '';

                $total_dates             = !empty( $edd_options['edd_abandoned_mailchimp_merge_field_subscription_total_dates_id'] ) ? $edd_options['edd_abandoned_mailchimp_merge_field_subscription_total_dates_id'] : '';

                $subscription_status             = !empty( $edd_options['edd_abandoned_mailchimp_merge_field_subscription_status_id'] ) ? $edd_options['edd_abandoned_mailchimp_merge_field_subscription_status_id'] : '';

                $merge_detail = $this->get_merge_field_detail($merge_id);

                $total_spend_merge_detail = $this->get_merge_field_detail($total_spend);
                $subscription_expiring_merge_detail = $this->get_merge_field_detail($subscription_expiring);
                $subscription_started_merge_detail = $this->get_merge_field_detail($subscription_started);
                $total_dates_merge_detail = $this->get_merge_field_detail($total_dates); //?
                $subscription_status_merge_detail = $this->get_merge_field_detail($subscription_status);
                
                $tag = '';
                
                $merge_fields = ['FNAME'=>$fName, 'LNAME'=>$lName];
                if(isset($merge_detail['tag']) && !empty($merge_detail['tag'])){
                   $tag = $merge_detail['tag'];
                }

                if(!empty($tag) && !empty($discount_code)){
                    $merge_fields = ['FNAME'=>$fName, 'LNAME'=>$lName,$tag=>$discount_code];
                }


                $subscriber = new EDD_Recurring_Subscriber( $emailid );
                $expiration = $subscriber->get_expiration();
                if ($expiration) {
                    $merge_fields[$subscription_expiring_merge_detail['tag']] = $expiration;
                }
                $subs_start = $subscriber->date_created;
                if ($subs_start) {
                    $merge_fields[$subscription_started_merge_detail['tag']] = $subs_start;
                }
                $subs_total_spend = $subscriber->get_total_payments();
                if ($subs_total_spend) {
                    $merge_fields[$subscription_started_merge_detail['tag']] = $subs_start;   
                }

                $subs_status = $subscriber->get_status();
                if ($subs_status) {
                   $merge_fields[$total_spend_merge_detail['tag']] = $subs_status; 
                }

                if (!empty( $apikey ) && !empty( $list_id )) {
                    require EDD_MAILCHIMP_ABANDONED_CART_DIR . 'includes/libraries/MailChimpAPI3.php';
                    $MailChimp = new MailChimpAPI3($apikey);       

                    $subscriber_hash = $MailChimp->subscriberHash($emailid);

                    $result = $MailChimp->put("/lists/$list_id/members/$subscriber_hash", [
                                    'email_address'         => $emailid,
                                    'status'                => 'subscribed',
                                    'status_if_new'         => 'subscribed',
                                    'merge_fields'          => $merge_fields,
                                ]);

                    return $result;
                }else{
                    return false;
                }
            }else{
                return false;
            }
        }

        /**
         *  Delete Subscriber from Mailchimp List
         */
        /*public function delete_subscribe_member($emailid)
        {
            if(!empty($emailid)){
                
                $apikey = $this->apiKey;
                $list_id = $this->get_store_list_id($mailChimpKey,$mailChimpStoreId);
                
                if (!empty( $apikey ) && !empty( $list_id )) {
                    require EDD_MAILCHIMP_ABANDONED_CART_DIR . 'includes/libraries/MailChimpAPI3.php';
                    $MailChimp = new MailChimpAPI3($apikey); 
                    $subscriber_hash = $MailChimp->subscriberHash($emailid);
                    $result=$MailChimp->delete("lists/$list_id/members/$subscriber_hash");
                    return $result;
                }else{
                    return false;
                }
            }else{
                return false;
            }
        }*/

        /**
         *  Get All MailChimp Products
         */
        /*public function get_all_mailchimp_products(){
            
            $apikey = $this->apiKey;
            $store_id = $this->storeID;
            
            if (!empty( $apikey ) ) {

                $mailchimp_automations =array();

                require EDD_MAILCHIMP_ABANDONED_CART_DIR . 'includes/libraries/MailChimpAPI3.php';
                $MailChimp = new MailChimpAPI3($apikey);            
                return $retval = $MailChimp->get("/ecommerce/stores/$store_id/products");                   
                
            }

        }*/


        /**
         *  Sync Top 15 Most Earning product of EDD
         */
        public function edd_sync_top_seller_items(){

            global $wpdb;
            $table_posts = $wpdb->prefix . 'posts';
            $table_posts_meta = $wpdb->prefix . 'postmeta';
            $downloads = $wpdb->get_results( "SELECT SQL_CALC_FOUND_ROWS  p.ID FROM $table_posts as p INNER JOIN $table_posts_meta as pm ON ( p.ID = pm.post_id ) WHERE 1=1  AND ( pm.meta_key = '_edd_download_earnings') AND p.post_type = 'download' AND ((p.post_status = 'publish')) GROUP BY p.ID ORDER BY pm.meta_value+0 DESC LIMIT 0, 25" );

            if(isset($downloads) && count($downloads)){
                foreach ($downloads as $key => $download) {
                    $download_id = $download->ID;
                    $this->add_update_product($download_id);
                }
            }

        }

        /**
         *  Deleted plugin generated EDD Discount codes
         */
        public function delete_edd_cp(){

            global $wpdb;
            global $edd_options;
            $table_posts = $wpdb->prefix . 'posts';
            $table_posts_meta = $wpdb->prefix . 'postmeta';
            
            $edd_abandoned_mailchimp_discount_code_prefix = !empty( $edd_options['edd_abandoned_mailchimp_discount_code_prefix'] ) ? $edd_options['edd_abandoned_mailchimp_discount_code_prefix'] : EDD_ABD_CODE_PREFIX;

            $codes = $wpdb->get_results( "SELECT p.ID FROM $table_posts_meta as pm,$table_posts as p WHERE p.post_status != 'active' and pm.`meta_value` LIKE '%".$edd_abandoned_mailchimp_discount_code_prefix."%' AND pm.meta_key = '_edd_discount_code' AND p.ID = pm.post_id " );

            if(isset($codes) && count($codes)){
                foreach ($codes as $key => $code) {
                    $post_id = $code->ID;
                    wp_delete_post( $post_id, true );
                }
            }

        }

        /**
         *  Get Add New subscriber Status
         */
        public function get_subscriber_status(){

            $mailchimp_subscriber_status[0] = __( "False", 'edd-mailchimp-abandoned-cart' );
            $mailchimp_subscriber_status[1] = __( "True", 'edd-mailchimp-abandoned-cart' );

            $mailchimp_subscriber_status = !empty( $mailchimp_subscriber_status ) ? $mailchimp_subscriber_status : array();

            return $mailchimp_subscriber_status;
        }

        /**
         *  Get Add New subscriber Status
         */
        public function get_discountCode_status(){

            $edd_dc_status['on'] = __( "ON", 'edd-mailchimp-abandoned-cart' );
            $edd_dc_status['off'] = __( "OFF", 'edd-mailchimp-abandoned-cart' );

            $edd_dc_status = !empty( $edd_dc_status ) ? $edd_dc_status : array();

            return $edd_dc_status;
        }

        /**
         *  Get information about a specific product
         */
        public function get_product($store_id,$product_id)
        {
            if(!empty($product_id) && !empty($store_id)){
                $mailchimp_product =array();
               
                $apikey = $this->apiKey;
                
                if (!empty( $apikey ) ) {
                    require EDD_MAILCHIMP_ABANDONED_CART_DIR . 'includes/libraries/MailChimpAPI3.php';
                    $MailChimp = new MailChimpAPI3($apikey);         
                    $retval = $MailChimp->get("/ecommerce/stores/$store_id/products/$product_id");
                    
                    if (empty($retval)) {
                        $mailchimp_product['false'] = __( "Unable to load products , check your API Key.", 'edd-mailchimp-abandoned-cart' );
                    } else {
                        return $retval;
                    }
                }

                $mailchimp_product = !empty( $mailchimp_product ) ? $mailchimp_product : array();

                return $mailchimp_product;
            }
        }

        /**
         *  Create Product or Update Product on Mailchimp
        */
        public function add_update_product($download_id)
        {
            if(!empty($download_id)){
                
                $apikey = $this->apiKey;
                $store_id = $this->storeID;

                if (!empty( $apikey ) && !empty( $store_id )) {

                    $mailchimp_lists =array();

                    require EDD_MAILCHIMP_ABANDONED_CART_DIR . 'includes/libraries/MailChimpAPI3.php';
                    $MailChimp = new MailChimpAPI3($apikey);           


                    $download_title = get_the_title($download_id);
                    $download_url = get_permalink( $download_id );
                    $download_excerpt = get_the_excerpt( $download_id );

                    $download_featured_image = get_the_post_thumbnail_url($download_id,'post-thumbnail' );
                    if(!$download_featured_image){
                        $download_featured_image = '';
                    }
                    $download = new EDD_Download( $download_id );
                    
                    $variable_prices = $download->get_prices();
                    
                    $variants = array();
                    $count=0;
                    if(count($variable_prices)>0){
                        foreach ($variable_prices as  $value) {
                            $variants[$count]['id']  = PRODUCT_VARIANTS_PREFIX.$value['index'];
                            $variants[$count]['title']   = $value['name'];
                            $variants[$count]['price'] = $value['amount'];
                            $count++;
                        }
                    }else{
                        $download_amount = $download->get_price();

                        $variants[$count]['id']  = PRODUCT_VARIANTS_PREFIX.'0';
                        $variants[$count]['title']   = $download_title;
                        $variants[$count]['price'] = $download_amount;
                        
                    }
                    
                    $product_id = PRODUCT_PREFIX.$download_id;
                    $check_product = $this->get_product($store_id,$product_id);

                    if(isset($check_product['id'])){
                        $res = $MailChimp->patch("/ecommerce/stores/$store_id/products/$product_id", [
                                    'title'=>$download_title,
                                    'description'=>$download_excerpt,
                                    'url'=> $download_url,
                                    'image_url'=> $download_featured_image,
                                    'variants'=> $variants,
                                ]);
                        
                    }else{
                        $res = $MailChimp->post("/ecommerce/stores/$store_id/products", [
                                    "id"=>$product_id,
                                    'title'=>$download_title,
                                    'description'=>$download_excerpt,
                                    'url'=> $download_url,
                                    'image_url'=> $download_featured_image,
                                    'variants'=> $variants,
                                ]);
                       
                    }
                    return $res;
                }else{
                    return false;
                }
            }else{
                return false;
            }
        }

        /**
         *  Add Products to cart of mailchimp
         */
        public function mailchimp_add_to_cart($customer_array, $product_array,$edd_token,$order_total){
            
            if(count($customer_array)!=0 && count($product_array)!=0 && !empty($order_total) && !empty($edd_token)){
                
                
                $apikey = $this->apiKey;
                $store_id = $this->storeID;
                global $edd_options;


                $opt_in_status             = !empty( $edd_options['edd_abandoned_mailchimp_add_subscriber_status'] ) ? $edd_options['edd_abandoned_mailchimp_add_subscriber_status'] : '';

                if($opt_in_status == 1){
                    $opt_in_status = true;
                }else{
                    $opt_in_status = false;
                }
                if (!empty( $apikey ) && !empty($store_id)) {
                    
                    require EDD_MAILCHIMP_ABANDONED_CART_DIR . 'includes/libraries/MailChimpAPI3.php';
                    $MailChimp = new MailChimpAPI3($apikey);           

                    $result = $MailChimp->post("/ecommerce/stores/$store_id/carts", [
                            'id'=>  $edd_token,
                            'customer'=>["id"=>CUSTOMER_PREFIX.$customer_array['user_id'],'opt_in_status'=>$opt_in_status,'email_address'=> $customer_array['email'],'first_name'=> $customer_array['first_name'],'last_name' => $customer_array['last_name']],
                            'currency_code'=> edd_get_currency(),
                            'checkout_url'=> edd_get_checkout_uri().'?edd_action=restore_cart&edd_cart_token='.$edd_token,
                            'order_total'=>$order_total,
                            'lines'=>$product_array,
                            ]);
                    if(isset($result['id'])){
                        return $result['id'];
                    }else{
                        return false;
                    }
                }else{
                    return false;
                }
            }else{
                return false;
            }

        }


        /**
         *  Add a new order to a mailchimp store 
         */
        public function mailchimp_create_order($customer_array, $product_array,$order_array){
            
            if(count($customer_array)!=0 && count($product_array)!=0 && !empty($order_array) ){
                
                
                $apikey = $this->apiKey;
                $store_id = $this->storeID;
                
                if (!empty( $apikey ) && !empty($store_id)) {

                    require EDD_MAILCHIMP_ABANDONED_CART_DIR . 'includes/libraries/MailChimpAPI3.php';
                    $MailChimp = new MailChimpAPI3($apikey);           
                    
                    $result = $MailChimp->post("/ecommerce/stores/$store_id/orders", [
                            'id'=>  'ORDER_'.time(),
                            'customer'=>["id"=>CUSTOMER_PREFIX.$customer_array['user_id'],'first_name'=> $customer_array['first_name'],'last_name' => $customer_array['last_name'],'orders_count' => $customer_array['orders_count'],'total_spent' => $customer_array['total_spent'],'address' => ['address1'=>$customer_array['address1'],'address2'=>$customer_array['address2'],'city'=>$customer_array['city'],'province'=>$customer_array['province'],'postal_code'=>$customer_array['postal_code'],'country'=>$customer_array['country']]],
                            'currency_code'=> $order_array['currency_code'],
                            'order_total'=> $order_array['order_total'],
                            'lines'=>$product_array,
                            ]);
                    
                    if(isset($result['id'])){
                        return $result['id'];
                    }else{
                        return false;
                    }
                }else{
                    return false;
                }
            }else{
                return false;
            }

        }

        /**
         *  Remove cart of mailchimp
         */
        public function mailchimp_remove_cart($cart_id){
            
            if(!empty($cart_id)){
                
                $apikey = $this->apiKey;
                $store_id = $this->storeID;

                if (!empty( $apikey ) && !empty($store_id)) {

                    require EDD_MAILCHIMP_ABANDONED_CART_DIR . 'includes/libraries/MailChimpAPI3.php';
                    $MailChimp = new MailChimpAPI3($apikey);           
                    
                    $result = $MailChimp->delete("/ecommerce/stores/$store_id/carts/$cart_id");
                    
                }else{
                    return false;
                }
            }else{
                return false;
            }

        }

        /**
         *  Add a subscriber to a workflow email
         */
        /*public function add_subscriber_workflow($workflow_email_id){
            
            if(!empty($workflow_email_id)){
                
                $apikey = $this->apiKey;
                $store_id = $this->storeID;
                $workflow_id = '';

                if (!empty( $apikey ) && !empty($store_id)) {
                    $automations = $this->edd_get_mailchimp_automations();

                    if(count($automations)){
                        foreach ($automations['automations'] as $key => $value) {
                            if($value['trigger_settings']['workflow_type'] == 'abandonedCart' && $value['recipients']['store_id'] == $store_id){
                                $workflow_id = $value['id'];
                            }
                        }
                    }
                    
                    if(!empty($workflow_id)){
                        require EDD_MAILCHIMP_ABANDONED_CART_DIR . 'includes/libraries/MailChimpAPI3.php';
                        $MailChimp = new MailChimpAPI3($apikey);           
                        
                        return $MailChimp->post("/automations/$workflow_id/emails/38a32e2eac/queue",array('email_address'=>$workflow_email_id));
                    }
                    
                    
                }else{
                    return false;
                }
            }else{
                return false;
            }

        }*/

        /**
         *  Read subscriber to a workflow email
         */
        /*public function read_subscriber_workflow($workflow_email_id){
            
            if(!empty($workflow_email_id)){
                
                $apikey = $this->apiKey;
                $store_id = $this->storeID;
                $workflow_id = '';

                if (!empty( $apikey ) && !empty($store_id)) {
                    $automations = $this->edd_get_mailchimp_automations();

                    if(count($automations)){
                        foreach ($automations['automations'] as $value) {
                            if($value['trigger_settings']['workflow_type'] == 'abandonedCart' && $value['recipients']['store_id'] == $store_id){
                                $workflow_id = $value['id'];
                            }
                        }
                    }
                    
                    if(!empty($workflow_id)){
                        $subscriber_hash = md5(strtolower($workflow_email_id));
                        require EDD_MAILCHIMP_ABANDONED_CART_DIR . 'includes/libraries/MailChimpAPI3.php';
                        $MailChimp = new MailChimpAPI3($apikey);           
                        
                        return $result = $MailChimp->get("/automations/$workflow_id/emails");
                    }
                    
                    
                }else{
                    return false;
                }
            }else{
                return false;
            }

        }*/

        /**
         *  Get all merge fields for a list
         */
        public function mailchimp_get_all_merge_fields($_apiKey,$_listID){
            
            if(!empty($_apiKey)){
                $apikey = $_apiKey;
            }else{
                $apikey = $this->apiKey;
            }
            
            if(!empty($_listID)){
                $list_id = $_listID;
            }else{
                $list_id = $this->get_store_list_id($apikey,'');
            }

            
            if (!empty( $apikey ) && !empty($list_id)) {

                $mailchimp_merge_fields = array();

                require EDD_MAILCHIMP_ABANDONED_CART_DIR . 'includes/libraries/MailChimpAPI3.php';
                $MailChimp = new MailChimpAPI3($apikey);            
                $retval = $MailChimp->get("/lists/$list_id/merge-fields");
                   
                if (empty($retval)) {
                    $mailchimp_merge_fields['false'] = __( "Unable to load MailChimp merge fields, check your API Key.", 'edd-mailchimp-abandoned-cart' );
                } else {

                    if ( $retval['total_items'] == 0 ) {
                        $mailchimp_merge_fields['false'] = __( "You have not created any merge fields at MailChimp", 'edd-mailchimp-abandoned-cart' );
                        return $mailchimp_merge_fields;
                    }
                    $mailchimp_merge_fields[0] = __( "Select Merge Field", 'edd-mailchimp-abandoned-cart' );
                    foreach ( $retval['merge_fields'] as $list ) {
                        $mailchimp_merge_fields[$list['merge_id']] = $list['name'];
                    }
                    
                }
            }

            $mailchimp_merge_fields = !empty( $mailchimp_merge_fields ) ? $mailchimp_merge_fields : array();

            return $mailchimp_merge_fields;

        }
        
        /**
         *  Get a specific merge field
         */
        public function get_merge_field_detail($merge_id)
        {
            if(!empty($merge_id)){
                $mailchimp_merge_field =array();
                
                $apikey = $this->apiKey;
                $list_id = $this->get_store_list_id($apikey,'');
                
                if (!empty( $apikey ) && !empty($list_id)) {
                    require EDD_MAILCHIMP_ABANDONED_CART_DIR . 'includes/libraries/MailChimpAPI3.php';
                    $MailChimp = new MailChimpAPI3($apikey);         
                    $retval = $MailChimp->get("/lists/$list_id/merge-fields/$merge_id");
                    
                    if (empty($retval)) {
                        $mailchimp_merge_field['false'] = __( "Unable to load products , check your API Key.", 'edd-mailchimp-abandoned-cart' );
                    } else {
                        return $retval;
                    }
                }

                $mailchimp_merge_field = !empty( $mailchimp_merge_field ) ? $mailchimp_merge_field : array();

                return $mailchimp_merge_field;
            }
        }

        /**
         *  Get All Carts From Mailchimp
         */
        public function mailchimp_get_all_cart(){
            
            $apikey = $this->apiKey;
            $store_id = $this->storeID;

            
            if (!empty( $apikey ) && !empty($store_id)) {

                require EDD_MAILCHIMP_ABANDONED_CART_DIR . 'includes/libraries/MailChimpAPI3.php';
                $MailChimp = new MailChimpAPI3($apikey);           
                
                return $result = $MailChimp->get("/ecommerce/stores/$store_id/carts");
                
            }else{
                return false;
            }

        }

        /**
         *  Get ListId from Store
        */
        public function get_store_list_id($_apiKey,$_storeID) {
            if(!empty($_apiKey)){
                $apikey = $_apiKey;
            }else{
                $apikey = $this->apiKey;
            }

            if(!empty($_storeID)){
                $store_id = $_storeID;
            }else{
                $store_id = $this->storeID;
            }

            $store_detail = $this->get_store_detail($apikey,$store_id);
            if(isset($store_detail['id'])){
                return $store_detail['list_id'];
            }
            return;
        }

        /**
         *  Enqueue Javascript
        */
        public function load_js_scripts() {
            wp_enqueue_script('cart_script', EDD_MAILCHIMP_ABANDONED_CART_URL.'assets/js/scripts.js');
            wp_localize_script( 'cart_script', 'cart_script_object',
            array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
        }

        /**
         *  Enqueue Javascript for admin
        */
        public function load_admin_js_scripts() {
            wp_enqueue_script('abd_admin_script', EDD_MAILCHIMP_ABANDONED_CART_URL . 'assets/js/admin.js');
            wp_localize_script( 'abd_admin_script', 'abd_admin_script_object',
            array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
        }

        /**
         *  Ajax Callback Function
         */
        public function action_save_logout_user_callback(){
            $inser_data =    array();    
            if ( is_user_logged_in() ) {
                $user_id = get_current_user_id();
                $inser_data['user_id'] = $user_id;
                
                $user_info = get_userdata($user_id);

                $first_name = $user_info->first_name;
                $last_name = $user_info->last_name;
                $edd_email = $user_info->user_email;

                if(empty($first_name)){
                    $first_name = $_POST['edd_first_name'];
                }
                if(empty($last_name)){
                    $last_name = $_POST['edd_last_name'];
                }
                $_SESSION['EDD_Mailchimp_Abandoned_Session_Email'] = $edd_email;
                $_SESSION['EDD_Mailchimp_Abandoned_Session_Last_Name'] = $first_name;
                $_SESSION['EDD_Mailchimp_Abandoned_Session_First_Name'] = $last_name;
            } else {
                if(isset($_POST['edd_email'])){
                    if(filter_var($_POST['edd_email'], FILTER_VALIDATE_EMAIL) !== false){

                        $_SESSION['EDD_Mailchimp_Abandoned_Session_Email'] = $_POST['edd_email'];
                        $_SESSION['EDD_Mailchimp_Abandoned_Session_Last_Name'] = $_POST['edd_first_name'];
                        $_SESSION['EDD_Mailchimp_Abandoned_Session_First_Name'] = $_POST['edd_last_name'];

                    }
                }
            }

            $inser_data['email'] = $_SESSION['EDD_Mailchimp_Abandoned_Session_Email'];
            $inser_data['first_name'] = $_SESSION['EDD_Mailchimp_Abandoned_Session_Last_Name'];
            $inser_data['last_name'] = $_SESSION['EDD_Mailchimp_Abandoned_Session_First_Name'];
            edd_abd_save_cart();
            $unique_id  =    edd_get_cart_token();

            $inser_data['unique_id'] = $unique_id;
            $inser_data['created_at'] = date('Y-m-d H:i:s');
            
            
            $cart_contents = edd_get_cart_contents();

            if ( ! empty( $cart_contents )  && !empty($inser_data['email'])) {
                $inser_data['cart'] = serialize($cart_contents);
                
                global $wpdb;
                $table_carts = $wpdb->prefix . 'edd_mailchimp_abd_carts';
                $row_id = $wpdb->get_var( "SELECT id FROM $table_carts WHERE email='".$inser_data['email']."' OR unique_id='".$inser_data['unique_id']."'  " );
                if(isset($row_id) && !empty($row_id)){
                    $wpdb->update( $table_carts, $inser_data,array('id'=>$row_id));
                }else{
                    $wpdb->insert( $table_carts, $inser_data);    
                }
                
            }
            die();
        }

        /**
         *  Ajax Callback Function
         */
        public function load_mailchimp_merge_fields_callback(){
            $response =    array();
            if ( is_admin() && isset($_POST['mailChimpKey']) && isset($_POST['mailChimpStoreId'])) {
                $mailChimpKey = $_POST['mailChimpKey'];
                $mailChimpStoreId = $_POST['mailChimpStoreId'];
                if(!empty($mailChimpKey) && !empty($mailChimpStoreId)){
                    $_listID = 0;
                    
                    $_listID = $this->get_store_list_id($mailChimpKey,$mailChimpStoreId);
                    
                    $data = $this->mailchimp_get_all_merge_fields($mailChimpKey,$_listID);

                    if(count($data)){
                        $response['data'] = $data;
                    }
                }
            }
            echo json_encode($response);
            die();
        }

        /**
         *  Ajax Callback Function
         */
        public function load_mailchimp_stores_callback(){
            $response =    array();
            if ( is_admin() && isset($_POST['mailChimpKey'])) {
                $mailChimpKey = $_POST['mailChimpKey'];
                
                if(!empty($mailChimpKey)){
                    $data = $this->get_stores(false,$mailChimpKey);
                    if(count($data)){
                        $response['data'] = $data;
                    }
                }
            }
            echo json_encode($response);
            die();
        }

        

        /**
         *  Get Connection code from MailChimp and update into DB 
         */
        public function mailchimp_site_connection_code(){ 
    
            $url = '';
            
            $store_detail = $this->get_store_detail('','');
            if(isset($store_detail['id'])){
                $url = $store_detail['connected_site']['site_script']['url'];
            }
            if ( !empty($url) && strpos($url, 'chimpstatic') !== false) { 
                edd_update_option( 'edd_abandoned_mailchimp_connected_site_script', $url);
            } 
        }

        // public function showLicenseMessage() {
        //     $this->edd_abd_plugin_check_license();

        //      /*
        //      * Only show to admins
        //      */
        //     if( current_user_can('manage_options') && !empty($this->message) )
        //     {
        //         mailchimp_abd_show_message($this->message, $this->messageError);
        //     }
        // }

        // public function edd_abd_plugin_check_license() {
        //     $store_url = EDD_ABD_STORE_API_URL;
        //     global $edd_options;

        //     $license_key             = !empty( $edd_options['edd_edd_mailchimp_abandoned_cart_wordpress_plugin_license_key'] ) ? $edd_options['edd_edd_mailchimp_abandoned_cart_wordpress_plugin_license_key'] : '';
             
        //     $api_params = array(
        //         'edd_action' => 'check_license',
        //         'license' => $license_key,
        //         'item_id' => EDD_ABD_STORE_PRODUCT_ID,
        //         'url' => home_url()
        //     );
        //     $response = wp_remote_post( $store_url, array( 'body' => $api_params, 'timeout' => 15, 'sslverify' => false ) );
        //     if ( is_wp_error( $response ) ) {
        //         return false;
        //     }

        //     $license_data = json_decode( wp_remote_retrieve_body( $response ) );
        //     $licenseStatus = $license_data->license;
        //     $supportUrl  = 'https://www.pluginsandsnippets.com/support/';
        //     $pluginName = EDD_MAILCHIMP_ABANDONED_CART_NAME;

        //     $licensingPageUrl = esc_url_raw(add_query_arg(array('post_type' => 'download', 'page' => 'edd-settings', 'tab' => 'licenses'), admin_url('edit.php')));
        //     switch($licenseStatus)
        //     {
        //         case 'no_activations_left':
        //             /*
        //              * This license activation limit has beeen reached
        //              */
        //             $this->message = 'Your have reached your activation limit for "' . $pluginName . '"! <br/>'
        //                     . 'Please, purchase a new license or contact <a target="_blank" href="' . esc_url_raw($supportUrl) . '">support</a>.';
        //             $this->messageError = TRUE;
        //             break;
        //         case 'deactivated':

        //         case 'site_inactive':
        //             $this->message = __( 'Your license is not active for this URL.' );
        //             $this->messageError = TRUE;
        //             break;

        //         case 'inactive':
        //             /*
        //              * This license is invalid / either it has expired or the key was invalid
        //              */
        //             $this->message = 'Your license key provided for "' . $pluginName . '" is inactive! <br/>'
        //                     . 'Please, go to <a href="' . $licensingPageUrl . '">plugin\'s License page</a> and click "Save Changes".';
        //             $this->messageError = TRUE;
        //             break;
        //         case 'invalid':
        //             /*
        //              * This license is invalid / either it has expired or the key was invalid
        //              */
        //             $this->message = 'Your license key provided for "' . $pluginName . '" is invalid! <br/>'
        //                     . 'Please go to <a href="' . $licensingPageUrl . '">plugin\'s License page</a> for the licencing instructions.';
        //             $this->messageError = TRUE;
        //             break;
        //         case '':
        //             /*
        //              * This license is invalid / either it has expired or the key was invalid
        //              */
        //             $this->message = 'To use "' . $pluginName . '" you have to provide a valid license key! <br/>'
        //                     . 'Please go to <a href="' . $licensingPageUrl . '">plugin\'s License page</a> to enter your license.';
        //             $this->messageError = TRUE;
        //             break;
        //         case 'valid':
        //                 $now  = current_time( 'timestamp' );
        //                 $expiration = strtotime( $license_data->expires, current_time( 'timestamp' ) );
        //                 if( $expiration > $now && $expiration - $now < ( DAY_IN_SECONDS * 30 ) ) {
        //                     $this->message = sprintf(
        //                         __( 'Your license key provided for "' . $pluginName . '" is expires soon! It expires on %s. <a href="%s" target="_blank">Renew your license key</a>.', 'edd-mailchimp-abandoned-cart' ),
        //                         date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) ),
        //                         'https://www.pluginsandsnippets.com/my-purchase-history/'
        //                     );
        //                     $this->messageError = TRUE;
        //                 }
        //                 break;
        //         case 'item_name_mismatch' :
        //                 $this->message = sprintf( __( 'This appears to be an invalid license key for "%s."' ), $pluginName );
        //                 $this->messageError = TRUE;
        //                 break;
        //         case 'revoked' :
        //                 $this->message = __( 'Your license key has been disabled for "'.$pluginName.'".' );
        //                 $this->messageError = TRUE;
        //                 break;
        //         case 'expired' :
        //                 $this->message = sprintf(
        //                     __( 'Your license key expired on %s. for "'.$pluginName.'". Please Purchase a new license to receive further updates from <a target="_blank" href="' . esc_url_raw($store_url) . '">here</a>.' ),
        //                     date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
        //                 );
        //                 $this->messageError = TRUE;
        //                 break;

        //         default:
        //             break;
        //     }
        // }

    }   
} // End if class_exists check


   



/**
 * The main function responsible for returning the one true EDD_Mailchimp_Abandoned_Cart
 * instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \EDD_Mailchimp_Abandoned_Cart The one true EDD_Mailchimp_Abandoned_Cart
 *
 * @todo        Inclusion of the activation code below isn't mandatory, but
 *              can prevent any number of errors, including fatal errors, in
 *              situations where your extension is activated but EDD is not
 *              present.
 */
function EDD_Mailchimp_Abandoned_Cart_load() {
    if( ! class_exists( 'Easy_Digital_Downloads' ) ) {
        if( ! class_exists( 'EDD_Extension_Activation' ) ) {
            require_once 'includes/class.extension-activation.php';
        }

        $activation = new EDD_Extension_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );
        $activation = $activation->run();
    } else {
        return EDD_Mailchimp_Abandoned_Cart::instance();
    }
}
add_action( 'plugins_loaded', 'EDD_Mailchimp_Abandoned_Cart_load' );


/**
 * The activation hook is called outside of the singleton because WordPress doesn't
 * register the call from within the class, since we are preferring the plugins_loaded
 * hook for compatibility, we also can't reference a function inside the plugin class
 * for the activation function. If you need an activation function, put it here.
 *
 * @since       1.0.0
 * @return      void
 */
function edd_mailchimp_abandoned_cart_activation() {
    /* Activation functions here */
}
register_activation_hook( __FILE__, 'edd_mailchimp_abandoned_cart_activation' );


add_action ( 'init', 'edd_abd_load_functions' );

function edd_abd_load_functions() {
    require_once EDD_MAILCHIMP_ABANDONED_CART_DIR . 'includes/functions.php';
}

if( !function_exists('mailchimp_abd_show_message') )
{

    /**
     * Generic function to show a message to the user using WP's
     * standard CSS classes to make use of the already-defined
     * message colour scheme.
     *
     * @param $message The message you want to tell the user.
     * @param $errormsg If true, the message is an error, so use
     * the red message style. If false, the message is a status
     * message, so use the yellow information message style.
     */
    function mailchimp_abd_show_message($message, $errormsg = false)
    {
        if( $errormsg )
        {
            echo '<div id="message" class="error">';
        }
        else
        {
            echo '<div id="message" class="updated fade">';
        }

        echo "<p><strong>$message</strong></p></div>";
    }

}