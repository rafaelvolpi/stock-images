<?php
/**
 * Plugin Name: Stock Images
 * Plugin URI: https://github.com/rafaelvolpi/stock-images
 * Description: Integrate stock photos directly into your WordPress Media Library. Search and import high-quality images from multiple sources.
 * Version: 1.0.0
 * Author: Rafael Volpi, Indietech Solutions
 * Author URI: https://indietechsolutions.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: stock-images
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('STOCK_IMAGES_VERSION', '1.0.0');
define('STOCK_IMAGES_PLUGIN_URL', plugin_dir_url(__FILE__));
define('STOCK_IMAGES_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('STOCK_IMAGES_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Main plugin class
class StockImages {
    
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
        add_action('wp_ajax_stock_images_search', array($this, 'ajax_search'));
        add_action('wp_ajax_stock_images_import', array($this, 'ajax_import'));
        add_action('wp_ajax_stock_images_get_stats', array($this, 'ajax_get_stats'));
        add_action('wp_ajax_stock_images_get_recent', array($this, 'ajax_get_recent'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('enqueue_block_editor_assets', array($this, 'enqueue_block_editor_assets'));
        add_action('enqueue_block_assets', array($this, 'enqueue_block_assets'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('add_attachment', array($this, 'add_stock_attribution'));
        add_filter('attachment_fields_to_edit', array($this, 'add_stock_fields'), 10, 2);
        add_filter('attachment_fields_to_save', array($this, 'save_stock_fields'), 10, 2);
    }
    
    public function init() {
        load_plugin_textdomain('stock-images', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    public function admin_init() {
        // Register settings
        register_setting('stock_images_options', 'unsplash_access_key');
        register_setting('stock_images_options', 'unsplash_secret_key');
        register_setting('stock_images_options', 'pexels_api_key');
        register_setting('stock_images_options', 'stock_images_max_size');
    }
    
    public function enqueue_admin_scripts($hook) {
        // TEMP: Always enqueue for debugging
        wp_enqueue_script(
            'stock-images',
            STOCK_IMAGES_PLUGIN_URL . 'assets/js/stock-images.js',
            array('jquery', 'media-views'),
            STOCK_IMAGES_VERSION,
            true
        );

        wp_enqueue_style(
            'stock-images',
            STOCK_IMAGES_PLUGIN_URL . 'assets/css/stock-images.css',
            array(),
            STOCK_IMAGES_VERSION
        );

        wp_localize_script('stock-images', 'stockImagesAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('stock_images_nonce'),
            'strings' => array(
                'searching' => __('Searching...', 'stock-images'),
                'no_results' => __('No images found.', 'stock-images'),
                'importing' => __('Importing...', 'stock-images'),
                'imported' => __('Image imported successfully!', 'stock-images'),
                'error' => __('An error occurred.', 'stock-images'),
                'stock_images' => __('Stock Images', 'stock-images'),
                'search_stock_images' => __('Search Stock Images', 'stock-images'),
                'search_placeholder' => __('Search for images...', 'stock-images'),
                'search_button' => __('Search', 'stock-images'),
                'load_more' => __('Load More', 'stock-images'),
            )
        ));
    }
    
    public function enqueue_block_editor_assets() {
        wp_enqueue_script(
            'stock-images',
            STOCK_IMAGES_PLUGIN_URL . 'assets/js/stock-images.js',
            array('jquery', 'media-views'),
            STOCK_IMAGES_VERSION,
            true
        );

        wp_enqueue_style(
            'stock-images',
            STOCK_IMAGES_PLUGIN_URL . 'assets/css/stock-images.css',
            array(),
            STOCK_IMAGES_VERSION
        );

        wp_localize_script('stock-images', 'stockImagesAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('stock_images_nonce'),
            'strings' => array(
                'searching' => __('Searching...', 'stock-images'),
                'no_results' => __('No images found.', 'stock-images'),
                'importing' => __('Importing...', 'stock-images'),
                'imported' => __('Image imported successfully!', 'stock-images'),
                'error' => __('An error occurred.', 'stock-images'),
                'stock_images' => __('Stock Images', 'stock-images'),
                'search_stock_images' => __('Search Stock Images', 'stock-images'),
                'search_placeholder' => __('Search for images...', 'stock-images'),
                'search_button' => __('Search', 'stock-images'),
                'load_more' => __('Load More', 'stock-images'),
            )
        ));
    }
    
    public function enqueue_block_assets() {
        // Only enqueue in the editor (not on the frontend)
        if (!is_admin()) return;
        wp_enqueue_script(
            'stock-images',
            STOCK_IMAGES_PLUGIN_URL . 'assets/js/stock-images.js',
            array('jquery', 'media-views'),
            STOCK_IMAGES_VERSION,
            true
        );
        wp_enqueue_style(
            'stock-images',
            STOCK_IMAGES_PLUGIN_URL . 'assets/css/stock-images.css',
            array(),
            STOCK_IMAGES_VERSION
        );
        wp_localize_script('stock-images', 'stockImagesAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('stock_images_nonce'),
            'strings' => array(
                'searching' => __('Searching...', 'stock-images'),
                'no_results' => __('No images found.', 'stock-images'),
                'importing' => __('Importing...', 'stock-images'),
                'imported' => __('Image imported successfully!', 'stock-images'),
                'error' => __('An error occurred.', 'stock-images'),
                'stock_images' => __('Stock Images', 'stock-images'),
                'search_stock_images' => __('Search Stock Images', 'stock-images'),
                'search_placeholder' => __('Search for images...', 'stock-images'),
                'search_button' => __('Search', 'stock-images'),
                'load_more' => __('Load More', 'stock-images'),
            )
        ));
    }
    
    public function add_admin_menu() {
        add_menu_page(
            __('Stock Images', 'stock-images'),
            __('Stock Images', 'stock-images'),
            'upload_files',
            'stock-images',
            array($this, 'admin_page'),
            'dashicons-format-image',
            11
        );
        
        add_submenu_page(
            'stock-images',
            __('Settings', 'stock-images'),
            __('Settings', 'stock-images'),
            'manage_options',
            'stock-images-settings',
            array($this, 'settings_page')
        );
    }
    
    public function admin_page() {
        include STOCK_IMAGES_PLUGIN_PATH . 'templates/admin-page.php';
    }
    
    public function settings_page() {
        include STOCK_IMAGES_PLUGIN_PATH . 'templates/settings-page.php';
    }
    
    public function ajax_search() {
        check_ajax_referer('stock_images_nonce', 'nonce');
        
        if (!current_user_can('upload_files')) {
            wp_die(__('You do not have permission to perform this action.', 'stock-images'));
        }
        
        $query = sanitize_text_field($_POST['query']);
        $page = intval($_POST['page']) ?: 1;
        $per_page = 20;
        $source = sanitize_text_field($_POST['source'] ?? 'unsplash');
        
        if ($source === 'unsplash') {
            $this->search_unsplash($query, $page, $per_page);
        } elseif ($source === 'pexels') {
            $this->search_pexels($query, $page, $per_page);
        } else {
            wp_send_json_error(__('Invalid source specified.', 'stock-images'));
        }
    }
    
    private function search_unsplash($query, $page, $per_page) {
        $access_key = get_option('unsplash_access_key');
        if (empty($access_key)) {
            wp_send_json_error(__('Unsplash API key not configured.', 'stock-images'));
        }
        
        $url = add_query_arg(array(
            'query' => $query,
            'page' => $page,
            'per_page' => $per_page,
        ), 'https://api.unsplash.com/search/photos');
        
        $response = wp_remote_get($url, array(
            'headers' => array(
                'Authorization' => 'Client-ID ' . $access_key,
            ),
        ));
        
        if (is_wp_error($response)) {
            wp_send_json_error($response->get_error_message());
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (empty($data) || !isset($data['results'])) {
            wp_send_json_error(__('Invalid response from Unsplash API.', 'stock-images'));
        }
        
        // Add source information to each result
        foreach ($data['results'] as &$result) {
            $result['source'] = 'unsplash';
        }
        
        wp_send_json_success($data);
    }
    
    private function search_pexels($query, $page, $per_page) {
        $api_key = get_option('pexels_api_key');
        if (empty($api_key)) {
            wp_send_json_error(__('Pexels API key not configured.', 'stock-images'));
        }
        
        $url = add_query_arg(array(
            'query' => $query,
            'page' => $page,
            'per_page' => $per_page,
        ), 'https://api.pexels.com/v1/search');
        
        $response = wp_remote_get($url, array(
            'headers' => array(
                'Authorization' => $api_key,
            ),
        ));
        
        if (is_wp_error($response)) {
            wp_send_json_error($response->get_error_message());
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (empty($data) || !isset($data['photos'])) {
            wp_send_json_error(__('Invalid response from Pexels API.', 'stock-images'));
        }
        
        // Transform Pexels data to match Unsplash format for consistency
        $transformed_data = array(
            'results' => array(),
            'total' => $data['total_results'] ?? 0,
            'total_pages' => ceil(($data['total_results'] ?? 0) / $per_page)
        );
        
        foreach ($data['photos'] as $photo) {
            $transformed_photo = array(
                'id' => $photo['id'],
                'source' => 'pexels',
                'urls' => array(
                    'small' => $photo['src']['medium'],
                    'regular' => $photo['src']['large'],
                    'full' => $photo['src']['original'],
                    'raw' => $photo['src']['original']
                ),
                'alt_description' => $photo['alt'] ?? '',
                'description' => $photo['alt'] ?? '',
                'user' => array(
                    'name' => $photo['photographer'] ?? 'Unknown',
                    'links' => array(
                        'html' => $photo['photographer_url'] ?? ''
                    )
                ),
                'links' => array(
                    'html' => $photo['url'] ?? ''
                ),
                'width' => $photo['width'] ?? 0,
                'height' => $photo['height'] ?? 0
            );
            
            $transformed_data['results'][] = $transformed_photo;
        }
        
        wp_send_json_success($transformed_data);
    }
    
    public function ajax_import() {
        check_ajax_referer('stock_images_nonce', 'nonce');
        
        if (!current_user_can('upload_files')) {
            wp_die(__('You do not have permission to perform this action.', 'stock-images'));
        }
        
        $image_data = $_POST['image_data'];
        
        // Debug logging
        error_log('Stock Images Debug - Received image_data: ' . print_r($image_data, true));
        
        // Validate image data structure
        if (!is_array($image_data) || empty($image_data)) {
            error_log('Stock Images Debug - Invalid image data structure');
            wp_send_json_error(__('Invalid image data received.', 'stock-images'));
        }
        
        // Check if required fields exist
        if (!isset($image_data['urls']) || !isset($image_data['urls']['regular'])) {
            error_log('Stock Images Debug - URLs or regular URL not found');
            error_log('Stock Images Debug - image_data keys: ' . print_r(array_keys($image_data), true));
            if (isset($image_data['urls'])) {
                error_log('Stock Images Debug - urls keys: ' . print_r(array_keys($image_data['urls']), true));
            }
            wp_send_json_error(__('Image URL not found in data.', 'stock-images'));
        }
        
        if (!isset($image_data['id'])) {
            error_log('Stock Images Debug - Image ID not found');
            wp_send_json_error(__('Image ID not found in data.', 'stock-images'));
        }
        
        if (!isset($image_data['user']) || !isset($image_data['user']['name'])) {
            error_log('Stock Images Debug - User information not found');
            wp_send_json_error(__('Photographer information not found in data.', 'stock-images'));
        }
        
        // Get configured image size or use the selected size from the request
        $selected_size = isset($_POST['size']) ? sanitize_text_field($_POST['size']) : '';
        $max_size = !empty($selected_size) ? $selected_size : get_option('stock_images_max_size', 'medium');
        
        // Determine which URL to use based on configured size
        $image_url = '';
        if ($max_size === 'small' && isset($image_data['urls']['small'])) {
            $image_url = esc_url_raw($image_data['urls']['small']);
        } elseif ($max_size === 'medium' && isset($image_data['urls']['regular'])) {
            $image_url = esc_url_raw($image_data['urls']['regular']);
        } elseif ($max_size === 'full' && isset($image_data['urls']['full'])) {
            $image_url = esc_url_raw($image_data['urls']['full']);
        } else {
            // Default to medium size (regular)
            $image_url = esc_url_raw($image_data['urls']['regular']);
        }
        
        $image_id = sanitize_text_field($image_data['id']);
        $photographer = sanitize_text_field($image_data['user']['name']);
        $photographer_url = isset($image_data['user']['links']['html']) ? esc_url_raw($image_data['user']['links']['html']) : '';
        
        error_log('Stock Images Debug - Processed URL: ' . $image_url);
        error_log('Stock Images Debug - Image ID: ' . $image_id);
        error_log('Stock Images Debug - Photographer: ' . $photographer);
        error_log('Stock Images Debug - Configured size: ' . $max_size);
        
        // Validate the URL
        if (empty($image_url) || !filter_var($image_url, FILTER_VALIDATE_URL)) {
            error_log('Stock Images Debug - URL validation failed. URL: ' . $image_url);
            wp_send_json_error(__('Invalid image URL provided.', 'stock-images'));
        }
        
        error_log('Stock Images Debug - About to download image from URL: ' . $image_url);
        
        // Download the image manually
        $response = wp_remote_get($image_url, array(
            'timeout' => 60,
            'user-agent' => 'WordPress/' . get_bloginfo('version') . '; ' . get_bloginfo('url')
        ));
        
        if (is_wp_error($response)) {
            error_log('Stock Images Debug - wp_remote_get failed: ' . $response->get_error_message());
            wp_send_json_error(__('Failed to download image: ', 'stock-images') . $response->get_error_message());
        }
        
        $image_content = wp_remote_retrieve_body($response);
        $response_code = wp_remote_retrieve_response_code($response);
        
        if ($response_code !== 200 || empty($image_content)) {
            error_log('Stock Images Debug - HTTP response code: ' . $response_code);
            wp_send_json_error(__('Failed to download image. HTTP response code: ', 'stock-images') . $response_code);
        }
        
        // Get upload directory
        $upload_dir = wp_upload_dir();
        $upload_path = $upload_dir['path'];
        $upload_url = $upload_dir['url'];
        
        // Create filename based on source
        $source = isset($image_data['source']) ? $image_data['source'] : 'unsplash';
        $filename = $source . '-' . $image_id . '.jpg';
        $file_path = $upload_path . '/' . $filename;
        
        // Save the image
        $file_saved = file_put_contents($file_path, $image_content);
        
        if ($file_saved === false) {
            error_log('Stock Images Debug - Failed to save file to: ' . $file_path);
            wp_send_json_error(__('Failed to save image to server.', 'stock-images'));
        }
        
        error_log('Stock Images Debug - File saved successfully to: ' . $file_path);
        
        // Prepare attachment data with source-specific attribution
        $source_name = $source === 'pexels' ? 'Pexels' : 'Unsplash';
        $source_url = $source === 'pexels' ? 'https://pexels.com' : 'https://unsplash.com';
        
        $attachment = array(
            'post_mime_type' => 'image/jpeg',
            'post_title' => sanitize_text_field($image_data['alt_description'] ?: $image_data['description'] ?: $source_name . ' Image'),
            'post_content' => '',
            'post_status' => 'inherit',
            'post_excerpt' => sprintf(
                __('Photo by <a href="%s" target="_blank">%s</a> on <a href="%s" target="_blank">%s</a>', 'stock-images'),
                $photographer_url,
                $photographer,
                $source_url,
                $source_name
            ),
        );
        
        // Insert the attachment
        $attachment_id = wp_insert_attachment($attachment, $file_path);
        
        if (is_wp_error($attachment_id)) {
            error_log('Stock Images Debug - wp_insert_attachment failed: ' . $attachment_id->get_error_message());
            wp_send_json_error(__('Failed to create attachment: ', 'stock-images') . $attachment_id->get_error_message());
        }
        
        error_log('Stock Images Debug - Attachment created successfully. ID: ' . $attachment_id);
        
        // Generate attachment metadata
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attachment_data = wp_generate_attachment_metadata($attachment_id, $file_path);
        wp_update_attachment_metadata($attachment_id, $attachment_data);
        
        // Store source-specific metadata
        update_post_meta($attachment_id, '_stock_source', $source);
        update_post_meta($attachment_id, '_' . $source . '_id', $image_id);
        update_post_meta($attachment_id, '_' . $source . '_photographer', $photographer);
        update_post_meta($attachment_id, '_' . $source . '_photographer_url', $photographer_url);
        update_post_meta($attachment_id, '_' . $source . '_url', isset($image_data['links']['html']) ? $image_data['links']['html'] : '');
        
        wp_send_json_success(array(
            'attachment_id' => $attachment_id,
            'url' => wp_get_attachment_url($attachment_id),
        ));
    }
    
    public function add_stock_attribution($attachment_id) {
        // This method can be used to automatically add attribution when images are used
    }
    
    public function add_stock_fields($form_fields, $post) {
        $stock_source = get_post_meta($post->ID, '_stock_source', true);
        
        if ($stock_source === 'unsplash') {
            $unsplash_id = get_post_meta($post->ID, '_unsplash_id', true);
            $photographer = get_post_meta($post->ID, '_unsplash_photographer', true);
            $photographer_url = get_post_meta($post->ID, '_unsplash_photographer_url', true);
            $unsplash_url = get_post_meta($post->ID, '_unsplash_url', true);
            
            if ($unsplash_id) {
                $form_fields['stock_info'] = array(
                    'label' => __('Stock Image Info', 'stock-images'),
                    'input' => 'html',
                    'html' => sprintf(
                        '<p><strong>%s:</strong> %s</p><p><strong>%s:</strong> <a href="%s" target="_blank">%s</a></p><p><strong>%s:</strong> <a href="%s" target="_blank">%s</a></p>',
                        __('Photo ID', 'stock-images'),
                        $unsplash_id,
                        __('Photographer', 'stock-images'),
                        $photographer_url,
                        $photographer,
                        __('Source Link', 'stock-images'),
                        $unsplash_url,
                        __('View on Unsplash', 'stock-images')
                    )
                );
            }
        } elseif ($stock_source === 'pexels') {
            $pexels_id = get_post_meta($post->ID, '_pexels_id', true);
            $photographer = get_post_meta($post->ID, '_pexels_photographer', true);
            $photographer_url = get_post_meta($post->ID, '_pexels_photographer_url', true);
            $pexels_url = get_post_meta($post->ID, '_pexels_url', true);
            
            if ($pexels_id) {
                $form_fields['stock_info'] = array(
                    'label' => __('Stock Image Info', 'stock-images'),
                    'input' => 'html',
                    'html' => sprintf(
                        '<p><strong>%s:</strong> %s</p><p><strong>%s:</strong> <a href="%s" target="_blank">%s</a></p><p><strong>%s:</strong> <a href="%s" target="_blank">%s</a></p>',
                        __('Photo ID', 'stock-images'),
                        $pexels_id,
                        __('Photographer', 'stock-images'),
                        $photographer_url,
                        $photographer,
                        __('Source Link', 'stock-images'),
                        $pexels_url,
                        __('View on Pexels', 'stock-images')
                    )
                );
            }
        }
        
        return $form_fields;
    }
    
    public function save_stock_fields($post, $attachment) {
        // This method can be used to save additional stock image fields if needed
        return $post;
    }
    
    /**
     * Get the total number of imported stock images
     */
    public function get_imported_count() {
        global $wpdb;
        $count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = %s",
                '_stock_source'
            )
        );
        return intval($count);
    }
    
    /**
     * Get the number of images imported this month
     */
    public function get_this_month_count() {
        global $wpdb;
        $start_of_month = date('Y-m-01 00:00:00');
        $end_of_month = date('Y-m-t 23:59:59');
        
        $count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(DISTINCT pm.post_id) 
                FROM {$wpdb->postmeta} pm 
                INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID 
                WHERE pm.meta_key = %s 
                AND p.post_type = 'attachment' 
                AND p.post_date BETWEEN %s AND %s",
                '_stock_source',
                $start_of_month,
                $end_of_month
            )
        );
        return intval($count);
    }
    
    /**
     * Get total downloads (placeholder for future implementation)
     */
    public function get_total_downloads() {
        // This could be implemented to track actual downloads from stock image sources
        // For now, return the same as imported count
        return $this->get_imported_count();
    }
    
    /**
     * Display recent imports
     */
    public function display_recent_imports() {
        global $wpdb;
        
        $recent_imports = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT p.ID, p.post_title, p.post_date, pm.meta_value as stock_source 
                FROM {$wpdb->posts} p 
                INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id 
                WHERE pm.meta_key = %s 
                AND p.post_type = 'attachment' 
                ORDER BY p.post_date DESC 
                LIMIT 10",
                '_stock_source'
            )
        );
        
        if (empty($recent_imports)) {
            echo '<p>' . __('No images imported yet.', 'stock-images') . '</p>';
            return;
        }
        
        echo '<div class="stock-recent-grid">';
        foreach ($recent_imports as $import) {
            $image_url = wp_get_attachment_image_url($import->ID, 'thumbnail');
            $stock_source = $import->stock_source;
            $photographer = get_post_meta($import->ID, '_' . $stock_source . '_photographer', true);
            $photographer_url = get_post_meta($import->ID, '_' . $stock_source . '_photographer_url', true);
            $source_name = $stock_source === 'pexels' ? 'Pexels' : 'Unsplash';
            
            echo '<div class="stock-recent-item">';
            if ($image_url) {
                echo '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($import->post_title) . '" class="stock-recent-thumb">';
            }
            echo '<div class="stock-recent-info">';
            echo '<p class="stock-recent-title">' . esc_html($import->post_title) . '</p>';
            echo '<p class="stock-recent-source">' . esc_html($source_name) . '</p>';
            if ($photographer) {
                echo '<p class="stock-recent-photographer">';
                echo __('Photo by', 'stock-images') . ' ';
                if ($photographer_url) {
                    echo '<a href="' . esc_url($photographer_url) . '" target="_blank">' . esc_html($photographer) . '</a>';
                } else {
                    echo esc_html($photographer);
                }
                echo '</p>';
            }
            echo '<p class="stock-recent-date">' . date_i18n(get_option('date_format'), strtotime($import->post_date)) . '</p>';
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
        
        echo '<style>
        .stock-recent-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        .stock-recent-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            background: #f9f9f9;
            border-radius: 4px;
        }
        .stock-recent-thumb {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
        }
        .stock-recent-info {
            flex: 1;
        }
        .stock-recent-title {
            margin: 0 0 5px 0;
            font-weight: 600;
            font-size: 13px;
            line-height: 1.3;
        }
        .stock-recent-source {
            margin: 0 0 3px 0;
            font-size: 11px;
            color: #0073aa;
            font-weight: 600;
        }
        .stock-recent-photographer {
            margin: 0 0 3px 0;
            font-size: 11px;
            color: #666;
        }
        .stock-recent-photographer a {
            color: #0073aa;
            text-decoration: none;
        }
        .stock-recent-photographer a:hover {
            text-decoration: underline;
        }
        .stock-recent-date {
            margin: 0;
            font-size: 11px;
            color: #999;
        }
        </style>';
    }
    
    public function ajax_get_stats() {
        check_ajax_referer('stock_images_nonce', 'nonce');
        
        if (!current_user_can('upload_files')) {
            wp_die(__('You do not have permission to perform this action.', 'stock-images'));
        }
        
        $total_imported = $this->get_imported_count();
        $this_month = $this->get_this_month_count();
        $total_downloads = $this->get_total_downloads();
        
        wp_send_json_success(array(
            'total_imported' => $total_imported,
            'this_month' => $this_month,
            'total_downloads' => $total_downloads,
        ));
    }
    
    public function ajax_get_recent() {
        check_ajax_referer('stock_images_nonce', 'nonce');
        
        if (!current_user_can('upload_files')) {
            wp_die(__('You do not have permission to perform this action.', 'stock-images'));
        }
        
        ob_start();
        $this->display_recent_imports();
        $html = ob_get_clean();
        
        wp_send_json_success(array(
            'html' => $html,
        ));
    }
}

// Initialize the plugin
function stock_images_init() {
    return StockImages::get_instance();
}

add_action('plugins_loaded', 'stock_images_init');

// Activation hook
register_activation_hook(__FILE__, 'stock_images_activate');

function stock_images_activate() {
    // Create necessary directories
    $upload_dir = wp_upload_dir();
    $stock_dir = $upload_dir['basedir'] . '/stock-images-cache';
    
    if (!file_exists($stock_dir)) {
        wp_mkdir_p($stock_dir);
    }
    
    // Add default options
    add_option('stock_images_version', STOCK_IMAGES_VERSION);
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'stock_images_deactivate');

function stock_images_deactivate() {
    // Clean up if necessary
} 