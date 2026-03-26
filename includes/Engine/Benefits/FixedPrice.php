<?php

namespace WpulsePricingRules\Includes\Engine\Benefits;

use WpulsePricingRules\Includes\Engine\Context;
use WpulsePricingRules\Includes\Engine\TargetMatcher;

/**
 * Line-item benefit: fixed_price — override product price to a specific amount.
 *
 * benefit.price      (float)  Target unit price.
 * benefit.apply_to   (string) 'all' | 'lowest' | 'highest'
 *                             all     — every matching cart line gets the fixed price.
 *                             lowest  — only the matching line with the cheapest original price.
 *                             highest — only the matching line with the most expensive original price.
 * benefit.force      (bool)   false (default): only discount, never mark up (skip if original < target).
 *                             true: always set to the target price even if it is lower than the original.
 */
class FixedPrice {

    public static function apply( array $row, array $rule_data, Context $context ): void {
        $benefit  = $rule_data['benefit'] ?? [];
        $price    = isset( $benefit['price'] ) ? (float) $benefit['price'] : null;

        if ( $price === null || $price < 0 ) {
            return;
        }

        $apply_to = isset( $benefit['apply_to'] ) ? (string) $benefit['apply_to'] : 'all';
        $force    = ! empty( $benefit['force'] );
        $targets  = $rule_data['targets'] ?? [];
        $exclusions = $rule_data['exclusions'] ?? [];

        // Collect matching lines.
        $matched = [];
        foreach ( $context->cart_lines as $line ) {
            $cart_item = $line['cart_item'] ?? null;
            $product   = $cart_item['data'] ?? null;
            if ( ! $product ) {
                continue;
            }
            if ( TargetMatcher::isGloballyExcluded( $product ) ) {
                continue;
            }
            if ( ! TargetMatcher::lineMatchesTargets( $line, $targets ) ) {
                continue;
            }
            if ( TargetMatcher::isExcludedByRule( $product, $exclusions ) ) {
                continue;
            }
            if ( (int) ( $line['quantity'] ?? 0 ) <= 0 ) {
                continue;
            }
            $matched[] = $line;
        }

        if ( empty( $matched ) ) {
            return;
        }

        // When apply_to restricts to one item, narrow the set.
        if ( $apply_to === 'lowest' || $apply_to === 'highest' ) {
            usort( $matched, static function ( $a, $b ) {
                return (float) $a['price'] <=> (float) $b['price'];
            } );
            $matched = [ $apply_to === 'lowest' ? reset( $matched ) : end( $matched ) ];
        }

        $decimals = wc_get_price_decimals();

        foreach ( $matched as $line ) {
            $cart_item     = $line['cart_item'];
            $product       = $cart_item['data'];
            $original_price = (float) $line['price'];

            // Without force, only apply when the target is a genuine discount.
            if ( ! $force && $original_price <= $price ) {
                continue;
            }

            $new_price = round( max( 0.0, $price ), $decimals );
            PercentOff::setLinePrice( $cart_item, $new_price, $row['id'] );
        }
    }
}
