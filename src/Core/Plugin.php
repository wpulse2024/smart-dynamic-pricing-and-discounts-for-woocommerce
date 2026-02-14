<?php

namespace WpulsePricingRules\Core;

/**
 * Main plugin bootstrap – Laravel-style MVC with routes.
 */
final class Plugin {

    private static ?self $instance = null;

    private Router $router;

    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->router = new Router();
        // Set instance before loadRoutes() so Route facade can call getInstance()->getRouter()
        // without causing infinite recursion (api.php calls Plugin::getInstance() during require).
        self::$instance = $this;
        $this->registerHooks();
        $this->loadRoutes();
    }

    private function registerHooks(): void {
        add_action('init', [$this, 'onInit']);
        add_action('admin_menu', [$this, 'registerAdminMenu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);
        add_action('rest_api_init', [$this, 'registerRestRoutes']);
        add_filter('script_loader_tag', [$this, 'scriptLoaderTag'], 10, 3);
        add_action('woocommerce_loaded', [$this, 'registerEngine']);
        add_action('wp_ajax_wpulse_get_templates', [\WpulsePricingRules\Includes\Admin\Ajax::class, 'getTemplates']);
        add_action('wp_ajax_wpulse_create_rule_from_template', [\WpulsePricingRules\Includes\Admin\Ajax::class, 'createRuleFromTemplate']);
    }

    public function registerEngine(): void {
        \WpulsePricingRules\Includes\Engine\RuleEvaluator::register();
    }

    /**
     * Output admin script as ES module so Vite bundle (import/export) runs correctly.
     */
    public function scriptLoaderTag(string $tag, string $handle, string $src): string {
        if ($handle === 'wpulse-pricing-rules-app') {
            return str_replace('<script ', '<script type="module" ', $tag);
        }
        return $tag;
    }

    private function loadRoutes(): void {
        $routes_file = WPULSE_PRICING_RULES_PATH . 'routes/api.php';
        if (file_exists($routes_file)) {
            require $routes_file;
        }
    }

    public function onInit(): void {
        $this->router->dispatchRewriteRules();
    }

    public function registerAdminMenu(): void {
        add_menu_page(
            __('Pricing Rules', 'wpulse-pricing-rules-for-woocommerce'),
            __('Pricing Rules', 'wpulse-pricing-rules-for-woocommerce'),
            'manage_woocommerce',
            'wpulse-pricing-rules',
            [$this, 'renderAdminPage'],
            'dashicons-tag',
            56
        );
        \WpulsePricingRules\Includes\Admin\EditRulePage::register();
    }

    public function renderAdminPage(): void {
        echo '<div id="wpulse-pricing-rules-app"></div>';
    }

    public function enqueueAdminAssets(string $hook): void {
        if (strpos($hook, 'wpulse-pricing-rules') === false) {
            return;
        }

        $script_handle = 'wpulse-pricing-rules-app';
        $asset_path    = WPULSE_PRICING_RULES_PATH . 'assets/dist/admin.asset.php';
        $script_url    = WPULSE_PRICING_RULES_URL . 'assets/dist/admin.js';
        $style_url     = WPULSE_PRICING_RULES_URL . 'assets/dist/admin.css';

        if (file_exists($asset_path)) {
            $asset = require $asset_path;
            wp_enqueue_script(
                $script_handle,
                $script_url,
                $asset['dependencies'] ?? [],
                $asset['version'] ?? WPULSE_PRICING_RULES_VERSION,
                true
            );
            wp_enqueue_style($script_handle . '-style', $style_url, [], $asset['version'] ?? WPULSE_PRICING_RULES_VERSION);
        } else {
            wp_enqueue_script(
                $script_handle,
                $script_url,
                [],
                WPULSE_PRICING_RULES_VERSION,
                true
            );
            wp_enqueue_style($script_handle . '-style', $style_url, [], WPULSE_PRICING_RULES_VERSION);
        }

        wp_localize_script($script_handle, 'wpulsePricingRules', [
            'restUrl'      => rest_url('wpulse-pricing-rules/v1'),
            'nonce'        => wp_create_nonce('wp_rest'),
            'pluginUrl'    => WPULSE_PRICING_RULES_URL,
            'editRuleUrl'  => admin_url('admin.php?page=wpulse-pricing-rules-edit'),
        ]);
    }

    public function registerRestRoutes(): void {
        $this->router->registerRestRoutes();
    }

    public function getRouter(): Router {
        return $this->router;
    }

    public static function activate(): void {
        if (file_exists(WPULSE_PRICING_RULES_PATH . 'vendor/autoload.php')) {
            require_once WPULSE_PRICING_RULES_PATH . 'vendor/autoload.php';
        }
        \WpulsePricingRules\Includes\DB\Installer::install();
        flush_rewrite_rules();
    }

    public static function deactivate(): void {
        flush_rewrite_rules();
    }
}
