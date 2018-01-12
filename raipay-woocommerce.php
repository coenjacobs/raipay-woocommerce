<?php

/**
 * Plugin Name: RaiPay for WooCommerce
 * Description: RaiPay payment gateway for WooCommerce.
 */

require('vendor/autoload.php');

function raipay_woocommerce()
{
	static $raipay_woocommerce;

	if ( empty($raipay_woocommerce) || get_class($raipay_woocommerce) !== \CoenJacobs\RaiPay\WooCommerce\Plugin::class) {
		$raipay_woocommerce = new \CoenJacobs\RaiPay\WooCommerce\Plugin();
		$raipay_woocommerce->setup();
	}

	return $raipay_woocommerce;
}

add_action('plugins_loaded', 'raipay_woocommerce');