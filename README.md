# Stock Images

A WordPress plugin that integrates stock photos directly into your WordPress Media Library. Search and import high-quality images from multiple sources with proper attribution.

## Features

- 🔍 **Search Stock Images**: Search through millions of high-quality photos from multiple sources
- 📥 **One-Click Import**: Import images directly into your WordPress Media Library
- 📝 **Automatic Attribution**: Proper attribution is automatically added to imported images
- 📊 **Statistics Dashboard**: Track your imports and usage statistics
- 🎨 **Modern UI**: Clean, responsive interface that integrates seamlessly with WordPress
- 📱 **Mobile Friendly**: Works great on all devices
- 🔒 **Secure**: Proper nonce verification and sanitization
- 🌍 **Internationalization Ready**: Supports multiple languages
- 🔌 **Extensible**: Built to support multiple stock image providers

## Installation

### Method 1: Manual Installation

1. Download the plugin files
2. Upload the `stock-images` folder to your `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Go to 'Stock Images' > 'Settings' to configure your API keys

### Method 2: WordPress Admin

1. Go to Plugins > Add New
2. Click 'Upload Plugin'
3. Choose the plugin zip file
4. Click 'Install Now' and then 'Activate'

## Configuration

### Getting Your Unsplash API Key

1. Go to [https://unsplash.com/developers](https://unsplash.com/developers)
2. Sign in to your Unsplash account or create a new one
3. Click "New Application" to register your application
4. Fill in the application details:
   - **Application name**: Your website or blog name
   - **Description**: Brief description of how you'll use stock images
   - **What are you building**: WordPress website/blog
5. Accept the terms and create your application
6. Copy your Access Key

### Plugin Settings

1. Go to **Settings > Stock Images** in your WordPress admin
2. Enter your Unsplash API Access Key
3. Optionally enter your Secret Key (not required for basic usage)
4. Click "Save Settings"

## Usage

### Searching and Importing Images

1. Go to **Media > Stock Images** in your WordPress admin
2. Enter a search term in the search box
3. Browse through the results
4. Click "Import" on any image you want to add to your media library
5. The image will be downloaded and added to your WordPress Media Library with proper attribution

### Using Imported Images

Once imported, you can use stock images just like any other media file in WordPress:

- Insert them into posts and pages using the Media Library
- Use them as featured images
- Include them in galleries
- Use them in widgets and themes

### Attribution

The plugin automatically adds attribution information to imported images. When you view an image in the Media Library, you'll see:

- The stock image ID
- The photographer's name with a link to their profile
- A link to the original image on the source platform

## API Rate Limits

Unsplash API has the following rate limits:

- **Demo applications**: 50 requests per hour
- **Production applications**: 5,000 requests per hour

For production use, make sure to upgrade your application in the Unsplash developer dashboard.

## Usage Guidelines

### Attribution Requirements

When using stock images, you must provide proper attribution to the photographer. This plugin automatically adds attribution information to your media library entries.

### Commercial Use

Stock images are free to use for commercial and noncommercial purposes without permission from the photographer or the source platform.

### Modifications

You can modify stock images to fit your needs, but you cannot sell them as stock photos.

For complete usage guidelines, visit: [https://unsplash.com/license](https://unsplash.com/license)

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- API keys for desired stock image providers

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Internet Explorer 11+

## Development

### File Structure

```
stock-images/
├── stock-images.php           # Main plugin file
├── assets/
│   ├── js/
│   │   └── stock-images.js
│   └── css/
│       └── stock-images.css
├── templates/
│   ├── admin-page.php
│   └── settings-page.php
├── languages/                 # Translation files
└── README.md
```

### Hooks and Filters

The plugin provides several hooks for developers:

#### Actions

- `stock_images_image_imported` - Fired when an image is imported
- `stock_images_before_search` - Fired before performing a search
- `stock_images_after_search` - Fired after performing a search

#### Filters

- `stock_images_search_results` - Filter search results
- `stock_images_image_data` - Filter image data before import
- `stock_images_attribution_text` - Filter attribution text

### Example Usage

```php
// Add custom action when image is imported
add_action('stock_images_image_imported', function($attachment_id, $image_data) {
    // Your custom code here
    error_log('Stock image imported: ' . $attachment_id);
}, 10, 2);

// Filter attribution text
add_filter('stock_images_attribution_text', function($text, $photographer, $photographer_url) {
    return sprintf('Photo by <a href="%s">%s</a> on Stock Images', $photographer_url, $photographer);
}, 10, 3);
```

## Troubleshooting

### Common Issues

**"API key not configured" error**
- Make sure you've entered your API key in the settings
- Verify the API key is correct and active

**"No images found" error**
- Check your search terms
- Verify your API key has sufficient rate limits
- Check your internet connection

**Images not importing**
- Check file permissions on your uploads directory
- Verify your server can make external HTTP requests
- Check for any security plugins that might block external requests

### Debug Mode

To enable debug mode, add this to your `wp-config.php`:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

Then check the debug log for any error messages.

## Support

For support, please:

1. Check the troubleshooting section above
2. Search existing issues
3. Create a new issue with:
   - WordPress version
   - Plugin version
   - PHP version
   - Error messages
   - Steps to reproduce

## Contributing

Contributions are welcome! Please:

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## License

This plugin is licensed under the GPL v2 or later.

## Changelog

### Version 1.0.0
- Initial release
- Search and import functionality
- Settings page
- Statistics dashboard
- Automatic attribution
- Responsive design
- Support for Unsplash API

## Future Plans

- Support for additional stock image providers (Pexels, Pixabay, etc.)
- Advanced filtering options
- Bulk import functionality
- Custom attribution templates
- Image optimization features

## Credits

- Built for WordPress
- Uses the [Unsplash API](https://unsplash.com/developers)
- Icons from [Dashicons](https://developer.wordpress.org/resource/dashicons/) 