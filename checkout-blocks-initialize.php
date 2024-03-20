<?php

use Automattic\WooCommerce\StoreApi\StoreApi;
use Automattic\WooCommerce\StoreApi\Schemas\ExtendSchema;
use Automattic\WooCommerce\StoreApi\Schemas\V1\CheckoutSchema;

add_action(
    'woocommerce_blocks_loaded',
    function() {
        require_once 'class-blocks-integration.php';
        add_action(
            'woocommerce_blocks_checkout_block_registration',
            function( $integration_registry ) {
                $integration_registry->register( new Blocks_Integration() );
            }
        );

        if ( function_exists( 'woocommerce_store_api_register_endpoint_data' ) ) {
            woocommerce_store_api_register_endpoint_data(
                array(
                    'endpoint'        => CheckoutSchema::IDENTIFIER,
                    'namespace'       => 'checkout-block-example',
                    'data_callback'   => 'cb_data_callback',
                    'schema_callback' => 'cb_schema_callback',
                    'schema_type'     => ARRAY_A,
                )
            );
        }

        if ( function_exists( 'woocommerce_store_api_register_update_callback' ) ) {
            woocommerce_store_api_register_update_callback(
                array(
                    'namespace' => 'checkout-block-example',
                    'callback'  => 'update_cart_fees',
                )
            );
        }

        add_action( 'woocommerce_cart_calculate_fees', 'ts_add_bacs_fee', 20, 1 );
    }
);


/**
 * Callback function to register endpoint data for blocks.
 *
 * @return array
 */
function cb_data_callback() {
	return array(
		'gift_message' => '',
	);
}

/**
 * Callback function to register schema for data.
 *
 * @return array
 */
function cb_schema_callback() {
	return array(
		'gift_message'  => array(
			'description' => __( 'Gift Message', 'checkout-block-example' ),
			'type'        => array( 'string', 'null' ),
			'readonly'    => true,
		),
	);
}

function update_cart_fees( $data ) {
	if ( isset( $data['shipping_method'] ) ) {
		WC()->session->set( 'cb_shipping_method', $data['shipping_method'] );
	}

    if ( isset( $data['payment_method'] ) ) {
		WC()->session->set( 'cb_payment_method', $data['payment_method'] );
	}

	WC()->cart->calculate_totals();
}

function ts_add_bacs_fee( $cart ) {
    if ( is_admin() && ! defined( 'DOING_AJAX' ) )
        return;
    ## ------ Your Settings (below) ------ ##
    $your_payment_id      = 'bacs'; // The payment method
    $your_shipping_method = 'local_pickup'; // The shipping method
    $fee_amount           = 19; // The fee amount
    ## ----------------------------------- ##
    $chosen_payment_method_id  = WC()->session->get( 'cb_payment_method' );
    // var_dump( WC()->session->get('cb_payment_method') );
    // exit();
    $chosen_shipping_method_id = WC()->session->get( 'cb_shipping_method' );
    $chosen_shipping_method    = explode( ':', $chosen_shipping_method_id )[0];
    if ( $chosen_shipping_method == $your_shipping_method && $chosen_payment_method_id == $your_payment_id ) {
        $fee_text = __( "Additional Fee", "woocommerce" );
        $cart->add_fee( $fee_text, $fee_amount, false );
    }
}