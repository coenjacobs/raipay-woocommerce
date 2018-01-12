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
		$this->listener = new Listener();
	}

	public function setup()
	{
		$this->currency->setup();
		$this->listener->setup();

		add_filter('woocommerce_payment_gateways', [$this, 'addGateway']);
	}

	public function addGateway($array)
	{
		$array[] = Gateway::class;
		return $array;
	}
}