<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Automattic\WooCommerce\Admin\Features\Navigation\Menu;
use Automattic\WooCommerce\Admin\Features\Navigation\Screen;

class WC_Box_Office_Ticket_Admin {

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Add extension pages to WooCommerce screens.
		add_filter( 'woocommerce_screen_ids', array( $this, 'screen_ids' ), 10, 1 );

		// Filterings.
		add_action( 'restrict_manage_posts', array( $this, 'filter_ticket_product_id' ) );
		add_action( 'parse_query', array( $this, 'filter_ticket_product_id_query' ) );

		// Add metabox to ticket and ticket's email edit screens.
		add_action( 'add_meta_boxes_event_ticket', array( $this, 'event_ticket_meta_box' ), 10, 1 );
		add_action( 'add_meta_boxes_event_ticket_email', array( $this, 'event_ticket_email_meta_box' ), 10, 1 );
		add_action( 'save_post', array( $this, 'event_ticket_meta_box_save' ), 10, 1 );

		// Update order item meta for this ticket when ticket is updated.
		add_action( 'save_post', array( $this, 'update_order_item_meta' ), 9, 3 );

		// Manage admin columns for tickets.
		add_filter( 'manage_event_ticket_posts_columns', array( $this, 'manage_ticket_columns' ), 11, 1 );
		add_action( 'manage_event_ticket_posts_custom_column', array( $this, 'display_ticket_columns' ), 10, 2 );

		// Set row actions.
		add_filter( 'post_row_actions', array( $this, 'row_actions' ), 10, 2 );

		// Bulk actions.
		add_filter( 'bulk_actions-edit-event_ticket', array( $this, 'modify_bulk_actions' ) );
		add_filter( 'handle_bulk_actions-edit-event_ticket', array( $this, 'handle_bulk_actions' ), 10, 3 );

		// Manage admin columns for ticket emails.
		add_filter( 'manage_event_ticket_email_posts_columns', array( $this, 'manage_ticket_email_columns' ), 11, 1 );

		// Prevent add ticket page & ticket email list table from being accessible.
		add_action( 'admin_menu', array( $this, 'hide_ticket_add' ) );
		add_action( 'init', array( $this, 'block_admin_pages' ) );

		// Add create ticket page.
		add_action( 'admin_menu', array( $this, 'add_create_ticket_page' ) );

		// Register menu items in the new WooCommerce navigation.
		add_action( 'admin_menu', array( $this, 'register_navigation_items' ) );
	}

	/**
	 * Add Box Office pages to WooCommerce screen IDs.
	 *
	 * @param  array $screen_ids Existing IDs
	 * @return array             Modified IDs
	 */
	public function screen_ids( $screen_ids = array() ) {
		$screen_ids[] = 'edit-event_ticket';
		$screen_ids[] = 'event_ticket';
		$screen_ids[] = 'event_ticket_email';
		$screen_ids[] = 'edit-event_ticket_email';
		$screen_ids[] = 'event_ticket_page_ticket_tools';
		$screen_ids[] = 'event_ticket_page_create_ticket';

		return $screen_ids;
	}

	/**
	 * Add ticket product_id filter.
	 *
	 * @return void
	 */
	public function filter_ticket_product_id() {
		global $typenow, $wp_query;

		if ( 'event_ticket' !== $typenow ) {
			return;
		}

		$output  = '';
		$tickets = wc_box_office_get_all_ticket_products();
		if ( $tickets ) {
			// Filter by ticket product.
			$current_id     = ! empty( $_GET['filter_ticket_product_id'] ) ? absint( $_GET['filter_ticket_product_id'] ) : '';
			$checkin_status = isset( $_GET['filter_ticket_by_checkin_status'] ) ? sanitize_text_field( $_GET['filter_ticket_by_checkin_status'] ) : '';
			$output .= '<select name="filter_ticket_product_id">';
			$output .= '<option value="">' . __( 'All Ticket Products', 'woocommerce-box-office' ) . '</option>';

			foreach ( $tickets as $ticket ) {
				$output .= sprintf( '<option value="%s"%s>%s</option>', esc_attr( $ticket->ID ), selected( $ticket->ID, $current_id, false ), esc_html( $ticket->post_title ) );
			}

			$output .= '</select>';

			// Filter by checkin status.
			$output .= '<select name="filter_ticket_by_checkin_status">';
			$output .= '<option value="">' . __( 'All status', 'woocommerce-box-office' ) . '</option>';
			$output .= sprintf( '<option %s value="attended">%s</option>', selected( $checkin_status, 'attended', false ),  __( 'Attended', 'woocommerce-box-office' ) );
			$output .= sprintf( '<option %s value="not-attended">%s</option>', selected( $checkin_status, 'not-attended', false ),  __( 'Not attended', 'woocommerce-box-office' ) );
			$output .= '</select>';
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $output;
	}

	/**
	 * Filter ticket products query.
	 *
	 * @param mixed $query
	 *
	 * @return void
	 */
	public function filter_ticket_product_id_query( $query ) {
		global $typenow;

		if ( 'event_ticket' !== $typenow ) {
			return;
		}

		if ( ! empty( $query->query_vars['suppress_filters'] ) ) {
			return;
		}

		$product_id     = isset( $_GET['filter_ticket_product_id'] ) ? absint( $_GET['filter_ticket_product_id'] ) : 0;
		$checkin_status = isset( $_GET['filter_ticket_by_checkin_status'] ) ? sanitize_text_field( $_GET['filter_ticket_by_checkin_status'] ) : '';
		$meta_query     = array();

		// Meta query to filter by product id.
		if ( $product_id ) {
			$meta_query[] = array(
				'key'   => '_product_id',
				'value' => absint( $_GET['filter_ticket_product_id'] ),
			);
		}

		// Meta query to filter by checkin status.
		if ( $checkin_status ) {
			if ( 'attended' === $checkin_status ) {
				$meta_query[] = array(
					'key'   => '_attended',
					'value' => 'yes',
				);
			} else if ( 'not-attended' === $checkin_status ) {
				$meta_query[] = array(
					'key'     => '_attended',
					'compare' => 'NOT EXISTS',
				);
			}
		}

		if ( empty( $meta_query ) ) {
			return;
		}

		$query->query_vars['meta_query'] = array_merge(
			isset( $query->query_vars['meta_query'] ) ? $query->query_vars['meta_query'] : array(),
			$meta_query
		);
	}

	/**
	 * Create meta box on ticket edit screen.
	 *
	 * @param  object $post Post object
	 * @return void
	 */
	public function event_ticket_meta_box( $post ) {
		add_meta_box( 'ticket-info', __( 'Ticket Information', 'woocommerce-box-office' ), array( $this, 'event_ticket_meta_box_content' ), 'event_ticket', 'normal', 'high' );

		if ( function_exists( 'WC_Order_Barcodes' ) ) {
			add_meta_box( 'ticket-barcode', __( 'Ticket Barcode', 'woocommerce-box-office' ), array( $this, 'display_ticket_barcode_meta_box' ), 'event_ticket', 'side', 'default' );
		}
	}

	/**
	 * Ticket barcode meta box.
	 *
	 * @param  object $post Post object
	 * @return void
	 */
	public function display_ticket_barcode_meta_box( $post ) {
		WCBO()->components->ticket_barcode->display_ticket_barcode( $post );
	}

	/**
	 * Load content for ticket meta box.
	 *
	 * @param  object $post Post object
	 * @return void
	 */
	public function event_ticket_meta_box_content ( $post ) {
		wp_nonce_field( 'woocommerce_box_office_ticket_info', 'event_ticket_meta_box_nonce' );

		$ticket      = wc_box_office_get_ticket( $post );
		$ticket_form = null;
		if ( is_a( $ticket->product, 'WC_Product' ) ) {
			$ticket_form = new WC_Box_Office_Ticket_Form( $ticket->product, wp_list_pluck( $ticket->fields, 'value' ) );
		}

		add_filter( 'wocommerce_box_office_input_field_template_vars', array( $this, 'custom_field_wrapper' ) );
		add_filter( 'wocommerce_box_office_option_field_template_vars', array( $this, 'custom_field_wrapper' ) );
		require_once( WCBO()->dir . 'includes/views/admin/ticket-meta-box.php' );
		remove_filter( 'wocommerce_box_office_input_field_template_vars', array( $this, 'custom_field_wrapper' ) );
		remove_filter( 'wocommerce_box_office_option_field_template_vars', array( $this, 'custom_field_wrapper' ) );
	}

	/**
	 * Custom field wrapper in ticket meta box.
	 *
	 * @param array $tpl_vars Template vars for field
	 *
	 * @return array Template vars
	 */
	public function custom_field_wrapper( $tpl_vars ) {
		$tpl_vars['before_field'] = '<p class="form-field">';
		$tpl_vars['after_field']  = '</p>';

		return $tpl_vars;
	}

	/**
	 * Save fields from event ticket meta box.
	 *
	 * @param  integer $post_id Ticket ID
	 * @return void
	 */
	public function event_ticket_meta_box_save( $post_id = 0 ) {
		if ( ! $post_id || ! is_admin() ) {
			return;
		}

		$post_type = get_post_type( $post_id );
		if ( 'event_ticket' !== $post_type ) {
			return;
		}

		if ( empty( $_POST['event_ticket_meta_box_nonce'] ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		if ( ! wp_verify_nonce( $_POST['event_ticket_meta_box_nonce'], 'woocommerce_box_office_ticket_info' ) ) {
			return;
		}

		if ( ! isset( $_POST['ticket_fields'] ) || ! is_array( $_POST['ticket_fields'] ) ) {
			return;
		}

		try {
			$ticket = wc_box_office_get_ticket( $post_id );
			$ticket_form = new WC_Box_Office_Ticket_Form( $ticket->product );
			$ticket_form->validate( $_POST );

			// TODO(gedex) should we send email if email field is changed -- like
			// in front-end?
			remove_action( 'save_post', array( $this, 'event_ticket_meta_box_save' ), 10 );
			$ticket->update( $ticket_form->get_clean_data() );
			add_action( 'save_post', array( $this, 'event_ticket_meta_box_save' ), 10, 1 );

			if ( isset( $_POST['_attended'] ) ) {
				update_post_meta( $post_id, '_attended', sanitize_text_field( wp_unslash( $_POST['_attended'] ) ) );
			} else {
				delete_post_meta( $post_id, '_attended' );
			}
		} catch ( Exception $e ) {
			WC_Admin_Meta_Boxes::add_error( $e->getMessage() );
		}
	}

	/**
	 * Update order item meta when ticket is updated.
	 *
	 * @since 1.1.4
	 * @version 1.1.4
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 * @param bool    $update  Whether this is an existing post being updated or not.
	 */
	public function update_order_item_meta( $post_id, $post, $update ) {
		if ( ! $post_id || ! is_admin() ) {
			return;
		}

		// Ignore if this is created initially since it's generated already by
		// order handler when an order is created.
		if ( ! $update ) {
			return;
		}

		WCBO()->components->order->update_item_meta_from_ticket( $post_id );
	}

	/**
	 * Create meta box on ticket edit screen.
	 *
	 * @param  object $post Post object
	 * @return void
	 */
	public function event_ticket_email_meta_box ( $post ) {
		add_meta_box( 'email-info', __( 'Email Information', 'woocommerce-box-office' ), array( $this, 'event_ticket_email_meta_box_content' ), 'event_ticket_email', 'normal', 'high' );
		add_meta_box( 'email-content', __( 'Email Content', 'woocommerce-box-office' ), array( $this, 'event_ticket_email_content_meta_box_content' ), 'event_ticket_email', 'normal', 'high' );
		add_meta_box( 'email-log', __( 'Email Log', 'woocommerce-box-office' ), array( $this, 'event_ticket_email_log_meta_box_content' ), 'event_ticket_email', 'normal', 'high' );
		remove_meta_box( 'submitdiv', 'event_ticket_email', 'side' );
	}

	/**
	 * Load content for ticket email meta box.
	 *
	 * @param  object $post Post object
	 * @return void
	 */
	public function event_ticket_email_meta_box_content( $post ) {
		require_once( WCBO()->dir . 'includes/views/admin/ticket-email-meta-box.php' );
	}

	/**
	 * Load content for ticket email meta box.
	 *
	 * @param  object $post Post object
	 * @return void
	 */
	public function event_ticket_email_content_meta_box_content( $post ) {
		echo wp_kses_post( wpautop( $post->post_content ) );
	}

	public function event_ticket_email_log_meta_box_content( $post ) {
		require_once( WCBO()->dir . 'includes/views/admin/ticket-email-log-meta-box.php' );;
	}

	/**
	 * Modify admin columns for tickets list table.
	 *
	 * @param  array  $columns Default columns
	 * @return array           Modified columns
	 */
	public function manage_ticket_columns( $columns = array() ) {
		// Remove WordPress SEO columns.
		unset( $columns['wpseo-score'] );
		unset( $columns['wpseo-title'] );
		unset( $columns['wpseo-metadesc'] );
		unset( $columns['wpseo-focuskw'] );

		// Remove WP columns.
		unset( $columns['title'] );
		unset( $columns['date'] );

		// Add custom columns.
		$columns['ticket']     = __( 'Ticket', 'woocommerce-box-office' );
		$columns['order']      = __( 'Order', 'woocommerce-box-office' );
		$columns['checked-in'] = __( 'Checked-in yet?', 'woocommerce-box-office' );
		$columns['opted-out']  = __( 'Opted-out of public listing', 'woocommerce-box-office' );
		$columns['date']       = __( 'Date', 'woocommerce-box-office' );

		return $columns;
	}

	/**
	 * Display data in ticket list table columns.
	 *
	 * @param  string  $column  Column name
	 * @param  integer $post_id Ticket ID
	 * @return void
	 */
	public function display_ticket_columns( $column = '', $post_id = 0 ) {
		if ( ! $column || ! $post_id ) {
			return;
		}

		switch ( $column ) {
			case 'ticket':
				printf(
					'
					<strong>
						<a class="row-title" href="%1$s">%2$s</a>%3$s
					</strong>
					<div class="ticket-fields">%4$s</div>
					',
					esc_url( admin_url( 'post.php?post=' . $post_id . '&action=edit' ) ),
					esc_html( get_the_title( $post_id ) ),
					( 'pending' === get_post_status( $post_id ) ) ? sprintf( ' - <span class="post-state">%s</span>', esc_html__( 'Pending', 'woocommerce-box-office' ) ) : '',
					wp_kses_post( wc_box_office_get_ticket_description( $post_id ) )
				);
				break;
			case 'order':
				$order_id     = wp_get_post_parent_id( $post_id );
				$order        = wc_get_order( $order_id );
				if ( $order_id && $order instanceof \WC_Order ) {
					printf(
						'<strong><a href="%1$s">%2$s</a> - %3$s</strong>',
						esc_url( $order->get_edit_order_url() ),
						esc_html( '#' . $order_id ),
						esc_html( wc_get_order_status_name( $order->get_status() ) )
					);
				} else {
					echo '-';
				}
				break;
			case 'checked-in':
				if ( get_post_meta( $post_id, '_attended', true ) ) {
					printf( '<strong>%s</strong>', esc_html__( 'Yes', 'woocommerce-box-office' ) );
				}
				break;

			case 'opted-out':
				if ( 'opted-out' === get_post_meta( $post_id, '_user_pii_preference', true ) ) {
					printf( '<strong>%s</strong>', esc_html__( 'Yes', 'woocommerce-box-office' ) );
				}
				break;

			default:
				break;
		}
	}

	/**
	 * Set row actions for event_ticket post type.
	 *
	 * @param array   $actions List of actions
	 * @param WP_Post $post    Post object
	 *
	 * @return array
	 */
	public function row_actions( $actions, $post ) {
		if ( 'event_ticket' === $post->post_type && isset( $actions['inline hide-if-no-js'] ) ) {
			unset( $actions['inline hide-if-no-js'] );
		}

		return $actions;
	}

	/**
	 * Add new bulk actions for event_ticket post type and remove edit action.
	 *
	 * @param array $actions Bulk actions.
	 *
	 * @return array $actions Bulk actions
	 */
	public function modify_bulk_actions( $actions ) {
		unset( $actions['edit'] );
		$actions['mark_attended']       = __( 'Mark as attended', 'woocommerce-box-office' );
		$actions['mark_not_checked_in'] = __( 'Mark as not checked-in yet', 'woocommerce-box-office' );
		$actions['mark_opted_out']      = __( 'Mark as opted-out of public listing', 'woocommerce-box-office' );
		$actions['mark_opted_in']       = __( 'Mark as opted-in for public listing', 'woocommerce-box-office' );

		return $actions;
	}

	/**
	 * Handle bulk actions for event_ticket post type.
	 *
	 * @param string $redirect_to Redirect URL after bulk actions.
	 * @param string $doaction    Action to perform.
	 * @param array  $post_ids    Post IDs.
	 * @return string Redirect URL
	 */
	public function handle_bulk_actions( $redirect_to, $doaction, $post_ids ) {
		$supported_actions = array( 'mark_attended', 'mark_not_checked_in', 'mark_opted_out', 'mark_opted_in' );

		if ( empty( $post_ids ) || ! in_array( $doaction, $supported_actions, true ) ) {
			return $redirect_to;
		}

		$updated = 0;
		foreach ( $post_ids as $post_id ) {
			$succeed = false;
			switch ( $doaction ) {
				case 'mark_attended':
					$succeed = update_post_meta( $post_id, '_attended', 'yes' );
					break;
				case 'mark_not_checked_in':
					$succeed = delete_post_meta( $post_id, '_attended' );
					break;
				case 'mark_opted_out':
					$succeed = update_post_meta( $post_id, '_user_pii_preference', 'opted-out' );
					break;
				case 'mark_opted_in':
					$succeed = update_post_meta( $post_id, '_user_pii_preference', 'opted-in' );
					break;
			}

			if ( $succeed ) {
				$updated++;
			}
		}

		$redirect_to = add_query_arg( 'updated', $updated, $redirect_to );

		return esc_url_raw( $redirect_to );
	}

	/**
	 * Modify admin columns for ticket emails list table.
	 *
	 * @param  array  $columns Default columns
	 * @return array           Modified columns
	 */
	public function manage_ticket_email_columns( $columns = array() ) {
		// Remove WordPress SEO columns.
		unset( $columns['wpseo-score'] );
		unset( $columns['wpseo-title'] );
		unset( $columns['wpseo-metadesc'] );
		unset( $columns['wpseo-focuskw'] );

		return $columns;
	}

	/**
	 * Remove 'Add New' menu item from Tickets
	 * @return void
	 */
	public function hide_ticket_add () {
		global $submenu;
		unset( $submenu['edit.php?post_type=event_ticket'][10] );
	}

	/**
	 * Prevent access to specific admin pages
	 * @return void
	 */
	public function block_admin_pages () {
		if ( ! is_admin() ) {
			return;
		}

		global $pagenow;

		$type = '';

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['post_type'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$type = esc_attr( sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) );
		}

		if ( ! $type ) {
			return;
		}

		$url = '';

		if ( 'post-new.php' === $pagenow && 'event_ticket' === $type ) {
			$url = admin_url( 'edit.php?post_type=event_ticket&page=create_ticket' );
		} elseif ( 'post-new.php' === $pagenow && 'event_ticket_email' === $type ) {
			$url = admin_url( 'edit.php?post_type=event_ticket&page=ticket_tools&tab=email' );
		} elseif ( 'edit.php' === $pagenow && 'event_ticket_email' === $type ) {
			$url = admin_url( 'edit.php?post_type=event_ticket&page=ticket_tools&tab=email' );
		}

		if ( $url ) {
			wp_safe_redirect( $url );
			exit;
		}
	}

	/**
	 * Add create ticket page.
	 *
	 * @return void
	 */
	public function add_create_ticket_page() {
		$create_ticket_page = add_submenu_page( 'edit.php?post_type=event_ticket', __( 'Create Ticket', 'woocommerce-box-office' ), __( 'Create Ticket', 'woocommerce-box-office' ), 'manage_woocommerce', 'create_ticket', array( $this, 'create_ticket_page' ) );
	}

	/**
	 * Render create ticket page on admin.
	 *
	 * @return void
	 */
	public function create_ticket_page() {
		$create_page = new WC_Box_Office_Ticket_Create_Admin();
		$create_page->render( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	}

	/**
	 * Register the navigation items in the WooCommerce navigation.
	 */
	public function register_navigation_items() {
		if (
			! method_exists( Screen::class, 'register_post_type' ) ||
			! method_exists( Menu::class, 'add_plugin_item' ) ||
			! method_exists( Menu::class, 'add_plugin_category' ) ||
			! method_exists( Menu::class, 'get_post_type_items' )
		) {
			return;
		}

		Menu::add_plugin_category(
			array(
				'id'    => 'woocommerce-box-office',
				'title' => 'Box Office',
			)
		);

		$box_office_item = Menu::get_post_type_items(
			'event_ticket',
			array(
				'parent' => 'woocommerce-box-office',
				'order'  => 1,
			)
		);

		Menu::add_plugin_item( $box_office_item['all'] );

		Menu::add_plugin_item(
			array(
				'id'         => 'create_ticket',
				'title'      => __( 'Create Ticket', 'woocommerce-box-office' ),
				'capability' => 'manage_woocommerce',
				'url'        => 'edit.php?post_type=event_ticket&page=create_ticket',
				'parent'     => 'woocommerce-box-office',
				'order'      => 2,
			)
		);

		Menu::add_plugin_item(
			array(
				'id'         => 'ticket_tool',
				'title'      => __( 'Tools', 'woocommerce-box-office' ),
				'capability' => 'manage_woocommerce',
				'url'        => 'edit.php?post_type=event_ticket&page=ticket_tools&tab=export',
				'parent'     => 'woocommerce-box-office',
				'order'      => 3,
			)
		);

		Screen::register_post_type( 'event_ticket' );
	}
}
