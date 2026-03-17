<?php

namespace WpulsePricingRules\Includes\Frontend;

use WpulsePricingRules\Includes\DB\RulesRepository;
use WpulsePricingRules\Includes\Engine\ConditionEvaluator;
use WpulsePricingRules\Includes\Engine\Context;
use WpulsePricingRules\Includes\Engine\RuleSchedule;
use WpulsePricingRules\Includes\Engine\TargetMatcher;

/**
 * Displays discount messages and badges on single product and shop/archive pages.
 * Shows only the one rule that would actually apply (highest priority that passes conditions).
 * Respects rule meta: show_badge, show_on_shop, custom_message.
 */
class ProductDiscountMessage {

	/** @var array<int, array>|null */
	private static ?array $cached_rules = null;

	private static function getActiveRules(): array {
		if (self::$cached_rules === null) {
			self::$cached_rules = RulesRepository::all('priority', 'DESC');
		}
		return self::$cached_rules;
	}

	public static function register(): void {
		add_action( 'woocommerce_single_product_summary', [ __CLASS__, 'renderSingleProductMessage' ], 11 );
		add_action( 'woocommerce_after_shop_loop_item_title', [ __CLASS__, 'renderShopLoopMessage' ], 15 );
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueueStyles' ] );
	}

	public static function enqueueStyles(): void {
		if ( ! is_product() && ! is_shop() && ! is_product_category() ) {
			return;
		}
		wp_add_inline_style( 'woocommerce-general', self::getCss() );
	}

	private static function getCss(): string {
		return '
			.wpulse-discount-message { margin: 0.5em 0; padding: 0.5em 0.75em; background: #f0f6fc; border-left: 3px solid #2271b1; color: #1d2327; font-size: 0.95em; }
			.wpulse-discount-message p { margin: 0 0 0.25em 0; }
			.wpulse-discount-message p:last-child { margin-bottom: 0; }
			.wpulse-discount-badge { display: inline-block; margin-top: 0.25em; padding: 0.2em 0.5em; background: #2271b1; color: #fff; font-size: 0.85em; border-radius: 3px; }
		';
	}

	/**
	 * Single product page: show message after price (priority 11).
	 */
	public static function renderSingleProductMessage(): void {
		global $product;
		if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
			return;
		}
		$items = self::getMessagesForProduct( $product, true );
		if ( empty( $items ) ) {
			return;
		}
		echo '<div class="wpulse-discount-message wpulse-discount-message--single">';
		foreach ( $items as $item ) {
			echo '<p class="wpulse-discount-message__text">' . wp_kses_post( $item['message'] ) . '</p>';
		}
		echo '</div>';
	}

	/**
	 * Shop/loop: show compact badge when show_on_shop is true.
	 */
	public static function renderShopLoopMessage(): void {
		global $product;
		if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
			return;
		}
		$items = self::getMessagesForProduct( $product, false );
		if ( empty( $items ) ) {
			return;
		}
		echo '<div class="wpulse-discount-message wpulse-discount-message--loop">';
		foreach ( $items as $item ) {
			echo '<span class="wpulse-discount-badge">' . wp_kses_post( $item['message'] ) . '</span> ';
		}
		echo '</div>';
	}

	/**
	 * Get the message for the one rule that would apply (highest priority that passes conditions).
	 * Returns at most one item so we show only the applicable rule, not all matching rules.
	 *
	 * @param \WC_Product $product
	 * @param bool        $single_product If true, use show_badge or custom_message; if false, only when show_on_shop.
	 * @return array<int, array{message: string}>
	 */
	public static function getMessagesForProduct( \WC_Product $product, bool $single_product ): array {
		$product_id = (int) $product->get_id();
		$line       = [
			'product_id' => $product_id,
			'categories' => RuleSchedule::getTermIds( $product_id, 'product_cat' ),
			'tags'       => RuleSchedule::getTermIds( $product_id, 'product_tag' ),
		];

		// Context with no cart so cart-dependent conditions fail; only rules that pass (e.g. user role) are applicable.
		$context   = Context::forProductPage();
		$all_rules = self::getActiveRules();

		foreach ( $all_rules as $row ) {
			if ( ( $row['status'] ?? '' ) !== 'active' ) {
				continue;
			}
			$rule_data = is_string( $row['rule_json'] ?? null ) ? json_decode( $row['rule_json'], true ) : ( $row['rule'] ?? [] );
			if ( ! is_array( $rule_data ) ) {
				$rule_data = [];
			}
			if ( ! RuleSchedule::inSchedule( $rule_data ) ) {
				continue;
			}
			// Conditions must pass (e.g. user role, page). Cart conditions fail on product page.
			if ( ! ConditionEvaluator::evaluate( $rule_data, $context ) ) {
				continue;
			}
			$targets = $rule_data['targets'] ?? [];
			if ( ! TargetMatcher::lineMatchesTargets( $line, $targets ) ) {
				continue;
			}
			if ( TargetMatcher::isExcludedByRule( $product, $rule_data['exclusions'] ?? [] ) ) {
				continue;
			}
			$meta         = $rule_data['meta'] ?? [];
			$show_badge   = ! isset( $meta['show_badge'] ) || $meta['show_badge'];
			$show_on_shop = ! isset( $meta['show_on_shop'] ) || $meta['show_on_shop'];
			$custom_msg   = trim( (string) ( $meta['custom_message'] ?? '' ) );

			if ( $single_product ) {
				if ( ! $show_badge && $custom_msg === '' ) {
					continue;
				}
			} else {
				if ( ! $show_on_shop ) {
					continue;
				}
			}

			$message = self::buildMessage( $rule_data, $row['name'] ?? '', $custom_msg, $single_product );
			if ( $message !== '' ) {
				// Only the first (highest priority) applicable rule.
				return [ [ 'message' => $message ] ];
			}
		}

		return [];
	}

	private static function buildMessage( array $rule_data, string $rule_name, string $custom_message, bool $single_product ): string {
		if ( $custom_message !== '' ) {
			$msg = self::replaceShortcodes( $custom_message, $rule_data );
			return wp_kses_post( $msg );
		}
		$benefit = $rule_data['benefit'] ?? [];
		$kind    = $benefit['kind'] ?? '';
		switch ( $kind ) {
			case 'percent_off':
				$pct = (int) ( $benefit['percent'] ?? 0 );
				return $pct > 0 ? sprintf( __( '%d%% off', 'wpulse-pricing-rules-for-woocommerce' ), $pct ) : '';
			case 'fixed_off':
				$amount = (float) ( $benefit['amount'] ?? 0 );
				return $amount > 0 ? sprintf( __( '%s off', 'wpulse-pricing-rules-for-woocommerce' ), wc_price( $amount ) ) : '';
			case 'x_for_y':
				$buy = (int) ( $benefit['buy_qty'] ?? 0 );
				$pay = (int) ( $benefit['pay_qty'] ?? 0 );
				if ( $buy > 0 && $pay >= 0 && $pay < $buy ) {
					return sprintf( __( 'Buy %d pay for %d', 'wpulse-pricing-rules-for-woocommerce' ), $buy, $pay );
				}
				return '';
			case 'nth_percent_off':
				$nth = (int) ( $benefit['nth'] ?? 0 );
				$pct = (int) ( $benefit['percent'] ?? 0 );
				if ( $nth > 0 && $pct > 0 ) {
					return sprintf( __( '%1$d%% off %2$s unit', 'wpulse-pricing-rules-for-woocommerce' ), $pct, self::ordinal( $nth ) );
				}
				return '';
			case 'tiered':
				return __( 'Quantity discount available', 'wpulse-pricing-rules-for-woocommerce' );
			case 'category_discounts':
				return __( 'Category discount available', 'wpulse-pricing-rules-for-woocommerce' );
			case 'free_gift':
				return __( 'Free gift available', 'wpulse-pricing-rules-for-woocommerce' );
			case 'cart_percent_off':
			case 'cart_fixed_off':
				$pct = (int) ( $benefit['percent'] ?? 0 );
				return $pct > 0 ? sprintf( __( 'Cart: %d%% off when conditions met', 'wpulse-pricing-rules-for-woocommerce' ), $pct ) : __( 'Cart discount when conditions met', 'wpulse-pricing-rules-for-woocommerce' );
			case 'free_shipping':
				return __( 'Free shipping when conditions met', 'wpulse-pricing-rules-for-woocommerce' );
			default:
				return $rule_name ? sprintf( __( 'Discount: %s', 'wpulse-pricing-rules-for-woocommerce' ), $rule_name ) : __( 'Discount available', 'wpulse-pricing-rules-for-woocommerce' );
		}
	}

	private static function replaceShortcodes( string $text, array $rule_data ): string {
		// On product page we don't have cart context, so use placeholders or remove.
		$text = str_replace( [ '[save_amount]', '[save_percentage]' ], [ '', '' ], $text );
		return $text;
	}

	private static function ordinal( int $n ): string {
		$s = (string) $n;
		$last = substr( $s, -1 );
		$last2 = substr( $s, -2 );
		if ( (int) $last2 >= 11 && (int) $last2 <= 13 ) {
			return $s . __( 'th', 'wpulse-pricing-rules-for-woocommerce' );
		}
		if ( $last === '1' ) {
			return $s . __( 'st', 'wpulse-pricing-rules-for-woocommerce' );
		}
		if ( $last === '2' ) {
			return $s . __( 'nd', 'wpulse-pricing-rules-for-woocommerce' );
		}
		if ( $last === '3' ) {
			return $s . __( 'rd', 'wpulse-pricing-rules-for-woocommerce' );
		}
		return $s . __( 'th', 'wpulse-pricing-rules-for-woocommerce' );
	}
}
