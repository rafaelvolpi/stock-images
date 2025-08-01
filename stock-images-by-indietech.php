<?php
/**
 * Plugin Name: Stock Images by Indietech
 * Plugin URI: https://github.com/rafaelvolpi/stock-images
 * Description: Integrate stock photos directly into your WordPress Media Library. Search and import high-quality images from multiple sources.
 * Version: 1.2.0
 * Author: Indietech
 * Author URI: https://indietechsolutions.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: stock-images-by-indietech
 * Domain Path: /languages
 * Requires at least: 6.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 */


// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('STK_IMG_ITS_VERSION', '1.2.0');
define('STK_IMG_ITS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('STK_IMG_ITS_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('STK_IMG_ITS_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Main plugin class
class StockImagesByITS {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('init', array($this, 'init'));
        add_action('admin_init', array($this, 'admin_init'));
        add_action('network_admin_init', array($this, 'network_admin_init')); // Add this line
        add_action('wp_ajax_stk_img_its_search', array($this, 'ajax_search'));
        add_action('wp_ajax_stk_img_its_import', array($this, 'ajax_import'));
        add_action('wp_ajax_stk_img_its_get_stats', array($this, 'ajax_get_stats'));
        add_action('wp_ajax_stk_img_its_get_recent', array($this, 'ajax_get_recent'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('enqueue_block_editor_assets', array($this, 'enqueue_block_editor_assets'));
        add_action('enqueue_block_assets', array($this, 'enqueue_block_assets'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('network_admin_menu', array($this, 'add_network_admin_menu')); // Add this line
        add_action('add_attachment', array($this, 'add_stock_attribution'));
        add_filter('attachment_fields_to_edit', array($this, 'add_stock_fields'), 10, 2);
        add_filter('attachment_fields_to_save', array($this, 'save_stock_fields'), 10, 2);
    }
    
    public function init() {
        // WordPress.org will automatically load translations for the plugin
        // No need to manually call load_plugin_textdomain() since WordPress 4.6
    }
    
    public function admin_init() {
        // Only register settings for single site or if network settings are not configured
        if (!is_multisite() || !$this->is_network_configured()) {
            $this->register_site_settings();
        }
    }
    
    public function network_admin_init() {
        // Register network settings
        $this->register_network_settings();
    }
    
    private function register_site_settings() {
        // Register settings for single site
        register_setting('stk_img_its_options', 'stk_img_its_unsplash_access_key', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        register_setting('stk_img_its_options', 'stk_img_its_unsplash_secret_key', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        register_setting('stk_img_its_options', 'stk_img_its_pexels_api_key', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        register_setting('stk_img_its_options', 'stk_img_its_pixabay_api_key', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        register_setting('stk_img_its_options', 'stk_img_its_max_size', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
    }
    
    private function register_network_settings() {
        // Register network settings
        register_setting('stk_img_its_network_options', 'stk_img_its_unsplash_access_key', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        register_setting('stk_img_its_network_options', 'stk_img_its_unsplash_secret_key', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        register_setting('stk_img_its_network_options', 'stk_img_its_pexels_api_key', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        register_setting('stk_img_its_network_options', 'stk_img_its_pixabay_api_key', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        register_setting('stk_img_its_network_options', 'stk_img_its_max_size', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
    }
    
    public function enqueue_admin_scripts($hook) {
        // TEMP: Always enqueue for debugging
        wp_enqueue_script(
            'stock-images-by-indietech',
            STK_IMG_ITS_PLUGIN_URL . 'assets/js/stock-images.js',
            array('jquery', 'media-views'),
            STK_IMG_ITS_VERSION,
            true
        );

        wp_enqueue_style(
            'stock-images-by-indietech',
            STK_IMG_ITS_PLUGIN_URL . 'assets/css/stock-images.css',
            array(),
            STK_IMG_ITS_VERSION
        );

        wp_localize_script('stock-images-by-indietech', 'stockImagesAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('stk_img_its_nonce'),
            'configured_apis' => $this->get_configured_apis_for_js(),
            'strings' => array(
                'searching' => __('Searching...', 'stock-images-by-indietech'),
                'no_results' => __('No images found.', 'stock-images-by-indietech'),
                'importing' => __('Importing...', 'stock-images-by-indietech'),
                'imported' => __('Image imported successfully!', 'stock-images-by-indietech'),
                'error' => __('An error occurred.', 'stock-images-by-indietech'),
                'stock_images' => __('Stock Images', 'stock-images-by-indietech'),
                'search_stock_images' => __('Search Stock Images', 'stock-images-by-indietech'),
                'search_placeholder' => __('Search for images...', 'stock-images-by-indietech'),
                'search_button' => __('Search', 'stock-images-by-indietech'),
                'load_more' => __('Load More', 'stock-images-by-indietech'),
            )
        ));
    }
    
    public function enqueue_block_editor_assets() {
        wp_enqueue_script(
            'stock-images-by-indietech',
            STK_IMG_ITS_PLUGIN_URL . 'assets/js/stock-images.js',
            array('jquery', 'media-views'),
            STK_IMG_ITS_VERSION,
            true
        );

        wp_enqueue_style(
            'stock-images-by-indietech',
            STK_IMG_ITS_PLUGIN_URL . 'assets/css/stock-images.css',
            array(),
            STK_IMG_ITS_VERSION
        );

        wp_localize_script('stock-images-by-indietech', 'stockImagesAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('stk_img_its_nonce'),
            'configured_apis' => $this->get_configured_apis_for_js(),
            'strings' => array(
                'searching' => __('Searching...', 'stock-images-by-indietech'),
                'no_results' => __('No images found.', 'stock-images-by-indietech'),
                'importing' => __('Importing...', 'stock-images-by-indietech'),
                'imported' => __('Image imported successfully!', 'stock-images-by-indietech'),
                'error' => __('An error occurred.', 'stock-images-by-indietech'),
                'stock_images' => __('Stock Images', 'stock-images-by-indietech'),
                'search_stock_images' => __('Search Stock Images', 'stock-images-by-indietech'),
                'search_placeholder' => __('Search for images...', 'stock-images-by-indietech'),
                'search_button' => __('Search', 'stock-images-by-indietech'),
                'load_more' => __('Load More', 'stock-images-by-indietech'),
            )
        ));
    }
    
    public function enqueue_block_assets() {
        // Only enqueue in the editor (not on the frontend)
        if (!is_admin()) return;
        wp_enqueue_script(
            'stock-images-by-indietech',
            STK_IMG_ITS_PLUGIN_URL . 'assets/js/stock-images.js',
            array('jquery', 'media-views'),
            STK_IMG_ITS_VERSION,
            true
        );
        wp_enqueue_style(
            'stock-images-by-indietech',
            STK_IMG_ITS_PLUGIN_URL . 'assets/css/stock-images.css',
            array(),
            STK_IMG_ITS_VERSION
        );
        wp_localize_script('stock-images-by-indietech', 'stockImagesAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('stk_img_its_nonce'),
            'configured_apis' => $this->get_configured_apis_for_js(),
            'strings' => array(
                'searching' => __('Searching...', 'stock-images-by-indietech'),
                'no_results' => __('No images found.', 'stock-images-by-indietech'),
                'importing' => __('Importing...', 'stock-images-by-indietech'),
                'imported' => __('Image imported successfully!', 'stock-images-by-indietech'),
                'error' => __('An error occurred.', 'stock-images-by-indietech'),
                'stock_images' => __('Stock Images', 'stock-images-by-indietech'),
                'search_stock_images' => __('Search Stock Images', 'stock-images-by-indietech'),
                'search_placeholder' => __('Search for images...', 'stock-images-by-indietech'),
                'search_button' => __('Search', 'stock-images-by-indietech'),
                'load_more' => __('Load More', 'stock-images-by-indietech'),
            )
        ));
    }
    
    public function add_admin_menu() {
        // Add Stock Images as a submenu under Media
        add_submenu_page(
            'upload.php', // Parent: Media
            __('Stock Images', 'stock-images-by-indietech'),
            __('Stock Images', 'stock-images-by-indietech'),
            'upload_files',
            'stock-images-by-indietech',
            array($this, 'admin_page')
        );
        
        // Only add site settings if not multisite or if network settings are not configured
        if (!is_multisite() || !$this->is_network_configured()) {
            add_submenu_page(
                'options-general.php', // Parent: Settings
                __('Stock Images Settings', 'stock-images-by-indietech'),
                __('Stock Images', 'stock-images-by-indietech'),
                'manage_options',
                'stock-images-settings',
                array($this, 'settings_page')
            );
        }
    }
    
    public function add_network_admin_menu() {
        // Add network settings page
        add_submenu_page(
            'settings.php', // Parent: Network Settings
            __('Stock Images Settings', 'stock-images-by-indietech'),
            __('Stock Images', 'stock-images-by-indietech'),
            'manage_network_options',
            'stock-images-network-settings',
            array($this, 'network_settings_page')
        );
    }
    
    public function admin_page() {
        include STK_IMG_ITS_PLUGIN_PATH . 'templates/admin-page.php';
    }
    
    public function settings_page() {
        include STK_IMG_ITS_PLUGIN_PATH . 'templates/settings-page.php';
    }
    
    public function network_settings_page() {
        include STK_IMG_ITS_PLUGIN_PATH . 'templates/network-settings-page.php';
    }
    
    // Helper method to get option value (checks network first, then site)
    private function get_option_value($option_name, $default = '') {
        if (is_multisite()) {
            // Check network option first
            $network_value = get_site_option($option_name);
            if ($network_value !== false && $network_value !== '') {
                return $network_value;
            }
        }
        
        // Fall back to site option
        return get_option($option_name, $default);
    }
    
    // Helper method to check if network is configured
    public function is_network_configured() {
        if (!is_multisite()) {
            return false;
        }
        
        $network_configured = false;
        $api_keys = array(
            'stk_img_its_unsplash_access_key',
            'stk_img_its_pexels_api_key',
            'stk_img_its_pixabay_api_key'
        );
        
        foreach ($api_keys as $key) {
            if (get_site_option($key)) {
                $network_configured = true;
                break;
            }
        }
        
        return $network_configured;
    }

    public function ajax_search() {
        check_ajax_referer('stk_img_its_nonce', 'nonce');
        
        if (!current_user_can('upload_files')) {
            wp_die(esc_html__('You do not have permission to perform this action.', 'stock-images-by-indietech'));
        }
        
        // SANITIZE EARLY
        $query = isset($_POST['query']) ? sanitize_text_field(wp_unslash($_POST['query'])) : '';
        $page = isset($_POST['page']) ? intval(wp_unslash($_POST['page'])) : 1;
        $per_page = 20;
        $source = isset($_POST['source']) ? sanitize_text_field(wp_unslash($_POST['source'])) : 'unsplash';
        
        // VALIDATE
        $allowed_sources = array('unsplash', 'pexels', 'pixabay');
        if (!in_array($source, $allowed_sources, true)) {
            wp_send_json_error(__('Invalid source specified.', 'stock-images-by-indietech'));
        }
        
        // Validate page number
        if ($page < 1) {
            $page = 1;
        }
        
        if ($source === 'unsplash') {
            $this->search_unsplash($query, $page, $per_page);
        } elseif ($source === 'pexels') {
            $this->search_pexels($query, $page, $per_page);
        } elseif ($source === 'pixabay') {
            $this->search_pixabay($query, $page, $per_page);
        } else {
            wp_send_json_error(__('Invalid source specified.', 'stock-images-by-indietech'));
        }
    }
    
    private function search_unsplash($query, $page, $per_page) {
        $access_key = $this->get_option_value('stk_img_its_unsplash_access_key');
        
        if (empty($access_key)) {
            wp_send_json_error(__('Unsplash API key not configured.', 'stock-images-by-indietech'));
        }
        
        $url = add_query_arg(array(
            'query' => urlencode($query),
            'page' => $page,
            'per_page' => $per_page,
            'client_id' => $access_key
        ), 'https://api.unsplash.com/search/photos');
        
        $response = wp_remote_get($url, array(
            'timeout' => 15,
            'headers' => array(
                'Accept-Version' => 'v1'
            )
        ));
        
        if (is_wp_error($response)) {
            wp_send_json_error(__('Failed to connect to Unsplash API: ', 'stock-images-by-indietech') . $response->get_error_message());
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            $body = wp_remote_retrieve_body($response);
            wp_send_json_error(__('Unsplash API error (HTTP ', 'stock-images-by-indietech') . $response_code . '): ' . $body);
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (empty($data) || !isset($data['results'])) {
            wp_send_json_error(__('Invalid response from Unsplash API: ', 'stock-images-by-indietech') . $body);
        }
        
        $images = array();
        foreach ($data['results'] as $photo) {
            $images[] = array(
                'id' => $photo['id'],
                'urls' => array(
                    'small' => $photo['urls']['small'],
                    'regular' => $photo['urls']['regular'],
                    'full' => $photo['urls']['full']
                ),
                'thumb' => $photo['urls']['thumb'],
                'alt_description' => $photo['alt_description'] ?? $photo['description'] ?? '',
                'description' => $photo['description'] ?? '',
                'user' => array(
                    'name' => $photo['user']['name'] ?? '',
                    'links' => array(
                        'html' => $photo['user']['links']['html'] ?? ''
                    )
                ),
                'photographer' => $photo['user']['name'] ?? '',
                'photographer_url' => $photo['user']['links']['html'] ?? '',
                'download_url' => $photo['links']['download'] ?? '',
                'source' => 'unsplash'
            );
        }
        
        wp_send_json_success(array(
            'results' => $images,
            'total' => $data['total'] ?? 0,
            'total_pages' => $data['total_pages'] ?? 0
        ));
    }
    
    private function search_pexels($query, $page, $per_page) {
        $api_key = $this->get_option_value('stk_img_its_pexels_api_key');
        
        if (empty($api_key)) {
            wp_send_json_error(__('Pexels API key not configured.', 'stock-images-by-indietech'));
        }
        
        $url = add_query_arg(array(
            'query' => urlencode($query),
            'page' => $page,
            'per_page' => $per_page
        ), 'https://api.pexels.com/v1/search');
        
        // Pexels API expects the API key directly as Authorization header
        $response = wp_remote_get($url, array(
            'timeout' => 15,
            'headers' => array(
                'Authorization' => $api_key
            )
        ));
        
        if (is_wp_error($response)) {
            wp_send_json_error(__('Failed to connect to Pexels API: ', 'stock-images-by-indietech') . $response->get_error_message());
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            $body = wp_remote_retrieve_body($response);
            wp_send_json_error(__('Pexels API error (HTTP ', 'stock-images-by-indietech') . $response_code . '): ' . $body);
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (empty($data) || !isset($data['photos'])) {
            wp_send_json_error(__('Invalid response from Pexels API: ', 'stock-images-by-indietech') . $body);
        }
        
        $images = array();
        foreach ($data['photos'] as $photo) {
            $images[] = array(
                'id' => $photo['id'],
                'urls' => array(
                    'small' => $photo['src']['small'],
                    'regular' => $photo['src']['large2x'],
                    'full' => $photo['src']['original']
                ),
                'thumb' => $photo['src']['medium'],
                'alt_description' => $photo['alt'] ?? '',
                'description' => $photo['alt'] ?? '',
                'user' => array(
                    'name' => $photo['photographer'] ?? '',
                    'links' => array(
                        'html' => $photo['photographer_url'] ?? ''
                    )
                ),
                'photographer' => $photo['photographer'] ?? '',
                'photographer_url' => $photo['photographer_url'] ?? '',
                'download_url' => $photo['url'] ?? '',
                'source' => 'pexels'
            );
        }
        
        wp_send_json_success(array(
            'results' => $images,
            'total' => $data['total_results'] ?? 0,
            'total_pages' => ceil(($data['total_results'] ?? 0) / $per_page)
        ));
    }
    
    private function search_pixabay($query, $page, $per_page) {
        $api_key = $this->get_option_value('stk_img_its_pixabay_api_key');
        
        if (empty($api_key)) {
            wp_send_json_error(__('Pixabay API key not configured.', 'stock-images-by-indietech'));
        }
        
        $url = add_query_arg(array(
            'q' => urlencode($query),
            'page' => $page,
            'per_page' => $per_page,
            'key' => $api_key,
            'image_type' => 'photo',
            'safesearch' => 'true'
        ), 'https://pixabay.com/api/');
        
        $response = wp_remote_get($url, array(
            'timeout' => 15
        ));
        
        if (is_wp_error($response)) {
            wp_send_json_error(__('Failed to connect to Pixabay API: ', 'stock-images-by-indietech') . $response->get_error_message());
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            $body = wp_remote_retrieve_body($response);
            wp_send_json_error(__('Pixabay API error (HTTP ', 'stock-images-by-indietech') . $response_code . '): ' . $body);
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (empty($data) || !isset($data['hits'])) {
            wp_send_json_error(__('Invalid response from Pixabay API: ', 'stock-images-by-indietech') . $body);
        }
        
        $images = array();
        foreach ($data['hits'] as $photo) {
            $images[] = array(
                'id' => $photo['id'],
                'urls' => array(
                    'small' => $photo['previewURL'],
                    'regular' => $photo['webformatURL'],
                    'full' => $photo['largeImageURL']
                ),
                'thumb' => $photo['previewURL'],
                'alt_description' => $photo['tags'] ?? '',
                'description' => $photo['tags'] ?? '',
                'user' => array(
                    'name' => $photo['user'] ?? '',
                    'links' => array(
                        'html' => $photo['pageURL'] ?? ''
                    )
                ),
                'photographer' => $photo['user'] ?? '',
                'photographer_url' => $photo['pageURL'] ?? '',
                'download_url' => $photo['webformatURL'] ?? '',
                'source' => 'pixabay'
            );
        }
        
        wp_send_json_success(array(
            'results' => $images,
            'total' => $data['totalHits'] ?? 0,
            'total_pages' => ceil(($data['totalHits'] ?? 0) / $per_page)
        ));
    }
    
    public function ajax_import() {
        check_ajax_referer('stk_img_its_nonce', 'nonce');
        
        if (!current_user_can('upload_files')) {
            wp_die(esc_html__('You do not have permission to perform this action.', 'stock-images-by-indietech'));
        }
        
        // SANITIZE EARLY
        $image_url = isset($_POST['image_url']) ? esc_url_raw(wp_unslash($_POST['image_url'])) : '';
        $image_data = array();
        if (!empty($_POST['image_data'])) {
            // phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
            $raw_image_data = wp_unslash($_POST['image_data']);
            // phpcs:enable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
            if (is_array($raw_image_data)) {
                // Define which fields need which sanitization
                $text_fields = array('id', 'alt_description', 'alt', 'photographer', 'source');
                $url_fields = array('photographer_url');
                
                // Sanitize text fields
                foreach ($text_fields as $field) {
                    if (isset($raw_image_data[$field])) {
                        $image_data[$field] = sanitize_text_field($raw_image_data[$field]);
                    }
                }
                
                // Sanitize URL fields
                foreach ($url_fields as $field) {
                    if (isset($raw_image_data[$field])) {
                        $image_data[$field] = esc_url_raw($raw_image_data[$field]);
                    }
                }
            }
        }
        $selected_size = isset($_POST['selected_size']) ? sanitize_text_field(wp_unslash($_POST['selected_size'])) : '';
        
        // VALIDATE
        if (empty($image_url) || empty($image_data)) {
            wp_send_json_error(__('Invalid image data provided.', 'stock-images-by-indietech'));
        }
        
        // Validate that required fields are present and not empty
        $required_fields = array('id', 'photographer', 'photographer_url', 'source');
        foreach ($required_fields as $field) {
            if (empty($image_data[$field])) {
                wp_send_json_error(__('Missing required field: ', 'stock-images-by-indietech') . $field);
            }
        }
        
        // Validate source is one of the allowed values
        $allowed_sources = array('unsplash', 'pexels', 'pixabay');
        if (!in_array($image_data['source'], $allowed_sources, true)) {
            wp_send_json_error(__('Invalid source specified.', 'stock-images-by-indietech'));
        }
        
        // Sanitize image_data
        $sanitized_data = array(
            'id' => sanitize_text_field($image_data['id']),
            'alt' => sanitize_text_field($image_data['alt_description'] ?? $image_data['alt'] ?? ''),
            'photographer' => sanitize_text_field($image_data['photographer']),
            'photographer_url' => esc_url_raw($image_data['photographer_url']),
            'source' => sanitize_text_field($image_data['source'])
        );
        
        // Get the appropriate image URL based on selected size
        $max_size = !empty($selected_size) ? $selected_size : $this->get_option_value('stk_img_its_max_size', 'medium');
        
        // Determine the best image URL based on source and size
        $download_url = $image_url;
        
        // For now, let's use the original URL and let the APIs handle sizing
        // This is more reliable than trying to manipulate URLs
        if ($sanitized_data['source'] === 'unsplash') {
            // Unsplash URLs already come with the correct size from the API
            $download_url = $image_url;
        } elseif ($sanitized_data['source'] === 'pexels') {
            // Pexels URLs already come with the correct size from the API
            $download_url = $image_url;
        } elseif ($sanitized_data['source'] === 'pixabay') {
            // Pixabay URLs already come with the correct size from the API
            $download_url = $image_url;
        }
        
        // Download the image
        $response = wp_remote_get($download_url, array(
            'timeout' => 30,
            'user-agent' => 'WordPress/' . get_bloginfo('version') . '; ' . get_bloginfo('url')
        ));
        
        if (is_wp_error($response)) {
            wp_send_json_error(__('Failed to download image: ', 'stock-images-by-indietech') . $response->get_error_message());
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            $body = wp_remote_retrieve_body($response);
            wp_send_json_error(__('Failed to download image. HTTP error: ', 'stock-images-by-indietech') . $response_code . ' - ' . $body);
        }
        
        $headers = wp_remote_retrieve_headers($response);
        $content_type = wp_remote_retrieve_header($response, 'content-type');
        
        // Validate content type
        if (strpos($content_type, 'image/') !== 0) {
            wp_send_json_error(__('Invalid content type. Expected image.', 'stock-images-by-indietech'));
        }
        
        // Get file extension from content type
        $extensions = array(
            'image/jpeg' => 'jpg',
            'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp'
        );
        
        $extension = $extensions[$content_type] ?? 'jpg';
        
        // Generate filename
        $filename = sanitize_file_name($sanitized_data['alt'] ?: 'stock-image-' . $sanitized_data['id']);
        $filename = $filename . '.' . $extension;
        
        // Ensure unique filename
        $counter = 1;
        $original_filename = $filename;
        while (file_exists(wp_upload_dir()['path'] . '/' . $filename)) {
            $filename = pathinfo($original_filename, PATHINFO_FILENAME) . '-' . $counter . '.' . $extension;
            $counter++;
        }
        
        // Save the image
        $upload_dir = wp_upload_dir();
        $file_path = $upload_dir['path'] . '/' . $filename;
        
        $file_content = wp_remote_retrieve_body($response);
        
        // Check if we actually got content
        if (empty($file_content)) {
            wp_send_json_error(__('Failed to retrieve image content. The response was empty.', 'stock-images-by-indietech'));
        }
        
        // Check if content is reasonable size (at least 1KB for an image)
        if (strlen($file_content) < 1024) {
            wp_send_json_error(__('Image content is too small. Expected at least 1KB, got ', 'stock-images-by-indietech') . strlen($file_content) . ' bytes.');
        }
        
        $saved = file_put_contents($file_path, $file_content);
        
        if ($saved === false) {
            wp_send_json_error(__('Failed to save image to server.', 'stock-images-by-indietech'));
        }
        
        // Verify the file was actually written and has content
        if (!file_exists($file_path) || filesize($file_path) === 0) {
            wp_send_json_error(__('File was not properly saved to server.', 'stock-images-by-indietech'));
        }
        
        // Prepare file array for wp_handle_sideload
        $file_array = array(
            'name' => $filename,
            'tmp_name' => $file_path,
            'type' => $content_type,
            'error' => 0
        );
        
        // Move file to proper location and generate thumbnails
        $file_info = wp_handle_sideload($file_array, array('test_form' => false));
        
        if (isset($file_info['error'])) {
            wp_send_json_error(__('Error processing image: ', 'stock-images-by-indietech') . $file_info['error']);
        }
        
        // Generate attribution text for caption (HTML with links)
        $attribution_text = $this->generate_attribution_text($sanitized_data['photographer'], $sanitized_data['photographer_url'], $sanitized_data['source'], true);
        
        // Prepare attachment data
        $attachment_data = array(
            'post_title' => $sanitized_data['alt'] ?: __('Stock Image', 'stock-images-by-indietech'),
            'post_content' => '', // Description field (empty)
            'post_excerpt' => $attribution_text, // Caption field
            'post_status' => 'inherit',
            'post_mime_type' => $content_type
        );
        
        // Insert attachment
        $attachment_id = wp_insert_attachment($attachment_data, $file_info['file']);
        
        if (is_wp_error($attachment_id)) {
            wp_send_json_error(__('Failed to create attachment.', 'stock-images-by-indietech'));
        }
        
        // Generate attachment metadata
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attachment_metadata = wp_generate_attachment_metadata($attachment_id, $file_info['file']);
        wp_update_attachment_metadata($attachment_id, $attachment_metadata);
        
        // Save alt text
        if (!empty($sanitized_data['alt'])) {
            update_post_meta($attachment_id, '_wp_attachment_image_alt', $sanitized_data['alt']);
        }
        
        // Save stock image metadata
        update_post_meta($attachment_id, '_stk_img_its_source', $sanitized_data['source']);
        update_post_meta($attachment_id, '_stk_img_its_original_id', $sanitized_data['id']);
        update_post_meta($attachment_id, '_stk_img_its_photographer', $sanitized_data['photographer']);
        update_post_meta($attachment_id, '_stk_img_its_photographer_url', $sanitized_data['photographer_url']);
        update_post_meta($attachment_id, '_stk_img_its_attribution_required', 'yes');
        
        // Clear cache
        $this->clear_cache();
        
        // Trigger action for other plugins
        do_action('stk_img_its_image_imported', $attachment_id, $sanitized_data);
        
        wp_send_json_success(array(
            'attachment_id' => $attachment_id,
            'url' => wp_get_attachment_url($attachment_id),
            'message' => __('Image imported successfully!', 'stock-images-by-indietech')
        ));
    }
    
    private function clear_cache() {
        wp_cache_delete('stk_img_its_total_imported', 'stk_img_its');
        wp_cache_delete('stk_img_its_this_month_' . gmdate('Y-m'), 'stk_img_its');
        wp_cache_delete('stk_img_its_recent_imports', 'stk_img_its');
    }
    
    public function add_stock_attribution($attachment_id) {
        // This will be handled by the attachment fields
    }
    
    public function add_stock_fields($form_fields, $post) {
        $source = get_post_meta($post->ID, '_stk_img_its_source', true);
        
        if (empty($source)) {
            return $form_fields;
        }
        
        $photographer = get_post_meta($post->ID, '_stk_img_its_photographer', true);
        $photographer_url = get_post_meta($post->ID, '_stk_img_its_photographer_url', true);
        $original_id = get_post_meta($post->ID, '_stk_img_its_original_id', true);
        $attribution_required = get_post_meta($post->ID, '_stk_img_its_attribution_required', true);
        
        // Add stock image information
        $form_fields['stk_img_its_source'] = array(
            'label' => __('Stock Source', 'stock-images-by-indietech'),
                    'input' => 'html',
            'html' => '<input type="text" readonly value="' . esc_attr(ucfirst($source)) . '" class="widefat" />'
        );
        
        $form_fields['stk_img_its_photographer'] = array(
            'label' => __('Photographer', 'stock-images-by-indietech'),
                    'input' => 'html',
            'html' => '<input type="text" name="attachments[' . $post->ID . '][stk_img_its_photographer]" value="' . esc_attr($photographer) . '" class="widefat" />'
        );
        
        $form_fields['stk_img_its_photographer_url'] = array(
            'label' => __('Photographer URL', 'stock-images-by-indietech'),
                    'input' => 'html',
            'html' => '<input type="url" name="attachments[' . $post->ID . '][stk_img_its_photographer_url]" value="' . esc_attr($photographer_url) . '" class="widefat" />'
        );
        
        $form_fields['stk_img_its_original_id'] = array(
            'label' => __('Original ID', 'stock-images-by-indietech'),
            'input' => 'html',
            'html' => '<input type="text" readonly value="' . esc_attr($original_id) . '" class="widefat" />'
        );
        
        $form_fields['stk_img_its_attribution_required'] = array(
            'label' => __('Attribution Required', 'stock-images-by-indietech'),
            'input' => 'html',
            'html' => '<select name="attachments[' . $post->ID . '][stk_img_its_attribution_required]" class="widefat">
                <option value="yes" ' . selected($attribution_required, 'yes', false) . '>' . __('Yes', 'stock-images-by-indietech') . '</option>
                <option value="no" ' . selected($attribution_required, 'no', false) . '>' . __('No', 'stock-images-by-indietech') . '</option>
            </select>'
        );
        
        // Add attribution preview (HTML)
        $attribution_text = $this->generate_attribution_text($photographer, $photographer_url, $source, true);
        $form_fields['stk_img_its_attribution_preview'] = array(
            'label' => __('Attribution Text', 'stock-images-by-indietech'),
            'input' => 'html',
            'html' => '<textarea readonly class="widefat" rows="3">' . esc_textarea($attribution_text) . '</textarea>
            <p class="description">' . __('This is the recommended attribution text for this image.', 'stock-images-by-indietech') . '</p>'
        );
        
        return $form_fields;
    }
    
    public function save_stock_fields($post, $attachment) {
        if (isset($attachment['stk_img_its_photographer'])) {
            update_post_meta($post['ID'], '_stk_img_its_photographer', sanitize_text_field($attachment['stk_img_its_photographer']));
        }
        if (isset($attachment['stk_img_its_photographer_url'])) {
            update_post_meta($post['ID'], '_stk_img_its_photographer_url', esc_url_raw($attachment['stk_img_its_photographer_url']));
        }
        if (isset($attachment['stk_img_its_attribution_required'])) {
            update_post_meta($post['ID'], '_stk_img_its_attribution_required', sanitize_text_field($attachment['stk_img_its_attribution_required']));
        }
        return $post;
    }
    
    private function generate_attribution_text($photographer, $photographer_url, $source, $html = true) {
        $text = '';
        
        if ($source === 'unsplash') {
            if ($html) {
                $text = sprintf(
                    // translators: %1$s is the photographer name (with link if available), %2$s is the Unsplash link
                    __('Photo by %1$s on %2$s', 'stock-images-by-indietech'),
                    $photographer_url ? '<a href="' . esc_url($photographer_url) . '">' . esc_html($photographer) . '</a>' : esc_html($photographer),
                    '<a href="https://unsplash.com">Unsplash</a>'
                );
            } else {
                $text = sprintf(
                    // translators: %s is the photographer name
                    __('Photo by %s on Unsplash', 'stock-images-by-indietech'),
                    $photographer
                );
            }
        } elseif ($source === 'pexels') {
            if ($html) {
                $text = sprintf(
                    // translators: %1$s is the photographer name (with link if available), %2$s is the Pexels link
                    __('Photo by %1$s from %2$s', 'stock-images-by-indietech'),
                    $photographer_url ? '<a href="' . esc_url($photographer_url) . '">' . esc_html($photographer) . '</a>' : esc_html($photographer),
                    '<a href="https://www.pexels.com">Pexels</a>'
                );
            } else {
                $text = sprintf(
                    // translators: %s is the photographer name
                    __('Photo by %s from Pexels', 'stock-images-by-indietech'),
                    $photographer
                );
            }
        } elseif ($source === 'pixabay') {
            if ($html) {
                $text = sprintf(
                    // translators: %1$s is the photographer name (with link if available), %2$s is the Pixabay link
                    __('Image by %1$s from %2$s', 'stock-images-by-indietech'),
                    $photographer_url ? '<a href="' . esc_url($photographer_url) . '">' . esc_html($photographer) . '</a>' : esc_html($photographer),
                    '<a href="https://pixabay.com">Pixabay</a>'
                );
            } else {
                $text = sprintf(
                    // translators: %s is the photographer name
                    __('Image by %s from Pixabay', 'stock-images-by-indietech'),
                    $photographer
                );
            }
        }
        
        return apply_filters('stk_img_its_attribution_text', $text, $photographer, $photographer_url, $source);
    }
    
    public function get_imported_count() {
        $cache_key = 'stk_img_its_total_imported';
        $count = wp_cache_get($cache_key, 'stk_img_its');
        
        if ($count === false) {
            global $wpdb;
            // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery
            $count = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->postmeta} 
                WHERE meta_key = %s",
                '_stk_img_its_source'
            ));
            // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery
            wp_cache_set($cache_key, $count, 'stk_img_its', HOUR_IN_SECONDS);
        }
        
        return intval($count);
    }
    
    public function get_this_month_count() {
        $cache_key = 'stk_img_its_this_month_' . gmdate('Y-m');
        $count = wp_cache_get($cache_key, 'stk_img_its');
        
        if ($count === false) {
            global $wpdb;
            
            $start_date = gmdate('Y-m-01 00:00:00');
            $end_date = gmdate('Y-m-t 23:59:59');
            // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery
            $count = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->postmeta} pm
                JOIN {$wpdb->posts} p ON pm.post_id = p.ID
                WHERE pm.meta_key = %s
                AND p.post_type = 'attachment'
                AND p.post_date >= %s
                AND p.post_date <= %s",
                '_stk_img_its_source',
                $start_date,
                $end_date
            ));
            // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery
            wp_cache_set($cache_key, $count, 'stk_img_its', HOUR_IN_SECONDS);
        }
        
        return intval($count);
    }
    

    
    public function display_recent_imports() {
        $cache_key = 'stk_img_its_recent_imports';
        $recent_imports = wp_cache_get($cache_key, 'stk_img_its');
        
        if ($recent_imports === false) {
            global $wpdb;
            // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery
            $recent_imports = $wpdb->get_results($wpdb->prepare(
                "SELECT p.ID, p.post_title, p.post_date, pm.meta_value as source
                FROM {$wpdb->posts} p
                JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                WHERE pm.meta_key = %s
                AND p.post_type = 'attachment'
                ORDER BY p.post_date DESC
                LIMIT 10",
                '_stk_img_its_source'
            ));
            // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery
            wp_cache_set($cache_key, $recent_imports, 'stk_img_its', 30 * MINUTE_IN_SECONDS);
        }
        
        if (empty($recent_imports)) {
            echo '<p>' . esc_html__('No recent imports found.', 'stock-images-by-indietech') . '</p>';
            return;
        }
        
        echo '<div class="stk-img-its-recent-imports">';
        foreach ($recent_imports as $import) {
            echo '<div class="stk-img-its-recent-item">';
            echo wp_get_attachment_image(
                $import->ID, 
                'thumbnail', 
                false, 
                array('class' => 'stk-img-its-recent-thumb')
            );
            echo '<div class="stk-img-its-recent-info">';
            echo '<h4>' . esc_html($import->post_title) . '</h4>';
            echo '<p class="stk-img-its-recent-source">' . esc_html(ucfirst($import->source)) . '</p>';
            echo '<p class="stk-img-its-recent-date">' . esc_html(date_i18n(get_option('date_format'), strtotime($import->post_date))) . '</p>';
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
    }
    
    public function get_configured_apis_for_js() {
        $apis = array();
        
        $unsplash_key = $this->get_option_value('stk_img_its_unsplash_access_key');
        if (!empty($unsplash_key)) {
            $apis[] = 'unsplash';
        }
        
        $pexels_key = $this->get_option_value('stk_img_its_pexels_api_key');
        if (!empty($pexels_key)) {
            $apis[] = 'pexels';
        }
        
        $pixabay_key = $this->get_option_value('stk_img_its_pixabay_api_key');
        if (!empty($pixabay_key)) {
            $apis[] = 'pixabay';
        }
        
        return $apis;
    }
    
    public function ajax_get_stats() {
        check_ajax_referer('stk_img_its_nonce', 'nonce');
        
        if (!current_user_can('upload_files')) {
            wp_die(esc_html__('You do not have permission to perform this action.', 'stock-images-by-indietech'));
        }
        
        $stats = array(
            'total_imported' => $this->get_imported_count(),
            'this_month' => $this->get_this_month_count()
        );
        
        wp_send_json_success($stats);
    }
    
    public function ajax_get_recent() {
        check_ajax_referer('stk_img_its_nonce', 'nonce');
        
        if (!current_user_can('upload_files')) {
            wp_die(esc_html__('You do not have permission to perform this action.', 'stock-images-by-indietech'));
        }
        
        ob_start();
        $this->display_recent_imports();
        $html = ob_get_clean();
        
        wp_send_json_success(array('html' => $html));
    }
}

// Initialize the plugin
function stk_img_its_init() {
    StockImagesByITS::get_instance();
}

// Hook into WordPress
add_action('plugins_loaded', 'stk_img_its_init');

// Activation hook
register_activation_hook(__FILE__, 'stk_img_its_activate');

function stk_img_its_activate() {
    // Set default options
    add_option('stk_img_its_max_size', 'medium');
    add_option('stk_img_its_version', STK_IMG_ITS_VERSION);
    
    // Clear any existing cache
    wp_cache_delete('stk_img_its_total_imported', 'stk_img_its');
    wp_cache_delete('stk_img_its_this_month_' . gmdate('Y-m'), 'stk_img_its');
    wp_cache_delete('stk_img_its_recent_imports', 'stk_img_its');
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'stk_img_its_deactivate');

function stk_img_its_deactivate() {
    // Clear cache on deactivation
    wp_cache_delete('stk_img_its_total_imported', 'stk_img_its');
    wp_cache_delete('stk_img_its_this_month_' . gmdate('Y-m'), 'stk_img_its');
    wp_cache_delete('stk_img_its_recent_imports', 'stk_img_its');
} 