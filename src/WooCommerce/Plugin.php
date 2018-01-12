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
		return in_array( get_woocommerce_currency(), apply_filters( 'raipay_woocommerce_supported_currencies', array( 'USD', 'XRB' ) ) );
	}
}