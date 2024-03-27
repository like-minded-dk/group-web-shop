<?php
/**
 * Plugin Name: My Custom Fields for WooCommerce
 * Description: Adds custom fields to WooCommerce products.
 * Version: 1.0
 * Author: Your Name
 */

// Hook to add custom fields to the product edit page
add_action('woocommerce_product_options_general_product_data', 'add_custom_field_to_products');

function add_custom_field_to_products() {
    $custom_array = array(
        'minimum_quantity' => array( 
            'id' => 'minimum_quantity',
            'label' => 'minimum_quantity',
            'description' => 'minimum_quantity',
        ),
        'offer_expiry_date' => array( 
            'id' => 'offer_expiry_date',
            'label' => 'offer_expiry_date',
            'description' => 'offer_expiry_date',
        ),
        'ship_to_option' => array( 
            'id' => 'ship_to_option',
            'label' => 'ship_to_option',
            'description' => 'ship_to_option',
        ),
        'pay_ship_by' => array( 
            'id' => 'pay_ship_by',
            'label' => 'pay_ship_by',
            'description' => 'pay_ship_by',
        ),
        'offer_avaliability' => array( 
            'id' => 'offer_avaliability',
            'label' => 'offer_avaliability',
            'description' => 'offer_avaliability',
        ),
        'brand_description' => array( 
            'id' => 'brand_description',
            'label' => 'brand_description',
            'description' => 'brand_description',
        ),
    
    );
    foreach ($custom_array as $custom_field ) {
        woocommerce_wp_text_input($custom_field);
    }
}

// Hook to save custom field value
add_action('woocommerce_admin_process_product_object', 'save_custom_field_value');

function save_custom_field_value($product) {
    if (isset($_POST['_custom_product_text_field'])) {
        $product->update_meta_data('_custom_product_text_field', sanitize_text_field($_POST['_custom_product_text_field']));
        $product->save();
    }
}

// Other related code for displaying custom field value...
