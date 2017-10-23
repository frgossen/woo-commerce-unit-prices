<?php
/* Plugin Name: WooCommerce Unit Prices
 * Description: Adds unit prices to all product price statements
 * Version: 1.0
 * Author: Frederik Gossen 
 * Text Domain: woo-unit-prices
 * Domain Path: /languages
 */

defined('ABSPATH') or die('No script kiddies please!');

/* load translations */
add_action('init', 'woo_unit_prices_load_translation');
function woo_unit_prices_load_translation() {
	load_plugin_textdomain('woo-unit-prices', false, 'woo-commerce-unit-prices/languages/');
}

/* add custom product fields to backend */
add_action('woocommerce_product_options_general_product_data', 'woo_unit_prices_render_product_tab');
function woo_unit_prices_render_product_tab() {
	global $woocommerce, $post;
	echo '<div class="options_group">';
	woocommerce_wp_text_input(array( 
		'id'    => 'unit_price_measure', 
		'label' => __('Measure', 'woo-unit-prices'), 
		'type'              => 'number',
		'custom_attributes' => array(
			'min'  => '0',
			'step' => '0.01')));
	woocommerce_wp_select(array( 
		'id'      => 'unit_price_unit', 
		'label'   => __('Unit', 'woo-unit-prices'), 
		'options' => array(
			'litre'        => __('Litre (l)', 'woo-unit-prices'),
			'metre'        => __('Metre (m)', 'woo-unit-prices'),
			'centimetre'   => __('Centimetre (cm)', 'woo-unit-prices'),
			'millimetre'   => __('Millimetre (mm)', 'woo-unit-prices'),
			'gram'         => __('Gram (g)', 'woo-unit-prices'),
			'hundredgrams' => __('100 Grams (100g)', 'woo-unit-prices'),
			'kilogram'     => __('Kilogram (kg)', 'woo-unit-prices'))));
	echo '</div>';
}
add_action('woocommerce_process_product_meta', 'woo_unit_prices_save');
function woo_unit_prices_save($post_id) {
	if (isset($_POST['unit_price_measure'])) 
		update_post_meta($post_id, 'unit_price_measure', round(esc_attr($_POST['unit_price_measure']), 2));
	if (isset($_POST['unit_price_unit'])) 
		update_post_meta($post_id, 'unit_price_unit', esc_attr($_POST['unit_price_unit']));
}

/* display unit prices in product presentation */
add_filter('woocommerce_get_price_html', 'woo_unit_prices_get_price_html_filter', 10, 2);
function woo_unit_prices_get_price_html_filter($price_html, $product) {
	$price_html .= '<small>' . woo_unit_prices_format($product) . '</small>';
	return $price_html;
}

/* display unit prices in cart */
add_filter('woocommerce_cart_item_price', 'woo_unit_prices_cart_item_price_filter', 10, 2);
function woo_unit_prices_cart_item_price_filter($price, $cart_item, $cart_item_key) {
	$product = $cart_item['data'];
	$price .= ' <small>' . woo_unit_prices_format($product) . '</small>';
	return $price;
}

/* unit price formatting */
function woo_unit_prices_format($product) {
	$product_id = $product->get_id();
	$measure = get_post_meta($product_id, 'unit_price_measure', true);
	$out = '';
	if (!empty($measure)) {
		$raw_price = $product->get_price();
		$unit_price = $raw_price / $measure;
		$unit = get_post_meta($product_id, 'unit_price_unit', true);
		$out .= ' (' . wc_price($unit_price) . '&nbsp;/&nbsp;' . format_unit($unit) . ')';
	}
	return $out;
}
function format_unit($unit) {
	switch($unit) {
		case 'litre':
		$unit = __('l', 'woo-unit-prices');
		break;
		case 'metre':
		$unit = __('m', 'woo-unit-prices');
		break;
		case 'centimetre':
		$unit = __('cm', 'woo-unit-prices');
		break;
		case 'millimetre':
		$unit = __('mm', 'woo-unit-prices');
		break;
		case 'gram':
		$unit = __('g', 'woo-unit-prices');
		break;
		case 'hundredgrams':
		$unit = __('100g', 'woo-unit-prices');
		break;
		case 'kilogram':
		$unit = __('kg', 'woo-unit-prices');
		break;
	}
	return $unit;
}
?>
