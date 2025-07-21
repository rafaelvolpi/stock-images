<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Handle form submission
if (isset($_POST['submit'])) {
    if (isset($_POST['stock_network_settings_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['stock_network_settings_nonce'])), 'stock_network_settings')) {
        // SANITIZE EARLY
        $unsplash_access_key = isset($_POST['unsplash_access_key']) ? sanitize_text_field(wp_unslash($_POST['unsplash_access_key'])) : '';
        $unsplash_secret_key = isset($_POST['unsplash_secret_key']) ? sanitize_text_field(wp_unslash($_POST['unsplash_secret_key'])) : '';
        $pexels_api_key = isset($_POST['pexels_api_key']) ? sanitize_text_field(wp_unslash($_POST['pexels_api_key'])) : '';
        $pixabay_api_key = isset($_POST['pixabay_api_key']) ? sanitize_text_field(wp_unslash($_POST['pixabay_api_key'])) : '';
        $stk_img_its_max_size = isset($_POST['stk_img_its_max_size']) ? sanitize_text_field(wp_unslash($_POST['stk_img_its_max_size'])) : 'medium';
        
        // VALIDATE
        $allowed_sizes = array('small', 'medium', 'full');
        if (!in_array($stk_img_its_max_size, $allowed_sizes, true)) {
            $stk_img_its_max_size = 'medium';
        }
        
        update_site_option('stk_img_its_unsplash_access_key', $unsplash_access_key);
        update_site_option('stk_img_its_unsplash_secret_key', $unsplash_secret_key);
        update_site_option('stk_img_its_pexels_api_key', $pexels_api_key);
        update_site_option('stk_img_its_pixabay_api_key', $pixabay_api_key);
        update_site_option('stk_img_its_max_size', $stk_img_its_max_size);
        echo '<div class="notice notice-success"><p>' . esc_html__('Network settings saved successfully! These settings will apply to all sites in the network.', 'stock-images-by-indietech') . '</p></div>';
    }
}

$access_key = get_site_option('stk_img_its_unsplash_access_key');
$secret_key = get_site_option('stk_img_its_unsplash_secret_key');
$pexels_api_key = get_site_option('stk_img_its_pexels_api_key');
$pixabay_api_key = get_site_option('stk_img_its_pixabay_api_key');
$max_size = get_site_option('stk_img_its_max_size', 'medium'); // Default to medium size
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e('Stock Images Network Settings', 'stock-images-by-indietech'); ?></h1>
    <a href="<?php echo esc_url(network_admin_url('upload.php?page=stock-images-by-indietech')); ?>" class="page-title-action">
        <?php esc_html_e('Back to Stock Images', 'stock-images-by-indietech'); ?>
    </a>
    
    <div class="stock-settings-form">
        <h2><?php esc_html_e('Network API Configuration', 'stock-images-by-indietech'); ?></h2>
        <p><?php esc_html_e('These settings will apply to all sites in your network. Individual sites can override these settings if needed.', 'stock-images-by-indietech'); ?></p>
        
        <form method="post" action="">
            <?php wp_nonce_field('stock_network_settings', 'stock_network_settings_nonce'); ?>
            
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
            
            <h2><?php esc_html_e('Network Image Settings', 'stock-images-by-indietech'); ?></h2>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="stk_img_its_max_size"><?php esc_html_e('Maximum Image Size', 'stock-images-by-indietech'); ?></label>
                    </th>
                    <td>
                        <select id="stk_img_its_max_size" name="stk_img_its_max_size" class="stock-api-key-input">
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
                <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e('Save Network Settings', 'stock-images-by-indietech'); ?>">
            </p>
        </form>
    </div>
    
    <!-- API Instructions -->
    <div class="stock-api-instructions">
        <h2><?php esc_html_e('API Configuration Instructions', 'stock-images-by-indietech'); ?></h2>
        <p><?php esc_html_e('To use this plugin, you need to obtain API keys from the following services:', 'stock-images-by-indietech'); ?></p>
        
        <h3><?php esc_html_e('Unsplash', 'stock-images-by-indietech'); ?></h3>
        <p><?php esc_html_e('Get your free API keys from', 'stock-images-by-indietech'); ?> <a href="https://unsplash.com/developers" target="_blank">https://unsplash.com/developers</a></p>
        
        <h3><?php esc_html_e('Pexels', 'stock-images-by-indietech'); ?></h3>
        <p><?php esc_html_e('Get your free API key from', 'stock-images-by-indietech'); ?> <a href="https://www.pexels.com/api/" target="_blank">https://www.pexels.com/api/</a></p>
        
        <h3><?php esc_html_e('Pixabay', 'stock-images-by-indietech'); ?></h3>
        <p><?php esc_html_e('Get your free API key from', 'stock-images-by-indietech'); ?> <a href="https://pixabay.com/api/docs/" target="_blank">https://pixabay.com/api/docs/</a></p>
    </div>
</div> 