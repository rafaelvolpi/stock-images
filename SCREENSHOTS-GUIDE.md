# 📸 Screenshots Guide - Quick Reference

## 🎯 Where to Save Screenshots

**Save your screenshots in the `screenshots/` directory** (already created for you)

```
stock-images/
└── screenshots/
    ├── screenshot-1.png  ← Save here
    ├── screenshot-2.png  ← Save here
    ├── screenshot-3.png  ← Save here
    └── screenshot-4.png  ← Save here
```

## 📋 Required Screenshots

| File | Content | Size |
|------|---------|------|
| `screenshots/screenshot-1.png` | Main search interface with results | 1200x900px |
| `screenshots/screenshot-2.png` | Settings page with API configuration | 1200x900px |
| `screenshots/screenshot-3.png` | Media Library integration | 1200x900px |
| `screenshots/screenshot-4.png` | Attribution information display | 1200x900px |

## 🚀 How to Create Screenshots

### Step 1: Set Up Your WordPress Site
1. Install and activate your plugin
2. Configure at least one API key (Unsplash, Pexels, or Pixabay)
3. Use a clean WordPress admin theme (Twenty Twenty-Four recommended)

### Step 2: Take Screenshots
1. **screenshot-1.png**: Go to Media > Stock Images, search for something, show results
2. **screenshot-2.png**: Go to Settings > Stock Images, show API configuration
3. **screenshot-3.png**: Go to Media Library, show imported stock images
4. **screenshot-4.png**: Click on an imported image, show attribution details

### Step 3: Edit Screenshots
- **Resize to exactly 1200x900 pixels**
- **Save as PNG format**
- **Keep file size under 2MB each**
- **Ensure text is readable**

### Step 4: Save in Correct Location
Save all 4 screenshots in the `screenshots/` directory with exact names:
- `screenshots/screenshot-1.png`
- `screenshots/screenshot-2.png`
- `screenshots/screenshot-3.png`
- `screenshots/screenshot-4.png`

## ✅ Verification

After creating screenshots, run:
```bash
ls -la screenshots/
```

You should see:
```
screenshot-1.png
screenshot-2.png
screenshot-3.png
screenshot-4.png
README.md
```

## 🎉 Ready to Submit

Once all screenshots are in place:
1. Run: `./prepare-submission.sh`
2. The script will automatically include your screenshots
3. Upload `stock-images.zip` to WordPress.org

## 💡 Tips for Great Screenshots

- Use a clean, modern WordPress admin theme
- Show realistic content (not placeholder text)
- Highlight key features clearly
- Ensure good contrast and readability
- Use consistent styling across all screenshots
- Show the plugin in action with real data

## 🆘 Need Help?

If you need help creating screenshots:
1. Check the `screenshots/README.md` file for detailed instructions
2. Test your plugin thoroughly before taking screenshots
3. Make sure all features work correctly
4. Use a high-resolution monitor for best quality 