<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Handle form submission
if (isset($_POST['submit'])) {
    if (isset($_POST['stock_settings_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['stock_settings_nonce'])), 'stock_settings')) {
        // SANITIZE EARLY
        $unsplash_access_key = isset($_POST['unsplash_access_key']) ? sanitize_text_field(wp_unslash($_POST['unsplash_access_key'])) : '';
        $unsplash_secret_key = isset($_POST['unsplash_secret_key']) ? sanitize_text_field(wp_unslash($_POST['unsplash_secret_key'])) : '';
        $pexels_api_key = isset($_POST['pexels_api_key']) ? sanitize_text_field(wp_unslash($_POST['pexels_api_key'])) : '';
        $pixabay_api_key = isset($_POST['pixabay_api_key']) ? sanitize_text_field(wp_unslash($_POST['pixabay_api_key'])) : '';
        $stock_images_max_size = isset($_POST['stock_images_max_size']) ? sanitize_text_field(wp_unslash($_POST['stock_images_max_size'])) : 'medium';
        
        // VALIDATE
        $allowed_sizes = array('small', 'medium', 'full');
        if (!in_array($stock_images_max_size, $allowed_sizes, true)) {
            $stock_images_max_size = 'medium';
        }
        
        update_option('stk_img_its_unsplash_access_key', $unsplash_access_key);
        update_option('stk_img_its_unsplash_secret_key', $unsplash_secret_key);
        update_option('stk_img_its_pexels_api_key', $pexels_api_key);
        update_option('stk_img_its_pixabay_api_key', $pixabay_api_key);
        update_option('stk_img_its_max_size', $stock_images_max_size);
        echo '<div class="notice notice-success"><p>' . esc_html__('Settings saved successfully!', 'stock-images-by-indietech') . '</p></div>';
    }
}

$access_key = get_option('stk_img_its_unsplash_access_key');
$secret_key = get_option('stk_img_its_unsplash_secret_key');
$pexels_api_key = get_option('stk_img_its_pexels_api_key');
$pixabay_api_key = get_option('stk_img_its_pixabay_api_key');
$max_size = get_option('stk_img_its_max_size', 'medium'); // Default to medium size
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e('Stock Images Settings', 'stock-images-by-indietech'); ?></h1>
    <a href="<?php echo esc_url(admin_url('upload.php?page=stock-images-by-indietech')); ?>" class="page-title-action">
        <?php esc_html_e('Back to Stock Images', 'stock-images-by-indietech'); ?>
    </a>
    
    <div class="stock-settings-form">
        <h2><?php esc_html_e('API Configuration', 'stock-images-by-indietech'); ?></h2>
        
        <form method="post" action="">
            <?php wp_nonce_field('stock_settings', 'stock_settings_nonce'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="unsplash_access_key"><?php esc_html_e('Access Key', 'stock-images-by-indietech'); ?></label>
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
                            <?php esc_html_e('Your Unsplash API Access Key. Get it from', 'stock-images-by-indietech'); ?>
                            <a href="https://unsplash.com/developers" target="_blank">https://unsplash.com/developers</a>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="unsplash_secret_key"><?php esc_html_e('Secret Key', 'stock-images-by-indietech'); ?></label>
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
                            <?php esc_html_e('Your Unsplash API Secret Key (optional for basic usage).', 'stock-images-by-indietech'); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="pexels_api_key"><?php esc_html_e('Pexels API Key', 'stock-images-by-indietech'); ?></label>
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
                            <?php esc_html_e('Your Pexels API Key. Get it from', 'stock-images-by-indietech'); ?>
                            <a href="https://www.pexels.com/api/" target="_blank">https://www.pexels.com/api/</a>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="pixabay_api_key"><?php esc_html_e('Pixabay API Key', 'stock-images-by-indietech'); ?></label>
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
                            <?php esc_html_e('Your Pixabay API Key. Get it from', 'stock-images-by-indietech'); ?>
                            <a href="https://pixabay.com/api/docs/" target="_blank">https://pixabay.com/api/docs/</a>
                        </p>
                    </td>
                </tr>
            </table>
            
            <h2><?php esc_html_e('Image Settings', 'stock-images-by-indietech'); ?></h2>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="stock_images_max_size"><?php esc_html_e('Maximum Image Size', 'stock-images-by-indietech'); ?></label>
                    </th>
                    <td>
                        <select id="stock_images_max_size" name="stock_images_max_size" class="stock-api-key-input">
                            <option value="small" <?php selected($max_size, 'small'); ?>><?php esc_html_e('Small (350px width - Unsplash, 350px width - Pexels, 640px width - Pixabay)', 'stock-images-by-indietech'); ?></option>
                            <option value="medium" <?php selected($max_size, 'medium'); ?>><?php esc_html_e('Medium (700px width - Unsplash, 1200px width - Pexels, 1280px width - Pixabay)', 'stock-images-by-indietech'); ?></option>
                            <option value="full" <?php selected($max_size, 'full'); ?>><?php esc_html_e('Full (1920px width - Unsplash, Original size - Pexels, 1920px width - Pixabay)', 'stock-images-by-indietech'); ?></option>
                        </select>
                        <p class="stock-help-text">
                            <?php esc_html_e('Choose the maximum size of images to download. Smaller sizes download faster and use less storage space.', 'stock-images-by-indietech'); ?>
                        </p>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e('Save Settings', 'stock-images-by-indietech'); ?>">
            </p>
        </form>
    </div>
    
    <!-- API Key Instructions -->
    <div class="stock-api-instructions">
        <h3><?php esc_html_e('How to Get Your API Keys', 'stock-images-by-indietech'); ?></h3>
        
        <div class="stock-api-section">
            <h4><?php esc_html_e('Unsplash API', 'stock-images-by-indietech'); ?></h4>
            <ol>
                <li>
                    <?php esc_html_e('Go to', 'stock-images-by-indietech'); ?>
                    <a href="https://unsplash.com/developers" target="_blank">https://unsplash.com/developers</a>
                </li>
                <li><?php esc_html_e('Sign in to your Unsplash account or create a new one.', 'stock-images-by-indietech'); ?></li>
                <li><?php esc_html_e('Click "New Application" to register your application.', 'stock-images-by-indietech'); ?></li>
                <li><?php esc_html_e('Fill in the application details:', 'stock-images-by-indietech'); ?>
                    <ul>
                        <li><strong><?php esc_html_e('Application name:', 'stock-images-by-indietech'); ?></strong> <?php esc_html_e('Your website or blog name', 'stock-images-by-indietech'); ?></li>
                        <li><strong><?php esc_html_e('Description:', 'stock-images-by-indietech'); ?></strong> <?php esc_html_e('Brief description of how you\'ll use Unsplash images', 'stock-images-by-indietech'); ?></li>
                        <li><strong><?php esc_html_e('What are you building:', 'stock-images-by-indietech'); ?></strong> <?php esc_html_e('WordPress website/blog', 'stock-images-by-indietech'); ?></li>
                    </ul>
                </li>
                <li><?php esc_html_e('Accept the terms and create your application.', 'stock-images-by-indietech'); ?></li>
                <li><?php esc_html_e('Copy your Access Key and paste it in the field above.', 'stock-images-by-indietech'); ?></li>
            </ol>
        </div>
        
        <div class="stock-api-section">
            <h4><?php esc_html_e('Pexels API', 'stock-images-by-indietech'); ?></h4>
            <ol>
                <li>
                    <?php esc_html_e('Go to', 'stock-images-by-indietech'); ?>
                    <a href="https://www.pexels.com/api/" target="_blank">https://www.pexels.com/api/</a>
                </li>
                <li><?php esc_html_e('Sign in to your Pexels account or create a new one.', 'stock-images-by-indietech'); ?></li>
                <li><?php esc_html_e('Click "Get Started" to apply for API access.', 'stock-images-by-indietech'); ?></li>
                <li><?php esc_html_e('Fill in the application details:', 'stock-images-by-indietech'); ?>
                    <ul>
                        <li><strong><?php esc_html_e('Application name:', 'stock-images-by-indietech'); ?></strong> <?php esc_html_e('Your website or blog name', 'stock-images-by-indietech'); ?></li>
                        <li><strong><?php esc_html_e('Description:', 'stock-images-by-indietech'); ?></strong> <?php esc_html_e('Brief description of how you\'ll use Pexels images', 'stock-images-by-indietech'); ?></li>
                        <li><strong><?php esc_html_e('Website URL:', 'stock-images-by-indietech'); ?></strong> <?php esc_html_e('Your WordPress site URL', 'stock-images-by-indietech'); ?></li>
                    </ul>
                </li>
                <li><?php esc_html_e('Submit your application and wait for approval.', 'stock-images-by-indietech'); ?></li>
                <li><?php esc_html_e('Once approved, copy your API key and paste it in the field above.', 'stock-images-by-indietech'); ?></li>
            </ol>
        </div>
        
        <div class="stock-api-section">
            <h4><?php esc_html_e('Pixabay API', 'stock-images-by-indietech'); ?></h4>
            <ol>
                <li>
                    <?php esc_html_e('Go to', 'stock-images-by-indietech'); ?>
                    <a href="https://pixabay.com/api/docs/" target="_blank">https://pixabay.com/api/docs/</a>
                </li>
                <li><?php esc_html_e('Sign in to your Pixabay account or create a new one.', 'stock-images-by-indietech'); ?></li>
                <li><?php esc_html_e('Click "Get API Key" to register for API access.', 'stock-images-by-indietech'); ?></li>
                <li><?php esc_html_e('Fill in the application details:', 'stock-images-by-indietech'); ?>
                    <ul>
                        <li><strong><?php esc_html_e('Application name:', 'stock-images-by-indietech'); ?></strong> <?php esc_html_e('Your website or blog name', 'stock-images-by-indietech'); ?></li>
                        <li><strong><?php esc_html_e('Description:', 'stock-images-by-indietech'); ?></strong> <?php esc_html_e('Brief description of how you\'ll use Pixabay images', 'stock-images-by-indietech'); ?></li>
                        <li><strong><?php esc_html_e('Website URL:', 'stock-images-by-indietech'); ?></strong> <?php esc_html_e('Your WordPress site URL', 'stock-images-by-indietech'); ?></li>
                    </ul>
                </li>
                <li><?php esc_html_e('Accept the terms and create your account.', 'stock-images-by-indietech'); ?></li>
                <li><?php esc_html_e('Copy your API key and paste it in the field above.', 'stock-images-by-indietech'); ?></li>
            </ol>
        </div>
        
        <div class="stock-rate-limits">
            <h4><?php esc_html_e('Rate Limits', 'stock-images-by-indietech'); ?></h4>
            <p>
                <?php esc_html_e('Both APIs have the following rate limits:', 'stock-images-by-indietech'); ?>
            </p>
            <div class="stock-rate-limit-section">
                <h5><?php esc_html_e('Unsplash API', 'stock-images-by-indietech'); ?></h5>
                <ul>
                    <li><?php esc_html_e('Demo applications: 50 requests per hour', 'stock-images-by-indietech'); ?></li>
                    <li><?php esc_html_e('Production applications: 5,000 requests per hour', 'stock-images-by-indietech'); ?></li>
                </ul>
                <p>
                    <?php esc_html_e('For production use, make sure to upgrade your application in the Unsplash developer dashboard.', 'stock-images-by-indietech'); ?>
                </p>
            </div>
            <div class="stock-rate-limit-section">
                <h5><?php esc_html_e('Pexels API', 'stock-images-by-indietech'); ?></h5>
                <ul>
                    <li><?php esc_html_e('Free tier: 200 requests per hour', 'stock-images-by-indietech'); ?></li>
                    <li><?php esc_html_e('Paid plans: Higher limits available', 'stock-images-by-indietech'); ?></li>
                </ul>
                <p>
                    <?php esc_html_e('For higher limits, consider upgrading to a paid plan in your Pexels account.', 'stock-images-by-indietech'); ?>
                </p>
            </div>
            <div class="stock-rate-limit-section">
                <h5><?php esc_html_e('Pixabay API', 'stock-images-by-indietech'); ?></h5>
                <ul>
                    <li><?php esc_html_e('Free tier: 5,000 requests per hour', 'stock-images-by-indietech'); ?></li>
                    <li><?php esc_html_e('No paid plans required', 'stock-images-by-indietech'); ?></li>
                </ul>
                <p>
                    <?php esc_html_e('Pixabay offers generous free limits suitable for most websites.', 'stock-images-by-indietech'); ?>
                </p>
            </div>
        </div>
    </div>
    
    <!-- Usage Guidelines -->
    <div class="stock-usage-guidelines">
        <h3><?php esc_html_e('Usage Guidelines', 'stock-images-by-indietech'); ?></h3>
        
        <div class="stock-guideline">
            <h4><?php esc_html_e('Attribution Requirements', 'stock-images-by-indietech'); ?></h4>
            <p><?php esc_html_e('When using stock images, you must provide proper attribution to the photographer. This plugin automatically adds attribution information to your media library entries.', 'stock-images-by-indietech'); ?></p>
        </div>
        
        <div class="stock-guideline">
            <h4><?php esc_html_e('Commercial Use', 'stock-images-by-indietech'); ?></h4>
            <p><?php esc_html_e('Images from Unsplash, Pexels, and Pixabay are free to use for commercial and noncommercial purposes without permission from the photographer.', 'stock-images-by-indietech'); ?></p>
        </div>
        
        <div class="stock-guideline">
            <h4><?php esc_html_e('Modifications', 'stock-images-by-indietech'); ?></h4>
            <p><?php esc_html_e('You can modify stock images to fit your needs, but you cannot sell them as stock photos.', 'stock-images-by-indietech'); ?></p>
        </div>
        
        <div class="stock-guideline">
            <h4><?php esc_html_e('License Information', 'stock-images-by-indietech'); ?></h4>
            <p>
                <?php esc_html_e('For complete usage guidelines, visit:', 'stock-images-by-indietech'); ?>
            </p>
            <ul>
                <li><a href="https://unsplash.com/license" target="_blank"><?php esc_html_e('Unsplash License', 'stock-images-by-indietech'); ?></a></li>
                <li><a href="https://www.pexels.com/license/" target="_blank"><?php esc_html_e('Pexels License', 'stock-images-by-indietech'); ?></a></li>
                <li><a href="https://pixabay.com/service/license/" target="_blank"><?php esc_html_e('Pixabay License', 'stock-images-by-indietech'); ?></a></li>
            </ul>
        </div>
    </div>
</div> 