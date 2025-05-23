<?php
/**
 * Dokan Dashboard Product Variation Template
 *
 * @since 2.4
 *
 * @package dokan
 */
?>
<div class="dokan-attribute-variation-options dokan-edit-row dokan-clearfix hide_if_external">
    <div class="dokan-section-heading" data-togglehandler="dokan_attribute_variation_options">
        <h2><i class="far fa-list-alt" aria-hidden="true"></i> <?php esc_html_e( 'Attribute', 'dokan' ); ?><span class="show_if_variable show_if_variable-subscription"><?php _e( ' and Variation', 'dokan' ) ?></span></h2>
        <p class="show_if_variable show_if_variable-subscription"><?php esc_html_e( 'Manage attributes and variations for this variable product.', 'dokan' ); ?></p>
        <p class="show_if_simple show_if_subscription show_if_grouped"><?php esc_html_e( 'Manage attributes for this simple product.', 'dokan' ); ?></p>

        <a href="#" class="dokan-section-toggle">
            <i class="fas fa-sort-down fa-flip-vertical" aria-hidden="true"></i>
        </a>

        <div class="dokan-clearfix"></div>
    </div>
    <div class="dokan-section-content">
        <div class="dokan-product-attribute-wrapper show_if_simple show_if_subscription show_if_variable show_if_subscription show_if_variable-subscription show_if_grouped">

            <ul class="dokan-attribute-option-list">
                <?php
                global $wc_product_attributes;

                // Product attributes - taxonomies and custom, ordered, with visibility and variation attributes set
                $attributes = maybe_unserialize( get_post_meta( $post_id, '_product_attributes', true ) );

                // Output All Set Attributes
                if ( ! empty( $attributes ) ) {
                    $attribute_keys  = array_keys( $attributes );
                    $attribute_total = sizeof( $attribute_keys );

                    for ( $i = 0; $i < $attribute_total; $i ++ ) {
                        $attribute     = $attributes[ $attribute_keys[ $i ] ];
                        $position      = empty( $attribute['position'] ) ? 0 : absint( $attribute['position'] );
                        $taxonomy      = '';
                        $metabox_class = array();

                        if ( $attribute['is_taxonomy'] ) {
                            $taxonomy = $attribute['name'];

                            if ( ! taxonomy_exists( $taxonomy ) ) {
                                continue;
                            }

                            $attribute_taxonomy = $wc_product_attributes[ $taxonomy ];
                            $metabox_class[]    = 'taxonomy';
                            $metabox_class[]    = $taxonomy;
                            $attribute_label    = wc_attribute_label( $taxonomy );
                        } else {
                            $attribute_label    = apply_filters( 'woocommerce_attribute_label', $attribute['name'], $attribute['name'], false );
                        }

                        dokan_get_template_part(
                            'products/edit/html-product-attribute', '', [
								'pro'                => true,
								'thepostid'          => $post_id,
								'taxonomy'           => $taxonomy,
								'attribute_taxonomy' => $attribute_taxonomy ?? null,
								'attribute_label'    => $attribute_label,
								'attribute'          => $attribute,
								'metabox_class'      => $metabox_class,
								'position'           => $position,
								'i'                  => $i,
                            ]
                        );

                    }
                }
                ?>
            </ul>
            <div class="dokan-product-attribute-alert dokan-hide dokan-alert dokan-alert-danger">
                <?php esc_html_e( 'Please choose Year Level and Recourse Type attributes as these two are require !!!', 'dokan' ); ?>
            </div>

            <div class="dokan-attribute-type">
                <select name="predefined_attribute" id="predefined_attribute" class="dokan-w5 dokan-form-control dokan_attribute_taxonomy" data-predefined_attr='<?php echo json_encode( $attribute_taxonomies ); ?>'>
                    <option value=""><?php esc_html_e( 'Custom Attribute', 'dokan' ); ?></option>
                    <?php
                    if ( ! empty( $attribute_taxonomies ) ) {
                        foreach ( $attribute_taxonomies as $attribute_taxonomy ) {
                            $attribute_taxonomy_name = wc_attribute_taxonomy_name( $attribute_taxonomy->attribute_name );
                            $label = wc_attribute_label( 'pa_' . $attribute_taxonomy->attribute_name );
                            echo '<option value="' . esc_attr( $attribute_taxonomy_name ) . '">' . esc_html( $label ) . '</option>';
                        }
                    }
                    ?>
                </select>
                <a href="#" class="dokan-btn dokan-btn-default add_new_attribute"><?php esc_html_e( 'Add attribute', 'dokan' ); ?></a>
                <a href="#" class="dokan-btn dokan-btn-default dokan-btn-theme dokan-save-attribute"><?php esc_html_e( 'Save attribute', 'dokan' ); ?></a>
                <span class="dokan-spinner dokan-attribute-spinner dokan-hide"></span>
            </div>
        </div>

        <div class="dokan-product-variation-wrapper show_if_variable show_if_variable-subscription">
            <?php dokan_product_output_variations(); ?>
        </div>
    </div>
</div>
<script>
    ;(function($){
        let htmlBody = $('body');
        htmlBody.on( 'click', '.sale_price_dates_from', function(){
            if ( ! $(this).hasClass( 'hasDatePicker' ) ) {
                let fromInput = $(this);
                let fromName  = fromInput.attr('name');
                let toName    = fromName.replace( '_from', '_to' );
                let toInput   = $( 'input[name^="'+ toName +'"]' );
                $(fromInput).datepicker({
                    defaultDate: '',
                    dateFormat: 'yy-mm-dd',
                    numberOfMonths: 1,
                    onSelect: function(selectedDate) {
                        let date = new Date(selectedDate);
                        date.setDate(date.getDate() + 1);
                        if ( ! $(toInput).hasClass( 'hasDatePicker' ) ) {
                            $(toInput).datepicker({
                                defaultDate: '',
                                dateFormat: 'yy-mm-dd',
                                numberOfMonths: 1,
                                minDate: date
                            });
                        } else {
                            $(toInput).datepicker('option', {
                                minDate: date
                            });
                        }
                    }
                });

                $(fromInput).datepicker( 'show' );
            }
        });

        htmlBody.on( 'click', '.sale_price_dates_to', function(){
            if ( ! $(this).hasClass( 'hasDatePicker' ) ) {
                let toInput = $(this);
                let toName  = toInput.attr('name');
                let fromName    = toName.replace( '_to', '_from' );
                let fromInput   = $( 'input[name^="'+ fromName +'"]' );
                $(toInput).datepicker({
                    defaultDate: '',
                    dateFormat: 'yy-mm-dd',
                    numberOfMonths: 1,
                    onSelect: function(selectedDate) {
                        let date = new Date(selectedDate);
                        date.setDate(date.getDate() + 1);

                        if ( ! $(fromInput).hasClass( 'hasDatePicker' ) ) {
                            $(fromInput).datepicker({
                                defaultDate: '',
                                dateFormat: 'yy-mm-dd',
                                numberOfMonths: 1,
                                maxDate: date
                            });
                        } else {
                            $(fromInput).datepicker('option', {
                                maxDate: date
                            });
                        }
                    }
                });

                $(toInput).datepicker( 'show' );
            }
        });
    })(jQuery);
</script>
