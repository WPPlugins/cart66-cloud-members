<?php

class CM_Cloud_Expiring_Products {


    /**
     * @var CC_Cloud_API_V1 Cart66 Cloud API class
     */
    public $cloud;

    /**
     * @var array An array of membership and subscription data
     */
    public $expiring_products;

    /**
     * @var CM_Cloud_Expiring_Products
     */
    public static $instance;

    public static function instance() {
        if ( empty( self::$instance ) ) {
            self::$instance = new CM_Cloud_Expiring_Products();
        }

        return self::$instance;
    }

    protected function __construct() {
        $this->cloud = new CC_Cloud_API_V1();
        $this->expiring_products = array();
    }

    /**
     * Return an array of the expiring products (memberships & subscriptions)
     *
     * Example of data returned
     *
     * Expiring products: Array
     * (
     *     [0] => Array
     *         (
     *             [id] => 51d10788dab9988fc5000031
     *             [name] => Premium Membership
     *             [sku] => membership
     *             [price] => 10.0
     *             [on_sale] =>
     *             [sale_price] =>
     *             [currency] => $
     *             [expires_after] => 365
     *         )
     *
     *     [1] => Array
     *         (
     *             [id] => 51d25dd0dab99830be0000b1
     *             [name] => E-commerce Training
     *             [sku] => training
     *             [price] => 10.0
     *             [on_sale] =>
     *             [sale_price] =>
     *             [currency] => $
     *             [expires_after] =>
     *         )
     * )
     *
     * @return array
     */
    public function load() {
        if( !empty( $this->expiring_products ) ) {
            $product_data = $this->expiring_products;
            // CM_Log::write('Reusing expiring products rather than loading them again from the cloud.');
        }
        else {
            $url = $this->cloud->api . 'products/expiring';
            $headers = array('Accept' => 'application/json');
            $response = wp_remote_get( $url, $this->cloud->basic_auth_header( $headers ) );
            CM_Log::write( "!! Loading expiring products from the cloud: $url" );
            // CM_Log::write( "Basic auth header: " . print_r( $this->cloud->basic_auth_header( $headers ), true ) );

            if( ! $this->cloud->response_ok( $response ) ) {
                CM_Log::write( "Loading expiring products failed: $url :: " . print_r($response, true));
                throw new CC_Exception_API("Failed to retrieve expiring products from Cart66 Cloud");
            }

            $product_data = json_decode( $response['body'], true );
            $this->expiring_products = $product_data;
            // CM_Log::write('Loaded expiring products from the cloud: ' . print_r($this->expiring_products, TRUE));
        }

        return $product_data;
    }


    public function expiring_product_list() {
        $products = $this->load();

        foreach($products as $p) {
            $memberships[ $p['name'] ] = $p['sku'];
        }

        return $memberships;
    }



}
