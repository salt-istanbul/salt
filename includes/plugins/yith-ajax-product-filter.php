<?php

// YITH remove filters from filters before
//remove_action('yith_wcan_before_preset_filters', 'active_filters_list');



//remove_action('yith_wcan_after_preset_filters', 'active_filters_list');

do_action( 'yith_wcan_before_query', function($query){
		      $query->get( 'tax_query' );
				$tax_query[] = array(
					'taxonomy' => 'product_cat',
					'field' => 'term_id',
					'terms' => array(get_option( 'default_product_cat' )), // Don't display products in the clothing category on the shop page.
					'operator' => 'NOT IN'
				);
				$query->set( 'tax_query', $tax_query );
				return $query;
});