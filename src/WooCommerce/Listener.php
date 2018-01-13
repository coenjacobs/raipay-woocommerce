<?php

namespace CoenJacobs\RaiPay\WooCommerce;

class Listener
{
	public function setup()
	{
		add_action( 'init', [$this, 'webhook_listener'] );
		add_action( 'raipay_woocommerce_webhook_listener', [$this, 'handle_webhook'] );
	}

	function webhook_listener() {
		if ( ! empty( $_GET['raipayListener'] ) && 'raipayWebhook' === $_GET['raipayListener'] ) {
			WC()->payment_gateways();
			do_action( 'raipay_woocommerce_webhook_listener' );
		}
	}

	public function handle_webhook() {
		$input = @file_get_contents("php://input");
		$data = json_decode($input);

		if ( $data->paid_at == null ) {
			$output = [
				'message' => 'Thanks for calling',
				'paid_at' => null,
			];

			echo json_encode($output);
			exit;
		}

		$order_id = intval(str_replace( 'order-', '', $data->tag));
		$order = wc_get_order($order_id);
		$token = get_post_meta($order_id, 'raipay_woocommerce_payment_token', true);

		if ( $token !== $data->token ) {
			$output = [
				'message' => 'Token mismatch',
			];

			echo json_encode($output);
			exit;
		}

		$order->payment_complete($data->token);

		$output = [
			'message' => 'Thanks for calling',
			'paid_at' => $data->paid_at,
		];

		echo json_encode($output);
		exit;
	}
}