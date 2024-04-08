<?php
/*
Plugin Name: IPhone Quick Search
Plugin URI: https://phonecase.at/
Description: A simple plugin to add a custom greeting shortcode.
Version: 1.0
Author: Phone Case
Author URI: https://phonecase.at/
*/

// Enqueue Bootstrap CSS and JavaScript
function custom_greeting_enqueue_scripts() {
    // Enqueue Bootstrap CSS
    wp_enqueue_style('bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css', array(), '5.3.3');

    // Enqueue Bootstrap JavaScript
    wp_enqueue_script('bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js', array('jquery'), '5.3.3', true);
}

add_action('wp_enqueue_scripts', 'custom_greeting_enqueue_scripts');

// Shortcode callback function
function custom_greeting_shortcode() {
    // WooCommerce Product Categories
    $args = array(
        'taxonomy'     => 'product_cat',
        'orderby'      => 'name',
        'show_count'   => 0,
        'pad_counts'   => 0,
        'hierarchical' => 1,
        'title_li'     => '',
        'hide_empty'   => 0
    );
    $product_categories = get_categories($args);

    $container = '<div class="product_container">';

    // Initialize an empty string to store the parent categories options
    $parent_options = '';

    // Loop through product categories to generate parent categories options
    foreach ($product_categories as $category) {
        if ($category->category_parent == 0) {
            $parent_options .= '<option value="' . $category->term_id . '">' . $category->name . '</option>';
        }
    }

    // Generate the complete select element for parent categories
    $parent_select_html = '<select id="parent-category" class="form-select form-select-lg mb-3" aria-label="Select parent category">';
    $parent_select_html .= '<option value="">Select parent category</option>';
    $parent_select_html .= $parent_options; // Append parent categories options
    $parent_select_html .= '</select>';

    // Generate the empty select element for subcategories
    $sub_select_html = '<select id="sub-category" class="form-select form-select-lg mb-3" aria-label="Select subcategory">';
    $sub_select_html .= '<option value="">Select subcategory</option>';
    $sub_select_html .= '</select>';

    // Generate the complete select element with both parent and subcategory selects
    $selects_html = '<div class="category-select">' . $parent_select_html . $sub_select_html . '</div>';

    $search_btn = '<button id="search-btn" class="btn btn-primary">Search</button>';

    // Overlay loading HTML
    $loading_overlay = '<div id="loading-overlay"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';

    // JavaScript to dynamically populate subcategories based on selected parent category and handle form submission
    $script = '
    <script>
    jQuery(document).ready(function($) {
        // Function to fetch and display products based on selected category or subcategory
        function fetchProducts(categoryId) {
            var data = {
                action: "fetch_products",
                category_id: categoryId
            };
            // Show loading overlay
            $("#loading-overlay").show();
            $.ajax({
                url: "' . admin_url('admin-ajax.php') . '",
                type: "GET",
                data: data,
                success: function(response) {
                    $("#product-list").html(response);
                    // Hide loading overlay
                    $("#loading-overlay").hide();
                }
            });
        }

        // Populate subcategories when parent category is selected
        $("#parent-category").change(function() {
            var parentCategoryId = $(this).val();
            $.ajax({
                url: "' . admin_url('admin-ajax.php') . '",
                type: "GET",
                data: {
                    action: "get_subcategories",
                    parent_id: parentCategoryId
                },
                success: function(response) {
                    $("#sub-category").html(response);
                }
            });
        });

        // Handle form submission
        $("#search-btn").click(function() {
            var selectedCategoryId = $("#sub-category").val() || $("#parent-category").val();
            fetchProducts(selectedCategoryId);
        });

        // Fetch all products initially
        fetchProducts("");








        
    });


    </script>';

    // CSS styles
    $styles = '
    <style>
    .category-select {
        display: flex;
        justify-content: center;
        margin-bottom: 20px;
    }
    #parent-category, #sub-category {
        margin-right: 10px;
    }

    #parent-category.form-select-lg {
        font-size: 19px !important;
    }
    #sub-category.form-select-lg {
        font-size: 19px !important;
    }


    #search-btn {
        display: block;
        margin: 0 auto;
    }
    #product-list {
        margin-top: 20px;
    }
    #loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.5);
        display: none;
        justify-content: center;
        align-items: center;
    }
    .spinner-border {
        position: absolute;
        left: 50%;
        top: 50%;
    }

    .product-box {
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        transition: box-shadow 0.3s ease;
    }
    
    .product-box:hover {
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    }
    
    .product-box img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        margin-bottom: 10px;
    }
    
    .product-title {
        font-size: 1.2rem;
        margin-bottom: 10px;
    }
    
    .product-description {
        font-size: 1rem;
        color: #666;
        margin-bottom: 10px;
    }
    
    .product-price {
        font-size: 1.2rem;
        font-weight: bold;
        color: #333;
        margin-bottom: 10px;
        display: block;
    }
    
    .btn {
        padding: 10px 20px;
        background-color: #007bff;
        color: #fff;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }
    
    .btn:hover {
        background-color: #0056b3;
    }

    .wd-product .product-wrapper {
        border-radius: var(--wd-brd-radius);
        padding: var(--wd-prod-bg-sp)
    }
    
    .wd-product :where(.product-wrapper,.product-element-bottom) {
        display: flex;
        flex-direction: column;
        gap: var(--wd-prod-gap)
    }
    
    .wd-product :is(.product-image-link,.hover-img) img {
        width: 100%
    }
    
    .wd-product .product-image-link {
        position: relative;
        display: block
    }
    
    .wd-product .hover-img {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: var(--bgcolor-white);
        opacity: 0;
        transition: opacity 0.5s ease,transform 2s cubic-bezier(0, 0, 0.44, 1.18)
    }
    
    .wd-product .hover-img>a {
        display: block;
        flex: 1 1 100%;
        max-width: 100%;
        width: 100%
    }
    
    .wd-product :is(.wd-entities-title,.wd-product-cats,.wd-product-brands-links,.wd-product-sku,.wd-product-stock,.price) {
        line-height: inherit;
        margin-block:-.2em}
    
    .wd-product .wd-entities-title {
        font-size: inherit
    }
    
    .wd-product :is(.wd-product-cats,.wd-product-brands-links) {
        font-size: 95%;
        --wd-link-color: var(--color-gray-400);
        --wd-link-color-hover: var(--color-gray-700)
    }
    
    .wd-product :is(.wd-product-cats,.wd-product-brands-links) a {
        display: inline-block
    }
    
    .wd-product .wd-product-sku {
        color: var(--color-gray-400);
        word-break: break-all;
        font-size: 95%
    }
    
    .wd-product .wd-product-sku .wd-label {
        color: var(--color-gray-800);
        font-weight: 600
    }
    
    .wd-product .wd-star-rating {
        display: inline-flex;
        align-items: center;
        align-self: var(--text-align);
        gap: 5px
    }
    
    .wd-product .star-rating {
        align-self: var(--text-align);
        line-height: 1
    }
    
    .wd-product .woocommerce-review-link {
        line-height: 1;
        color: var(--color-gray-500)
    }
    
    .wd-product .woocommerce-review-link:hover {
        color: var(--color-gray-900)
    }
    
    .wd-product .wd-swatches-grid:empty {
        display: none
    }
    
    .wd-product :is(.wd-product-countdown,.wd-swatches-grid) {
        justify-content: var(--text-align)
    }
    
    .wd-product .price {
        display: block
    }
    
    .wd-product .added_to_cart {
        display: none !important
    }
    
    .wd-product .shop_attributes {
        font-size: 90%;
        --wd-attr-v-gap: 10px;
        --wd-attr-brd-style: dashed;
        --wd-attr-img-width: 18px
    }
    
    .wd-product:is(.wd-variation-active,.product-swatched,.wd-loading-image) .hover-img {
        display: none
    }
    
    .wd-product:is(.quick-shop-shown,.wd-loading-quick-shop) .product-element-top .hover-img {
        opacity: 0;
        transition: opacity 0.25s ease,transform 2s cubic-bezier(0, 0, 0.44, 1.18)
    }
    
    </style>';

    // Close the container div
    $container .= $styles . $loading_overlay . $selects_html . $search_btn . '<div id="product-list"></div>' . $script . '</div>';

    return $container;
}


// Register shortcode
add_shortcode('custom_greeting', 'custom_greeting_shortcode');

// AJAX handler to get subcategories based on the selected parent category
add_action('wp_ajax_get_subcategories', 'get_subcategories_callback');
add_action('wp_ajax_nopriv_get_subcategories', 'get_subcategories_callback');

function get_subcategories_callback() {
    if (isset($_GET['parent_id'])) {
        $parent_id = $_GET['parent_id'];
        $subcategories = get_terms(array(
            'taxonomy' => 'product_cat',
            'parent' => $parent_id,
            'hide_empty' => false,
        ));

        $options = '<option value="">Select subcategory</option>';
        foreach ($subcategories as $subcategory) {
            $options .= '<option value="' . $subcategory->term_id . '">' . $subcategory->name . '</option>';
        }
        echo $options;
    }
    wp_die();
}

// AJAX handler to fetch products based on selected category or subcategory
add_action('wp_ajax_fetch_products', 'fetch_products_callback');
add_action('wp_ajax_nopriv_fetch_products', 'fetch_products_callback');

function fetch_products_callback() {
    $category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
    $args = array(
        'post_type' => 'product',
        'posts_per_page' => -1,
        'orderby' => 'date',
        'order' => 'DESC'
    );

    if ($category_id) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'product_cat',
                'field' => 'term_id',
                'terms' => $category_id,
                'operator' => 'IN'
            )
        );
    }

    $products_query = new WP_Query($args);

    if ($products_query->have_posts()) {
        echo '<div class="row">';
        while ($products_query->have_posts()) {
            $products_query->the_post();
    
            // Get product data
            $product_id = get_the_ID();
            $product_title = get_the_title();
            $product_image = get_the_post_thumbnail_url($product_id, 'medium'); // Change 'medium' to desired image size
            $product_price = wc_price(get_post_meta($product_id, '_price', true));
    
            // Get product category
            $product_categories = get_the_terms($product_id, 'product_cat');
            $category_link = '';
            if ($product_categories && !is_wp_error($product_categories)) {
                $category = current($product_categories);
                $category_link = get_term_link($category);
            }
    
            // Display product box
            echo '<div class="col-md-4"><div class="product-wrapper">
                <div class="product-element-top wd-quick-shop">
                    <a href="' . $category_link . '" class="product-image-link">
                        <img loading="lazy" decoding="async" width="400" height="457" src="' . $product_image . '" class="attachment-400x457 size-400x457" alt="' . $product_title . '">		
                    </a>
                </div>
                <h3 class="wd-entities-title"><a href="' . get_permalink($product_id) . '">' . $product_title . '</a></h3>
                <div class="wd-product-cats">';
                
            if ($category_link) {
                echo '<a href="' . $category_link . '" rel="tag">' . $category->name . '</a>';
            }
            
            echo '</div>
                <span class="price"><span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol">' . $product_price . '</span></span>
                </span>
            </div> </div>';
        }
        echo '</div>'; // Close the row after the loop
        wp_reset_postdata();
    }
    
    
     else {
        echo 'No products found.';
    }

    wp_die();
}

// Register shortcode
add_shortcode('custom_greeting', 'custom_greeting_shortcode');




