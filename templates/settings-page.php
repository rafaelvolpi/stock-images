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
        update_option('pexels_api_key', sanitize_text_field($_POST['pexels_api_key']));
        update_option('pixabay_api_key', sanitize_text_field($_POST['pixabay_api_key']));
        update_option('stock_images_max_size', sanitize_text_field($_POST['stock_images_max_size']));
        echo '<div class="notice notice-success"><p>' . __('Settings saved successfully!', 'stock-images') . '</p></div>';
    }
}

$access_key = get_option('unsplash_access_key');
$secret_key = get_option('unsplash_secret_key');
$pexels_api_key = get_option('pexels_api_key');
$pixabay_api_key = get_option('pixabay_api_key');
$max_size = get_option('stock_images_max_size', 'medium'); // Default to medium size
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Stock Images Settings', 'stock-images'); ?></h1>
    <a href="<?php echo admin_url('upload.php?page=stock-images'); ?>" class="page-title-action">
        <?php _e('Back to Stock Images', 'stock-images'); ?>
    </a>
    
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
                
                <tr>
                    <th scope="row">
                        <label for="pexels_api_key"><?php _e('Pexels API Key', 'stock-images'); ?></label>
                    </th>
                    <td>
                        <input 
                            type="text" 
                            id="pexels_api_key" 
                            name="pexels_api_key" 
                            value="<?php echo esc_attr($pexels_api_key); ?>" 
                            class="stock-api-key-input"
                        >
                        <p class="stock-help-text">
                            <?php _e('Your Pexels API Key. Get it from', 'stock-images'); ?>
                            <a href="https://www.pexels.com/api/" target="_blank">https://www.pexels.com/api/</a>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="pixabay_api_key"><?php _e('Pixabay API Key', 'stock-images'); ?></label>
                    </th>
                    <td>
                        <input 
                            type="text" 
                            id="pixabay_api_key" 
                            name="pixabay_api_key" 
                            value="<?php echo esc_attr($pixabay_api_key); ?>" 
                            class="stock-api-key-input"
                        >
                        <p class="stock-help-text">
                            <?php _e('Your Pixabay API Key. Get it from', 'stock-images'); ?>
                            <a href="https://pixabay.com/api/docs/" target="_blank">https://pixabay.com/api/docs/</a>
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
                            <option value="small" <?php selected($max_size, 'small'); ?>><?php _e('Small (350px width - Unsplash, 350px width - Pexels, 640px width - Pixabay)', 'stock-images'); ?></option>
                            <option value="medium" <?php selected($max_size, 'medium'); ?>><?php _e('Medium (700px width - Unsplash, 1200px width - Pexels, 1280px width - Pixabay)', 'stock-images'); ?></option>
                            <option value="full" <?php selected($max_size, 'full'); ?>><?php _e('Full (1920px width - Unsplash, Original size - Pexels, 1920px width - Pixabay)', 'stock-images'); ?></option>
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
        <h3><?php _e('How to Get Your API Keys', 'stock-images'); ?></h3>
        
        <div class="stock-api-section">
            <h4><?php _e('Unsplash API', 'stock-images'); ?></h4>
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
        </div>
        
        <div class="stock-api-section">
            <h4><?php _e('Pexels API', 'stock-images'); ?></h4>
            <ol>
                <li>
                    <?php _e('Go to', 'stock-images'); ?>
                    <a href="https://www.pexels.com/api/" target="_blank">https://www.pexels.com/api/</a>
                </li>
                <li><?php _e('Sign in to your Pexels account or create a new one.', 'stock-images'); ?></li>
                <li><?php _e('Click "Get Started" to apply for API access.', 'stock-images'); ?></li>
                <li><?php _e('Fill in the application details:', 'stock-images'); ?>
                    <ul>
                        <li><strong><?php _e('Application name:', 'stock-images'); ?></strong> <?php _e('Your website or blog name', 'stock-images'); ?></li>
                        <li><strong><?php _e('Description:', 'stock-images'); ?></strong> <?php _e('Brief description of how you\'ll use Pexels images', 'stock-images'); ?></li>
                        <li><strong><?php _e('Website URL:', 'stock-images'); ?></strong> <?php _e('Your WordPress site URL', 'stock-images'); ?></li>
                    </ul>
                </li>
                <li><?php _e('Submit your application and wait for approval.', 'stock-images'); ?></li>
                <li><?php _e('Once approved, copy your API key and paste it in the field above.', 'stock-images'); ?></li>
            </ol>
        </div>
        
        <div class="stock-api-section">
            <h4><?php _e('Pixabay API', 'stock-images'); ?></h4>
            <ol>
                <li>
                    <?php _e('Go to', 'stock-images'); ?>
                    <a href="https://pixabay.com/api/docs/" target="_blank">https://pixabay.com/api/docs/</a>
                </li>
                <li><?php _e('Sign in to your Pixabay account or create a new one.', 'stock-images'); ?></li>
                <li><?php _e('Click "Get API Key" to register for API access.', 'stock-images'); ?></li>
                <li><?php _e('Fill in the application details:', 'stock-images'); ?>
                    <ul>
                        <li><strong><?php _e('Application name:', 'stock-images'); ?></strong> <?php _e('Your website or blog name', 'stock-images'); ?></li>
                        <li><strong><?php _e('Description:', 'stock-images'); ?></strong> <?php _e('Brief description of how you\'ll use Pixabay images', 'stock-images'); ?></li>
                        <li><strong><?php _e('Website URL:', 'stock-images'); ?></strong> <?php _e('Your WordPress site URL', 'stock-images'); ?></li>
                    </ul>
                </li>
                <li><?php _e('Accept the terms and create your account.', 'stock-images'); ?></li>
                <li><?php _e('Copy your API key and paste it in the field above.', 'stock-images'); ?></li>
            </ol>
        </div>
        
        <div class="stock-rate-limits">
            <h4><?php _e('Rate Limits', 'stock-images'); ?></h4>
            <p>
                <?php _e('Both APIs have the following rate limits:', 'stock-images'); ?>
            </p>
            <div class="stock-rate-limit-section">
                <h5><?php _e('Unsplash API', 'stock-images'); ?></h5>
                <ul>
                    <li><?php _e('Demo applications: 50 requests per hour', 'stock-images'); ?></li>
                    <li><?php _e('Production applications: 5,000 requests per hour', 'stock-images'); ?></li>
                </ul>
                <p>
                    <?php _e('For production use, make sure to upgrade your application in the Unsplash developer dashboard.', 'stock-images'); ?>
                </p>
            </div>
            <div class="stock-rate-limit-section">
                <h5><?php _e('Pexels API', 'stock-images'); ?></h5>
                <ul>
                    <li><?php _e('Free tier: 200 requests per hour', 'stock-images'); ?></li>
                    <li><?php _e('Paid plans: Higher limits available', 'stock-images'); ?></li>
                </ul>
                <p>
                    <?php _e('For higher limits, consider upgrading to a paid plan in your Pexels account.', 'stock-images'); ?>
                </p>
            </div>
            <div class="stock-rate-limit-section">
                <h5><?php _e('Pixabay API', 'stock-images'); ?></h5>
                <ul>
                    <li><?php _e('Free tier: 5,000 requests per hour', 'stock-images'); ?></li>
                    <li><?php _e('No paid plans required', 'stock-images'); ?></li>
                </ul>
                <p>
                    <?php _e('Pixabay offers generous free limits suitable for most websites.', 'stock-images'); ?>
                </p>
            </div>
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
            <p><?php _e('Images from Unsplash, Pexels, and Pixabay are free to use for commercial and noncommercial purposes without permission from the photographer.', 'stock-images'); ?></p>
        </div>
        
        <div class="stock-guideline">
            <h4><?php _e('Modifications', 'stock-images'); ?></h4>
            <p><?php _e('You can modify stock images to fit your needs, but you cannot sell them as stock photos.', 'stock-images'); ?></p>
        </div>
        
        <div class="stock-guideline">
            <h4><?php _e('License Information', 'stock-images'); ?></h4>
            <p>
                <?php _e('For complete usage guidelines, visit:', 'stock-images'); ?>
            </p>
            <ul>
                <li><a href="https://unsplash.com/license" target="_blank"><?php _e('Unsplash License', 'stock-images'); ?></a></li>
                <li><a href="https://www.pexels.com/license/" target="_blank"><?php _e('Pexels License', 'stock-images'); ?></a></li>
                <li><a href="https://pixabay.com/service/license/" target="_blank"><?php _e('Pixabay License', 'stock-images'); ?></a></li>
            </ul>
        </div>
    </div>
</div> 