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
        // Hooks
        add_filter('woocommerce_states', array($this, 'custom_woocommerce_states'));
        add_filter('woocommerce_checkout_fields', array($this, 'custom_checkout_fields'));
        add_action('woocommerce_cart_calculate_fees', array($this, 'custom_shipping_fees'));
        add_action('admin_menu', array($this, 'custom_shipping_settings_menu'));
        add_action('admin_init', array($this, 'register_custom_shipping_settings'));

        // AJAX hooks
        add_action('wp_ajax_get_city_options', array($this, 'ajax_get_city_options'));
        add_action('wp_ajax_nopriv_get_city_options', array($this, 'ajax_get_city_options'));

        // Enqueue the script for AJAX functionality
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    public function enqueue_scripts() {
        wp_enqueue_script('venom-shipping-script', plugin_dir_url(__FILE__) . 'venom-shipping-script.js', array('jquery', 'select2'), '1.0', true);

        // Localize the script with the AJAX URL
        wp_localize_script('venom-shipping-script', 'venom_shipping_params', array(
            'ajaxurl' => admin_url('admin-ajax.php')
        ));

        // Enqueue Select2 CSS
        wp_enqueue_style('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
    }

    public function custom_woocommerce_states($states) {
        // Add custom states if needed
        return $states;
    }

    public function custom_checkout_fields($fields) {
        // Modify the city field to use AJAX for dynamic updates
        $fields['billing']['billing_city'] = array(
            'type'        => 'select',
            'label'       => __('City', 'woocommerce'),
            'required'    => true,
            'class'       => array('form-row-wide', 'city-select'),
            'options'     => array(
                '' => __('Select a state first', 'woocommerce'),
            ),
        );

        return $fields;
    }

    public function ajax_get_city_options() {
        check_ajax_referer('woocommerce-shipping', 'security'); // Add nonce check for security

        $selected_state = sanitize_text_field($_POST['state']);

        $cities = array(
            '' => __('Select a city', 'woocommerce')
        );

        $city_options = array(
            'LAG' => array(
                'Agege', 'Ajeromi-Ifelodun', 'Alimosho', 'Amuwo-Odofin', 'Apapa', 'Badagry', 'Epe',
                'Eti-Osa', 'Ibeju-Lekki', 'Ifako-Ijaiye', 'Ikeja', 'Ikorodu', 'Kosofe', 'Lagos Island',
                'Lagos Mainland', 'Mushin', 'Ojo', 'Oshodi-Isolo', 'Shomolu', 'Surulere'
            )
            // Add other states and their cities as needed
        );

        if (isset($city_options[$selected_state])) {
            foreach ($city_options[$selected_state] as $city) {
                $cities[$city] = $city;
            }
        } else {
            // Default to state capitals or any other logic
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
                $cities[$state_capitals[$selected_state]] = $state_capitals[$selected_state];
            }
        }

        wp_send_json_success($cities);
        wp_die(); // Terminate AJAX request
    }

    public function custom_shipping_fees() {
        global $woocommerce;

        $selected_state = WC()->customer->get_billing_state();
        $selected_city  = WC()->customer->get_billing_city();

        $shipping_prices = get_option('custom_shipping_prices', array());

        // Default shipping cost
        $shipping_cost = 3000;

        if (is_array($shipping_prices) && !empty($shipping_prices)) {
            if ($selected_state == 'LAG' && isset($shipping_prices['lagos'][$selected_city])) {
                $shipping_cost = $shipping_prices['lagos'][$selected_city];
            } elseif (isset($shipping_prices['states'][$selected_state])) {
                $shipping_cost = $shipping_prices['states'][$selected_state];
            }
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
            'states_shipping_prices',
            __('State Shipping Prices', 'woocommerce'),
            array($this, 'states_shipping_prices_field'),
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

    public function states_shipping_prices_field() {
        $shipping_prices = get_option('custom_shipping_prices', array());
        $state_prices = isset($shipping_prices['states']) ? $shipping_prices['states'] : array();

        // State capitals array
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

        foreach ($state_capitals as $state_code => $state_name) {
            $price = isset($state_prices[$state_code]) ? $state_prices[$state_code] : '';
            echo '<p>';
            echo '<label for="state_shipping_price_' . $state_code . '">' . $state_name . ' (' . $state_code . ')</label>';
            echo '<input type="number" id="state_shipping_price_' . $state_code . '" name="custom_shipping_prices[states][' . $state_code . ']" value="' . esc_attr($price) . '" step="0.01">';
            echo '</p>';
        }
    }

    public function lagos_shipping_prices_field() {
        $shipping_prices = get_option('custom_shipping_prices', array());
        $lagos_prices = isset($shipping_prices['lagos']) ? $shipping_prices['lagos'] : array();

        $lagos_cities = array(
            'Agege', 'Ajeromi-Ifelodun', 'Alimosho', 'Amuwo-Odofin', 'Apapa', 'Badagry', 'Epe',
            'Eti-Osa', 'Ibeju-Lekki', 'Ifako-Ijaiye', 'Ikeja', 'Ikorodu', 'Kosofe', 'Lagos Island',
            'Lagos Mainland', 'Mushin', 'Ojo', 'Oshodi-Isolo', 'Shomolu', 'Surulere'
        );

        foreach ($lagos_cities as $city) {
            $price = isset($lagos_prices[$city]) ? $lagos_prices[$city] : '';
            echo '<p>';
            echo '<label for="lagos_shipping_price_' . sanitize_title($city) . '">' . $city . '</label>';
            echo '<input type="number" id="lagos_shipping_price_' . sanitize_title($city) . '" name="custom_shipping_prices[lagos][' . $city . ']" value="' . esc_attr($price) . '" step="0.01">';
            echo '</p>';
        }
    }
}

new Custom_Shipping_Plugin();
