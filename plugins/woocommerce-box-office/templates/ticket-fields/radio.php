<?php echo $before_field; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	<label class="<?php echo esc_attr( $label_class ); ?>" for="<?php echo esc_attr( $id ); ?>">
		<?php echo esc_html( $label ); ?>:
		<?php if ( $required ) : ?>
		<span class="required">*</span>
		<?php endif;?>
	</label>
	<?php foreach ( $options as $index => $option ) : ?>
		<label for="<?php echo esc_attr( sprintf( '%s_%d', $id, $index + 1 ) ); ?>" class="ticket-field-option-label">
			<input
				type="radio"
				name="<?php echo esc_attr( $name ); ?>"
				class="<?php echo esc_attr( $input_class ); ?>"
				value="<?php echo esc_attr( $option ); ?>"
				id="<?php echo esc_attr( sprintf( '%s_%d', $id, $index + 1 ) ); ?>"
				<?php echo $required ? 'required' : ''; ?>
				<?php checked( $option, $value ); ?>
				<?php disabled( $disabled ); ?>>
			<?php echo esc_html( $option ); ?>
		</label>
	<?php endforeach; ?>
<?php echo $after_field; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
