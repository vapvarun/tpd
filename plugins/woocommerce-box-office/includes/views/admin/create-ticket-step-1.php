<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$data = $posted_data;

$data['product_id']          = ( ! empty( $data['product_id'] ) ) ? $data['product_id'] : '';
$data['create_order_method'] = ( ! empty( $data['create_order_method'] ) ) ? $data['create_order_method'] : 'no_order';
$data['ticket_order_id']     = ( ! empty( $data['ticket_order_id'] ) ) ? $data['ticket_order_id'] : '';
?>

<div class="wrap woocommerce">
	<h2><?php esc_html_e( 'Create Ticket', 'woocommerce-box-office' ); ?></h2>

	<p>
		<?php esc_html_e( 'You can create a new ticket for a customer here. This form will create a ticket for the user, and optionally an associated order. Created orders will be marked as pending payment.', 'woocommerce-box-office' ); ?>
	</p>

	<?php $this->maybe_print_errors(); ?>

	<form method="POST">
		<table class="form-table">
			<tr valign="top">
				<th scope="row">
					<label for="customer_id"><?php esc_html_e( 'Customer', 'woocommerce-box-office' ); ?></label>
				</th>
				<td>
					<select id="customer_id" class="wc-customer-search" name="customer_id" style="width:300px" data-placeholder="<?php esc_attr_e( 'Guest', 'woocommerce-box-office' ) ?>">
						<option value=""><?php esc_html_e( 'Guest', 'woocommerce-box-office' ) ?></option>
					</select>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row">
					<label for="product_id"><?php esc_html_e( 'Ticket-enabled Product', 'woocommerce-box-office' ); ?></label>
				</th>
				<td>
					<select id="product_id" name="product_id" class="chosen_select" style="width:300px">
						<option value=""><?php esc_html_e( 'Select a ticket-enabled product...', 'woocommerce-box-office' ); ?></option>
						<?php foreach ( wc_box_office_get_all_ticket_products() as $product ) : ?>
							<option value="<?php echo esc_attr( $product->ID ); ?>" <?php selected( $product->ID === $data['product_id'] ) ?>><?php echo esc_html( $product->post_title ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row">
					<label for="quantity"><?php esc_html_e( 'Ticket Quantity', 'woocommerce-box-office' ); ?></label>
				</th>
				<td>
					<input type="number" step="1" min="1" id="quantity" name="quantity" value="<?php echo esc_attr( ! empty( $data['quantity'] ) ? absint( $data['quantity'] ) : 1 ); ?>" title="<?php esc_html_e( 'Qty', 'woocommerce-box-office' ); ?>" class="input-text qty text" size="4">
				</td>
			</tr>

			<tr valign="top">
				<th scope="row">
					<label for="create_order_method"><?php esc_html_e( 'Create Order Method', 'woocommerce-box-office' ); ?></label>
				</th>
				<td>
					<p>
						<label>
							<input type="radio" name="create_order_method" value="new" class="checkbox" <?php checked( 'new' === $data['create_order_method'] ); ?> />
							<?php esc_html_e( 'Create a new corresponding order for this new ticket. Please note - the ticket will not be active until the order is processed/completed.', 'woocommerce-box-office' ); ?>
						</label>
					</p>
					<p>
						<label>
							<input type="radio" name="create_order_method" value="existing" class="checkbox" <?php checked( 'existing' === $data['create_order_method'] ); ?> />
							<?php esc_html_e( 'Assign ticket(s) to an existing order with this ID:', 'woocommerce-box-office' ); ?>
							<input type="number" name="ticket_order_id" value="<?php echo esc_attr( $data['ticket_order_id'] ); ?>" class="text" size="3" />
						</label>
					</p>
					<p>
						<label>
							<input type="radio" name="create_order_method" value="no_order" class="checkbox" <?php checked( 'no_order' === $data['create_order_method'] ); ?> />
							<?php esc_html_e( 'Don\'t create an order for this ticket.', 'woocommerce-box-office' ); ?>
						</label>
					</p>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row">&nbsp;</th>
				<td>
					<input type="submit" name="submit_create_ticket" class="button-primary" value="<?php esc_attr_e( 'Next', 'woocommerce-box-office' ); ?>" />

					<input type="hidden" name="create_ticket_step" value="1" />
					<?php wp_nonce_field( 'create_event_ticket' ); ?>
				</td>
			</tr>
		</table>
	</form>
</div>
