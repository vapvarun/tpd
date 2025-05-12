<?php
/**
 * Use this file for all your template filters and actions.
 * Requires PDF Invoices & Packing Slips for WooCommerce 1.4.13 or higher
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

add_action( 'wpo_wcpdf_custom_styles', 'wpo_wcpdf_custom_styles', 10, 2 );
function wpo_wcpdf_custom_styles ( $document_type, $document ) {
    ?>
    td.header .header_logo {
        width: 120px;
        background-size: 100%;
        height: 120px;
        background-image: url(https://tpdedu.s3.ap-southeast-2.amazonaws.com/uploads/2024/02/13155835/TPD_LOGO.png);
        background-repeat: no-repeat;
    }
    <?php
}