<?php
/**
 * Plugin Name: City Price
 * Description: Adds custom states and cities for WooCommerce in Nigeria, with admin settings for city prices.
 * Version: 1.0
 * Author: Bughacker
 */

if (!defined('ABSPATH')) {
    exit; 
}

add_filter('woocommerce_states', 'custom_woocommerce_states');
function custom_woocommerce_states($states) {
    include_once 'states/NG.php';
    return $states;
}

function include_places() {
    include_once 'places/NG.php';
}
include_places();

global $places;

add_filter('woocommerce_default_address_fields', 'custom_override_default_address_fields');
function custom_override_default_address_fields($address_fields) {
    $address_fields['state']['required'] = true;
    $address_fields['city']['required'] = true;
    return $address_fields;
}

add_action('wp_enqueue_scripts', 'wc_enqueue_script');
function wc_enqueue_script() {
    wp_enqueue_script('wc-state-city', plugin_dir_url(__FILE__) . 'js/place-select.js', array('jquery', 'wc-country-select'), '1.0', true);
    wp_localize_script('wc-state-city', 'wc_cities', get_cities());
}

function get_cities() {
    global $places;
    return $places;
}

// Plugin activation hook
register_activation_hook(__FILE__, 'wc_city_prices_plugin_activation');
function wc_city_prices_plugin_activation() {
    global $places;

    foreach ($places['NG'] as $state_code => $cities) {
        foreach ($cities as $city) {
            $price_key = 'wc_city_price_' . $state_code . '_' . sanitize_title($city);
            if (get_option($price_key) === false) {
                add_option($price_key, '');
            }
        }
    }
}

// Add admin menu for City Prices
add_action('admin_menu', 'wc_city_prices_admin_menu');
function wc_city_prices_admin_menu() {
    add_menu_page(
        'City Prices',
        'City Prices',
        'manage_options',
        'wc-city-prices',
        'wc_city_prices_settings_page',
        'dashicons-admin-generic',
        56
    );
}

function wc_city_prices_settings_page() {
    global $places;

    // Check if the form is submitted
    if (isset($_POST['wc_city_prices_submit'])) {
        // Save the prices
        foreach ($places['NG'] as $state_code => $cities) {
            foreach ($cities as $city) {
                $price_key = 'wc_city_price_' . $state_code . '_' . sanitize_title($city);
                update_option($price_key, sanitize_text_field($_POST[$price_key]));
            }
        }

        echo '<div class="updated"><p>Prices saved successfully.</p></div>';
    }

    ?>
    <div class="wrap">
        <h1>City Prices</h1>
        <form method="post" action="">
            <?php
            foreach ($places['NG'] as $state_code => $cities) {
                echo '<h2>' . $state_code . '</h2>';
                foreach ($cities as $city) {
                    $price_key = 'wc_city_price_' . $state_code . '_' . sanitize_title($city);
                    $price = get_option($price_key, '');
                    ?>
                    <p>
                        <label for="<?php echo esc_attr($price_key); ?>"><?php echo esc_html($city); ?>:</label>
                        <input type="text" name="<?php echo esc_attr($price_key); ?>" value="<?php echo esc_attr($price); ?>" />
                    </p>
                    <?php
                }
            }
            ?>
            <p><input type="submit" name="wc_city_prices_submit" value="Save Prices" class="button button-primary" /></p>
        </form>
    </div>
    <?php
}

// Enqueue city price script and localize city prices
add_action('wp_enqueue_scripts', 'wc_enqueue_city_price_script');
function wc_enqueue_city_price_script() {
    wp_enqueue_script('wc-city-price', plugin_dir_url(__FILE__) . 'js/city-price.js', array('jquery'), '1.0', true);

    // Localize the script with city prices
    $city_prices = array();
    global $places;
    foreach ($places['NG'] as $state_code => $cities) {
        foreach ($cities as $city) {
            $price_key = 'wc_city_price_' . $state_code . '_' . sanitize_title($city);
            $price = get_option($price_key, '');
            if (!empty($price)) {
                $city_prices[$state_code][$city] = $price;
            }
        }
    }
    wp_localize_script('wc-city-price', 'wc_city_prices', $city_prices);
}

add_action('woocommerce_review_order_after_shipping', 'display_city_price_checkout');
function display_city_price_checkout() {
    ?>
    <div id="city-price-display-checkout"></div>
    <script type="text/javascript">
    jQuery(function($) {
        $('#billing_city').on('change', function() {
            var city = $(this).val();
            var state = $('#billing_state').val();
            if (city && wc_city_prices[state] && wc_city_prices[state][city]) {
                var price = wc_city_prices[state][city];
                $('#city-price-display-checkout').html('City Price: ' + price);
            } else {
                $('#city-price-display-checkout').html('');
            }
        });
    });
    </script>
    <?php
}
?>
