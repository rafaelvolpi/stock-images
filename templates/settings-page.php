<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Handle form submission
if (isset($_POST['submit'])) {
    if (wp_verify_nonce($_POST['stock_settings_nonce'], 'stock_settings')) {
        update_option('unsplash_access_key', sanitize_text_field($_POST['unsplash_access_key']));
        update_option('unsplash_secret_key', sanitize_text_field($_POST['unsplash_secret_key']));
        update_option('stock_images_max_size', sanitize_text_field($_POST['stock_images_max_size']));
        echo '<div class="notice notice-success"><p>' . __('Settings saved successfully!', 'stock-images') . '</p></div>';
    }
}

$access_key = get_option('unsplash_access_key');
$secret_key = get_option('unsplash_secret_key');
$max_size = get_option('stock_images_max_size', 'medium'); // Default to medium size
?>

<div class="wrap">
    <h1><?php _e('Stock Images Settings', 'stock-images'); ?></h1>
    
    <div class="stock-settings-form">
        <h2><?php _e('API Configuration', 'stock-images'); ?></h2>
        
        <form method="post" action="">
            <?php wp_nonce_field('stock_settings', 'stock_settings_nonce'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="unsplash_access_key"><?php _e('Access Key', 'stock-images'); ?></label>
                    </th>
                    <td>
                        <input 
                            type="text" 
                            id="unsplash_access_key" 
                            name="unsplash_access_key" 
                            value="<?php echo esc_attr($access_key); ?>" 
                            class="stock-api-key-input"
                            required
                        >
                        <p class="stock-help-text">
                            <?php _e('Your Unsplash API Access Key. Get it from', 'stock-images'); ?>
                            <a href="https://unsplash.com/developers" target="_blank">https://unsplash.com/developers</a>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="unsplash_secret_key"><?php _e('Secret Key', 'stock-images'); ?></label>
                    </th>
                    <td>
                        <input 
                            type="password" 
                            id="unsplash_secret_key" 
                            name="unsplash_secret_key" 
                            value="<?php echo esc_attr($secret_key); ?>" 
                            class="stock-api-key-input"
                        >
                        <p class="stock-help-text">
                            <?php _e('Your Unsplash API Secret Key (optional for basic usage).', 'stock-images'); ?>
                        </p>
                    </td>
                </tr>
            </table>
            
            <h2><?php _e('Image Settings', 'stock-images'); ?></h2>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="stock_images_max_size"><?php _e('Maximum Image Size', 'stock-images'); ?></label>
                    </th>
                    <td>
                        <select id="stock_images_max_size" name="stock_images_max_size" class="stock-api-key-input">
                            <option value="small" <?php selected($max_size, 'small'); ?>><?php _e('Small (350px width)', 'stock-images'); ?></option>
                            <option value="medium" <?php selected($max_size, 'medium'); ?>><?php _e('Medium (700px width)', 'stock-images'); ?></option>
                            <option value="full" <?php selected($max_size, 'full'); ?>><?php _e('Full (1920px width)', 'stock-images'); ?></option>
                        </select>
                        <p class="stock-help-text">
                            <?php _e('Choose the maximum size of images to download. Smaller sizes download faster and use less storage space.', 'stock-images'); ?>
                        </p>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save Settings', 'stock-images'); ?>">
            </p>
        </form>
    </div>
    
    <!-- API Key Instructions -->
    <div class="stock-api-instructions">
        <h3><?php _e('How to Get Your Unsplash API Keys', 'stock-images'); ?></h3>
        
        <ol>
            <li>
                <?php _e('Go to', 'stock-images'); ?>
                <a href="https://unsplash.com/developers" target="_blank">https://unsplash.com/developers</a>
            </li>
            <li><?php _e('Sign in to your Unsplash account or create a new one.', 'stock-images'); ?></li>
            <li><?php _e('Click "New Application" to register your application.', 'stock-images'); ?></li>
            <li><?php _e('Fill in the application details:', 'stock-images'); ?>
                <ul>
                    <li><strong><?php _e('Application name:', 'stock-images'); ?></strong> <?php _e('Your website or blog name', 'stock-images'); ?></li>
                    <li><strong><?php _e('Description:', 'stock-images'); ?></strong> <?php _e('Brief description of how you\'ll use Unsplash images', 'stock-images'); ?></li>
                    <li><strong><?php _e('What are you building:', 'stock-images'); ?></strong> <?php _e('WordPress website/blog', 'stock-images'); ?></li>
                </ul>
            </li>
            <li><?php _e('Accept the terms and create your application.', 'stock-images'); ?></li>
            <li><?php _e('Copy your Access Key and paste it in the field above.', 'stock-images'); ?></li>
        </ol>
        
        <div class="stock-rate-limits">
            <h4><?php _e('Rate Limits', 'stock-images'); ?></h4>
            <p>
                <?php _e('Unsplash API has the following rate limits:', 'stock-images'); ?>
            </p>
            <ul>
                <li><?php _e('Demo applications: 50 requests per hour', 'stock-images'); ?></li>
                <li><?php _e('Production applications: 5,000 requests per hour', 'stock-images'); ?></li>
            </ul>
            <p>
                <?php _e('For production use, make sure to upgrade your application in the Unsplash developer dashboard.', 'stock-images'); ?>
            </p>
        </div>
    </div>
    
    <!-- Usage Guidelines -->
    <div class="stock-usage-guidelines">
        <h3><?php _e('Usage Guidelines', 'stock-images'); ?></h3>
        
        <div class="stock-guideline">
            <h4><?php _e('Attribution Requirements', 'stock-images'); ?></h4>
            <p><?php _e('When using stock images, you must provide proper attribution to the photographer. This plugin automatically adds attribution information to your media library entries.', 'stock-images'); ?></p>
        </div>
        
        <div class="stock-guideline">
            <h4><?php _e('Commercial Use', 'stock-images'); ?></h4>
            <p><?php _e('Stock images are free to use for commercial and noncommercial purposes without permission from the photographer or Unsplash.', 'stock-images'); ?></p>
        </div>
        
        <div class="stock-guideline">
            <h4><?php _e('Modifications', 'stock-images'); ?></h4>
            <p><?php _e('You can modify stock images to fit your needs, but you cannot sell them as stock photos.', 'stock-images'); ?></p>
        </div>
        
        <p>
            <?php _e('For complete usage guidelines, visit:', 'stock-images'); ?>
            <a href="https://unsplash.com/license" target="_blank">https://unsplash.com/license</a>
        </p>
    </div>
</div>

<style>
.stock-api-instructions {
    margin-top: 40px;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.stock-api-instructions h3 {
    margin-top: 0;
    color: #23282d;
}

.stock-api-instructions ol {
    margin-left: 20px;
}

.stock-api-instructions li {
    margin-bottom: 10px;
    line-height: 1.6;
}

.stock-api-instructions ul {
    margin-left: 20px;
    margin-top: 10px;
}

.stock-rate-limits {
    margin-top: 30px;
    padding: 20px;
    background: #f9f9f9;
    border-left: 4px solid #0073aa;
    border-radius: 4px;
}

.stock-rate-limits h4 {
    margin-top: 0;
    color: #0073aa;
}

.stock-usage-guidelines {
    margin-top: 40px;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.stock-usage-guidelines h3 {
    margin-top: 0;
    color: #23282d;
}

.stock-guideline {
    margin-bottom: 25px;
}

.stock-guideline h4 {
    color: #0073aa;
    margin-bottom: 10px;
}

.stock-guideline p {
    line-height: 1.6;
    margin: 0;
}

.stock-api-instructions a,
.stock-usage-guidelines a {
    color: #0073aa;
    text-decoration: none;
}

.stock-api-instructions a:hover,
.stock-usage-guidelines a:hover {
    text-decoration: underline;
}
</style> 