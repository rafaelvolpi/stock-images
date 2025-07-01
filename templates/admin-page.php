<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get plugin instance
$plugin = StockImages::get_instance();

// Get statistics
$total_imported = $this->get_imported_count();
$access_key = get_option('unsplash_access_key');
?>

<div class="wrap stock-images">
    <h1 class="wp-heading-inline"><?php _e('Stock Images', 'stock-images'); ?></h1>
    <a href="<?php echo admin_url('options-general.php?page=stock-images-settings'); ?>" class="page-title-action">
        <?php _e('Settings', 'stock-images'); ?>
    </a>
    
    <?php if (empty($access_key) && empty(get_option('pexels_api_key'))): ?>
        <div class="notice notice-warning">
            <p>
                <?php _e('Please configure your Unsplash or Pexels API key in the', 'stock-images'); ?>
                <a href="<?php echo admin_url('options-general.php?page=stock-images-settings'); ?>">
                    <?php _e('settings page', 'stock-images'); ?>
                </a>
                <?php _e('to start using this plugin.', 'stock-images'); ?>
            </p>
        </div>
    <?php endif; ?>
    
    <!-- Statistics -->
    <div class="stock-stats">
        <div class="stock-stat-card">
            <div class="stock-stat-number" data-stat="total"><?php echo $total_imported; ?></div>
            <div class="stock-stat-label"><?php _e('Images Imported', 'stock-images'); ?></div>
        </div>
        <div class="stock-stat-card">
            <div class="stock-stat-number" data-stat="month"><?php echo $this->get_this_month_count(); ?></div>
            <div class="stock-stat-label"><?php _e('This Month', 'stock-images'); ?></div>
        </div>
        <div class="stock-stat-card">
            <div class="stock-stat-number" data-stat="downloads"><?php echo $this->get_total_downloads(); ?></div>
            <div class="stock-stat-label"><?php _e('Total Downloads', 'stock-images'); ?></div>
        </div>
    </div>

    <!-- Search Interface -->
    <div class="stock-search-container">
        <div class="stock-search-header">
            <h3><?php _e('Search Stock Images', 'stock-images'); ?></h3>
            <div class="stock-search-form-wrapper">
                <select id="stock-source-select" class="stock-source-select">
                    <option value="unsplash"><?php _e('Unsplash', 'stock-images'); ?></option>
                    <option value="pexels"><?php _e('Pexels', 'stock-images'); ?></option>
                </select>
                <input 
                    type="text" 
                    id="stock-search-input" 
                    placeholder="<?php _e('Search for images...', 'stock-images'); ?>" 
                    class="stock-search-input"
                    <?php echo (empty($access_key) && empty(get_option('pexels_api_key'))) ? 'disabled' : ''; ?>
                >
                <button 
                    type="submit" 
                    class="button button-primary stock-search-btn"
                    <?php echo (empty($access_key) && empty(get_option('pexels_api_key'))) ? 'disabled' : ''; ?>
                >
                    <?php _e('Search', 'stock-images'); ?>
                </button>
            </div>
        </div>
        
        <div id="stock-results" class="stock-results">
            <?php if (empty($access_key)): ?>
                <div class="no-results">
                    <p><?php _e('Please configure your API key to start searching.', 'stock-images'); ?></p>
                </div>
            <?php else: ?>
                <div class="no-results">
                    <p><?php _e('Enter a search term above to find images.', 'stock-images'); ?></p>
                </div>
            <?php endif; ?>
        </div>
        
        <div id="stock-load-more-container" class="stock-load-more-container" style="display: none;">
            <button id="stock-load-more" class="button">
                <?php _e('Load More', 'stock-images'); ?>
            </button>
        </div>
    </div>
    
    <!-- Recent Imports -->
    <div class="stock-recent-imports">
        <h3><?php _e('Recent Imports', 'stock-images'); ?></h3>
        <?php $this->display_recent_imports(); ?>
    </div>
    
    <!-- Help Section -->
    <div class="stock-help">
        <h3><?php _e('How to Use', 'stock-images'); ?></h3>
        <div class="stock-help-content">
            <ol>
                <li><?php _e('Choose your preferred image source (Unsplash or Pexels) from the dropdown menu.', 'stock-images'); ?></li>
                <li><?php _e('Enter a search term in the search box above (e.g., "nature", "business", "food").', 'stock-images'); ?></li>
                <li><?php _e('Browse through the results and click the size button (S, M, L) on any image you want to import.', 'stock-images'); ?></li>
                <li><?php _e('The image will be downloaded and added to your WordPress Media Library with proper attribution.', 'stock-images'); ?></li>
                <li><?php _e('Use the imported image in your posts, pages, or anywhere else in WordPress like any other media file.', 'stock-images'); ?></li>
            </ol>
            
            <div class="stock-usage-tips">
                <h4><?php _e('Pro Tips', 'stock-images'); ?></h4>
                <ul>
                    <li><?php _e('Use specific search terms for better results (e.g., "mountain sunset" instead of just "mountain").', 'stock-images'); ?></li>
                    <li><?php _e('Choose the appropriate image size: Small (350px) for thumbnails, Medium (700px) for content, Large (1920px) for featured images.', 'stock-images'); ?></li>
                    <li><?php _e('You can also access stock images directly from the Media Library by clicking "Add Media" in any post or page.', 'stock-images'); ?></li>
                    <li><?php _e('All imported images include automatic attribution to comply with licensing requirements.', 'stock-images'); ?></li>
                </ul>
            </div>
            
            <div class="stock-attribution-notice">
                <h4><?php _e('Important: Attribution Requirements', 'stock-images'); ?></h4>
                <p>
                    <?php _e('When using stock images, you must provide proper attribution to the photographer. This plugin automatically adds attribution information to your media library entries.', 'stock-images'); ?>
                </p>
                <p>
                    <?php _e('For more information about usage requirements, visit:', 'stock-images'); ?>
                    <a href="https://unsplash.com/license" target="_blank">https://unsplash.com/license</a>
                    <?php _e('and', 'stock-images'); ?>
                    <a href="https://www.pexels.com/license/" target="_blank">https://www.pexels.com/license/</a>
                </p>
            </div>
        </div>
    </div>
</div> 