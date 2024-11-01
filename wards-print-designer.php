<?php
/*
Plugin Name: Ward's Print Designer Lite
Plugin URI: https://www.wardswebdesigns.com
Description: Add a print area of any size and location on your product images. Customers can add and customize text to create a print directly on your product image. To enable customers to add images to be added please purchase, download, and install the full version from <a href="https://wardswebdesigns.com/" >wardswebdesigns.com</a>
Version: 1.0.0
Author: Ward's Web Designs
Author URI: https://wardswebdesigns.com/
*/

//register plugin on plugin activation from wordpress plugin menu
register_activation_hook( __FILE__, 'wbpd_woocommerce_activation' );
function wbpd_woocommerce_activation() {
	set_transient( 'wc_pao_activation_notice', true, 60 );
	set_transient( 'wc_pao_pre_wc_30_notice', true, 60 );
}

//calling function to initialize the plugin
//initializing the plugin
add_action( 'plugins_loaded', 'wbpd_woocommerce_init', 9 );
function wbpd_woocommerce_init() {
	load_plugin_textdomain( 'woocommerce-product-addons', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
	//checking to make sure WooCommerce is installed and activated
	if ( ! class_exists( 'WooCommerce' ) ) {
			add_action( 'admin_notices', 'wbpd_woocommerce_class_missing_notice' );
			return;
	}
}

//display error if woocommerce is not installed when activating ward's product customizer plugin
function wbpd_woocommerce_class_missing_notice() {
	/* translators: %s WC download URL link. */
	echo '<div class="error"><p><strong>' . sprintf( esc_html__( "Ward's Print Designer requires WooCommerce to be installed and active. You can download %s here.", 'woocommerce-product-addons' ), '<a href="https://woocommerce.com/" target="_blank">WooCommerce</a>' ) . '</strong></p></div>';
}

//step 1: product setup
// Adding Print Designer Setup Tab to woocommerce product admin menu
add_filter( 'woocommerce_product_data_tabs', 'wbpd_product_data_tab' );
function wbpd_product_data_tab( $tabs ) {
	$tabs['wbpd'] = array(
		'label'    => "Ward's Print Designer Setup",
		'target'   => 'wbpd_product_data',
	);
	return $tabs;
}

// Adding print-area-setup.js and input fields to products in woocommerce product admin menu
add_action( 'woocommerce_product_data_panels', 'wbpd_product_data_fields', 10, 1 );
function wbpd_product_data_fields($post) {
	echo '<div id="wbpd_product_data" class="panel woocommerce_options_panel hidden">';
		$featured_image_url = get_the_post_thumbnail_url();
		echo '<script>var productFeaturedImageSrc = "'.esc_html($featured_image_url).'";</script>';
		$product = new WC_product(get_the_ID());
		$attachmentIds = $product->get_gallery_image_ids();
		echo '<script>var productGalleryImages = [productFeaturedImageSrc];</script>';
		
		foreach( $attachmentIds as $attachmentID ) {
			$URLholder = wp_get_attachment_image_url($attachmentID, 'full', false);
			// echo '<script>console.log("image url=='.$URLholder.'");</script>';
			echo '<script>productGalleryImages.push("'.esc_html($URLholder).'");</script>';
		}
		
		woocommerce_wp_checkbox( array(
			'id'      => 'is_product_customizable',
			'value'   => get_post_meta( get_the_ID(), 'is_product_customizable', true ),
			'label'   => 'Check if this product has one or more customizable print areas',
			'desc_tip' => true,
			'description' => 'Check if this product has one or more customizable print areas',
		) );
		
		for ($x = 0; $x <= count($attachmentIds); $x++) {
			echo '<h1 id="contentHeader_'.esc_html($x).'">Click For Print Area Settings</h1>';
			echo '<div id="print_area_settings_'.esc_html($x).'" style="display:none">';
			
			// is image customziable checkbox
			if (get_post_meta( get_the_ID(), 'is_product_customizable', true ) == "yes") {
				woocommerce_wp_checkbox( array(
					'id'      => 'is_image_customizable_'.$x.'',
					'value'   => get_post_meta( get_the_ID(), 'is_image_customizable_'.$x.'', true ),
					'label'   => 'This image is customizable',
					'desc_tip' => true,
					'description' => 'If this image has a customizable print area',
				) );
			} else {
				woocommerce_wp_checkbox( array(
					'id'      => 'is_image_customizable_'.$x.'',
					'value'   => get_post_meta( get_the_ID(), 'is_image_customizable_'.$x.'', true ),
					'label'   => 'This image is customizable',
					'desc_tip' => true,
					'description' => 'If this image has a customizable print area',
					'custom_attributes' => array(
						'disabled' => 'disabled',
					)
				) );
			}
			
			// print area height value
			// print area width value
			if (get_post_meta( get_the_ID(), 'is_product_customizable', true ) == "yes" && get_post_meta( get_the_ID(), 'is_image_customizable_'.$x.'', true ) == "yes") {
				woocommerce_wp_text_input( array(
					'id'          => 'print_area_height_value_'.$x.'',
					'value'       => get_post_meta( get_the_ID(), 'print_area_height_value_'.$x.'', true ),
					'label'       => 'Print Area Hight - % of picture',
					'desc_tip'    => true,
					'description' => 'Print Area Hight - % of picture',
					'type'        => 'number',
					'custom_attributes' => array(
						'step' => '0.1',
						'min'  => '0',
						'max'  => '100',
					)
				) );
				woocommerce_wp_text_input( array(
					'id'          => 'print_area_width_value_'.$x.'',
					'value'       => get_post_meta( get_the_ID(), 'print_area_width_value_'.$x.'', true ),
					'label'       => 'Print Area Width - % of picture',
					'desc_tip'    => true,
					'description' => 'Print Area Width - % of picture',
					'type'        => 'number',
					'custom_attributes' => array(
						'step' => '0.1',
						'min'  => '0',
						'max'  => '100',
					)
				) );
			} else {
				woocommerce_wp_text_input( array(
					'id'          => 'print_area_height_value_'.$x.'',
					'value'       => get_post_meta( get_the_ID(), 'print_area_height_value_'.$x.'', true ),
					'label'       => 'Print Area Hight - % of picture',
					'desc_tip'    => true,
					'description' => 'Print Area Hight - % of picture',
					'type'        => 'number',
					'custom_attributes' => array(
						'step' => '0.1',
						'min'  => '0',
						'max'  => '100',
						'readonly' => 'readonly',
					)
				) );
				woocommerce_wp_text_input( array(
					'id'          => 'print_area_width_value_'.$x.'',
					'value'       => get_post_meta( get_the_ID(), 'print_area_width_value_'.$x.'', true ),
					'label'       => 'Print Area Width - % of picture',
					'desc_tip'    => true,
					'description' => 'Print Area Width - % of picture',
					'type'        => 'number',
					'custom_attributes' => array(
						'step' => '0.1',
						'min'  => '0',
						'max'  => '100',
						'readonly' => 'readonly',
					)
				) );
			}

			// align center vertical checkbox
			if (get_post_meta( get_the_ID(), 'is_product_customizable', true ) == "yes" && get_post_meta( get_the_ID(), 'is_image_customizable_'.$x.'', true ) == "yes") {
				woocommerce_wp_checkbox( array(
					'id'      => 'align_center_vertical_'.$x.'',
					'value'   => get_post_meta( get_the_ID(), 'align_center_vertical_'.$x.'', true ),
					'label'   => 'Vertically Align Print Area',
					'desc_tip' => true,
					'description' => 'Vertically Align Print Area',
				) );
			} else {
				woocommerce_wp_checkbox( array(
					'id'      => 'align_center_vertical_'.$x.'',
					'value'   => get_post_meta( get_the_ID(), 'align_center_vertical_'.$x.'', true ),
					'label'   => 'Vertically Align Print Area',
					'desc_tip' => true,
					'description' => 'Vertically Align Print Area',
					'custom_attributes' => array(
						'disabled' => 'disabled',
					)
				) );
			}

			// print area top value
			if (get_post_meta( get_the_ID(), 'is_product_customizable', true ) == "yes" && get_post_meta( get_the_ID(), 'is_image_customizable_'.$x.'', true ) == "yes" && get_post_meta( get_the_ID(), 'align_center_vertical_'.$x.'', true ) != "yes") {
				woocommerce_wp_text_input( array(
					'id'          => 'print_area_top_value_'.$x.'',
					'value'       => get_post_meta( get_the_ID(), 'print_area_top_value_'.$x.'', true ),
					'label'       => 'Top side spacing - % of picture height',
					'desc_tip'    => true,
					'description' => 'Top side spacing - % of picture height',
					'type'        => 'number',
					'custom_attributes' => array(
						'step' => '0.1',
						'min'  => '0',
						'max'  => '100',
					)
				) );
			} else {
				woocommerce_wp_text_input( array(
					'id'          => 'print_area_top_value_'.$x.'',
					'value'       => get_post_meta( get_the_ID(), 'print_area_top_value_'.$x.'', true ),
					'label'       => 'Top side spacing - % of picture height',
					'desc_tip'    => true,
					'description' => 'Top side spacing - % of picture height',
					'type'        => 'number',
					'custom_attributes' => array(
						'step' => '0.1',
						'min'  => '0',
						'max'  => '100',
						'readonly' => 'readonly',
					)
				) );
			}
			
			// align center horizontal checkbox
			if (get_post_meta( get_the_ID(), 'is_product_customizable', true ) == "yes" && get_post_meta( get_the_ID(), 'is_image_customizable_'.$x.'', true ) == "yes") {
				woocommerce_wp_checkbox( array(
					'id'      => 'align_center_horizontal_'.$x.'',
					'value'   => get_post_meta( get_the_ID(), 'align_center_horizontal_'.$x.'', true ),
					'label'   => 'Horzontally Align Print Area',
					'desc_tip' => true,
					'description' => 'Horzontally Align Print Area',
				) );
			} else {
				woocommerce_wp_checkbox( array(
					'id'      => 'align_center_horizontal_'.$x.'',
					'value'   => get_post_meta( get_the_ID(), 'align_center_horizontal_'.$x.'', true ),
					'label'   => 'Horzontally Align Print Area',
					'desc_tip' => true,
					'description' => 'Horzontally Align Print Area',
					'custom_attributes' => array(
						'disabled' => 'disabled',
					)
				) );
			}

			// print area left value
			if (get_post_meta( get_the_ID(), 'is_product_customizable', true ) == "yes" && get_post_meta( get_the_ID(), 'is_image_customizable_'.$x.'', true ) == "yes" && get_post_meta( get_the_ID(), 'align_center_horizontal_'.$x.'', true ) != "yes") {
				woocommerce_wp_text_input( array(
					'id'          => 'print_area_left_value_'.$x.'',
					'value'       => get_post_meta( get_the_ID(), 'print_area_left_value_'.$x.'', true ),
					'label'       => 'Left side spacing - % of picture width',
					'desc_tip'    => true,
					'description' => 'Left side spacing - % of picture width',
					'type'        => 'number',
					'custom_attributes' => array(
						'step' => '0.1',
						'min'  => '0',
						'max'  => '100',
					)
				) );
			} else {
				woocommerce_wp_text_input( array(
					'id'          => 'print_area_left_value_'.$x.'',
					'value'       => get_post_meta( get_the_ID(), 'print_area_left_value_'.$x.'', true ),
					'label'       => 'Left side spacing - % of picture width',
					'desc_tip'    => true,
					'description' => 'Left side spacing - % of picture width',
					'type'        => 'number',
					'custom_attributes' => array(
						'step' => '0.1',
						'min'  => '0',
						'max'  => '100',
						'readonly' => 'readonly',
					)
				) );
			}

			// default text color
			// border color
			if (get_post_meta( get_the_ID(), 'is_product_customizable', true ) == "yes" && get_post_meta( get_the_ID(), 'is_image_customizable_'.$x.'', true ) == "yes") {
				woocommerce_wp_text_input( array(
					'id'          => 'default_text_color_'.$x.'',
					'value'       => get_post_meta( get_the_ID(), 'default_text_color_'.$x.'', true ),
					'label'       => 'Default color of text',
					'desc_tip'    => true,
					'description' => 'Default color of text',
					'type'        => 'color',
				) );
				woocommerce_wp_text_input( array(
					'id'          => 'border_color_'.$x.'',
					'value'       => get_post_meta( get_the_ID(), 'border_color_'.$x.'', true ),
					'label'       => 'Print Area Border Color',
					'desc_tip'    => true,
					'description' => 'Print Area Border Color description',
					'type'        => 'color',
				) );
			} else {
				woocommerce_wp_text_input( array(
					'id'          => 'default_text_color_'.$x.'',
					'value'       => get_post_meta( get_the_ID(), 'default_text_color_'.$x.'', true ),
					'label'       => 'Default color of text',
					'desc_tip'    => true,
					'description' => 'Default color of text',
					'type'        => 'color',
					'custom_attributes' => array(
						'readonly' => 'readonly',
					)
				) );
				woocommerce_wp_text_input( array(
					'id'          => 'border_color_'.$x.'',
					'value'       => get_post_meta( get_the_ID(), 'border_color_'.$x.'', true ),
					'label'       => 'Print Area Border Color',
					'desc_tip'    => true,
					'description' => 'Print Area Border Color description',
					'type'        => 'color',
					'custom_attributes' => array(
						'readonly' => 'readonly',
					)
				) );
			}
			
			echo '<script>
			jQuery("#contentHeader_'.esc_html($x).'").click(function() {
				if(jQuery("#print_area_settings_'.esc_html($x).'").css("display") !== "none") {;
					jQuery("#print_area_settings_'.esc_html($x).'").css("display", "none");
				} else {
					jQuery("#print_area_settings_'.esc_html($x).'").css("display", "block");
				}
			});
			</script>';
			echo '</div>';
			echo '<div id="print-area-setup_'.esc_html($x).'" class="print-area-setup"></div>';
		}
		wp_enqueue_script('pasjs', plugin_dir_url(__FILE__).'print-area-setup.js');
	echo '</div>';
}

// saving product canvas settings/metadata to product metadata on product update
add_action( 'woocommerce_process_product_meta', 'wbpd_save_meta_fields', 10, 2 );
function wbpd_save_meta_fields( $id, $post ){
	$product = new WC_product(get_the_ID());
	$attachmentIds = $product->get_gallery_image_ids();
	update_post_meta( $id, 'is_product_customizable', sanitize_text_field($_POST['is_product_customizable']) );
	for ($x = 0; $x <= count($attachmentIds); $x++) {
		update_post_meta( $id, 'is_image_customizable_'.$x.'', sanitize_text_field($_POST['is_image_customizable_'.$x.'']) );

		update_post_meta( $id, 'align_center_vertical_'.$x.'', sanitize_text_field($_POST['align_center_vertical_'.$x.'']) );
		update_post_meta( $id, 'align_center_horizontal_'.$x.'', sanitize_text_field($_POST['align_center_horizontal_'.$x.'']) );

		update_post_meta( $id, 'print_area_height_value_'.$x.'', sanitize_text_field($_POST['print_area_height_value_'.$x.'']) );
		update_post_meta( $id, 'print_area_width_value_'.$x.'', sanitize_text_field($_POST['print_area_width_value_'.$x.'']) );
		update_post_meta( $id, 'print_area_top_value_'.$x.'', sanitize_text_field($_POST['print_area_top_value_'.$x.'']) );
		update_post_meta( $id, 'print_area_left_value_'.$x.'', sanitize_text_field($_POST['print_area_left_value_'.$x.'']) );
		
		update_post_meta( $id, 'default_text_color_'.$x.'', sanitize_text_field($_POST['default_text_color_'.$x.'']) );
		update_post_meta( $id, 'border_color_'.$x.'', sanitize_text_field($_POST['border_color_'.$x.'']) );
	}	
}

//step 2: product customization
// Adding print-designer.js to product images for customers to add images too
// loads canvas settings from product in wordpress database
// and creates javascript variables to be used in React
add_action( 'woocommerce_before_add_to_cart_button', 'wbpd_add_product_customizer', 9, 1 );
function wbpd_add_product_customizer( $item ){
	
	echo '<script>var isProductCustomizable = "";</script>';

	echo '<script>var isImageCustomizableArray = [];</script>';

	echo '<script>var canvasIsVerticalAlignArray = [];</script>';
	echo '<script>var canvasIsHorizontalAlignArray = [];</script>';

	echo '<script>var canvasHeightPercentArray = [];</script>';
	echo '<script>var canvasWidthPercentArray = [];</script>';
	echo '<script>var canvasTopPercentArray = [];</script>';
	echo '<script>var canvasLeftPercentArray = [];</script>';

	echo '<script>var defaultTextColorArray = [];</script>';
	echo '<script>var borderColorArray = [];</script>';
	
	$is_product_customizable_holder = get_post_meta( get_the_ID(), 'is_product_customizable', true );
	echo '<script>isProductCustomizable="'.esc_html($is_product_customizable_holder).'"</script>';

	//if product is customizable, populate javascript variables
	if( $is_product_customizable_holder == 'yes' ) {
		$product = new WC_product(get_the_ID());
		$attachmentIds = $product->get_gallery_image_ids();
		for ($x = 0; $x <= count($attachmentIds); $x++) {
			$is_image_customizable_value_holder = get_post_meta( get_the_ID(), 'is_image_customizable_'.$x.'', true );

			$align_center_vertical_holder = get_post_meta( get_the_ID(), 'align_center_vertical_'.$x.'', true );
			$align_center_horizontal_holder = get_post_meta( get_the_ID(), 'align_center_horizontal_'.$x.'', true );

			//if set to empty string, change to 0
			if (get_post_meta( get_the_ID(), 'print_area_height_value_'.$x.'', true ) !== "") {
				$print_area_height_value_holder = get_post_meta( get_the_ID(), 'print_area_height_value_'.$x.'', true );
			} else {
				$print_area_height_value_holder = 0;
			}
			//if set to empty string, change to 0
			if (get_post_meta( get_the_ID(), 'print_area_width_value_'.$x.'', true ) !== "") {
				$print_area_width_value_holder = get_post_meta( get_the_ID(), 'print_area_width_value_'.$x.'', true );
			} else {
				$print_area_width_value_holder = 0;
			}

			$print_area_top_value_holder = get_post_meta( get_the_ID(), 'print_area_top_value_'.$x.'', true );
			$print_area_left_value_holder = get_post_meta( get_the_ID(), 'print_area_left_value_'.$x.'', true );
			
			$default_text_color_holder = get_post_meta( get_the_ID(), 'default_text_color_'.$x.'', true );
			$border_color_value_holder = get_post_meta( get_the_ID(), 'border_color_'.$x.'', true );

			// echo '<script>console.log("is_image_customizable_value_holder=='.$is_image_customizable_value_holder.'");</script>';

			// echo '<script>console.log("align_center_vertical_holder=='.$align_center_vertical_holder.'");</script>';
			// echo '<script>console.log("align_center_horizontal_holder=='.$align_center_horizontal_holder.'");</script>';

			// echo '<script>console.log("print_area_height_value_holder=='.$print_area_height_value_holder.'");</script>';
			// echo '<script>console.log("print_area_width_value_holder=='.$print_area_width_value_holder.'");</script>';
			// echo '<script>console.log("print_area_top_value_holder=='.$print_area_top_value_holder.'");</script>';
			// echo '<script>console.log("print_area_left_value_holder=='.$print_area_left_value_holder.'");</script>';

			// echo '<script>console.log("default_text_color_holder=='.$default_text_color_holder.'");</script>';
			// echo '<script>console.log("border_color_value_holder=='.$border_color_value_holder.'");</script>';
			
			echo '<script>isImageCustomizableArray.push("'.esc_html($is_image_customizable_value_holder).'")</script>';

			echo '<script>canvasIsVerticalAlignArray.push("'.esc_html($align_center_vertical_holder).'")</script>';
			echo '<script>canvasIsHorizontalAlignArray.push("'.esc_html($align_center_horizontal_holder).'")</script>';

			echo '<script>canvasHeightPercentArray.push('.esc_html($print_area_height_value_holder).')</script>';
			echo '<script>canvasWidthPercentArray.push('.esc_html($print_area_width_value_holder).')</script>';
			echo '<script>canvasTopPercentArray.push('.esc_html($print_area_top_value_holder).')</script>';
			echo '<script>canvasLeftPercentArray.push('.esc_html($print_area_left_value_holder).')</script>';
			
			echo '<script>defaultTextColorArray.push("'.esc_html($default_text_color_holder).'")</script>';
			echo '<script>borderColorArray.push("'.esc_html($border_color_value_holder).'")</script>';

			echo '<div id="print-designer_'.esc_html($x).'"></div>';
		}
		
		//creating arry of image url from featured product image and all additional product images
		$productFeaturedImageSrc = get_the_post_thumbnail_url();
		// echo '<script>console.log("productFeaturedImageSrc=='.$productFeaturedImageSrc.'");</script>';
		echo '<script>var productFeaturedImageSrc = "'.esc_html($productFeaturedImageSrc).'";</script>';
		echo '<script>var productGalleryImages = [productFeaturedImageSrc];</script>';
		foreach ( $attachmentIds as $attachmentID ) {
			$URLholder = wp_get_attachment_image_url($attachmentID, 'full', false);
			// echo '<script>console.log("image url=='.$URLholder.'");</script>';
			echo '<script>productGalleryImages.push("'.esc_html($URLholder).'");</script>';
		}

		// echo '<script>console.log("from php isProductCustomizable==" + isProductCustomizable);</script>';

		// echo '<script>console.log("from php isImageCustomizableArray==" + isImageCustomizableArray);</script>';

		// echo '<script>console.log("from php canvasIsVerticalAlignArray==" + canvasIsVerticalAlignArray);</script>';
		// echo '<script>console.log("from php canvasIsHorizontalAlignArray==" + canvasIsHorizontalAlignArray);</script>';

		// echo '<script>console.log("from php canvasHeightPercentArray==" + canvasHeightPercentArray);</script>';
		// echo '<script>console.log("from php canvasWidthPercentArray==" + canvasWidthPercentArray);</script>';
		// echo '<script>console.log("from php canvasTopPercentArray==" + canvasTopPercentArray);</script>';
		// echo '<script>console.log("from php canvasLeftPercentArray==" + canvasLeftPercentArray);</script>';

		// echo '<script>console.log("from php defaultTextColorArray==" + defaultTextColorArray);</script>';
		// echo '<script>console.log("from php borderColorArray==" + borderColorArray);</script>';
		
		wp_enqueue_script('pdjs', plugin_dir_url(__FILE__).'print-designer.js');
	}
}

// saving json of canvas and original canvas width to cart meta
// also saving canvas settings and border color to cart in case the product settings ever change
add_filter( 'woocommerce_add_cart_item_data', 'wbpd_add_custom_fields_data_as_custom_cart_item_data', 10, 2 );
function wbpd_add_custom_fields_data_as_custom_cart_item_data( $cart_item, $product_id ){
	$product = new WC_product($product_id);
	$attachmentIds = $product->get_gallery_image_ids();

	if ( get_post_meta( $product_id, 'is_product_customizable', true ) == 'yes' ) {
		// is product customizable
		$cart_item['is_product_customizable'] = sanitize_text_field( get_post_meta( $product_id, 'is_product_customizable', true ) );

		for ($x = 0; $x <= count($attachmentIds); $x++) {
				//canvas is canvas customizable setting
				$cart_item['is_image_customizable_'.$x.''] = sanitize_text_field( get_post_meta( $product_id, 'is_image_customizable_'.$x.'', true ) );

				//canvas alignment settings
				$cart_item['align_center_vertical_'.$x.''] = sanitize_text_field( get_post_meta( $product_id, 'align_center_vertical_'.$x.'', true ) );
				$cart_item['align_center_horizontal_'.$x.''] = sanitize_text_field( get_post_meta( $product_id, 'align_center_horizontal_'.$x.'', true ) );
				
				//canvas position settings
				$cart_item['print_area_height_value_'.$x.''] = sanitize_text_field( get_post_meta( $product_id, 'print_area_height_value_'.$x.'', true ) );
				$cart_item['print_area_width_value_'.$x.''] = sanitize_text_field( get_post_meta( $product_id, 'print_area_width_value_'.$x.'', true ) );
				$cart_item['print_area_top_value_'.$x.''] = sanitize_text_field( get_post_meta( $product_id, 'print_area_top_value_'.$x.'', true ) );
				$cart_item['print_area_left_value_'.$x.''] = sanitize_text_field( get_post_meta( $product_id, 'print_area_left_value_'.$x.'', true ) );
				
				//default text color
				$cart_item['default_text_color_'.$x.''] = sanitize_text_field( get_post_meta( $product_id, 'default_text_color_'.$x.'', true ) );

				//canvas border color
				$cart_item['border_color_'.$x.''] = sanitize_text_field( get_post_meta( $product_id, 'border_color_'.$x.'', true ) );

				//canvas_json
				$cart_item['canvas_json_'.$x.''] = sanitize_text_field( $_POST['canvas_json_'.$x.''] );

				//canvas_json
				$cart_item['original_canvas_width_'.$x.''] = sanitize_text_field( $_POST['original_canvas_width_'.$x.''] );
		}
		$cart_item['unique_key'] = md5( microtime().rand() ); // Avoid merging items
		return $cart_item;
	}
}

// displaying saved meta data in cart
// add_filter( 'woocommerce_get_item_data', 'wbpd_display_custom_item_data', 10, 2 );
// function wbpd_display_custom_item_data( $cart_item_data, $cart_item ) {
// 	$product = new WC_product($cart_item['product_id']);
// 	$attachmentIds = $product->get_gallery_image_ids();
// 	// echo '<script>console.log("displaying in cart number of gallery images=='.count($attachmentIds).'");</script>';
// 	for ($x = 0; $x <= count($attachmentIds); $x++) {
// 		$cart_item_data[] = array(
// 			'name' => __( 'Canvas Origina Width', 'woocommerce' ),
// 			'value' =>  $cart_item['original_canvas_width_'.$x.''],
// 		);
// 		$cart_item_data[] = array(
// 			'name' => __( 'JSON', 'woocommerce' ),
// 			'value' =>  $cart_item['canvas_json_'.$x.''],
// 		);
// 	}
//     return $cart_item_data;
// }

// add custom css to wordpress admin menu to hide meta data table
add_action('admin_head', 'wbpd_custom_admin_style');
function wbpd_custom_admin_style() {
  echo '<style>
	.display_meta {
		display: none;
	}
  </style>';
}

// Step 3: View Order
// Save canvas settings from cart to order
add_action( 'woocommerce_checkout_create_order_line_item', 'wbpd_custom_field_update_order_item_meta', 20, 4 );
function wbpd_custom_field_update_order_item_meta( $item, $cart_item_key, $values, $order ) {	
	$product = new WC_product($item['product_id']);
	$attachmentIds = $product->get_gallery_image_ids();

	if( $values['is_product_customizable'] == 'yes' ) {
		// is product customizable
		$item->update_meta_data( 'is_product_customizable',  sanitize_text_field($values['is_product_customizable']) );

		for ($x = 0; $x <= count($attachmentIds); $x++) {
			//saving canvas is canvas customizable setting to order item
			if ( isset( $values['is_image_customizable_'.$x.''] ) ){
				$item->update_meta_data( 'is_image_customizable_'.$x.'',  sanitize_text_field($values['is_image_customizable_'.$x.'']) );
			}

			//saving canvas alignment settings to order item
			if ( isset( $values['align_center_vertical_'.$x.''] ) ){
				$item->update_meta_data( 'align_center_vertical_'.$x.'',  sanitize_text_field($values['align_center_vertical_'.$x.'']) );
			}
			if ( isset( $values['align_center_horizontal_'.$x.''] ) ){
				$item->update_meta_data( 'align_center_horizontal_'.$x.'',  sanitize_text_field($values['align_center_horizontal_'.$x.'']) );
			}
			
			//saving canvas position settings to order item
			if ( isset( $values['print_area_height_value_'.$x.''] ) ){
				$item->update_meta_data( 'print_area_height_value_'.$x.'',  sanitize_text_field($values['print_area_height_value_'.$x.'']) );
			}
			if ( isset( $values['print_area_width_value_'.$x.''] ) ){
				$item->update_meta_data( 'print_area_width_value_'.$x.'',  sanitize_text_field($values['print_area_width_value_'.$x.'']) );
			}
			if ( isset( $values['print_area_top_value_'.$x.''] ) ){
				$item->update_meta_data( 'print_area_top_value_'.$x.'',  sanitize_text_field($values['print_area_top_value_'.$x.'']) );
			}
			if ( isset( $values['print_area_left_value_'.$x.''] ) ){
				$item->update_meta_data( 'print_area_left_value_'.$x.'',  sanitize_text_field($values['print_area_left_value_'.$x.'']) );
			}
			
			//saving default text color to order item
			if ( isset( $values['default_text_color_'.$x.''] ) ){
				$item->update_meta_data( 'default_text_color_'.$x.'',  sanitize_text_field($values['default_text_color_'.$x.'']) );
			}
			//saving canvas border settings to order item
			if ( isset( $values['border_color_'.$x.''] ) ){
				$item->update_meta_data( 'border_color_'.$x.'',  sanitize_text_field($values['border_color_'.$x.'']) );
			}
			
			//canvas_json
			if ( isset( $values['canvas_json_'.$x.''] ) ){
				$item->update_meta_data( 'canvas_json_'.$x.'',  sanitize_text_field($values['canvas_json_'.$x.'']) );
			}

			//original_canvas_width
			if ( isset( $values['original_canvas_width_'.$x.''] ) ){
				$item->update_meta_data( 'original_canvas_width_'.$x.'',  sanitize_text_field($values['original_canvas_width_'.$x.'']) );
			}
		}
	}
}

// default text color and is_product_customizable not needed in React
// creating javascript variables from wordpress order metadata for print-order-viewer.js
add_action( 'woocommerce_after_order_itemmeta', 'wbpd_backend_image_link_after_order_itemmeta', 10, 3 );
function wbpd_backend_image_link_after_order_itemmeta( $order_item_id, $orderItem, $product ) {
	if ($orderItem->get_meta( 'is_product_customizable' )) {
		$product = new WC_product($orderItem['product_id']);
		$productname = $product->get_title();
		// echo '<script>console.log("productname=='.$productname.'");</script>';
		$attachmentIds = $product->get_gallery_image_ids();	

		for ($x = 0; $x <= count($attachmentIds); $x++) {
			if( is_admin() && $orderItem->is_type('line_item') ) {			
				//javascript variable creation
				echo '<script>			
					if(!orderItemID) {var orderItemID = []};
					
					if(!canvasIndex) {var canvasIndex = []};
					
					if(!backgroundSrc) {var backgroundSrc = []};
					
					if(!adminOrderCanvasIsCustomizable) {var adminOrderCanvasIsCustomizable = []};
					
					if(!adminOrderCanvasHeightPercent) {var adminOrderCanvasHeightPercent = []};
					if(!adminOrderCanvasWidthPercent) {var adminOrderCanvasWidthPercent = []};
					if(!adminOrderCanvasTopPercent) {var adminOrderCanvasTopPercent = []};
					if(!adminOrderCanvasLeftPercent) {var adminOrderCanvasLeftPercent = []};
					
					if(!adminOrderCanvasIsVerticalAlign) {var adminOrderCanvasIsVerticalAlign = []};
					if(!adminOrderCanvasIsHorizontalAlign) {var adminOrderCanvasIsHorizontalAlign = []};
					
					if(!adminOrderCanvasBorderColor) {var adminOrderCanvasBorderColor = []};
					if(!canvas_json) {var canvas_json = []};
					if(!original_canvas_width) {var original_canvas_width = []};
				</script>';
				//getting product image url to be used for background of order detail product viewer
				if($x == 0) {
					$image_id  = $product->get_image_id();
					$image_url = wp_get_attachment_image_url( $image_id, 'full' );
				} else {
					$image_url = wp_get_attachment_image_url( $attachmentIds[$x-1], 'full' );
				}
		
				//canvas is customizable setting
				$adminOrderCanvasIsCustomizableHolder = $orderItem->get_meta( 'is_image_customizable_'.$x.'' );
				
				//canvas alignment setting
				$adminOrderCanvasIsVerticalAlignHolder = $orderItem->get_meta( 'align_center_vertical_'.$x.'' );
				$adminOrderCanvasIsHorizontalAlignHolder = $orderItem->get_meta( 'align_center_horizontal_'.$x.'' );

				//canvas position setting
				$adminOrderCanvasHeightPercentHolder = $orderItem->get_meta( 'print_area_height_value_'.$x.'' );
				$adminOrderCanvasWidthPercentHolder = $orderItem->get_meta( 'print_area_width_value_'.$x.'' );
				$adminOrderCanvasTopPercentHolder = $orderItem->get_meta( 'print_area_top_value_'.$x.'' );
				$adminOrderCanvasLeftPercentHolder = $orderItem->get_meta( 'print_area_left_value_'.$x.'' );
				
				//canvas border setting
				$adminOrderCanvasBorderColor = $orderItem->get_meta( 'border_color_'.$x.'' );

				//canvas json
				$canvas_json = $orderItem->get_meta( 'canvas_json_'.$x.'' );

				//canvas original width
				$original_canvas_width = $orderItem->get_meta( 'original_canvas_width_'.$x.'' );

				//adding data to javascript canvas variables
				echo '<script>			
					orderItemID.push("'.esc_html($order_item_id).'");
					
					canvasIndex.push("'.esc_html($x).'");
				
					backgroundSrc.push("'.esc_html($image_url).'");
								
					adminOrderCanvasIsCustomizable.push("'.esc_html($adminOrderCanvasIsCustomizableHolder).'");
					
					adminOrderCanvasHeightPercent.push("'.esc_html($adminOrderCanvasHeightPercentHolder).'");
					adminOrderCanvasWidthPercent.push("'.esc_html($adminOrderCanvasWidthPercentHolder).'");
					adminOrderCanvasTopPercent.push("'.esc_html($adminOrderCanvasTopPercentHolder).'");
					adminOrderCanvasLeftPercent.push("'.esc_html($adminOrderCanvasLeftPercentHolder).'");
					
					adminOrderCanvasIsVerticalAlign.push("'.esc_html($adminOrderCanvasIsVerticalAlignHolder).'");
					adminOrderCanvasIsHorizontalAlign.push("'.esc_html($adminOrderCanvasIsHorizontalAlignHolder).'");
					
					adminOrderCanvasBorderColor.push("'.esc_html($adminOrderCanvasBorderColor).'");

					canvas_json.push("'.esc_html($canvas_json).'");

					original_canvas_width.push("'.esc_html($original_canvas_width).'");
				</script>';
				echo '<div id="print-order-viewer_'.esc_html($order_item_id).'_'.esc_html($x).'"></div>';
			}
		} //end of for loop	
	}
}

// add print-order-viewer.js to order items
add_action( 'woocommerce_admin_order_data_after_order_details', 'wbpd_add_print_order_viewer' );
function wbpd_add_print_order_viewer( $order ) {
	// echo '<script>console.log("Including print-order-viewer.js");</script>';
	wp_enqueue_script('aopv', plugin_dir_url(__FILE__).'print-order-viewer.js');
}

// Hide container meta in order received
add_action( 'woocommerce_before_thankyou', 'wbpd_custom_content_thankyou', 10, 1 );
function wbpd_custom_content_thankyou( $order_id ) {
    echo '<style>.wc-item-meta {display: none}</style>';
}

// Hide container meta in emails
add_filter( 'woocommerce_order_item_get_formatted_meta_data', 'wbpd_unset_specific_order_item_meta_data', 10, 2);
function wbpd_unset_specific_order_item_meta_data($formatted_meta, $item) {
    // Only on emails notifications
    if( is_admin() || is_wc_endpoint_url() )
        return $formatted_meta;

    foreach( $formatted_meta as $key => $meta ){
        // if( in_array( $meta->key, array('Qty Selector', 'Qty', 'Total') ) )
            unset($formatted_meta[$key]);
    }
    return $formatted_meta;
}

// deregister plugin when deregister plugin clucked in wordpress plugin menu
register_deactivation_hook( __FILE__, 'wbpd_woocommerce_deactivation' );
function wbpd_woocommerce_deactivation() {

}

?>