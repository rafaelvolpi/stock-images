<?php
/**
 * Stock Images Plugin Test Script
 * 
 * This script tests the basic functionality of the Stock Images plugin
 * Run this in your WordPress environment to verify everything is working
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    // If running standalone, include WordPress
    require_once('../../../wp-load.php');
}

// Test function
function test_stock_images_plugin() {
    echo "<h2>Stock Images Plugin Test Results</h2>\n";
    
    // Test 1: Check if plugin class exists
    if (class_exists('StockImages')) {
        echo "✅ Plugin class exists<br>\n";
    } else {
        echo "❌ Plugin class not found<br>\n";
        return;
    }
    
    // Test 2: Check if plugin instance can be created
    try {
        $plugin = StockImages::get_instance();
        echo "✅ Plugin instance created successfully<br>\n";
    } catch (Exception $e) {
        echo "❌ Failed to create plugin instance: " . $e->getMessage() . "<br>\n";
        return;
    }
    
    // Test 3: Check if required constants are defined
    $constants = ['STOCK_IMAGES_VERSION', 'STOCK_IMAGES_PLUGIN_URL', 'STOCK_IMAGES_PLUGIN_PATH'];
    foreach ($constants as $constant) {
        if (defined($constant)) {
            echo "✅ Constant {$constant} is defined<br>\n";
        } else {
            echo "❌ Constant {$constant} is not defined<br>\n";
        }
    }
    
    // Test 4: Check if assets exist
    $assets = [
        'js' => STOCK_IMAGES_PLUGIN_PATH . 'assets/js/stock-images.js',
        'css' => STOCK_IMAGES_PLUGIN_PATH . 'assets/css/stock-images.css',
        'scss' => STOCK_IMAGES_PLUGIN_PATH . 'assets/scss/stock-images.scss'
    ];
    
    foreach ($assets as $type => $path) {
        if (file_exists($path)) {
            echo "✅ {$type} asset exists: " . basename($path) . "<br>\n";
        } else {
            echo "❌ {$type} asset missing: " . basename($path) . "<br>\n";
        }
    }
    
    // Test 5: Check if templates exist
    $templates = [
        'admin' => STOCK_IMAGES_PLUGIN_PATH . 'templates/admin-page.php',
        'settings' => STOCK_IMAGES_PLUGIN_PATH . 'templates/settings-page.php'
    ];
    
    foreach ($templates as $type => $path) {
        if (file_exists($path)) {
            echo "✅ {$type} template exists<br>\n";
        } else {
            echo "❌ {$type} template missing<br>\n";
        }
    }
    
    // Test 6: Check API keys configuration
    $unsplash_key = get_option('unsplash_access_key');
    $pexels_key = get_option('pexels_api_key');
    
    if ($unsplash_key) {
        echo "✅ Unsplash API key configured<br>\n";
    } else {
        echo "⚠️ Unsplash API key not configured<br>\n";
    }
    
    if ($pexels_key) {
        echo "✅ Pexels API key configured<br>\n";
    } else {
        echo "⚠️ Pexels API key not configured<br>\n";
    }
    
    // Test 7: Check if hooks are registered
    global $wp_filter;
    $hooks = [
        'wp_ajax_stock_images_search',
        'wp_ajax_stock_images_import',
        'admin_menu'
    ];
    
    foreach ($hooks as $hook) {
        if (isset($wp_filter[$hook])) {
            echo "✅ Hook '{$hook}' is registered<br>\n";
        } else {
            echo "❌ Hook '{$hook}' is not registered<br>\n";
        }
    }
    
    echo "<br><strong>Test completed!</strong><br>\n";
    echo "If all tests pass, your plugin should be working correctly.<br>\n";
    echo "Configure your API keys in the settings to start using the plugin.<br>\n";
}

// Run the test
if (isset($_GET['test_stock_images'])) {
    test_stock_images_plugin();
}
?>

<!-- Test Button -->
<div style="margin: 20px; padding: 20px; border: 1px solid #ccc;">
    <h3>Stock Images Plugin Test</h3>
    <p>Click the button below to test the plugin functionality:</p>
    <a href="?test_stock_images=1" class="button button-primary">Run Plugin Test</a>
</div> 