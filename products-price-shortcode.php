<?php
/**
 * Plugin Name: Products Price Shortcode
 * Plugin URI:  https://github.com/MatinKhamooshi/products-price-shortcode
 * Description: Display list of products name and price with shortcode [products_price ids="1,2"]
 * Version:     1.0.0
 * Author:      Matin Khamooshi
 * Author URI:  https://matinkhamooshi.ir/
 * Text Domain: products-price-shortcode
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * شورت‌کد [products_price ids="1,2,3"]
 * نمایش نام و قیمت (یا قیمت اصلی + قیمت تخفیف‌خورده) محصولات
 *
 * @param array $atts آرایه ورودی شورت‌کد (ids: رشته آیدی‌ها جداشده با کاما)
 * @return string خروجی HTML
 */
function pps_products_price_shortcode( $atts ) {
    // مقداردهی پیش‌فرض و پاک‌سازی ورودی
    $atts = shortcode_atts(
        array(
            'ids' => '',
        ),
        $atts,
        'products_price'
    );

    $ids_raw = sanitize_text_field( $atts['ids'] );
    if ( empty( $ids_raw ) ) {
        return ''; // اگر آیدی وارد نشده
    }

    // آرایه آیدی‌ها
    $ids = array_filter( array_map( 'absint', explode( ',', $ids_raw ) ) );
    if ( empty( $ids ) ) {
        return '';
    }

    $items = array();

    foreach ( $ids as $product_id ) {
        // دریافت شیء محصول
        $product = wc_get_product( $product_id );
        if ( ! $product instanceof WC_Product ) {
            continue; // اگر محصول نامعتبر
        }
        // بررسی موجودی
        if ( ! $product->is_in_stock() ) {
            continue;
        }

        // نام و قیمت‌ها
        $name          = $product->get_name();
        $regular_price = $product->get_regular_price();
        $sale_price    = $product->get_sale_price();

        // آماده‌سازی متن قیمت
        if ( $sale_price && $sale_price < $regular_price ) {
            $price_text = sprintf(
                '%s + %s',
                wc_price( $regular_price ),
                wc_price( $sale_price )
            );
        } else {
            $price_text = wc_price( $regular_price );
        }

        // اضافه کردن آیتم به لیست
        $items[] = sprintf(
            '<li>قیمت %s %s</li>',
            esc_html( $name ),
            $price_text
        );
    }

    if ( empty( $items ) ) {
        return '';
    }

    // خروجی HTML
    $output  = '<ul class="pps-products-price-list">';
    $output .= implode( "\n", $items );
    $output .= '</ul>';

    return $output;
}
add_shortcode( 'products_price', 'pps_products_price_shortcode' );