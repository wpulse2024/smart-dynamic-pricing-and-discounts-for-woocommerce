<?php

namespace SmartDynamicPricingDiscounts\Core;

use SmartDynamicPricingDiscounts\Routes\Router;
use SmartDynamicPricingDiscounts\Database\Database;
use SmartDynamicPricingDiscounts\Services\ServiceContainer;

/**
 * Main Plugin class - Bootstrap and service container
 */
class Plugin
{
    /**
     * Plugin instance
     */
    protected static $instance;

    /**
     * Service container
     */
    protected $container;

    /**
     * Router instance
     */
    protected $router;

    /**
     * Database instance
     */
    protected $database;

    /**
     * Plugin version
     */
    protected $version;

    /**
     * Constructor
     */
    protected function __construct()
    {
        $this->version = SMART_DYNAMIC_PRICING_AND_DISCOUNTS_FOR_WOOCOMMERCE_VERSION;
        $this->container = new ServiceContainer();
        $this->router = new Router();
        $this->database = new Database();
        
        $this->registerServices();
        $this->init();
    }

    /**
     * Get plugin instance
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }

    /**
     * Register services in the container
     */
    protected function registerServices(): void
    {
        // Register core services
        $this->container->singleton('router', function() {
            return $this->router;
        });
        
        $this->container->singleton('database', function() {
            return $this->database;
        });
        
        $this->container->singleton('plugin', function() {
            return $this;
        });
    }

    /**
     * Initialize the plugin
     */
    protected function init(): void
    {
        // Load text domain
        add_action('init', [$this, 'loadTextDomain']);
        
        // Register routes
        add_action('init', [$this, 'registerRoutes']);
        
        // Register admin menu
        add_action('admin_menu', [$this, 'registerAdminMenu']);
        
        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', [$this, 'enqueuePublicAssets']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);
        
        // Register AJAX handlers
        add_action('wp_ajax_my_plugin_action', [$this, 'handleAjaxRequest']);
        add_action('wp_ajax_nopriv_my_plugin_action', [$this, 'handleAjaxRequest']);
        
        // Initialize components
        $this->initializeComponents();
        
        // Register shortcodes
        add_action('init', ['SmartDynamicPricingDiscounts\\Controllers\\ShortcodeController', 'register']);
    }

    /**
     * Load text domain for translations
     */
    public function loadTextDomain(): void
    {
        load_plugin_textdomain(
            'smart-dynamic-pricing-and-discounts-for-woocommerce',
            false,
            dirname(SMART_DYNAMIC_PRICING_AND_DISCOUNTS_FOR_WOOCOMMERCE_BASENAME) . '/languages'
        );
    }

    /**
     * Register routes
     */
    public function registerRoutes(): void
    {
        // Register API routes
        \SmartDynamicPricingDiscounts\Routes\ApiRoutes::register();
        
        // Register REST API routes
        $this->router->registerRestRoutes('my-plugin/v1');
        
        // Register admin routes
        $this->router->registerAdminRoutes();
    }

    /**
     * Register admin menu
     */
    public function registerAdminMenu(): void
    {
        global $submenu;
        add_menu_page(
            'smart-dynamic-pricing-and-discounts-for-woocommerce',
            __('Dynamic Pricing & Discounts', 'smart-dynamic-pricing-and-discounts-for-woocommerce'),
            'manage_options',
            'smart-dynamic-pricing-and-discounts-for-woocommerce.php',
            array($this, 'renderAdminPage'),
            'dashicons-editor-code',
            25
        );

        $submenu['smart-dynamic-pricing-and-discounts-for-woocommerce.php']['dashboard'] = array(
            __('Dashboard', 'smart-dynamic-pricing-and-discounts-for-woocommerce'),
            'manage_options',
            'admin.php?page=smart-dynamic-pricing-and-discounts-for-woocommerce.php#/',
        );
    }

    /**
     * Render admin page
     */
    public function renderAdminPage(): void
    {
        echo '<div id="my-plugin-admin"></div>';
    }

    /**
     * Render settings page
     */
    public function renderSettingsPage(): void
    {
        echo '<div class="wrap">';
        echo '<h1>' . __('My Plugin Settings', 'smart-dynamic-pricing-and-discounts-for-woocommerce') . '</h1>';
        echo '<div id="my-plugin-settings"></div>';
        echo '</div>';
    }

    /**
     * Enqueue public assets
     */
    public function enqueuePublicAssets(): void
    {
        wp_enqueue_style(
            'my-plugin-public',
            SMART_DYNAMIC_PRICING_AND_DISCOUNTS_FOR_WOOCOMMERCE_URL . 'public/css/public.css',
            [],
            $this->version
        );
        
        wp_enqueue_script(
            'my-plugin-public',
            SMART_DYNAMIC_PRICING_AND_DISCOUNTS_FOR_WOOCOMMERCE_URL . 'public/js/public.js',
            ['jquery'],
            $this->version,
            true
        );
    }

    /**
     * Enqueue admin assets
     */
    public function enqueueAdminAssets(string $hook): void
    {
        // Only load on our admin pages
        if (strpos($hook, 'smart-dynamic-pricing-and-discounts-for-woocommerce') === false) {
            return;
        }
        
        wp_enqueue_style(
            'smart-dynamic-pricing-and-discounts-for-woocommerce-admin',
            SMART_DYNAMIC_PRICING_AND_DISCOUNTS_FOR_WOOCOMMERCE_URL . 'assets/css/admin.css',
            [],
            $this->version
        );
        
        wp_enqueue_script(
            'smart-dynamic-pricing-and-discounts-for-woocommerce-admin',
            SMART_DYNAMIC_PRICING_AND_DISCOUNTS_FOR_WOOCOMMERCE_URL . 'assets/js/admin.js',
            ['jquery'],
            $this->version,
            true
        );

        // get woocomerce all products , categories and tags also all users
        $products = get_posts([
            'post_type' => 'product',
            'numberposts' => -1,
            'fields' => ['id', 'post_title'],
        ]);
        $categories = get_terms([
            'taxonomy' => 'product_cat',
        ]);
        $tags = get_terms([
            'taxonomy' => 'product_tag',
        ]);

        $users = get_users([
            'fields' => ['ID', 'user_login'],
        ]);

        $allUserRoles = wp_roles()->get_names();
        
        // Localize script with data
        wp_localize_script('smart-dynamic-pricing-and-discounts-for-woocommerce-admin', 'SmartDynamicPricingDiscount', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'restNonce' => wp_create_nonce('wp_rest'),
            'restUrl' => rest_url('my-plugin/v1/'),
            'nonce' => wp_create_nonce('my_plugin_nonce'),
            'products' => $products,
            'categories' => $categories,
            'tags' => $tags,
            'users' => $users,
            'roles' => $allUserRoles,
            'strings' => [
                'loading' => __('Loading...', 'smart-dynamic-pricing-and-discounts-for-woocommerce'),
                'error' => __('An error occurred', 'smart-dynamic-pricing-and-discounts-for-woocommerce'),
                'success' => __('Success!', 'smart-dynamic-pricing-and-discounts-for-woocommerce'),
            ]
        ]);
    }

    /**
     * Handle AJAX requests
     */
    public function handleAjaxRequest(): void
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'my_plugin_nonce')) {
            wp_die('Security check failed');
        }
        
        $action = $_POST['action'] ?? '';
        $method = $_POST['method'] ?? 'GET';
        
        // Route to appropriate handler
        $this->router->handle($method, '/ajax/' . $action);
    }

    /**
     * Initialize components
     */
    protected function initializeComponents(): void
    {
        // Initialize services
        $services = [
            'SmartDynamicPricingDiscounts\\Services\\TripService',
            'SmartDynamicPricingDiscounts\\Services\\UserService',
        ];
        
        foreach ($services as $service) {
            if (class_exists($service)) {
                $this->container->singleton($service, function() use ($service) {
                    return new $service();
                });
            }
        }
    }

    /**
     * Get service from container
     */
    public function get(string $service)
    {
        return $this->container->get($service);
    }

    /**
     * Get router instance
     */
    public function getRouter(): Router
    {
        return $this->router;
    }

    /**
     * Get database instance
     */
    public function getDatabase(): Database
    {
        return $this->database;
    }

    /**
     * Get plugin version
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * Plugin activation
     */
    public static function activate(): void
    {
        // Create database tables
        $database = new Database();
        
        // Run migrations
        $migrations = [
            'SmartDynamicPricingDiscounts\\Database\\Migrations\\CreateTripsTable',
        ];
        
        foreach ($migrations as $migration) {
            if (class_exists($migration)) {
                $migrationInstance = new $migration();
                $migrationInstance->up();
            }
        }
        
        // Set activation flag
        update_option('my_plugin_activated', true);
        update_option('my_plugin_version', SMART_DYNAMIC_PRICING_AND_DISCOUNTS_FOR_WOOCOMMERCE_VERSION);
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Plugin deactivation
     */
    public static function deactivate(): void
    {
        // Remove activation flag
        delete_option('my_plugin_activated');
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Plugin uninstall
     */
    public static function uninstall(): void
    {
        // Drop database tables
        $database = new Database();
        
        $tables = [
            'trips',
        ];
        
        foreach ($tables as $table) {
            $database->dropTable($table);
        }
        
        // Remove all plugin options
        delete_option('my_plugin_version');
        delete_option('my_plugin_settings');
    }
}
