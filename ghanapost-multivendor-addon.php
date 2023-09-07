<?php
 
/**
 * Plugin Name: GhanaPost Multivendor Addon
 * Plugin URI: https://thetechproviders.com
 * Description: Custom Shipping Method for WooCommerce
 * Version: 1.0.0
 * Author: Farhan Ahmed
 * Author URI: http://www.thetechproviders.com
 * License: GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Domain Path: /lang
 * Text Domain: ghanapostshipping
 */
 
if ( ! defined( 'WPINC' ) ) {
 
    die;
 
}
 
/*
 * Check if WooCommerce is active
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    

    function ghanapost_shipping_method() {
        if ( ! class_exists( 'Ghanapost_Shipping_Method' ) ) {
            class Ghanapost_Shipping_Method extends WC_Shipping_Method {
                /**
                 * Constructor for your shipping class
                 *
                 * @access public
                 * @return void
                 */
                // public $deliveryURL = 'https://live.swooveapi.com/delivery/create-delivery?app_key=';
                // public $baseURL = 'https://live.swooveapi.com/estimates/create-estimate?platform=swoove_multivendor_addon&app_key=';
                public function __construct() {
                    $this->id                 = 'ghanapostshipping'; 
                    $this->method_title       = __( 'GhanaPost Shipping', 'ghanapostshipping' );  
                    $this->method_description = __( 'Custom Shipping Method for GhanaPost', 'ghanapostshipping' ); 
 
                    // Availability & Countries
                    $this->availability = 'including';
                    $this->countries = array('GH');
 
                    $this->init();
 
                    $this->enabled = isset( $this->settings['enabled'] ) ? $this->settings['enabled'] : 'no';
                    $this->title = isset( $this->settings['title'] ) ? $this->settings['title'] : __( 'GhanaPost', 'ghanapostshipping' );
                    $this->apikey = isset( $this->settings['apikey'] ) ? $this->settings['apikey'] : '';
                    $this->clientid = isset( $this->settings['clientid'] ) ? $this->settings['clientid'] : '';
                    $this->username = isset( $this->settings['username'] ) ? $this->settings['username'] : '';
                    $this->password = isset( $this->settings['password'] ) ? $this->settings['password'] : '';

                }
 
                /**
                 * Init your settings
                 *
                 * @access public
                 * @return void
                 */
                function init() {
                    // Load the settings API
                    $this->init_form_fields(); 
                    $this->init_settings();  
 
                    // Save settings in admin if you have any defined
                    add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
                    add_action('woocommerce_view_order', array($this, 'customer_order'), 20);
                    
                    add_action('woocommerce_admin_order_data_after_shipping_address', array($this, 'display_swoove_info'));
                    
                }


                function display_swoove_info($order)
                {
                    $sd_code = get_post_meta($order->id, 'ghanapost_clinttrackingno', true);
                    $ss_code = get_post_meta($order->id, 'ghanapost_batchno', true);
                    

                    ?>
                        <div class="order_data_column" style="width: 100%">
                            <img style="display:inline !important" src="<?php echo dirname(__DIR__) ?>/assets/img/swoove-white.png" alt="" width="80px">
                            <div class="address">
                                <?php
                                //echo dirname(__DIR__);
                                if(!empty($sd_code)):
                                    echo __('<div><b>' . __('Tracking No', 'swoove') . ': </b>' . (empty($sd_code) ? 'Not Created' : $sd_code) . '</div>');
                                endif;
                                if(!empty($ss_code)):
                                    echo __('<div><b>' . __('Batch No', 'swoove') . ': </b>' . (empty($ss_code) ? 'Not Created' : $ss_code) . '</div>');
                                endif;
                                if (!empty($track))
                                    echo __('<a href="' . $track . '"  target="_blank" class="button"> Track Delivery
                                        <span class="dashicons dashicons-external" style="font-size: 17px;margin-top: 4px;"></span></a>');
                                ?>
                            </div>
                            <div class="edit_address">
                                <?php
                                woocommerce_wp_text_input(array(
                                    'id' => 'cust_lat',
                                    'label' => 'Customer Lat:',
                                    'value' => $lat,
                                    'wrapper_class' => 'form-field-wide'
                                ));
                                woocommerce_wp_text_input(array(
                                    'id' => 'cust_lng',
                                    'label' => 'Customer Lng:',
                                    'value' => $lng,
                                    'wrapper_class' => 'form-field-wide'
                                ));
                                ?>
                            </div>
                        </div>
                    <?php
                }


                /**
                 * Define settings field for this shipping
                 * @return void 
                 */
                function init_form_fields() { 
 
                    $this->form_fields = array(
 
                     'enabled' => array(
                          'title' => __( 'Enable', 'ghanapostshipping' ),
                          'type' => 'checkbox',
                          'description' => __( 'Enable this shipping.', 'ghanapostshipping' ),
                          'default' => 'yes'
                          ),
                     
                     'title' => array(
                        'title' => __( 'Title', 'ghanapostshipping' ),
                          'type' => 'text',
                          'description' => __( 'Title to be display on site', 'ghanapostshipping' ),
                          'default' => __( 'Swoove Shipping', 'ghanapostshipping' )
                          ),
                     'apikey' => array(
                        'title' => __( 'API Key ', 'ghanapostshipping' ),
                        'type' => 'text',
                        'description' => __( 'Enter API key provided by ghanapost.', 'ghanapostshipping' ),
                        'default' => 'no'
                        ),
                    'clientid' => array(
                        'title' => __( 'Client ID', 'ghanapostshipping' ),
                            'type' => 'text',
                            'description' => __( 'Enter your ghanapost Client-ID', 'ghanapostshipping' ),
                            'default' => __( '', 'ghanapostshipping' )
                            ),
                    'username' => array(
                        'title' => __( 'Username', 'ghanapostshipping' ),
                            'type' => 'text',
                            'description' => __( 'Username of your ghanapost account', 'ghanapostshipping' ),
                            'default' => __( '', 'ghanapostshipping' )
                    ),
                    'password' => array(
                        'title' => __( 'Password', 'ghanapostshipping' ),
                            'type' => 'text',
                            'description' => __( 'Password of your ghanapost account', 'ghanapostshipping' ),
                            'default' => __( '', 'ghanapostshipping' )
                    )
 
                     );
 
                }

                

 
                // Customer View Order details.
                public function customer_order($order_id)
                {
                    // echo esc_html("<h2 class='woocommerce-column__title' style='margin-bottom: 0'>Delivery</h2>");

                    // $sd_code = get_post_meta($order_id, 'swoove_id', true);
                    // $status = get_post_meta($order_id, 'swoove_delivery_status', true);
                    // $track = get_post_meta($order_id, 'swoove_tracking_link', true);
                    // $address = wc_get_order($order_id)->get_billing_address_1();

                    // echo esc_html('<p style="margin-bottom: 0"><b>' . __('Delivery Code', 'swoove') . ': </b>' . (empty($sd_code) ? 'Not Created' : $sd_code) . '</p>');
                    // echo esc_html('<p style="margin-bottom: 0"><b>' . __('Status', 'swoove') . ': </b>' . $status . '</p>');
                    // echo esc_html('<p><b>' . __('DropOff', 'swoove') . ': </b>' . $address . '</p>');
                    // if (!empty($track))
                    //     echo esc_html('<a href="' . $track . '"  target="_blank" class="button">
                    //                 Track Delivery
                    //                 <span class="dashicons dashicons-external" style="font-size: 17px;margin-top: 4px;"></span></a>');
                }
 

                // Get Destination ids for local
                public function get_local_destinations(){
                    $response = get_transient('ghanapost_local_destinations');
                    if(!empty($response)){
                        return $response;
                    }

                    $options = array (
                        "METHOD" => "PARCELDESTINATION",
                        "APIKEY" => $this->apikey,
                        "USERNAME" => $this->username,
                        "PASSWORD" => $this->password,
                        "CLIENTID" => $this->clientid,
                        "USERID" => $this->username,
                        "TYPEOFDESTINATION" => "local"
                    );
                    $curl = curl_init();

                    curl_setopt_array($curl, array(
                        CURLOPT_URL => "https://eps.v2.ghanapost.com.gh/api/",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "POST",
                        CURLOPT_POSTFIELDS => json_encode($options),
                        CURLOPT_HTTPHEADER => array(
                            "Content-Type: application/x-www-form-urlencoded"
                        ),
                    ));

                    $response = curl_exec($curl);
                    
                    $response = wp_list_pluck(  json_decode($response), 'destination_name' , 'destination_id');
                    set_transient('ghanapost_local_destinations', $response, 86400 );

                    return $response;

                }
              

                // Our hooked in function - $address_fields is passed via the filter!
               


                public function ghanapost_create_delivery($order_id) 
                {
                    if( $this->enabled != 'yes'){
                        return;
                    }         
                    $order = wc_get_order($order_id); 
                    
                    if(get_post_meta($order->id, 'ghanapost_tracking', true) != ''){
                        return;
                    } 
                    $custy = WC()->cart->get_customer(); 
                    $customer_digital_address = get_post_meta($order->id,'_billing_digital_address', true); 
                    $billing_destination_id = get_post_meta($order->id,'billing_destination_id', true); 
                    
                    //echo $billing_destination_id; die();
                    $billing_details = $order->get_address('billing'); 
                    
                    $customer_name = $billing_details['first_name']. " " . $billing_details['last_name'];
                    $customer_address = $billing_details['address_1'] ;
                    if(!empty($billing_details['address_2'])) $customer_address .= "," . $billing_details['address_2'] ;
                    if(!empty($billing_details['city'])) $customer_address .= "," . $billing_details['city'];
                    $customer_country = WC()->countries->countries[$billing_details['country']];
                    if(!empty($customer_country)) $customer_address .= "," . $customer_country;
                    $customer_zone = $billing_details['postcode'];
                    $customerMobile = empty($custy->get_shipping_phone()) ? $custy->get_billing_phone() : $custy->get_shipping_phone();
                    $customerEmail = $custy->get_billing_email();

                    $seller = dokan_get_seller_id_by_order( $order_id ); 
                    
                    if($seller == 0){ 
                        $sub_orders = dokan_get_suborder_ids_by($order_id);
                        $counter = 0;
                        foreach($sub_orders as $sub_order) { 
                            $seller = dokan_get_seller_id_by_order( $sub_order->ID ); 
                            $order = wc_get_order($sub_order->ID);
                            if(get_post_meta($order->id, 'ghanapost_clinttrackingno', true) != ''){
                                continue;
                            }   
                            if(  strpos(WC()->session->get( 'chosen_shipping_methods')[$counter],'ghanapost') !== false) {
                               // echo 'ffffffff'; die();
                            }else{
                                continue;
                               // echo 'fff'; die();
                            }
     
                            // echo 'ghanapost' . $seller . $billing_destination_id; echo '<br>' . WC()->session->get( 'chosen_shipping_methods')[$counter]; 
                            // if(   WC()->session->get( 'chosen_shipping_methods')[$counter] != 'ghanapost' . $seller . $billing_destination_id) {
                            //     return;
                            // }
                            // $shipping = current($order->get_shipping_methods());
                            //  echo '<pre>'; print_r($shipping->get_method_id()); echo '</pre>';
                            // if(!empty($shipping) && $shipping->get_method_id() != 'ghanapostshipping' ) {
                            //     continue;
                            // }
                            $store_info  = dokan_get_store_info($seller); 
                            $user_info = get_userdata($seller);

                            $description = '';
                            $description = 'StoreName: ' . $store_info["store_name"] . ', StorePhone: ' . $store_info["phone"] . ', StoreEamil: ' . $user_info->user_email . ', Postoffice: '. $store_info["ghanapost_postoffice_info"];


                            $options = array(
                                "METHOD" => "ADDPARCEL",
                                "APIKEY" => "6de4bf5a9b1a77c6e70463c4e9cceadb48c8cc40",
                                "USERNAME" => "nabyk",
                                "PASSWORD" => "1cde9cc751ee93ba5b8cef845a4e95d7184dad7d",
                                "CLIENTID" => 3592,
                                "USERID" => "nabyk",
                                "DESTINATIONID" => $billing_destination_id,
                                "PARCELWT" => "0.5",
                                "TYPEOFDESTINATION" => "LOCAL",
                                "ITEMTYPE" => "EMS",
                                "ITEMINSURED" => "Yes",
                                "VALUEOFITEM" => "100",
                                "CONSIGNEE" => $customer_name,
                                "CLIENTTRACKINGNO" => "CUTE".$order->id,
                                "CONSIGNEEADDRESS" => $customer_address,
                                "CONSIGNEEDIGITALADDRESS" => $customer_digital_address,
                                "CONSIGNEETEL" => $customerMobile,
                                "CONSIGNEEEMAI" => $customerEmail,
                                "CONSIGNEEPOSTCODE" => $customer_zone,
                                "CONSIGNEECITY" => $billing_details['city'],
                                "PARCELDESTINATION" => $billing_destination_id,
                                "PARCELNO" => "1",
                                "DOCTYPE" => "DOCUMENT",
                                "DESCRIPTION" => $description,
                                "FACILITYLOCATION" => $store_info['ghanapost_postoffice_info'],
                            );

                            $curl = curl_init();

                            curl_setopt_array($curl, array(
                                CURLOPT_URL => "https://eps.v2.ghanapost.com.gh/api/",
                                CURLOPT_RETURNTRANSFER => true,
                                CURLOPT_ENCODING => "",
                                CURLOPT_MAXREDIRS => 10,
                                CURLOPT_TIMEOUT => 0,
                                CURLOPT_FOLLOWLOCATION => true,
                                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                CURLOPT_CUSTOMREQUEST => "POST",
                                CURLOPT_POSTFIELDS => json_encode($options),
                                CURLOPT_HTTPHEADER => array(
                                    "Content-Type: application/x-www-form-urlencoded"
                                ),
                            ));

                            $response = curl_exec($curl);                    
                            $response = array_shift(json_decode($response)); 
                            //$this->print($response);
                            if($response->TRANSACTION_STATUS == 'Success'){
                                update_post_meta($order->id, 'ghanapost_batchno', $response->BATCHNO);
                                update_post_meta($order->id, 'ghanapost_clinttrackingno', $response->CLIENTTRACKINGNO);
                            }
                            //$this->print($response); 

                        }
                    }else{
                        $seller = dokan_get_seller_id_by_order( $order->id ); 
                        $order = wc_get_order($order->id);
                        if(get_post_meta($order->id, 'ghanapost_clinttrackingno', true) != ''){
                            return;
                        }

                        $shipping = current($order->get_shipping_methods()); 
                        // echo '<pre>'; print_r(WC()->session->get( 'chosen_shipping_methods')); echo '</pre>';
                        if(  strpos(WC()->session->get( 'chosen_shipping_methods')[0],'ghanapost') !== false) {
                               
                        }else{
                            
                           return;
                        }

                        $store_info  = dokan_get_store_info($seller); 
                        $user_info = get_userdata($seller);

                        $description = '';
                        $description = 'StoreName: ' . $store_info["store_name"] . ', StorePhone: ' . $store_info["phone"] . ', StoreEamil: ' . $user_info->user_email . ', Postoffice: '. $store_info["ghanapost_postoffice_info"];


                        $options = array(
                            "METHOD" => "ADDPARCEL",
                            "APIKEY" => "6de4bf5a9b1a77c6e70463c4e9cceadb48c8cc40",
                            "USERNAME" => "nabyk",
                            "PASSWORD" => "1cde9cc751ee93ba5b8cef845a4e95d7184dad7d",
                            "CLIENTID" => 3592,
                            "USERID" => "nabyk",
                            "DESTINATIONID" => $billing_destination_id,
                            "PARCELWT" => "0.5",
                            "TYPEOFDESTINATION" => "LOCAL",
                            "ITEMTYPE" => "EMS",
                            "ITEMINSURED" => "Yes",
                            "VALUEOFITEM" => "100",
                            "CONSIGNEE" => $customer_name,
                            "CLIENTTRACKINGNO" => "CUTE".$order->id,
                            "CONSIGNEEADDRESS" => $customer_address,
                            "CONSIGNEEDIGITALADDRESS" => $customer_digital_address,
                            "CONSIGNEETEL" => $customerMobile,
                            "CONSIGNEEEMAI" => $customerEmail,
                            "CONSIGNEEPOSTCODE" => $customer_zone,
                            "CONSIGNEECITY" => $billing_details['city'],
                            "PARCELDESTINATION" => $billing_destination_id,
                            "PARCELNO" => "1",
                            "DOCTYPE" => "DOCUMENT",
                            "DESCRIPTION" => $description,
                            "FACILITYLOCATION" => $store_info['ghanapost_postoffice_info'],
                        );


                        $curl = curl_init();

                        curl_setopt_array($curl, array(
                            CURLOPT_URL => "https://eps.v2.ghanapost.com.gh/api/",
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_ENCODING => "",
                            CURLOPT_MAXREDIRS => 10,
                            CURLOPT_TIMEOUT => 0,
                            CURLOPT_FOLLOWLOCATION => true,
                            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                            CURLOPT_CUSTOMREQUEST => "POST",
                            CURLOPT_POSTFIELDS => json_encode($options),
                            CURLOPT_HTTPHEADER => array(
                                "Content-Type: application/x-www-form-urlencoded"
                            ),
                        ));

                        $response = curl_exec($curl);                    
                        $response = array_shift(json_decode($response));
                        if($response->TRANSACTION_STATUS == 'Success'){
                            update_post_meta($order->id, 'ghanapost_batchno', $response->BATCHNO);
                            update_post_meta($order->id, 'ghanapost_clinttrackingno', $response->CLIENTTRACKINGNO);
                        }
                        
                    }

                   
                    
                }


                
                public function print($object){
                    echo '<pre>'; print_r($object); echo '</pre>'; 
                }

                




                /**
                 * This function is used to calculate the shipping cost. Within this function we can check for weights, dimensions and other parameters.
                 *
                 * @access public
                 * @param mixed $package
                 * @return void
                 */
                public function calculate_shipping( $package ) {
                    if( $this->enabled != 'yes'){
                        return;
                    }
                    //$custy = WC()->cart->get_customer(); 
                    $raw_postdata = explode('&',$_POST['post_data']);
                    $postedData = array();
                    
                    foreach($raw_postdata as $data){
                        $data = explode('=',$data);
                        $postedData[$data[0]] = urldecode($data[1]);
                    } //echo 'fasf'; 
                    //$this->print($postedData); die();
                    $destination_id = !empty($postedData['destination_id']) ? $postedData['destination_id'] : WC()->session->get('billing_destination_id');
                   // echo $destination_id;die();
                    //$this->print($custy); 
                    //$digital_address = WC()->session->get('billing_digital_address');
                    $options = array (
                        "METHOD" => "CHECKSHIPMENTCOST",
                        "APIKEY" => $this->apikey,
                        "USERNAME" => $this->username,
                        "PASSWORD" => $this->password,
                        "CLIENTID" => $this->clientid,
                        "USERID" => $this->username,
                        "DESTINATIONID" => $destination_id,
                        "PARCELWT" => "0.5",
                        "TYPEOFDESTINATION" => "local",
                        "ITEMTYPE" => "EMS",
                        "ITEMINSURED" => "Yes",
                        "VALUEOFITEM" => "100"
                    );
                    //echo 'printdata<pre>'; print_r(WC()->cart->get_customer()->get_billing()); echo '</pre>';
                    $curl = curl_init();

                    curl_setopt_array($curl, array(
                    CURLOPT_URL => "https://eps.v2.ghanapost.com.gh/api/",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS => json_encode($options),
                    CURLOPT_HTTPHEADER => array(
                        "Content-Type: application/x-www-form-urlencoded"
                    ),
                    ));

                    $response = curl_exec($curl);
                    $response = json_decode($response);


                   //$this->print($response);
                    //$est_response = wp_remote_post($this->baseURL . $swoove_key, $options);  //echo '<pre>'; print_r($est_response); echo '</pre>'; die();
                        //$this->print($est_response); echo 'fafadsfa';die();
                        
                    $rates = [];

                    foreach ($response as $estimate) {
                        $rate = [
                            'id' => 'ghanapost' . $package["seller_id"] . $estimate->DESTINATIONID,
                            'label' => 'GhanaPost',
                            'cost' => $estimate->PRICE,
                            'calc_tax' => 'per_item'
                        ];
                        $rates[] = $rate;
                        $this->add_rate($rate);
                    }
                               
                    
                   
                }
            }
        }
    }
 
    add_action( 'woocommerce_shipping_init', 'ghanapost_shipping_method' );
    add_filter( 'woocommerce_shipping_methods', 'add_ghanapost_shipping_method' );

    function add_ghanapost_shipping_method( $methods ) {
        $methods[] = 'Ghanapost_Shipping_Method';
        return $methods;
    }
   
    add_action( 'woocommerce_thankyou', 'ghanapost_create_delivery');
  
    function ghanapost_create_delivery( $order_id ){ 
        $Swoove_Shipping_Method = new Ghanapost_Shipping_Method();
        $Swoove_Shipping_Method->ghanapost_create_delivery($order_id);

    }



    add_action('woocommerce_checkout_update_order_meta', 'ghanapost_destination_save_extra_checkout_fields', 10, 2);
    // Saving Order Meta Destination ID 
    function ghanapost_destination_save_extra_checkout_fields($order_id, $posted)
    {    //echo '<pre>'; print_r($_POST['billing_destination_id']); echo '</pre>'; die();
        // don't forget appropriate sanitization if you are using a different field type
        if (isset($_POST['billing_destination_id'])) {
            update_post_meta($order_id, 'billing_destination_id', sanitize_text_field($_POST['billing_destination_id']));
        }
    }


    add_filter( 'dokan_settings_form_bottom', 'ghanapost_postoffice_info', 9, 2);
    function ghanapost_postoffice_info( $current_user, $profile_info ){
    $ghanapost_postoffice_info= isset( $profile_info['ghanapost_postoffice_info'] ) ? $profile_info['ghanapost_postoffice_info'] : '';
    ?>
    <div class="gregcustom dokan-form-group">
            <label class="dokan-w3 dokan-control-label" for="setting_address">
                <?php _e( 'Ghanapost Dropoff Postoffice (required for ghanapost shipping)', 'dokan' ); ?>
            </label>
            <div class="dokan-w5">
                <input type="text" class="dokan-form-control input-md valid" name="ghanapost_postoffice_info" id="reg_ghanapost_postoffice_info" value="<?php echo $ghanapost_postoffice_info; ?>" />
                <!-- <span id="dokan_ghanapost_postoffice_info-error" style="display:none;text-align:left;" class="error">This field is required</span> -->
                <!-- <style>span#dokan_ghanapost_postoffice_info-error {
                    display: block;
                }</style> -->
            </div>
        </div>
        <?php
    }

    add_action( 'dokan_store_profile_saved', 'save_store_post_office_info_for_ghanapost_shipping', 15 );
    function save_store_post_office_info_for_ghanapost_shipping( $store_id ) {
        $dokan_settings = dokan_get_store_info($store_id);
        if ( isset( $_POST['ghanapost_postoffice_info'] ) ) {
            $dokan_settings['ghanapost_postoffice_info'] = $_POST['ghanapost_postoffice_info'];      
        }
        update_user_meta( $store_id, 'dokan_profile_settings', $dokan_settings );
    }



    add_filter('manage_edit-shop_order_columns', function($columns){
		if ($_GET['post_status'] != 'trash') {
			$columns['g_order-status'] = __('GhanaPost Shipping', 'ghanapost');
		}
		return $columns;
	},11);

    add_action('manage_shop_order_posts_custom_column', function($column){
		global $the_order;
		if ($column == 'g_order-status'){
            if(get_post_meta($the_order->id, 'ghanapost_clinttrackingno', true) != ''){
                echo '<span><i class="fa fa-check"></i></span>';
            }else{
               // echo '<span><i class="fa fa-times"></i></span>';
            }
        }
	},100,2);

    add_action( 'dokan_order_detail_after_order_items', function($order){
        if(get_post_meta($order->id, 'ghanapost_clinttrackingno', true) == ''){
            return;
        }
        echo '<style>input#dokan-add-tracking-number{display:none !important;}</style>';
        echo '<div class="dokan-panel dokan-panel-default">
        <div class="dokan-panel-heading"><strong>GhanaPost Shipping</strong></div>'; 
        if(get_post_meta($order->id, 'ghanapost_confirmed',true) == 'Yes' && empty(get_post_meta($order->id, 'ghanapost_hawb'))){
            echo '<div class="dokan-panel-body" style="text-align:center;">
                Shipping confirmed but tracking number is not assigned yet. Try to refresh shipping.
                <button data-orderID="'.$order->id.'" style="display: block; margin: auto;" class="confirm-ghanapost-delivery">Refresh Shipping</button>
            </div>'; 

        }elseif(get_post_meta($order->id, 'ghanapost_hawb', true) != ''){
            $options = array(
                "METHOD" => "TRACKING",
                "APIKEY" => "6de4bf5a9b1a77c6e70463c4e9cceadb48c8cc40",
                "USERNAME" => "nabyk",
                "PASSWORD" => "1cde9cc751ee93ba5b8cef845a4e95d7184dad7d",
                "CLIENTID" => 3592,
                "USERID" => "nabyk",
                "HAWD"  => get_post_meta($order->id, 'ghanapost_hawb', true)
            );


            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://eps.v2.ghanapost.com.gh/api/",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => json_encode($options),
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/x-www-form-urlencoded"
                ),
            ));

            $response = curl_exec($curl);                    
            $response = array_shift(json_decode($response));
        }else{
           
           echo '<div class="dokan-panel-body">
                <button data-orderID="'.$order->id.'" style="display: block; margin: auto;" class="confirm-ghanapost-delivery">Confirm Ghanapost Delivery</button>
            </div>'; 
            ?>
            <script>
                jQuery(document).ready(function(){
                    jQuery('.confirm-ghanapost-delivery').click(function(){
                        if(confirm("This action is not reversible please confirm are you ready to deliver?")){
                            jQuery.blockUI({message: ''});
                            jQuery.ajax({
                                type : 'post',
                                url : "<?php echo admin_url( 'admin-ajax.php' ); ?>",
                                data : {
                                    action : 'mark_ready_for_delivery_ghanapost',
                                    orderid : jQuery(this).data().orderid
                                },
                                success: function(res){
                                    alert(res.data.message);
                                    jQuery.unblockUI();
                                    window.location.href = window.location.href; 
                                }
                                    

                            });
                        }
                        else{
                            return false;
                        }
                    });
                   
                });
            </script>
            <?php
            
        }
        echo '</div>';

    });

    add_action("wp_ajax_mark_ready_for_delivery_ghanapost", "mark_ready_for_delivery_ghanapost");
    add_action("wp_ajax_nopriv_mark_ready_for_delivery_ghanapost", "mark_ready_for_delivery_ghanapost");
    
    function mark_ready_for_delivery_ghanapost() {
        if(isset($_POST['orderid']) && !empty($_POST['orderid'])){ 
            $order = wc_get_order($_POST['orderid']);
            $options = array(
                "METHOD" => "CONFIRMANDGETHAWB",
                "APIKEY" => "6de4bf5a9b1a77c6e70463c4e9cceadb48c8cc40",
                "USERNAME" => "nabyk",
                "PASSWORD" => "1cde9cc751ee93ba5b8cef845a4e95d7184dad7d",
                "CLIENTID" => 3592,
                "USERID" => "nabyk",
                "CLIENTTRACKINGNO"  => get_post_meta($order->id, 'ghanapost_clinttrackingno', true),
                "BATCHNO" => get_post_meta($order->id, 'ghanapost_batchno', true)
            );


            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://eps.v2.ghanapost.com.gh/api/",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => json_encode($options),
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/x-www-form-urlencoded"
                ),
            ));
            $response = curl_exec($curl);               
            $response = json_decode($response); 
            if(!empty($response->HAWB)){
                update_post_meta($order->id, 'ghanapost_hawb',$response->HAWB);
                wp_send_json_success(array('message'=>'Shipping Confirmed')); die();
                
            }
            if(empty($response->HAWB) && (strpos($response->TRANSACTION_STATUS,'Success') == false)){
                update_post_meta($order->id, 'ghanapost_confirmed','Yes'); 
                wp_send_json_success(array('message'=>'Shipping Confirmed but Tracking number not assigned.'));
            }
            wp_send_json_error(array('message'=>'Something went wrong Please try again later.'));
            
           
        }
        exit();
    }
    // The Wordpress Ajax PHP receiver (set data to a WC_Session variable)
    add_action( 'wp_ajax_destination_id', 'set_destination_id_to_wc_session' );
    add_action( 'wp_ajax_nopriv_destination_id', 'set_destination_id_to_wc_session' );
    function set_destination_id_to_wc_session() {
        $field_key = 'destination_id';
        if ( isset($_POST[$field_key])){
            WC()->session->set('billing_destination_id', $_POST[$field_key]);
            wp_send_json_success(array('meesage' => 'Biling Destination Set')); // always use die() or wp_die() at the end to avoird errors
        }
    }

    add_filter( 'woocommerce_billing_fields' , 'custom_override_default_address_fields',200);
    function custom_override_default_address_fields( $address_fields ) { 
        ghanapost_shipping_method();
        $Swoove_Shipping_Method = new Ghanapost_Shipping_Method();
        $address_fields['destination_id']['required'] = false;
        $address_fields['destination_id']['label'] = __('<span class="dgtl-span">Destination for GhanaPost Shipping</span><span class="details-span"><span>Select your destination to see available shipping options at checkout.</span><span>Purchases are delivered to the Post Office at the selected destination for you to pick up. </span> </span>');
        $address_fields['destination_id']['type'] = 'select';
        $address_fields['destination_id']['options'] = $Swoove_Shipping_Method->get_local_destinations();
        $address_fields['destination_id']['class'] = array ( 'form-row-wide','address-field' );
        //$this->print($address_fields); die();
        return $address_fields;
    }

    
    add_action('wp_footer',function(){ ?>
        <script>
            jQuery( function($){
                // if (typeof wc_checkout_params === 'undefined')
                //     return false;
                //     alert();
                // Function that send the Ajax request
                function sendAjaxRequestDestinationID( value ) {
                    $.ajax({
                        type: 'POST',
                        url: '<?= admin_url( 'admin-ajax.php' ) ?>',
                        data: {
                            'action': 'destination_id',
                            'destination_id': value             
                        },
                        success: function (result) {
                            $(document.body).trigger('update_checkout'); // Update checkout processes
                            console.log( result ); // For testing (output data sent)
                        }
                    });
                }

                // Billing fias code change & input events
                $(document.body).on( 'change', '[name="destination_id"]', function() { 
                    sendAjaxRequestDestinationID( $(this).val() );
                });

                // Shipping fias code change & input events
                // $(document.body).on( 'change input', 'input[name=shipping_digital_address]', function() {
                //     sendAjaxRequest( $(this).val(), 'shipping' );
                // });
                if(jQuery('#ship-to-different-address-checkbox').length > 0){
                    if(jQuery('#ship-to-different-address-checkbox').is(":checked")){
                        jQuery('#billing_digital_address_field,#destination_id_field').insertAfter('#shipping_postcode_field');
                    }else{
                        jQuery('#billing_digital_address_field,#destination_id_field').insertAfter('#billing_email_field');
                    } 
                }

                jQuery('#ship-to-different-address-checkbox').change(function(){
                    if(jQuery(this).is(":checked")){
                        jQuery('#billing_digital_address_field,#destination_id_field').insertAfter('#shipping_postcode_field');
                    }else{
                        jQuery('#billing_digital_address_field,#destination_id_field').insertAfter('#billing_email_field');
                    }

                });
            }); 
        </script><?php
    });

}