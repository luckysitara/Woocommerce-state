jQuery(document).ready(function($) {
    // Initialize Select2 for city select
    function initializeCitySelect() {
        $('.city-select select').select2({
            placeholder: "Select a city",
            allowClear: true
        });
    }

    initializeCitySelect();

    $('select[name="billing_state"]').change(function() {
        var selectedState = $(this).val();

        if (selectedState) {
            $.ajax({
                url: venom_shipping_params.ajaxurl,
                type: 'POST',
                data: {
                    action: 'get_city_options',
                    state: selectedState,
                    security: venom_shipping_params.security // Add nonce
                },
                success: function(response) {
                    if (response.success) {
                        var citySelect = $('select[name="billing_city"]');
                        citySelect.empty(); // Clear existing options

                        $.each(response.data, function(city, text) {
                            citySelect.append($('<option></option>')
                                .attr('value', city).text(text));
                        });

                        citySelect.trigger('change'); // Trigger the change event to refresh Select2
                        initializeCitySelect(); // Reinitialize Select2 after options are added
                    }
                }
            });
        }
    }).change(); // Trigger the change event on page load to populate cities if a state is pre-selected
});
