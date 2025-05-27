<?php


/**
 * All social settings in one place.
 *
 * @return array
 */
function wbcom_wc_vendors_get_social_media_settings() {
	$settings = array(
		'twitter'   => array(
			'id'           => '_wcv_twitter_username',
			'label'        => __( 'Twitter Username', 'tpd-core' ),
			'placeholder'  => __( 'YourTwitterUserHere', 'tpd-core' ),
			'desc_tip'     => 'true',
			'description'  => __( 'Your <a href="https://twitter.com/">Twitter</a> username without the url.', 'tpd-core' ),
			'type'         => 'text',
			'icon'         => 'twitter-square',
			'url_template' => '//twitter.com/%s',
		),
		'instagram' => array(
			'id'           => '_wcv_instagram_username',
			'label'        => __( 'Instagram Username', 'tpd-core' ),
			'placeholder'  => __( 'YourInstagramUsername', 'tpd-core' ),
			'desc_tip'     => 'true',
			'description'  => __( 'Your <a href="https://instagram.com/">Instagram</a> username without the url.', 'tpd-core' ),
			'type'         => 'text',
			'icon'         => 'instagram',
			'url_template' => '//instagram.com/%s',
		),
		'facebook'  => array(
			'id'           => '_wcv_facebook_url',
			'label'        => __( 'Facebook URL', 'tpd-core' ),
			'placeholder'  => __( 'http://yourfacebookurl/here', 'tpd-core' ),
			'desc_tip'     => 'true',
			'description'  => __( 'Your <a href="https://facebook.com/">Facebook</a> url.', 'tpd-core' ),
			'type'         => 'text',
			'icon'         => 'facebook-square',
			'url_template' => '%s',
		),
		'linkedin'  => array(
			'id'           => '_wcv_linkedin_url',
			'label'        => __( 'LinkedIn URL', 'tpd-core' ),
			'placeholder'  => __( 'http://linkedinurl.com/here', 'tpd-core' ),
			'desc_tip'     => 'true',
			'description'  => __( 'Your <a href="https://linkedin.com/">LinkedIn</a> url.', 'tpd-core' ),
			'type'         => 'url',
			'icon'         => 'linkedin',
			'url_template' => '%s',
		),
		'youtube'   => array(
			'id'           => '_wcv_youtube_url',
			'label'        => __( 'YouTube URL', 'tpd-core' ),
			'placeholder'  => __( 'http://youtube.com/here', 'tpd-core' ),
			'desc_tip'     => 'true',
			'description'  => __( 'Your <a href="https://youtube.com/">Youtube</a> url.', 'tpd-core' ),
			'type'         => 'url',
			'icon'         => 'youtube-square',
			'url_template' => '%s',
		),
		'pinterest' => array(
			'id'           => '_wcv_pinterest_url',
			'label'        => __( 'Pinterest URL', 'tpd-core' ),
			'placeholder'  => __( 'https://www.pinterest.com/username/', 'tpd-core' ),
			'desc_tip'     => 'true',
			'description'  => __( 'Your <a href="https://www.pinterest.com/">Pinterest</a> url.', 'tpd-core' ),
			'type'         => 'url',
			'icon'         => 'pinterest-square',
			'url_template' => '%s',
		),
		'snapchat'  => array(
			'id'           => '_wcv_snapchat_username',
			'label'        => __( 'Snapchat Username', 'tpd-core' ),
			'placeholder'  => __( 'snapchatUsername', 'tpd-core' ),
			'desc_tip'     => 'true',
			'description'  => __( 'Your snapchat username.', 'tpd-core' ),
			'type'         => 'text',
			'icon'         => 'snapchat',
			'url_template' => '//www.snapchat.com/add/%s',
		),
		'telegram'  => array(
			'id'           => '_wcv_telegram_username',
			'label'        => __( 'Telegram Username', 'tpd-core' ),
			'placeholder'  => __( 'TelegramUsername', 'tpd-core' ),
			'desc_tip'     => 'true',
			'description'  => __( 'Your telegram username.', 'tpd-core' ),
			'type'         => 'text',
			'icon'         => 'telegram-square',
			'url_template' => '//telegram.me/%s',
		),
	);

	return apply_filters( 'wbcom_wc_vendors_get_social_media_settings', $settings );
}


/**
 * Formate Store Url
 *
 * @var integer
 * @return string
 */
if ( 'wbcom_wc_vendors_format_store_url' ) {

	function wbcom_wc_vendors_format_store_url( $vendor_id ) {
		$store_url = get_user_meta( $vendor_id, '_wcv_company_url', true );

		if ( empty( $store_url ) || 'ARRAY' === $store_url || 'Array' === $store_url || is_array( $store_url ) ) {
			return '';
		}

		return apply_filters(
			'wbcom_wc_vendors_format_store_url',
			sprintf( '<a href="%1$s">%1$s</a>', $store_url ),
			$vendor_id
		);
	}
}

/**
 * Format and print store address.
 *
 * @param  integer $vendor_id [description]
 * @return string
 */
if ( ! function_exists( 'wbcom_wc_vendors_format_store_address' ) ) {

	function wbcom_wc_vendors_format_store_address( $vendor_id ) {
		$store_address_args = apply_filters(
			'wbcom_wc_vendors_format_store_address_args',
			array(
				'address1' => get_user_meta( $vendor_id, '_wcv_store_address1', true ),
				'city'     => get_user_meta( $vendor_id, '_wcv_store_city', true ),
				'state'    => get_user_meta( $vendor_id, '_wcv_store_state', true ),
				'postcode' => get_user_meta( $vendor_id, '_wcv_store_postcode', true ),
				'country'  => isset( WC()->countries->countries[ get_user_meta( $vendor_id, '_wcv_store_country', true ) ] ) ? WC()->countries->countries[ get_user_meta( $vendor_id, '_wcv_store_country', true ) ] : '',
			),
			$vendor_id
		);

		$store_address_args = array_filter( $store_address_args );

		return apply_filters( 'wbcom_wc_vendors_format_store_address_output', implode( ', ', $store_address_args ), $vendor_id );
	}
}


/**
 * Format store social icons
 *
 * @param int    $vendor_id Vendor ID.
 * @param string $size      Icon size.
 * @param array  $hidden    Hidden items.
 *
 * @since 1.6.2
 * @version 1.6.3
 *
 * @return false|string
 */
if ( ! function_exists( 'wbcom_wc_vendors_format_store_social_icons' ) ) {

	function wbcom_wc_vendors_format_store_social_icons( $vendor_id, $size = 'sm', $hidden = array() ) {
		ob_start();

		foreach ( wbcom_wc_vendors_get_social_media_settings() as $key => $setting ) {
			if ( in_array( $key, $hidden ) ) {
				continue;
			}

			$value = get_user_meta( $vendor_id, $setting['id'], true );

			if ( ! $value ) {
				continue;
			}
			?>
			<li>
				<a href="<?php printf( $setting['url_template'], $value ); ?>" target="_blank">
					<i class="fab fa-<?php echo esc_attr( $setting['icon'] ); ?>"></i>
				</a>
			</li>
			<?php
		}

		$list = trim( ob_get_clean() );
		if ( ! $list ) {
			return;
		}
		return '<ul class="social-icons">' . $list . '</ul>';
	}
}


/**
 * Display the banner image  of vendor.
 *
 * @param  integer $vendor_id Venor's Id
 * @return string           Return a background image html.
 */
if ( ! function_exists( 'wbcom_wc_vendors_banner_image' ) ) {

	function wbcom_wc_vendors_banner_image( $vendor_id ) {
		$store_bg = '';
		if ( class_exists( 'WCVendors_Pro' ) ) {
			$store_icon_src = wp_get_attachment_image_src( get_user_meta( $vendor_id, '_wcv_store_banner_id', true ), 'full' );
			if ( is_array( $store_icon_src ) ) {
				$store_bg = $store_icon_src[0];
			}
			if ( empty( $store_bg ) ) {
				$store_bg = WCVendors_Pro::get_option( 'default_store_banner_src' );
			}
		} else {

		}
		$bg_styles = ( ! empty( $store_bg ) ) ? ' style="background-image: url(' . $store_bg . '); background-repeat: no-repeat;background-size: cover;"' : '';
		if ( ! empty( $bg_styles ) ) {
			return $bg_styles;
		}
	}
}


/**
 * Create vendor stor icon.
 *
 * @param  integer $vendor_id Vendor ID
 * @param  integer $width     Width of icon
 * @param  integer $height    Heignt of Icon
 * @return
 */
if ( ! function_exists( 'wbcom_wc_vendors_stor_icon' ) ) {

	function wbcom_wc_vendors_stor_icon( $vendor_id, $width = 150, $height = 150 ) {

		if ( ! $vendor_id ) {
			return;
		}
		$store_icon_url = '';

		if ( class_exists( 'WCVendors_Pro' ) ) {
			$store_icon_src = wp_get_attachment_image_src( get_user_meta( $vendor_id, '_wcv_store_icon_id', true ), array( 150, 150 ) );
			if ( is_array( $store_icon_src ) ) {
				$store_icon_url = $store_icon_src[0];
			} else {
				$store_icon_url = get_avatar_url( $vendor_id, 150 );
			}
		} else {
			$store_icon_url = get_avatar_url( $vendor_id, 150 );
		}
		return $store_icon_url;
	}
}


/**
 * Print shop rating under the shop icon.
 *
 * @param  integer $vendor_id Vendor ID
 */
if ( ! function_exists( 'wbcom_wc_vendors_shop_rating' ) ) {

	function wbcom_wc_vendors_shop_rating( $vendor_id ) {

		if ( class_exists( 'WCVendors_Pro' ) ) {
			if ( ! WCVendors_Pro::get_option( 'ratings_management_cap' ) ) {
				echo '<div class="wcv_grid_rating">';
				echo WCVendors_Pro_Ratings_Controller::ratings_link( $vendor_id, true );
				echo '</div>';
			}
		}
	}
}


/**
 * This function print the desciption under the vendor rating,
 *
 * @param  integer $vendor_id Vendor Id
 * @return string            Return trimed description
 */
if ( ! function_exists( 'wbcom_wc_vendors_shop_description' ) ) {

	function wbcom_wc_vendors_shop_description( $vendor_id ) {
		$vendor_meta = array_map(
			function( $a ) {
				return $a[0];
			},
			get_user_meta( $vendor_id )
		);

		$shop_descr = array_key_exists( 'pv_shop_description', $vendor_meta ) ? $vendor_meta['pv_shop_description'] : '';

		$length  = apply_filters( 'wbcom_wc_vendors_shop_description_limit', 350 );
		$maxchar = ! empty( $length ) ? (int) trim( $length ) : 350;
		$text    = ! empty( $shop_descr ) ? trim( $shop_descr ) : '';

		$out = '';

		$out = $text . $out;

		$out = preg_replace( '~\[/?.*?\]~', '', $out );
		$out = strip_tags( strip_shortcodes( $out ) );

		if ( mb_strlen( $out ) > $maxchar ) {
			$out = mb_substr( $out, 0, $maxchar );
			$out = preg_replace( '@(.*)\s[^\s]*$@s', '\\1 ...', $out );
		}

		return $out;
	}
}



/**
 * List vendors products
 *
 * @param  integer $vendor_id Vendor's ID
 */
if ( ! function_exists( 'wbcom_wc_vendors_vendor_products' ) ) {

	function wbcom_wc_vendors_vendor_products( $vendor_id ) {
		$args     = array(
			'post_type'           => 'product',
			'posts_per_page'      => 3,
			'author'              => $vendor_id,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		);
		$products = new WP_Query( $args );

		if ( ! empty( $products->posts ) ) {

			$i = 0;
			foreach ( $products->posts as $product ) {

				$product_id    = $product->ID;
				$product_title = get_the_title( $product_id );
				$product_url   = get_permalink( $product_id );
				$atachment_url = wp_get_attachment_url( get_post_thumbnail_id( $product_id ) );
				$store_url     = WCV_Vendors::get_vendor_shop_page( $vendor_id );
				$totaldeals    = count_user_posts( $vendor_id, $post_type      = 'product' ) - 3;
				$i++;
				?>
				<a href="<?php echo esc_url( $product_url ); ?>" class="vendor_product">
					<img src="<?php echo esc_url( $atachment_url ); ?>" width=70 height=70 alt="<?php echo esc_attr( $product_title ); ?>"/>
				</a>
				<?php
			}
			if ( $i == 3 && $totaldeals > 0 ) {
				?>
				<a href="<?php echo esc_url( $store_url ); ?>" target="_blank" class="vendor_product">
					<span class="product_count_in_member"><?php echo '+' . $totaldeals; ?></span>
				</a>
				<?php
			}
		}
		wp_reset_query();
	}
}


/**
 * Remove additional information tab form product tabs
 *
 * @param  [type] $tabs               [description]
 * @return [type]       [description]
 */
function wbcom_remove_additional_info( $tabs ) {
	global $product;
	unset( $tabs['additional_information'] ); // To remove the additional information tab

	if ( ! empty( get_field( 'curriculum' ) ) ) {
		$tabs['curriculum'] = array(
			'title'    => __( 'Curriculum', 'tpd-core' ),
			'priority' => 10,
			'callback' => 'wbcom_render_curriculum_tab',
		);
	}

	if ( ! empty( get_field( 'inclusions' ) ) ) {
		$tabs['inclusions'] = array(
			'title'    => __( 'Inclusions', 'tpd-core' ),
			'priority' => 11,
			'callback' => 'wbcom_render_inclusions_tab',
		);
	}

	if ( ! empty( get_field( 'related_resources' ) ) ) {
		$tabs['related_resources'] = array(
			'title'    => __( 'Related Resources', 'tpd-core' ),
			'priority' => 12,
			'callback' => 'wbcom_render_related_resources_tab',
		);
	}

	return $tabs;
}
add_filter( 'woocommerce_product_tabs', 'wbcom_remove_additional_info', 98 );


function wbcom_render_curriculum_tab() {
	the_field( 'curriculum' );
}

function wbcom_render_inclusions_tab() {
	the_field( 'inclusions' );
}

function wbcom_render_related_resources_tab() {
	the_field( 'related_resources' );
}


function wbcom_vendor_sold_by( $vendor_id ) {
	$sold_by = '';
	if ( WCV_Vendors::is_vendor( $vendor_id ) ) {

		$store_icon_src = wp_get_attachment_image_src(
			get_user_meta( $vendor_id, '_wcv_store_icon_id', true ),
			array( 50, 50 )
		);
		$store_icon     = '<img src="' . get_avatar_url( $vendor_id, array( 'size' => 50 ) ) . '" alt="" class="store-icon" />';

		// see if the array is valid
		if ( is_array( $store_icon_src ) ) {
			$store_icon = '<img src="' . $store_icon_src[0] . '" alt="" class="store-icon" />';
		}

		$sold_by = sprintf( '<a href="%s">%s %s</a>', WCV_Vendors::get_vendor_shop_page( $vendor_id ), $store_icon, WCV_Vendors::get_vendor_sold_by( $vendor_id ) );
	} else {
		$sold_by = get_bloginfo( 'name' );
	}

	return $sold_by;
}

/**
 * Format store address.
 *
 * @param $vendor_id
 *
 * @since 1.6.2
 *
 * @return string
 */
function wbcom_format_store_address( $vendor_id ) {
	$vendor_state       = get_user_meta( $vendor_id, '_wcv_company_state', true );
	$vendor_country     = get_user_meta( $vendor_id, '_wcv_store_country', true );
	$store_address_args = apply_filters(
		'wcv_format_store_address_args',
		array(
			'address1' => get_user_meta( $vendor_id, '_wcv_store_address1', true ),
			'city'     => get_user_meta( $vendor_id, '_wcv_store_city', true ),
			'state'    => get_user_meta( $vendor_id, '_wcv_store_state', true ),
			'state'    => ! empty( $vendor_state ) ? WC()->countries->get_states( $vendor_country )[ $vendor_state ] : '',
			'postcode' => get_user_meta( $vendor_id, '_wcv_store_postcode', true ),
			'country'  => WC()->countries->countries[ $vendor_country ],
		),
		$vendor_id
	);

	$store_address_args = array_filter( $store_address_args );
	$store_address_text = implode( ', ', $store_address_args );

	return apply_filters( 'wcv_format_store_address_output', $store_address_text, $vendor_id, $store_address_args );
}

function wbcom_get_dokan_seller_avatar( $seller_id ) {

	$store_user    = dokan()->vendor->get( $seller_id );

	if ( strpos( $store_user->get_avatar(),'gravatar') !== false ) {
    	$store_avatar = wp_get_attachment_image_src( get_user_meta( $store_user->get_id(), '_wcv_store_icon_id', true ), array( 150, 150 ) );

	    if(  empty( $store_avatar  ) || ! is_array( $store_avatar ) ) {
	        $store_avatar = $store_user->get_avatar();
	    }else{
	        $store_avatar = $store_avatar[0];
	    }

	} else {
	    $store_avatar = $store_user->get_avatar();
	}

	return $store_avatar;

}


// Store name and Logo

add_shortcode('store_name', 'wbcom_display_vendor_name_shop');
function wbcom_display_vendor_name_shop() {
	global $product;

	$seller     = get_post_field( 'post_author', get_the_ID() );
	$author     = get_user_by( 'id', $seller );
	$store_user    = dokan()->vendor->get( $author->ID );
	$store_info    = $store_user->get_shop_info();
	
	if ( ! empty( $store_info['store_name'] ) ) {
		$store_icon = '<img src="' . esc_url( wbcom_get_dokan_seller_avatar( $store_user->get_id() ) ) . '" alt="'. $store_user->get_store_name() .'" class="store-icon" />';
		$sold_by = sprintf( '<a href="%s" class="vendor-store-icon">%s %s</a>', dokan_get_store_url( $author->ID ), $store_icon, $store_user->get_store_name() );
	} else {
		$sold_by = get_bloginfo( 'name' );
	}
	
	return ' <div class="store-name-logo">' . $sold_by . '</div>';
}
