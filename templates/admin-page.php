<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get plugin instance
$plugin = StockImagesByITS::get_instance();
$access_key = $plugin->get_option_value('stk_img_its_unsplash_access_key');
$pexels_api_key = $plugin->get_option_value('stk_img_its_pexels_api_key');
$pixabay_api_key = $plugin->get_option_value('stk_img_its_pixabay_api_key');

// Check which APIs are configured
$configured_apis = array();
if (!empty($access_key)) {
    $configured_apis['unsplash'] = esc_html__('Unsplash', 'stock-images-by-indietech');
}
if (!empty($pexels_api_key)) {
    $configured_apis['pexels'] = esc_html__('Pexels', 'stock-images-by-indietech');
}
if (!empty($pixabay_api_key)) {
    $configured_apis['pixabay'] = esc_html__('Pixabay', 'stock-images-by-indietech');
}

// If no APIs are configured, show all options but disabled
$has_configured_apis = !empty($configured_apis);

// Determine settings page URL and check permissions
$settings_url = '';
$can_access_settings = false;

if (is_multisite() && $plugin->is_network_configured()) {
    // Network settings
    if (current_user_can('manage_network_options')) {
        $settings_url = network_admin_url('settings.php?page=stock-images-network-settings');
        $can_access_settings = true;
    }
} else {
    // Site settings
    if (current_user_can('manage_options')) {
        $settings_url = admin_url('options-general.php?page=stock-images-settings');
        $can_access_settings = true;
    }
}
?>

<div class="wrap stock-images">
    <h1 class="wp-heading-inline"><?php esc_html_e('Stock Images', 'stock-images-by-indietech'); ?></h1>
    <?php if ($can_access_settings && !empty($settings_url)): ?>
        <a href="<?php echo esc_url($settings_url); ?>" class="page-title-action">
            <?php esc_html_e('Settings', 'stock-images-by-indietech'); ?>
        </a>
    <?php endif; ?>
    
    <?php if (!$has_configured_apis): ?>
        <div class="notice notice-warning">
            <p>
                <?php esc_html_e('Please configure at least one API key (Unsplash, Pexels, or Pixabay) in the', 'stock-images-by-indietech'); ?>
                <?php if ($can_access_settings && !empty($settings_url)): ?>
                    <a href="<?php echo esc_url($settings_url); ?>">
                        <?php esc_html_e('settings page', 'stock-images-by-indietech'); ?>
                    </a>
                <?php else: ?>
                    <?php esc_html_e('settings page', 'stock-images-by-indietech'); ?>
                <?php endif; ?>
                <?php esc_html_e('to start using this plugin.', 'stock-images-by-indietech'); ?>
            </p>
        </div>
    <?php endif; ?>
    
    <!-- Statistics -->
    <div class="stock-stats">
        <div class="stock-stat-card">
            <div class="stock-stat-number" data-stat="total"><?php echo esc_html($this->get_imported_count()); ?></div>
            <div class="stock-stat-label"><?php esc_html_e('Images Imported', 'stock-images-by-indietech'); ?></div>
        </div>
        <div class="stock-stat-card">
            <div class="stock-stat-number" data-stat="month"><?php echo esc_html($this->get_this_month_count()); ?></div>
            <div class="stock-stat-label"><?php esc_html_e('This Month', 'stock-images-by-indietech'); ?></div>
        </div>
    </div>

    <!-- Search Interface -->
    <div class="stock-search-container">
        <div class="stock-search-header">
            <h3><?php esc_html_e('Search Stock Images', 'stock-images-by-indietech'); ?></h3>
            <div class="stock-search-form-wrapper">
                <select id="stock-source-select" class="stock-source-select">
                    <?php if ($has_configured_apis): ?>
                        <?php foreach ($configured_apis as $api => $name): ?>
                            <option value="<?php echo esc_attr($api); ?>"><?php echo esc_html($name); ?></option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="unsplash"><?php esc_html_e('Unsplash', 'stock-images-by-indietech'); ?></option>
                        <option value="pexels"><?php esc_html_e('Pexels', 'stock-images-by-indietech'); ?></option>
                        <option value="pixabay"><?php esc_html_e('Pixabay', 'stock-images-by-indietech'); ?></option>
                    <?php endif; ?>
                </select>
                <input 
                    type="text" 
                    id="stock-search-input" 
                    placeholder="<?php esc_attr_e('Search for images...', 'stock-images-by-indietech'); ?>" 
                    class="stock-search-input"
                    <?php echo ($has_configured_apis) ? '' : 'disabled'; ?>
                >
                <button 
                    type="submit" 
                    class="button button-primary stock-search-btn"
                    <?php echo ($has_configured_apis) ? '' : 'disabled'; ?>
                >
                    <?php esc_html_e('Search', 'stock-images-by-indietech'); ?>
                </button>
            </div>
        </div>
        
        <div id="stock-results" class="stock-results">
            <?php if (!$has_configured_apis): ?>
                <div class="no-results">
                    <p><?php esc_html_e('Please configure at least one API key to start searching.', 'stock-images-by-indietech'); ?></p>
                </div>
            <?php else: ?>
                <div class="no-results">
                    <p><?php esc_html_e('Enter a search term above to find images.', 'stock-images-by-indietech'); ?></p>
                </div>
            <?php endif; ?>
        </div>
        
        <div id="stock-load-more-container" class="stock-load-more-container" style="display: none;">
            <button id="stock-load-more" class="button">
                <?php esc_html_e('Load More', 'stock-images-by-indietech'); ?>
            </button>
        </div>
    </div>
    
    <!-- Recent Imports -->
    <div class="stock-recent-imports">
        <h3><?php esc_html_e('Recent Imports', 'stock-images-by-indietech'); ?></h3>
        <?php $this->display_recent_imports(); ?>
    </div>
    
    <!-- Help Section -->
    <div class="stock-help">
        <h3><?php esc_html_e('How to Use', 'stock-images-by-indietech'); ?></h3>
        <div class="stock-help-content">
            <ol>
                <li><?php esc_html_e('Choose your preferred image source (Unsplash, Pexels, or Pixabay) from the dropdown menu.', 'stock-images-by-indietech'); ?></li>
                <li><?php esc_html_e('Enter a search term in the search box above (e.g., "nature", "business", "food").', 'stock-images-by-indietech'); ?></li>
                <li><?php esc_html_e('Browse through the results and click the size button (S, M, L) on any image you want to import.', 'stock-images-by-indietech'); ?></li>
                <li><?php esc_html_e('The image will be downloaded and added to your WordPress Media Library with proper attribution.', 'stock-images-by-indietech'); ?></li>
                <li><?php esc_html_e('Use the imported image in your posts, pages, or anywhere else in WordPress like any other media file.', 'stock-images-by-indietech'); ?></li>
            </ol>
            
            <div class="stock-usage-tips">
                <h4><?php esc_html_e('Pro Tips', 'stock-images-by-indietech'); ?></h4>
                <ul>
                    <li><?php esc_html_e('Use specific search terms for better results (e.g., "mountain sunset" instead of just "mountain").', 'stock-images-by-indietech'); ?></li>
                    <li><?php esc_html_e('Choose the appropriate image size: Small (350px) for thumbnails, Medium (700px) for content, Large (1920px) for featured images.', 'stock-images-by-indietech'); ?></li>
                    <li><?php esc_html_e('You can also access stock images directly from the Media Library by clicking "Add Media" in any post or page.', 'stock-images-by-indietech'); ?></li>
                    <li><?php esc_html_e('All imported images include automatic attribution to comply with licensing requirements.', 'stock-images-by-indietech'); ?></li>
                </ul>
            </div>
            
            <div class="stock-attribution-notice">
                <h4><?php esc_html_e('Important: Attribution Requirements', 'stock-images-by-indietech'); ?></h4>
                <p>
                    <?php esc_html_e('When using stock images, you must provide proper attribution to the photographer. This plugin automatically adds attribution information to your media library entries.', 'stock-images-by-indietech'); ?>
                </p>
                <p>
                    <?php esc_html_e('For more information about usage requirements, visit:', 'stock-images-by-indietech'); ?>
                    <a href="https://unsplash.com/license" target="_blank">https://unsplash.com/license</a>
                    <?php esc_html_e(',', 'stock-images-by-indietech'); ?>
                    <a href="https://www.pexels.com/license/" target="_blank">https://www.pexels.com/license/</a>
                    <?php esc_html_e('and', 'stock-images-by-indietech'); ?>
                    <a href="https://pixabay.com/service/license/" target="_blank">https://pixabay.com/service/license/</a>
                </p>
            </div>
        </div>
    </div>
</div> 