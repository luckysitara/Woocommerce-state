<?php
/*
Plugin Name: Venom Shipping Plugin
Description: Venom shipping plugin to handle state and city-based shipping prices.
Version: 2.1
Author: Bughacker
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Custom_Shipping_Plugin {

    public function __construct() {
        add_filter('woocommerce_states', array($this, 'custom_woocommerce_states'));
        add_filter('woocommerce_checkout_fields', array($this, 'custom_checkout_fields'));
        add_action('woocommerce_cart_calculate_fees', array($this, 'custom_shipping_fees'));
        add_action('admin_menu', array($this, 'custom_shipping_settings_menu'));
        add_action('admin_init', array($this, 'register_custom_shipping_settings'));
    }

    public function custom_woocommerce_states($states) {
        // Add custom states if needed
        return $states;
    }

    public function custom_checkout_fields($fields) {
        // Modify the city field based on the selected state
        $fields['billing']['billing_city'] = array(
            'type' => 'select',
            'label' => __('City', 'woocommerce'),
            'required' => true,
            'class' => array('form-row-wide'),
            'options' => $this->get_city_options()
        );
        return $fields;
    }

    public function get_city_options() {
        // Get selected state
        $selected_state = WC()->customer->get_billing_state();

        // Define cities based on the selected state
        $cities = array(
            '' => __('Select a city', 'woocommerce')
        );

        $lagos_cities = array(
            'Agege', 'Ajeromi-Ifelodun', 'Alimosho', 'Amuwo-Odofin', 'Apapa', 'Badagry', 'Epe',
            'Eti-Osa', 'Ibeju-Lekki', 'Ifako-Ijaiye', 'Ikeja', 'Ikorodu', 'Kosofe', 'Lagos Island',
            'Lagos Mainland', 'Mushin', 'Ojo', 'Oshodi-Isolo', 'Shomolu', 'Surulere'
        );

        if ($selected_state == 'LAG') {
            foreach ($lagos_cities as $city) {
                $cities[$city] = __($city, 'woocommerce');
            }
        } else {
            // State capitals for other states
            $state_capitals = array(
                'AB' => 'Umuahia',
                'AD' => 'Yola',
                'AK' => 'Uyo',
                'AN' => 'Awka',
                'BA' => 'Bauchi',
                'BY' => 'Yenagoa',
                'BE' => 'Makurdi',
                'BO' => 'Maiduguri',
                'CR' => 'Calabar',
                'DE' => 'Asaba',
                'EB' => 'Abakaliki',
                'ED' => 'Benin City',
                'EK' => 'Ado Ekiti',
                'EN' => 'Enugu',
                'GO' => 'Gombe',
                'IM' => 'Owerri',
                'JI' => 'Dutse',
                'KD' => 'Kaduna',
                'KN' => 'Kano',
                'KT' => 'Katsina',
                'KE' => 'Birnin Kebbi',
                'KO' => 'Lokoja',
                'KW' => 'Ilorin',
                'LA' => 'Ikeja',
                'NA' => 'Lafia',
                'NI' => 'Minna',
                'OG' => 'Abeokuta',
                'ON' => 'Akure',
                'OS' => 'Oshogbo',
                'OY' => 'Ibadan',
                'PL' => 'Jos',
                'RI' => 'Port Harcourt',
                'SO' => 'Sokoto',
                'TA' => 'Jalingo',
                'YO' => 'Damaturu',
                'ZA' => 'Gusau'
            );

            if (isset($state_capitals[$selected_state])) {
                $cities[$state_capitals[$selected_state]] = __($state_capitals[$selected_state], 'woocommerce');
            }
        }

        return $cities;
    }

    public function custom_shipping_fees() {
        global $woocommerce;

        $selected_state = WC()->customer->get_billing_state();
        $selected_city = WC()->customer->get_billing_city();

        // Define shipping prices
        $shipping_prices = get_option('custom_shipping_prices', array());
        $default_price = isset($shipping_prices['default']) ? $shipping_prices['default'] : 0;
        $lagos_cities_prices = isset($shipping_prices['lagos']) ? $shipping_prices['lagos'] : array();

        $shipping_cost = $default_price;

        if ($selected_state == 'LAG' && isset($lagos_cities_prices[$selected_city])) {
            $shipping_cost = $lagos_cities_prices[$selected_city];
        } elseif (isset($shipping_prices[$selected_state])) {
            $shipping_cost = $shipping_prices[$selected_state];
        }

        $woocommerce->cart->add_fee(__('Shipping', 'woocommerce'), $shipping_cost);
    }

    public function custom_shipping_settings_menu() {
        add_options_page(
            'Custom Shipping Settings',
            'Custom Shipping Settings',
            'manage_options',
            'custom-shipping-settings',
            array($this, 'custom_shipping_settings_page')
        );
    }

    public function custom_shipping_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Custom Shipping Settings', 'woocommerce'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('custom_shipping_settings_group');
                do_settings_sections('custom-shipping-settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function register_custom_shipping_settings() {
        register_setting('custom_shipping_settings_group', 'custom_shipping_prices');

        add_settings_section(
            'custom_shipping_settings_section',
            __('Shipping Prices', 'woocommerce'),
            null,
            'custom-shipping-settings'
        );

        add_settings_field(
            'default_shipping_price',
            __('Default Shipping Price', 'woocommerce'),
            array($this, 'default_shipping_price_field'),
            'custom-shipping-settings',
            'custom_shipping_settings_section'
        );

        add_settings_field(
            'lagos_shipping_prices',
            __('Lagos City Shipping Prices', 'woocommerce'),
            array($this, 'lagos_shipping_prices_field'),
            'custom-shipping-settings',
            'custom_shipping_settings_section'
        );
    }

    public function default_shipping_price_field() {
        $shipping_prices = get_option('custom_shipping_prices', array());
        $default_price = isset($shipping_prices['default']) ? $shipping_prices['default'] : '';
        echo '<input type="number" name="custom_shipping_prices[default]" value="' . esc_attr($default_price) . '" class="regular-text">';
    }

    public function lagos_shipping_prices_field() {
        $shipping_prices = get_option('custom_shipping_prices', array());
        $lagos_prices = isset($shipping_prices['lagos']) ? $shipping_prices['lagos'] : array();

        $cities = array(
            'Agege', 'Ajeromi-Ifelodun', 'Alimosho', 'Amuwo-Odofin', 'Apapa', 'Badagry', 'Epe',
            'Eti-Osa', 'Ibeju-Lekki', 'Ifako-Ijaiye', 'Ikeja', 'Ikorodu', 'Kosofe', 'Lagos Island',
            'Lagos Mainland', 'Mushin', 'Ojo', 'Oshodi-Isolo', 'Shomolu', 'Surulere'
        );

        foreach ($cities as $city) {
            $price = isset($lagos_prices[$city]) ? $lagos_prices[$city] : '';
            echo '<label>' . $city . ':</label> <input type="number" name="custom_shipping_prices[lagos][' . $city . ']" value="' . esc_attr($price) . '" class="regular-text"><br>';
        }
    }
}

new Custom_Shipping_Plugin();
