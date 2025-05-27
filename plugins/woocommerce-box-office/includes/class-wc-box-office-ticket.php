<?php

/**
 * Class that represents a ticket.
 */
class WC_Box_Office_Ticket {

	/**
	 * Ticket's ID.
	 *
	 * @var int
	 */
	public $id;

	/**
	 * Post object of this ticket.
	 *
	 * @var WP_Post
	 */
	public $post;

	/**
	 * Ticket title.
	 *
	 * @var string
	 */
	public $title;

	/**
	 * Ticket status.
	 *
	 * @var string
	 */
	public $status;

	/**
	 * Ticket fields defined from product.
	 *
	 * @var array
	 */
	public $fields;

	/**
	 * Product ID.
	 *
	 * @var int
	 */
	public $product_id;

	/**
	 * Variation ID.
	 *
	 * @var int
	 */
	public $variation_id;

	/**
	 * Product in which this ticket is purchased from.
	 *
	 * @var WC_Product
	 */
	public $product;

	/**
	 * Order ID.
	 *
	 * @var int
	 */
	public $order_id;

	/**
	 * Order that creates this ticket.
	 *
	 * @var WC_Order
	 */
	public $order;

	/**
	 * Unique key associated to ticket.
	 *
	 * @var string
	 */
	public $uid = '';

	/**
	 * Ticket position/index relative to other tickets in same order item.
	 *
	 * @var int
	 */
	public $index = 0;

	/**
	 * Flag to indicate properties of instance has been populated or not.
	 *
	 * @var bool
	 */
	public $populated = false;

	/**
	 * Temporary order item data.
	 *
	 * @var array
	 */
	private $_order_item_data;

	/**
	 * Constructor.
	 *
	 * Instantiating this class may not populate the properties or create a post.
	 * If order data is passed and an ticket need to be created, invoke `create`
	 * method.
	 *
	 * @param mixed $ticket If array is passed, then it's assumed as order's data.
	 *                      Otherwise WP_Post or Post ID is expected.
	 */
	public function __construct( $ticket = false ) {
		if ( is_array( $ticket ) ) {
			$this->_order_item_data = $ticket;
		} else if ( is_int( $ticket ) || is_a( $ticket, 'WP_Post' ) ) {
			$this->populate( $ticket );
		}
	}

	/**
	 * Populate properties based on WP_Post.
	 *
	 * @param int|WP_Post $post  Post ID or object.
	 * @param bool        $force Force populate
	 *
	 * @return void
	 */
	public function populate( $ticket, $force = false ) {
		if ( ! $ticket ) {
			return;
		}

		$post = get_post( $ticket );
		if ( 'event_ticket' !== get_post_type( $post ) ) {
			return;
		}

		if ( $this->populated && ! $force ) {
			return;
		}

		$this->post         = $post;
		$this->id           = $post->ID;
		$this->title        = $post->post_title;
		$this->status       = $post->post_status;
		$this->order_id     = get_post_meta( $this->id, '_order', true );
		$this->order        = wc_get_order( $this->order_id );
		$this->product_id   = get_post_meta( $this->id, '_product', true );
		$this->product      = wc_get_product( $this->product_id );
		$this->variation_id = get_post_meta( $this->id, '_variation_id', true );
		$this->uid          = get_post_meta( $this->id, '_uid', true );
		$this->index        = absint( get_post_meta( $this->id, '_index', true ) );

		$ticket_fields = wc_box_office_get_product_ticket_fields( $this->product_id );
		foreach ( $ticket_fields as $field_key => $field ) {
			$this->fields[ $field_key ] = array_merge(
				$field,
				array(
					'value' => get_post_meta( $this->id, $field_key, true )
				)
			);
		}

		$this->populated = true;
	}

	/**
	 * Create new ticket from an order item data.
	 *
	 * @param string $status Ticket's status
	 *
	 * @return mixed
	 */
	public function create( $status = 'publish' ) {
		global $wpdb;

		if ( empty( $this->_order_item_data ) ) {
			return false;
		}

		$data = wp_parse_args(
			$this->_order_item_data,
			array(
				'uid'           => '',
				'index'         => 1,
				'product_id'    => '',
				'variation_id'  => '',
				'variations'    => array(),
				'fields'        => array(),
				'order_item_id' => '',
				'customer_id'   => is_user_logged_in() ? get_current_user_id() : 0,
			)
		);

		if ( ! $data['product_id'] ) {
			return false;
		}

		$product = wc_get_product( $data['product_id'] );

		$title = $this->maybe_create_title_variation( $product, $data );

		// Get order ID from order item.
		$order_id = $data['order_item_id'] ? wc_get_order_id_by_order_item_id( $data['order_item_id'] ) : 0;

		// Post parent ID (order ID in fact) is no more guaranteed to come from posts table since COT introduced.
		// However it will work as ususal as long as we do things with the order ID in WooCommerce way.
		$ticket_data = array(
			'post_type'    => 'event_ticket',
			'post_title'   => $title,
			'post_status'  => $status,
			'ping_status'  => 'closed',
			'post_excerpt' => '',
			'post_parent'  => $order_id,
		);

		$ticket_id = wp_insert_post( $ticket_data );
		if ( ! $ticket_id ) {
			return $ticket_id;
		}

		// Add ticket meta data.
		$ticket_fields = wc_box_office_get_product_ticket_fields( $product->get_id() );
		$content_array = array();
		foreach ( $ticket_fields as $key => $field ) {
			if ( isset( $data['fields'][ $key ] ) ) {
				$content_array[ $key ] = $data['fields'][ $key ];
				$this->save_ticket_field( $ticket_id, $key, $data['fields'][ $key ], $field['type'] );
			}
		}

		if ( ! empty( $content_array ) ) {
			wp_update_post( array(
				'ID'           => $ticket_id,
				'post_content' => maybe_serialize( $content_array ),
			) );
		}

		if ( isset( $data['fields']['pii_preference'] ) && 'opted-out' === $data['fields']['pii_preference'] ) {
			update_post_meta( $ticket_id, '_user_pii_preference', 'opted-out' );
		}

		update_post_meta( $ticket_id, '_token', $this->_generate_token( $ticket_id ) );

		update_post_meta( $ticket_id, '_product_id', $product->get_id() );

		if ( isset( $data['variation_id'] ) ) {
			update_post_meta( $ticket_id, '_variation_id', $data['variation_id'] );
		}

		update_post_meta( $ticket_id, '_customer_id', $data['customer_id'] );

		// @TODO(gedex) remove this and update all references to this meta to use _product_id instead.
		update_post_meta( $ticket_id, '_product', $product->get_id() );

		// @TODO(gedex) remove this and update all references to this meta to use post_parent instead.
		update_post_meta( $ticket_id, '_order', $order_id );

		update_post_meta( $ticket_id, '_ticket_order_item_id', $data['order_item_id'] );

		// @TODO(gedex) remove this and update all references to this meta to use _customer_id instead.
		update_post_meta( $ticket_id, '_user', $data['customer_id'] );

		update_post_meta( $ticket_id, '_uid', $data['uid'] );
		update_post_meta( $ticket_id, '_index', absint( $data['index'] ) );

		do_action( 'woocommerce_box_office_event_ticket_created', $ticket_id );

		$this->id = $ticket_id;

		// Force populate after creation.
		$this->populate( $this->id, true );
	}

	/**
	 * Update the ticket.
	 *
	 * @throws Exception
	 *
	 * @param array $data Ticket fields data for the update
	 *
	 * @return void
	 */
	public function update( $data ) {
		if ( ! $this->populated ) {
			throw new Exception( __( 'Unknown ticket to update', 'woocommerce-box-office' ) );
		}

		$content_array = array();
		foreach ( $this->fields as $key => $field ) {
			if ( isset( $data[ $key ] ) ) {
				// Build serialized fields as post_content so it's searchable.
				$content_array[ $key ] = $data[ $key ];

				$this->save_ticket_field( $this->id, $key, $data[ $key ], $field['type'] );
			}
		}

		$pii_preference = isset( $data['pii_preference'] ) ? 'opted-out' : 'opted-in';

		update_post_meta( $this->id, '_user_pii_preference', $pii_preference );

		if ( ! empty( $content_array ) ) {
			$post_data = array(
				'ID'           => $this->id,
				'post_content' => maybe_serialize( $content_array ),
			);
			wp_update_post( $post_data );
		}

		// Force populate with the new update.
		$this->populate( $this->id, true );
	}

	/**
	 * Get printed content with label variables replaced with real values.
	 *
	 * @return string Printed ticket content
	 */
	public function get_printed_content() {
		// Load print content.
		$ticket_content = get_post_meta( $this->product_id, '_ticket_content', true );

		// Replace content with ticket field's values.
		foreach ( $this->fields as $field_key => $field ) {
			$val = $field['value'];
			if ( is_array( $val ) ) {
				$val = implode( ', ', $val );
			}
			$ticket_content = str_replace( '{' . $field['label'] . '}', esc_html( $val ), $ticket_content );
		}

		$post = get_post( $this->product_id );

		// Get the product/variation price and SKU.
		$price = $this->product->get_price_html();
		$sku   = $this->product->get_sku();
		if ( $this->variation_id ) {
			$variation = wc_get_product( $this->variation_id );
			$price     = $variation->get_price_html();
			$sku       = $variation->get_sku();
		}

		// Replace dynamic variables.
		$post_vars = array(
			'{post_title}'    => $this->title,
			'{product_price}' => $price ? '<b>Price:</b> ' . $price : '',
			'{product_sku}'   => $sku ? '<b>SKU:</b> ' . $sku : '',
			'{post_content}'  => $post->post_content,
			'{ticket_id}'     => $this->id,
		);
		foreach ( $post_vars as $var => $value ) {
			$ticket_content = str_replace( $var, $value, $ticket_content );
		}

		/**
		 * Display ticket content with paragraph formatting and shortcodes
		 * Follows same steps as the_content filters
		 */
		$ticket_content = wpautop( $ticket_content );
		$ticket_content = shortcode_unautop( $ticket_content );
		$ticket_content = do_shortcode( $ticket_content );

		/**
		 * Provides an opportunity to modify the ticket content.
		 *
		 * @param string $ticket_content     Ticket HTML.
		 * @param string $raw_ticket_content Raw ticket HTML.
		 */
		return apply_filters( 'woocommerce_box_office_get_printed_ticket_content', wp_kses_post( $ticket_content ), $ticket_content );
	}

	/**
	 * Get ticket fields by its type.
	 *
	 * @param string
	 */
	public function get_ticket_fields_by_type( $type ) {
		if ( ! $this->populated ) {
			return null;
		}

		$filtered_fields = array();
		foreach ( $this->fields as $key => $field ) {
			if ( ! empty( $field['type'] ) && $type === $field['type'] ) {
				$filtered_fields[ $key ] = $field;
			}
		}

		return $filtered_fields;
	}

	/**
	 * Maybe create title with its formatted variations.
	 *
	 * @param WC_Product $product Product object.
	 * @param array      $data    Ticket data.
	 *
	 * @return string Title that might be formatted with variations
	 */
	private function maybe_create_title_variation( $product, $data ) {
		$title = $product->get_title();

		// If this is a variation then add the variation attributes to the ticket title.
		if ( ! empty( $data['variations'] ) ) {
			$variation_list = array();

			foreach ( $data['variations'] as $name => $value ) {
				$name = str_replace( 'attribute_', '', $name );

				if ( taxonomy_exists( $name ) ) {
					$term = get_term_by( 'slug', $value, $name );
					if ( ! is_wp_error( $term ) && $term && null !== $term->name && '' !== $term->name ) {
						$value = $term->name;
					}
				}
				$variation_list[] = wc_attribute_label( $name, $product ) . ': ' . rawurldecode( $value );
			}

			$variation_list = implode( ', ', $variation_list );

			$title = sprintf(
				__( '%1$s (%2$s)', 'woocommerce-box-office' ),
				$title,
				$variation_list
			);
		}

		return apply_filters( 'woocommerce_box_office_create_ticket_title', $title );
	}

	/**
	 * Save ticket field.
	 *
	 * @param integer $ticket_id Ticket ID
	 * @param string  $key       Field key
	 * @param string  $value     Submitted value
	 * @param string  $type      Field type
	 *
	 * @return void
	 */
	public function save_ticket_field( $ticket_id, $key, $value, $type = 'text' ) {
		if ( ! $ticket_id || ! $key ) {
			return;
		}

		// Validate field according to type.
		if ( $type ) {
			switch ( $type ) {
				case 'email':
					$value = sanitize_email( $value );
					break;

				case 'twitter':
					$value = str_replace( 'http://', '', $value );
					$value = str_replace( 'https://', '', $value );
					$value = str_replace( 'www.', '', $value );
					$value = str_replace( 'twitter.com', '', $value );
					$value = trim( $value, '/' );
					$value = trim( $value, '.' );
					$value = str_replace( '@', '', $value );
					$value = sanitize_text_field( $value );
					break;

				case 'url':
					$value = esc_url( $value );
					break;

				case 'checkbox':
					$value = array_map( 'sanitize_text_field', $value );
					break;

				case 'text':
				default:
					$value = sanitize_text_field( $value );
			}
		}

		update_post_meta( $ticket_id, $key, $value );
	}

	/**
	 * Set ticket to an order and order item by ID.
	 *
	 * @param int $order_id      Order ID
	 * @param int $order_item_id Order item ID
	 *
	 * @return void
	 */
	public function set_order_item_id( $order_id, $order_item_id ) {
		$this->order_id = $order_id;
		wp_update_post( array( 'ID' => $this->id, 'post_parent' => $this->order_id ) );
		update_post_meta( $this->id, '_ticket_order_item_id', $order_item_id );
	}

	/**
	 * Generate token from a given ticket ID. Copied from wp_hash_password.
	 *
	 * @param int $ticket_id Ticket ID
	 */
	private function _generate_token( $ticket_id ) {
		global $wp_hasher;

		if ( empty($wp_hasher) ) {
			require_once( ABSPATH . WPINC . '/class-phpass.php');
			// By default, use the portable hash from phpass
			$wp_hasher = new PasswordHash(8, true);
		}

		return md5( $wp_hasher->HashPassword( 'woocommerce_box_office_ticket_' . $ticket_id . '_token' ) );
	}
}
