<?php
/*
Plugin Name: Limit Product for Woocommerce
Plugin URI: https://github.com/Dhokito/Limit-product-for-Woocommerce
Description: Este plugin permite limitar la compra y solo permitir que los usuarios registrados y conectados compren el producto una vez.
Version: 1.0.1
Author: Mr Dhoko
Author URI: https://github.com/Dhokito
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt
*/

// Agregar campo personalizado para limitar la compra de productos
function my_add_custom_product_field() {
    woocommerce_wp_text_input( array(
        'id' => 'product_limit',
        'class' => 'wc_input_decimal',
        'label' => __( 'Límite de compra', 'woocommerce' ),
        'description' => __( 'Especifique el número máximo de veces que un usuario puede comprar este producto', 'woocommerce' ),
        'desc_tip' => true,
        'type' => 'number',
    ) );
}
add_action( 'woocommerce_product_options_general_product_data', 'my_add_custom_product_field' );

// Guardar campo personalizado de límite de compra
function my_save_custom_product_field( $product_id ) {
    $product_limit = isset( $_POST['product_limit'] ) ? absint( $_POST['product_limit'] ) : '';
    update_post_meta( $product_id, 'product_limit', $product_limit );
}
add_action( 'woocommerce_process_product_meta', 'my_save_custom_product_field' );

// Verificar si el usuario ha iniciado sesión antes de agregar un producto al carrito
function my_check_user_login() {
    if ( ! is_user_logged_in() ) {
        wc_add_notice( __( 'Debe iniciar sesión para comprar este producto', 'woocommerce' ), 'error' );
        return false;
    }
    return true;
}
add_filter( 'woocommerce_add_to_cart_validation', 'my_check_user_login', 10, 3 );

// Verificar si el usuario ya ha comprado este producto
function my_check_product_purchase( $passed, $product_id, $quantity ) {
    $product_limit = get_post_meta( $product_id, 'product_limit', true );
    if ( $product_limit ) {
        $customer_id = get_current_user_id();
        $order_count = wc_get_customer_order_count( $customer_id );
        if ( $order_count > 0 ) {
            $product_count = wc_customer_bought_product( get_current_user_email(), $customer_id, $product_id );
            if ( $product_count >= $product_limit ) {
                wc_add_notice( sprintf( __( 'Solo puede comprar %d unidad(es) de este producto', 'woocommerce' ), $product_limit ), 'error' );
                $passed = false;
            }
        }
    }
    return $passed;
}
add_filter( 'woocommerce_add_to_cart_validation', 'my_check_product_purchase', 10, 3 );
