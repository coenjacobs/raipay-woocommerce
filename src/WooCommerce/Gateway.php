<?php

namespace CoenJacobs\RaiPay\WooCommerce;

use Exception;
use WC_Payment_Gateway;

class Gateway extends WC_Payment_Gateway {
	public function __construct() {
		$this->id                 = 'raipay';
		$this->has_fields         = false;
		$this->title              = __( 'RaiPay', 'raipay-woocommerce');
		$this->method_title       = __( 'RaiPay', 'raipay-woocommerce');
		$this->order_button_text  = __( 'Proceed to RaiPay', 'raipay-woocommerce' );
		$this->method_description = __( 'RaiPay sends customers to RaiPay to enter their payment information. RaiPay webhooks requires fsockopen/cURL support to update order statuses after payment.', 'raipay-woocommerce');
		$this->supports           = array(
			'products',
		);

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

		if ( ! raipay_woocommerce()->is_valid_for_use() ) {
			$this->enabled = 'no';
		}

		$this->init_form_fields();
		$this->init_settings();
	}

	public function init_form_fields() {
		$this->form_fields = array(
			'enabled'     => array(
				'title'       => __( 'Enable gateway', 'raipay-woocommerce' ),
				'label'       => __( 'Enable RaiPay payment gateway', 'raipay-woocommerce' ),
				'type'        => 'checkbox',
				'description' => '',
				'default'     => 'no',
			),
			'vendor_id'       => array(
				'title'       => __( 'Vendor ID', 'raipay-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Your RaiPay vendor ID.', 'woocommerce' ),
				'default'     => '',
				'desc_tip'    => true,
			),
			'title'       => array(
				'title'       => __( 'Title', 'raipay-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Payment method description that the customer will see on your checkout.', 'woocommerce' ),
				'default'     => __( 'RaiPay', 'raipay-woocommerce' ),
				'desc_tip'    => true,
			),
			'description' => array(
				'title'       => __( 'Description', 'raipay-woocommerce' ),
				'type'        => 'textarea',
				'description' => __( 'Payment method description that the customer will see on your website.', 'raipay-woocommerce' ),
				'default'     => __( 'Pay with with RaiBlocks via RaiPay.', 'raipay-woocommerce' ),
				'desc_tip'    => true,
			),
		);
	}

	public function admin_options() {
		if ( raipay_woocommerce()->is_valid_for_use() ) {
			parent::admin_options();
		} else {
			?>
			<div class="inline error"><p><strong><?php _e( 'Gateway disabled', 'raipay-woocommerce' ); ?></strong>: <?php _e( 'RaiPay does not support your store currency.', 'raipay-woocommerce' ); ?></p></div>
			<?php
		}
	}

	public function init_settings() {
		parent::init_settings();
		$this->enabled  = ! empty( $this->settings['enabled'] ) && 'yes' === $this->settings['enabled'] ? 'yes' : 'no';
	}

	public function process_admin_options() {
		$this->init_settings();

		$post_data = $this->get_post_data();

		foreach ( $this->get_form_fields() as $key => $field ) {
			if ( 'title' !== $this->get_field_type( $field ) ) {
				try {
					$this->settings[ $key ] = $this->get_field_value( $key, $field, $post_data );
				} catch ( Exception $e ) {
					$this->add_error( $e->getMessage() );
				}
			}
		}

		return update_option( $this->get_option_key(), apply_filters( 'woocommerce_settings_api_sanitized_fields_' . $this->id, $this->settings ) );
	}

	public function process_payment( $order_id ) {
		if ( ! raipay_woocommerce()->is_valid_for_use() ) {
			return [ 'result' => 'failure' ];
		}

		/** @var \WC_Order $order */
		$order = wc_get_order( $order_id );
		$total = $order->get_total();

		$response = wp_remote_post('https://api.raipay.io/payments/' . $this->get_option('vendor_id'), [
			'body' => [
				'amount'       => $total,
				'currency'     => 'xrb',
				'tag'          => 'order-'. $order_id,
				'redirect_url' => $this->get_return_url($order),
				'webhook'      => add_query_arg('raipayListener', 'raipayWebhook', get_home_url()),
			],
		]);

		if ( is_wp_error($response) ) {
			return [ 'result' => 'failure' ];
		}

		if ( $response['response']['code'] != 200 ) {
			return [ 'result' => 'failure' ];
		}

		$body = json_decode($response['body']);

		$order->add_order_note('RaiPay payment token: ' . $body->token);
		update_post_meta($order_id, 'raipay_woocommerce_payment_token', $body->token);

		return [
			'result'    => 'success',
			'redirect'  => 'https://raipay.io/checkout/' . $body->token,
		];
	}
}