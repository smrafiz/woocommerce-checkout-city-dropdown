/**
 * Function to get US city data from local json file.
 */
function cx_get_json_city_data() {
    $json_url = file_get_contents( get_template_directory() . './us-cities.json' );
	$data = json_decode( $json_url );

	$cities = array();
	$output = array();

	foreach( $data as $key => $value ) {
		$cities[] = $value->city;
	}

	foreach( array_unique( $cities ) as $city ) {
		$output[strtolower( str_replace( " ", "-", $city ) )] = $city;
	}

	return $output;
}

/**
 * Change the checkout city field to a searchable dropdown field.
 */
add_filter( 'woocommerce_checkout_fields', 'cx_change_city_to_dropdown' );
function cx_change_city_to_dropdown( $fields ) {
	$city_args = wp_parse_args( array(
		'type'          => 'select',
		'options'       => cx_get_json_city_data(),
		'input_class'   => array(
			'wc-enhanced-select',
		)
	), $fields['shipping']['shipping_city'] );

	$fields['shipping']['shipping_city'] = $city_args;
	$fields['billing']['billing_city']   = $city_args; // Also change for billing field

	wc_enqueue_js( "
	jQuery( ':input.wc-enhanced-select' ).filter( ':not(.enhanced)' ).each( function() {
		var select2_args = { minimumResultsForSearch: 5 };
		jQuery( this ).select2( select2_args ).addClass( 'enhanced' );
	});" );

	return $fields;
}
