<?php

/*
 * Plugin Name: WooCommerce Pay Per Single Post
 * Plugin URI: http://github.con/janschumann/woocommerce-paypersinglepost
 * Description: Extends WooCommerce Pay Per Post plugin to allow purchasing single posts
 * Author: Jan Schumann
 * Version: 0.1.0
 * Author URI: http://github.con/janschumann
 */
if ( ! class_exists( 'Woocommerce_PayPerSinglePost' ) ) {

	class Woocommerce_PayPerSinglePost extends Woocommerce_PayPerPost {

		public static function init() {
			remove_filter( 'the_content', 'Woocommerce_PayPerPost::render' );
			add_filter( 'the_content', 'Woocommerce_PayPerSinglePost::render' );
			add_filter( 'woocommerce_add_cart_item_data', 'Woocommerce_PayPerSinglePost::add_cart_item_data', 10, 2);
			add_action( 'woocommerce_add_order_item_meta', 'Woocommerce_PayPerSinglePost::add_order_item_data', 10, 2);
		}

		public static function add_order_item_data($item_id, $values){
			if (isset($values['post_id'])) {
				wc_add_order_item_meta( $item_id, '_post_id', $values['post_id']);
			}
		}

		public static function add_cart_item_data($data, $product_id){
			$product = WooCommerce::instance()->product_factory->get_product($product_id);
			$attr = $product->get_attribute('post_id');
			if ($attr  === "post_id") {
				global $post;
				$data['post_id'] = $post->ID;
			}

			return $data;

		}

		public static function render( $content ) {
			$productID = self::get( self::METAKEY );

			if ( ! empty( $productID ) ) {
				if ( current_user_can( 'manage_options' ) || ( is_user_logged_in() && self::checkForProduct( $productID ) ) ) {
					return $content;
				} else {
					//Turn off comments for pages user doesn't have access to.
					add_filter( 'comments_open', function () {
						return false;
					} );

					$matches = array();
					if (preg_match('/(.*)<span.*"more-/', $content, $matches)) {
						// content contains a read more button. use this as teaser text
						$content = strip_tags($matches[1], '<img><a>');
					}
					else {
						$content = "";
					}

					return "<p>" . $content . "</p>" . str_replace( '{{product_id}}', $productID, get_option( 'wcppp_oops_content' ) );
				}
			} else {
				return $content;
			}
		}

		public static function checkForProduct( $id ) {
			$purchased    = false;
			$current_user = wp_get_current_user();
			$ids = str_replace( " ", '', $id );
			$ids = explode( ",", $ids );

			foreach ( $ids as $id ) {
				$purchased = wc_customer_bought_product( $current_user->user_email, $current_user->ID, $id );

				if ($purchased) {
					$product = WooCommerce::instance()->product_factory->get_product($id);
					$attr = $product->get_attribute('post_id');
					if ($attr  === "post_id") {
						global $wpdb, $post;

						$customer_data = array( $current_user->ID );

						if ( $current_user->ID ) {
							$user = get_user_by( 'id', $current_user->ID );

							if ( isset( $user->user_email ) ) {
								$customer_data[] = $user->user_email;
							}
						}

						if ( is_email( $current_user->user_email ) ) {
							$customer_data[] = $current_user->user_email;
						}

						$customer_data = array_map( 'esc_sql', array_filter( array_unique( $customer_data ) ) );

						if ( sizeof( $customer_data ) > 0 ) {
							$result = $wpdb->get_col("
								SELECT im.meta_value FROM {$wpdb->posts} AS p
								INNER JOIN {$wpdb->postmeta} AS pm ON p.ID = pm.post_id
								INNER JOIN {$wpdb->prefix}woocommerce_order_items AS i ON p.ID = i.order_id
								INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS im ON i.order_item_id = im.order_item_id
								WHERE p.post_status IN ( 'wc-completed', 'wc-processing' )
								AND pm.meta_key = '_customer_user' AND pm.meta_value = '$customer_data[0]'
								AND im.meta_key = '_post_id' AND im.meta_value = '{$post->ID}'
							");
							$purchased = in_array(absint($post->ID), $result);
						}
					}

					if ($purchased) {
						break;
					}
				}
			}

			return $purchased;
		}
	}

	Woocommerce_PayPerSinglePost::init();
}
