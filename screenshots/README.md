# Screenshots for WordPress.org Submission

## 📸 Where to Save Screenshots

Save your screenshots in this `screenshots/` directory with these exact names:

- **screenshot-1.png** - Main search interface (1200x900px)
- **screenshot-2.png** - Settings page with API configuration (1200x900px)
- **screenshot-3.png** - Media Library integration (1200x900px)
- **screenshot-4.png** - Attribution information display (1200x900px)

## 📋 Screenshot Requirements

### Format & Size
- **Format**: PNG only
- **Dimensions**: 1200x900 pixels (exact)
- **File size**: Keep under 2MB each
- **Quality**: High quality, clear images

### Content Guidelines
- **screenshot-1.png**: Show the main search interface with search results
- **screenshot-2.png**: Show the settings page with API key configuration
- **screenshot-3.png**: Show how images appear in the WordPress Media Library
- **screenshot-4.png**: Show the attribution information display

### Tips for Good Screenshots
1. Use a clean WordPress admin theme
2. Show realistic data/content
3. Highlight key features clearly
4. Ensure text is readable
5. Use consistent styling across all screenshots

## 🚀 After Creating Screenshots

Once you have all 4 screenshots in this directory:

1. Run the preparation script: `./prepare-submission.sh`
2. The script will automatically include these screenshots in your submission ZIP
3. Upload to WordPress.org

## 📁 File Structure

Your final plugin structure should look like this:

```
stock-images/
├── stock-images.php
├── readme.txt
├── screenshots/
│   ├── screenshot-1.png
│   ├── screenshot-2.png
│   ├── screenshot-3.png
│   └── screenshot-4.png
├── assets/
├── templates/
├── languages/
└── index.php
```

The screenshots will be automatically included when you run the submission script! 