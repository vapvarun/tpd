<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://wbcomdesigns.com/
 * @since      1.0.0
 *
 * @package    Tpd_Core
 * @subpackage Tpd_Core/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Tpd_Core
 * @subpackage Tpd_Core/public
 * @author     WBCOM Team <admin@wbcomdesigns.com>
 */
class Tpd_Core_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of the plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		add_filter( 'body_class' , array( $this, 'wbcom_add_body_class' ) );
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Tpd_Core_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Tpd_Core_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/tpd-core-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Tpd_Core_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Tpd_Core_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/tpd-core-public.js', array( 'jquery' ), time(), true );
		wp_localize_script(
			$this->plugin_name,
			'tpd_core',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
			)
		);

	}

	public function wbcom_add_body_class( $classes ) {
		$user = wp_get_current_user();
		$roles = ( array ) $user->roles;
		if( array_intersect( array( 'seller' ), $roles ) ) {
			$classes[] = 'wbcom-dokan-vendor';
		}
		return $classes;
	}

	/**
	 * Remove hooks form single product page for single product customization
	 *
	 * @return void
	 */
	public function wbcom_remove_single_page_hooks() {
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
		remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
		remove_action( 'woocommerce_before_main_content', array( 'WCV_Vendor_Shop', 'vendor_main_header' ), 20 );
		remove_action( 'woocommerce_before_single_product', array( 'WCV_Vendor_Shop', 'vendor_mini_header' ), 12 );
		remove_action( 'woocommerce_before_main_content', array( 'WCV_Vendor_Shop', 'shop_description' ), 30 );
		remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
		remove_action( 'woocommerce_archive_description', 'woocommerce_taxonomy_archive_description', 10 );
		remove_action( 'woocommerce_archive_description', 'woocommerce_product_archive_description', 10 );
		remove_filter( 'term_description', 'wp_kses_post' );
		remove_filter( 'pre_term_description', 'wp_filter_post_kses' );
	}

	public function wbcom_remove_comment_sinle_posts( $open, $post_id ) {

		if ( 'post' === get_post_type( $post_id ) ) {
			$open = false;
		} else {
			$open = true;
		}
		return $open;
	}


	public function wbcom_display_product_attributes() {
		global $product;
		?>
		<?php if ( wc_product_sku_enabled() && ( $product->get_sku() || $product->is_type( 'variable' ) ) ) : ?>
			<span class="sku_wrapper"><?php esc_html_e( 'SKU:', 'woocommerce' ); ?> <span class="sku"><?php echo ( $sku = $product->get_sku() ) ? $sku : esc_html__( 'N/A', 'woocommerce' ); ?></span></span>
		<?php endif; ?>
		<?php

		wc_display_product_attributes( $product );
		echo wc_get_product_tag_list( $product->get_id(), ', ', '<span class="tagged_as">' . _n( 'Tag:', 'Tags:', count( $product->get_tag_ids() ), 'woocommerce' ) . ' ', '</span>' );
	}

	public function wbcom_single_product_sold_by() {
		global $product;
		$seller     = get_post_field( 'post_author', $product->get_id());
		$author     = get_user_by( 'id', $seller );
		$vendor     = dokan()->vendor->get( $seller );
		$store_info = dokan_get_store_info( $author->ID );
		

		if ( ! empty( $store_info['store_name'] ) ) {
			?>
			<span class="details">
					<?php printf( 'Sold by: <a href="%s">%s</a>', $vendor->get_shop_url(), $vendor->get_store_name() ); ?>
				</span>
			<?php
		}
	}

	public function wbcom_display_seller_name_shop() {
		global $product;

		$seller     = get_post_field( 'post_author', $product->get_id() );
		$author     = get_user_by( 'id', $seller );
		$store_user    = dokan()->vendor->get( $author->ID );
		$store_info    = $store_user->get_shop_info();

		if ( ! empty( $store_info['store_name'] ) ) {
			$store_icon = '<img src="' . esc_url( wbcom_get_dokan_seller_avatar( $store_user->get_id() ) ) . '" alt="'. $store_user->get_store_name() .'" class="store-icon" />';
			$sold_by = sprintf( '<a href="%s" class="vendor-store-icon">%s %s</a>', dokan_get_store_url( $author->ID ), $store_icon, $store_user->get_store_name() );
		} else {
			$sold_by = get_bloginfo( 'name' );
		}

		echo $sold_by;
	}

	/**
	 * List vendors products
	 *
	 * @param  integer $vendor_id Vendor's ID
	 */
	public function wbcom_wc_vendors_vendor_products( $vendor_id ) {
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


	/**
	 * Load WC Vendor Template files
	 *
	 * @param  string $template
	 * @param  string $template_name
	 * @param  string $template_path
	 * @return string
	 */
	public function wbcom_wcvendors_plugin_template( $template, $template_name, $template_path ) {
		$_template = $template;
		if ( ! $template_path ) {
			$template_path = 'wc-vendors/';
		}

		$plugin_path = TPDCORE_PATH . 'templates/' . $template_path;

		// Look within passed path within the theme - this is priority.
		$template = locate_template(
			array(
				trailingslashit( $template_path ) . $template_name,
				$template_name,
			)
		);

		if ( ! $template && file_exists( $plugin_path . $template_name ) ) {
			$template = $plugin_path . $template_name;
		}

		if ( ! $template ) {
			$template = $_template;
		}

		return $template;
	}


	/**
	 * Header for single product page.
	 */
	public function wbcom_wcvendors_render_store_header_on_top() {
		// Set header on single vendor papge.
		if ( dokan_is_store_page() ) {
			$store_user  = dokan()->vendor->get( get_query_var( 'author' ) );
			$vendor_id   = $store_user->get_id();
			$vendor_meta = array_map(
				function ( $a ) {
					return $a[0];
				},
				get_user_meta( $vendor_id )
			);

			do_action( 'wbcom_wc_vendors_before_main_header', $vendor_id );

			wc_get_template(
				'store-header.php',
				array(
					'vendor_id'   => $vendor_id,
					'vendor_meta' => $vendor_meta,
				),
				'wc-vendors/store/',
				TPDCORE_PATH . 'templates/wc-vendors/store/'
			);
		}
	}

	public function wbcom_render_woocommerce_breadcrumb() {
		
			echo '<div class="wbcom-page-breadcrumb">';
				woocommerce_breadcrumb();
			echo '</div>';
	}


	public function wbcom_render_archive_description() {
		woocommerce_taxonomy_archive_description();
		woocommerce_product_archive_description();
	}

	/**
	 * get product category thumbnail
	 *
	 * @return [type] [description]
	 */
	public function wbcom_render_woocommerce_category_image() {
		if ( is_product_category() ) {
			global $wp_query;
			$cat = $wp_query->get_queried_object();

			$thumbnail_id = get_term_meta( $cat->term_id, 'thumbnail_id', true );
			$image        = wp_get_attachment_url( $thumbnail_id );
			if ( $image ) {
				echo '<img src="' . esc_url( $image ) . '" alt="' . esc_attr( $cat->name ) . '" />';
			} else {
				$parentcats = get_ancestors( $cat->term_id, 'product_cat' );

				for ( $i = 0; $i <= count( $parentcats ); $i++ ) {
					$parent_thumbnail_id = get_term_meta( $parentcats[ $i ], 'thumbnail_id', true );
					$parent_image        = wp_get_attachment_url( $parent_thumbnail_id );
					if ( $parent_image ) {
						echo '<img src="' . esc_url( $parent_image ) . '" alt="' . esc_attr( $cat->name ) . '" />';
						break;
					}
				}
			}
		}
	}

	public function wbcom_render_extra_register_fields() {
		?>
	   <p class="form-row form-row-first">
		   <label for="reg_billing_first_name"><?php _e( 'First name', 'woocommerce' ); ?><span class="required">*</span></label>
		   <input type="text" class="input-text" name="billing_first_name" id="reg_billing_first_name" value="<?php ! empty( $_POST['billing_first_name'] ) ? esc_attr_e( $_POST['billing_first_name'] ) : ''; ?>"/>
	   </p>
	   <p class="form-row form-row-last">
		   <label for="reg_billing_last_name"><?php _e( 'Last name', 'woocommerce' ); ?><span class="required">*</span></label>
		   <input type="text" class="input-text" name="billing_last_name" id="reg_billing_last_name" value="<?php ! empty( $_POST['billing_last_name'] ) ? esc_attr_e( $_POST['billing_last_name'] ) : ''; ?>"/>
	   </p>
	   <div class="clear"></div>
		<?php
	}

	/**
	 * Alter HTML for ratings.
	 *
	 * @since  3.0.0
	 * @param  float $rating Rating being shown.
	 * @param  int   $count  Total number of ratings.
	 * @return string
	 */
	public function wbcom_shop_product_rating( $html, $rating, $count ) {
		if ( 0 < $rating ) {
			global $product;

			if( ! empty( $product ) )  {
				$review_count = $product->get_review_count( 'view' );

				/* translators: %s: rating */
				$label = sprintf( __( 'Rated %s out of 5', 'woocommerce' ), $rating );
				$html  = '<div class="product-star-rating"><div class="star-rating" role="img" aria-label="' . esc_attr( $label ) . '">' . wc_get_star_rating_html( $rating, $count ) . '</div><div class="review-count">(' . $review_count . ')</div>' . '</div>';
			}
		}
		return $html;
	}


	public function wbcom_display_quantity_plus() {
		echo '<button type="button" class="plus" >+</button>';
	}

	public function wbcom_display_quantity_minus() {
		echo '<button type="button" class="minus" >-</button>';
	}

	public function wbcom_add_cart_quantity_plus_minus() {
		if ( ! is_product() && ! is_cart() ) {
			return;
		}

		wc_enqueue_js(
			"

			 $('form.cart,form.woocommerce-cart-form').on( 'click', 'button.plus, button.minus', function() {

					var qty = $( this ).parent( '.quantity' ).find( '.qty' );
					var val = parseFloat(qty.val());
					var max = parseFloat(qty.attr( 'max' ));
					var min = parseFloat(qty.attr( 'min' ));
					var step = parseFloat(qty.attr( 'step' ));

					if ( $( this ).is( '.plus' ) ) {
						 if ( max && ( max <= val ) ) {
								qty.val( max );
						 } else {
								qty.val( val + step );
						 }
					} else {
						 if ( min && ( min >= val ) ) {
								qty.val( min );
						 } else if ( val > 1 ) {
								qty.val( val - step );
						 }
					}

			 });

		"
		);
	}

	public function wbcom_add_to_cart_text( $text ) {
		global $product;
		if ( $product->is_type( 'variable' ) ) {
			$text = $product->is_purchasable() ? __( 'Read more', 'woocommerce' ) : '';
		}
		return $text;

	}

	public function wbcom_remove_actions() {
		remove_action( 'template_redirect', array( 'WCVM_Integration', 'prevent_save_settings' ), 5 );
	}

	public function wbcom_save_product_cat_description( $term_id, $tt_id = '', $taxonomy = '' ) {
		if ( isset( $_POST['description'] ) && 'product_cat' === $taxonomy ) {
			update_woocommerce_term_meta( $term_id, 'description', $_POST['description'] );
		}
	}

	public function wbcom_vendor_product_bulk_add_category() {
		if( false === get_option( 'seller_category_added' ) && 'yes' !== get_option( 'seller_category_added' ) ) {
			$vendors = get_users( array( 'fields' => array( 'id' ) ) );

			if( ! empty( $vendors ) ) {
				foreach( $vendors as $vendor ) {
					if( dokan_is_user_seller( $vendor->ID ) ) {
						$products = get_posts(
							array(
								'post_type' => 'product',
								'post_status' => 'publish',
								'numberposts' => -1,
								'author' => $vendor->ID
							)
						);

						if( ! empty( $products ) ) {
							foreach( $products as $product ) {
								wp_set_object_terms( $product->ID, 'sellers', 'product_cat', true );
							}
						}
					}
				}
			}
			update_option( 'seller_category_added', 'yes', false );
		}

	}


	public function wbcom_vendor_product_add_category( $product_id, $product_data ) {
		if( ! empty( $product_id ) ) {
			$product_author = get_post_field( 'post_author', $product_id );
			if( dokan_is_user_seller( $product_author ) ) {
				wp_set_object_terms( $product_id, 'sellers', 'product_cat', true );

			}
		}

	}

}
