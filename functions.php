/**
 * Function to get US city data from local json file.
 */
function cx_get_json_city_data() {

	// Get json file path.
	$json_path = get_template_directory() . './us-cities.json';

	// If local file not found, bail.
	if( ! file_exists( $json_path ) ) {
		return;
	}

	// Get the city data.
    $json_url = file_get_contents( $json_path );
	$data = json_decode( $json_url );

	$cities = array();
	$output = array();

	// Build up city names array.
	foreach( $data as $key => $value ) {
		$cities[] = $value->city;
	}

	// Build up city name-value pair (associative array).
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

	// Get cities list.
	$cities = cx_get_json_city_data();

	// If city list is empty, no change will be made.
	if( empty( $cities ) ) {
		return $fields;
	}

	// Build up the drop-down field.
	$city_args = wp_parse_args( array(
		'type'          => 'select',
		'options'       => $cities,
		'input_class'   => array(
			'wc-enhanced-select',
		)
	), $fields['shipping']['shipping_city'] );

	// Change shipping & billing addresses.
	$fields['shipping']['shipping_city'] = $city_args;
	$fields['billing']['billing_city']   = $city_args;

	// Get the searchable drop-down.
	wc_enqueue_js( "
	jQuery( ':input.wc-enhanced-select' ).filter( ':not(.enhanced)' ).each( function() {
		var select2_args = { minimumResultsForSearch: 5 };
		jQuery( this ).select2( select2_args ).addClass( 'enhanced' );
	});" );

	return $fields;
}
