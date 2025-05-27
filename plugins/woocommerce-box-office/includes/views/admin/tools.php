<?php
// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
$active_tab = ! empty( $_GET['tab'] ) ? $_GET['tab'] : 'export';
if ( ! in_array( $active_tab, array( 'export', 'email', 'user-privacy' ) ) ) {
	$active_tab = 'export';
}

$tab_url_fmt = 'edit.php?post_type=event_ticket&page=ticket_tools&tab=%s';
?>
<div class="wrap" id="woocommerce_box_office_tools">
	<?php echo $errors; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

	<h2><?php esc_html_e( 'Ticket Tools' , 'woocommerce-box-office' ); ?></h2>
	<h2 class="nav-tab-wrapper">
		<?php $class_attr = 'export' === $active_tab ? 'nav-tab nav-tab-active' : 'nav-tab'; ?>
		<a href="<?php echo esc_url( admin_url( sprintf( $tab_url_fmt, 'export' ) ) ) ?>" class="<?php echo esc_attr( $class_attr ); ?>">
			<?php esc_html_e( 'Export', 'woocommerce-box-office' ) ?>
		</a>

		<?php $class_attr = 'email' === $active_tab ? 'nav-tab nav-tab-active' : 'nav-tab'; ?>
		<a href="<?php echo esc_url( admin_url( sprintf( $tab_url_fmt, 'email' ) ) ) ?>" class="<?php echo esc_attr( $class_attr ); ?>">
			<?php esc_html_e( 'Email', 'woocommerce-box-office' ) ?>
		</a>

		<?php $class_attr = 'user-privacy' === $active_tab ? 'nav-tab nav-tab-active' : 'nav-tab'; ?>
		<a href="<?php echo esc_url( admin_url( sprintf( $tab_url_fmt, 'user-privacy' ) ) ) ?>" class="<?php echo esc_attr( $class_attr ); ?>">
			<?php esc_html_e( 'User Privacy', 'woocommerce-box-office' ) ?>
		</a>
	</h2>

	<?php require_once( WCBO()->dir . 'includes/views/admin/tools-' . $active_tab . '.php' ); ?>
</div>
