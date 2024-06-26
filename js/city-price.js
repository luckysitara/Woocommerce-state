jQuery(function($) {
    $('body').on('state_changing', function(e, country, state, $container) {
        if (country === 'NG' && wc_city_prices[state]) {
            var $citybox = $container.find('#billing_city, #shipping_city, #calc_shipping_city');
            $citybox.on('change', function() {
                var city = $(this).val();
                if (city && wc_city_prices[state][city]) {
                    var price = wc_city_prices[state][city];
                    $('#city-price-display').remove(); // Remove existing price display if any
                    $citybox.after('<div id="city-price-display">City Price: ' + price + '</div>');
                } else {
                    $('#city-price-display').remove();
                }
            });
        }
    });
});
