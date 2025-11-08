<?php

namespace MyPlugin\CLI;

use WP_CLI;
use WP_CLI_Command;

/**
 * WP-CLI command to rename the plugin
 */
class RenameCommand extends WP_CLI_Command
{
    /**
     * Rename the plugin with new slug, namespace, author, and description
     *
     * ## OPTIONS
     *
     * --slug=<slug>
     * : New plugin slug
     *
     * --namespace=<namespace>
     * : New namespace (e.g., MyNewPlugin)
     *
     * [--author=<author>]
     * : New author name
     *
     * [--desc=<description>]
     * : New plugin description
     *
     * ## EXAMPLES
     *
     *     wp myplugin rename --slug=new-plugin --namespace=NewPlugin --author="New Author" --desc="New Description"
     *
     * @when after_wp_load
     */
    public function rename($args, $assoc_args)
    {
        $slug = $assoc_args['slug'] ?? '';
        $namespace = $assoc_args['namespace'] ?? '';
        $author = $assoc_args['author'] ?? '';
        $description = $assoc_args['desc'] ?? '';

        if (empty($slug) || empty($namespace)) {
            WP_CLI::error('Both --slug and --namespace are required');
        }

        WP_CLI::log('Starting plugin rename process...');

        try {
            // Update main plugin file
            $this->updateMainPluginFile($slug, $namespace, $author, $description);
            
            // Update composer.json
            $this->updateComposerJson($slug, $namespace, $author);
            
            // Update package.json
            $this->updatePackageJson($slug, $author, $description);
            
            // Update namespace in PHP files
            $this->updateNamespaceInFiles($namespace);
            
            // Update text domain
            $this->updateTextDomain($slug);
            
            // Update constants
            $this->updateConstants($slug, $namespace);
            
            WP_CLI::success("Plugin successfully renamed to '{$slug}' with namespace '{$namespace}'");
            
        } catch (Exception $e) {
            WP_CLI::error('Error during rename: ' . $e->getMessage());
        }
    }

    /**
     * Update the main plugin file header
     */
    protected function updateMainPluginFile(string $slug, string $namespace, string $author, string $description): void
    {
        $pluginFile = SMART_PRICING_AND_DISCOUNTS_FOR_WOOCOMMERCE_PATH . 'smart-pricing.php';
        
        if (!file_exists($pluginFile)) {
            throw new Exception('Main plugin file not found');
        }

        $content = file_get_contents($pluginFile);
        
        // Update plugin header
        $content = preg_replace('/Plugin Name: .*/', "Plugin Name: " . ucwords(str_replace('-', ' ', $slug)), $content);
        $content = preg_replace('/Plugin URI: .*/', "Plugin URI: https://example.com/{$slug}", $content);
        $content = preg_replace('/Description: .*/', "Description: {$description}", $content);
        $content = preg_replace('/Author: .*/', "Author: {$author}", $content);
        $content = preg_replace('/Text Domain: .*/', "Text Domain: {$slug}", $content);
        
        // Update constants
        $content = preg_replace('/define\(\'SMART_PRICING_AND_DISCOUNTS_FOR_WOOCOMMERCE_/', "define('" . strtoupper(str_replace('-', '_', $slug)) . '_', $content);
        
        file_put_contents($pluginFile, $content);
        
        WP_CLI::log('✓ Updated main plugin file');
    }

    /**
     * Update composer.json
     */
    protected function updateComposerJson(string $slug, string $namespace, string $author): void
    {
        $composerFile = SMART_PRICING_AND_DISCOUNTS_FOR_WOOCOMMERCE_PATH . 'composer.json';
        
        if (!file_exists($composerFile)) {
            return;
        }

        $composer = json_decode(file_get_contents($composerFile), true);
        
        $composer['name'] = $slug . '/wordpress-plugin';
        $composer['autoload']['psr-4'] = ["{$namespace}\\" => "app/"];
        $composer['authors'][0]['name'] = $author;
        
        file_put_contents($composerFile, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        
        WP_CLI::log('✓ Updated composer.json');
    }

    /**
     * Update package.json
     */
    protected function updatePackageJson(string $slug, string $author, string $description): void
    {
        $packageFile = SMART_PRICING_AND_DISCOUNTS_FOR_WOOCOMMERCE_PATH . 'package.json';
        
        if (!file_exists($packageFile)) {
            return;
        }

        $package = json_decode(file_get_contents($packageFile), true);
        
        $package['name'] = $slug;
        $package['description'] = $description;
        $package['author'] = $author;
        
        file_put_contents($packageFile, json_encode($package, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        
        WP_CLI::log('✓ Updated package.json');
    }

    /**
     * Update namespace in PHP files
     */
    protected function updateNamespaceInFiles(string $namespace): void
    {
        $files = $this->getPhpFiles(SMART_PRICING_AND_DISCOUNTS_FOR_WOOCOMMERCE_PATH . 'app/');
        
        foreach ($files as $file) {
            $content = file_get_contents($file);
            $content = preg_replace('/namespace MyPlugin\\\/', "namespace {$namespace}\\\\", $content);
            $content = preg_replace('/use MyPlugin\\\/', "use {$namespace}\\\\", $content);
            $content = preg_replace('/MyPlugin\\\\/', "{$namespace}\\\\", $content);
            
            file_put_contents($file, $content);
        }
        
        WP_CLI::log('✓ Updated namespaces in PHP files');
    }

    /**
     * Update text domain in files
     */
    protected function updateTextDomain(string $slug): void
    {
        $files = $this->getPhpFiles(SMART_PRICING_AND_DISCOUNTS_FOR_WOOCOMMERCE_PATH);
        
        foreach ($files as $file) {
            $content = file_get_contents($file);
            $content = preg_replace('/\'my-plugin\'/', "'{$slug}'", $content);
            $content = preg_replace('/"smart-pricing"/', "\"{$slug}\"", $content);
            
            file_put_contents($file, $content);
        }
        
        WP_CLI::log('✓ Updated text domain');
    }

    /**
     * Update constants
     */
    protected function updateConstants(string $slug, string $namespace): void
    {
        $files = $this->getPhpFiles(SMART_PRICING_AND_DISCOUNTS_FOR_WOOCOMMERCE_PATH);
        $constantPrefix = strtoupper(str_replace('-', '_', $slug));
        
        foreach ($files as $file) {
            $content = file_get_contents($file);
            $content = preg_replace('/SMART_PRICING_AND_DISCOUNTS_FOR_WOOCOMMERCE_/', "{$constantPrefix}_", $content);
            
            file_put_contents($file, $content);
        }
        
        WP_CLI::log('✓ Updated constants');
    }

    /**
     * Get all PHP files in a directory recursively
     */
    protected function getPhpFiles(string $directory): array
    {
        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }
        
        return $files;
    }
}
