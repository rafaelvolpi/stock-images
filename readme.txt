=== Stock Images by Indietech ===
Contributors: indietech, rafaelvolpi
Tags: media, images, stock photos, unsplash, pexels
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.1.7
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Integrate stock photos directly into your WordPress Media Library. Search and import high-quality images from multiple sources.

== Description ==

Stock Images by Indietech is a powerful WordPress plugin that seamlessly integrates stock photography services directly into your WordPress Media Library. Search and import high-quality images from Unsplash, Pexels, and Pixabay with just a few clicks.

= Key Features =

* **Multi-Source Search**: Search through millions of high-quality photos from Unsplash, Pexels, and Pixabay
* **One-Click Import**: Import images directly into your WordPress Media Library
* **Automatic Attribution**: Proper attribution is automatically added to imported images
* **Statistics Dashboard**: Track your imports and usage statistics
* **Modern UI**: Clean, responsive interface that integrates seamlessly with WordPress
* **Mobile Friendly**: Works great on all devices
* **Secure**: Proper nonce verification and sanitization
* **Internationalization Ready**: Supports multiple languages
* **Smart Source Selection**: Only shows configured API sources in the dropdown menu

= How It Works =

1. Configure your API keys for the stock photo services you want to use
2. Go to Media > Stock Images in your WordPress admin
3. Search for images using keywords
4. Select your preferred image size and import directly to your Media Library
5. Use the imported images just like any other media file in WordPress

= Supported Services =

* **Unsplash**: High-quality, free stock photos
* **Pexels**: Curated free stock photos and videos
* **Pixabay**: Free images, videos, and music

= API Configuration =

The plugin requires API keys from the stock photo services you want to use. Get your free API keys from:

* [Unsplash Developers](https://unsplash.com/developers)
* [Pexels API](https://www.pexels.com/api/)
* [Pixabay API](https://pixabay.com/api/docs/)

= Usage Guidelines =

* All imported images include proper attribution to photographers
* Images are free for commercial and noncommercial use
* You can modify images to fit your needs
* Attribution requirements are automatically handled by the plugin

For complete usage guidelines, visit the respective service websites.

== Installation ==

1. Upload the `stock-images` folder to your `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to 'Settings > Stock Images' to configure your API keys
4. Start searching and importing images from Media > Stock Images

== Frequently Asked Questions ==

= Do I need API keys? =

Yes, you need API keys from the stock photo services you want to use. The plugin will work with whichever services you have configured.

= Are the images really free? =

Yes, all images from Unsplash, Pexels, and Pixabay are free for commercial and noncommercial use.

= Do I need to provide attribution? =

Yes, but the plugin automatically handles attribution for you. When you import an image, the photographer's information and links are automatically added to the media file.

= Can I modify the imported images? =

Yes, you can modify stock images to fit your needs, but you cannot sell them as stock photos.

= What are the API rate limits? =

* Unsplash: 50 requests/hour (demo) or 5,000 requests/hour (production)
* Pexels: 200 requests/hour (free tier)
* Pixabay: 5,000 requests/hour (free tier)

= Is the plugin secure? =

Yes, the plugin includes proper nonce verification, input sanitization, and follows WordPress security best practices.

== Screenshots ==

1. Stock Images search interface
2. Settings page for API configuration
3. Media Library integration
4. Attribution information display

== Changelog ==

= 1.1.7 =
* Improved error handling and user feedback
* Enhanced mobile responsiveness
* Bug fixes and performance improvements

= 1.1.6 =
* Added support for Pixabay API
* Improved search functionality
* Enhanced UI/UX

= 1.1.5 =
* Added Pexels API integration
* Improved attribution handling
* Bug fixes

= 1.1.0 =
* Initial release with Unsplash integration
* Basic search and import functionality
* Media Library integration

== Upgrade Notice ==

= 1.1.7 =
This update includes improved error handling, better mobile support, and various bug fixes for a smoother user experience.

== Developer Information ==

= Hooks and Filters =

The plugin provides several hooks for developers:

**Actions:**
* `stock_images_image_imported` - Fired when an image is imported
* `stock_images_before_search` - Fired before performing a search
* `stock_images_after_search` - Fired after performing a search

**Filters:**
* `stock_images_search_results` - Filter search results
* `stock_images_image_data` - Filter image data before import
* `stock_images_attribution_text` - Filter attribution text

= File Structure =

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
└── readme.txt
```

For more information, visit the [plugin homepage](https://github.com/rafaelvolpi/stock-images). 