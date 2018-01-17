<?php

namespace CoenJacobs\RaiPay\WooCommerce;

class Plugin
{
	/** @var Currency */
	public $currency;

	/** @var Listener */
	public $listener;

	public function __construct()
	{
		$this->currency = new Currency();

		if ( $this->is_valid_for_use() ) {
			$this->listener = new Listener();
		}
	}

	public function setup()
	{
		$this->currency->setup();

		if ( $this->is_valid_for_use() ) {
			$this->listener->setup();
		}

		add_filter('woocommerce_payment_gateways', [$this, 'addGateway']);
	}

	public function addGateway($array)
	{
		$array[] = Gateway::class;
		return $array;
	}

	public function is_valid_for_use() {
		$supported_currencies = array(
			'XRB',
			'AUD',
			'BGN',
			'BRL',
			'CAD',
			'CHF',
			'CNY',
			'CZK',
			'DKK',
			'EUR',
			'GBP',
			'HKD',
			'HRK',
			'HUF',
			'IDR',
			'ILS',
			'INR',
			'JPY',
			'KRW',
			'MXN',
			'MYR',
			'NOK',
			'NZD',
			'PHP',
			'PLN',
			'RON',
			'RUB',
			'SEK',
			'SGD',
			'THB',
			'TRY',
			'USD',
			'ZAR',
		);

		return in_array( get_woocommerce_currency(), apply_filters( 'raipay_woocommerce_supported_currencies', $supported_currencies ) );
	}
}
