<?php

namespace CoenJacobs\RaiPay\WooCommerce;

class Currency
{
	public function setup() {
		add_filter( 'woocommerce_currencies', [$this, 'add_xrb_currency'] );
		add_filter('woocommerce_currency_symbol', [$this, 'add_xrb_symbol'], 10, 2);
	}

	public function add_xrb_currency( $currencies ) {
		$currencies['XRB'] = __( 'RaiBlocks', 'raipay-woocommerce' );
		return $currencies;
	}

	function add_xrb_symbol( $currency_symbol, $currency ) {
		if ( $currency === 'XRB' ) {
			$currency_symbol = 'XRB';
		}

		return $currency_symbol;
	}
}