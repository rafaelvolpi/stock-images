const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

// Configuration
const pluginName = 'stock-images-by-indietech';
const outputDir = 'dist';

// Get version from package.json
const packageJson = JSON.parse(fs.readFileSync('package.json', 'utf8'));
const version = packageJson.version;
const zipName = `${pluginName}-v${version}.zip`;

// Function to update version in files
function updateVersionInFile(filePath, oldVersion, newVersion) {
    if (!fs.existsSync(filePath)) {
        console.log(`⚠️  File not found: ${filePath}`);
        return false;
    }

    let content = fs.readFileSync(filePath, 'utf8');
    let updated = false;

    // Update version in PHP file header
    if (filePath.endsWith('.php')) {
        const versionRegex = /Version:\s*([^\n\r]+)/;
        if (versionRegex.test(content)) {
            content = content.replace(versionRegex, `Version: ${newVersion}`);
            updated = true;
        }
        
        // Update STK_IMG_ITS_VERSION constant (correct constant name)
        const constantRegex = /define\('STK_IMG_ITS_VERSION',\s*'([^']+)'\);/;
        if (constantRegex.test(content)) {
            content = content.replace(constantRegex, `define('STK_IMG_ITS_VERSION', '${newVersion}');`);
            updated = true;
        }
    }

    // Update version in readme.txt
    if (filePath.endsWith('readme.txt')) {
        const stableTagRegex = /Stable tag:\s*([^\n\r]+)/;
        if (stableTagRegex.test(content)) {
            content = content.replace(stableTagRegex, `Stable tag: ${newVersion}`);
            updated = true;
        }
    }

    // Update version in asset enqueuing
    const assetVersionRegex = /'([^']*\.css|\.js)',\s*\[\],\s*'([^']+)'/g;
    if (assetVersionRegex.test(content)) {
        content = content.replace(assetVersionRegex, (match, asset, oldVer) => {
            return `'${asset}', [], '${newVersion}'`;
        });
        updated = true;
    }

    if (updated) {
        fs.writeFileSync(filePath, content, 'utf8');
        console.log(`✅ Updated version in ${filePath}`);
        return true;
    }

    return false;
}

// Function to update all version references
function updateAllVersions(newVersion) {
    console.log(`🔄 Updating version to ${newVersion}...`);
    
    const filesToUpdate = [
        'stock-images-by-indietech.php', // Correct filename
        'readme.txt'
    ];

    let updatedCount = 0;
    filesToUpdate.forEach(file => {
        if (updateVersionInFile(file, version, newVersion)) {
            updatedCount++;
        }
    });

    console.log(`✅ Updated version in ${updatedCount} files`);
    return updatedCount > 0;
}

// Function to increment version
function incrementVersion(type = 'patch') {
    const [major, minor, patch] = version.split('.').map(Number);
    let newVersion;

    switch (type) {
        case 'major':
            newVersion = `${major + 1}.0.0`;
            break;
        case 'minor':
            newVersion = `${major}.${minor + 1}.0`;
            break;
        case 'patch':
        default:
            newVersion = `${major}.${minor}.${patch + 1}`;
            break;
    }

    return newVersion;
}

// Function to copy directory recursively
function copyDirectory(src, dest) {
    if (!fs.existsSync(dest)) {
        fs.mkdirSync(dest, { recursive: true });
    }
    
    const items = fs.readdirSync(src);
    
    for (const item of items) {
        // Skip hidden files, system files, and other unwanted files
        if (item.startsWith('.') || 
            item === '.DS_Store' || 
            item === 'Thumbs.db' || 
            item === 'desktop.ini' ||
            item === 'node_modules' ||
            item === '.git' ||
            item === '.gitignore' ||
            item === 'package-lock.json' ||
            item.endsWith('.zip')) {
            continue;
        }
        
        const srcPath = path.join(src, item);
        const destPath = path.join(dest, item);
        
        if (fs.statSync(srcPath).isDirectory()) {
            copyDirectory(srcPath, destPath);
        } else {
            fs.copyFileSync(srcPath, destPath);
        }
    }
}

// Check if version update is requested
const args = process.argv.slice(2);
let shouldUpdateVersion = false;
let versionType = 'patch';

if (args.includes('--version') || args.includes('-v')) {
    shouldUpdateVersion = true;
    const versionIndex = args.indexOf('--version') !== -1 ? args.indexOf('--version') : args.indexOf('-v');
    if (args[versionIndex + 1]) {
        versionType = args[versionIndex + 1];
    }
}

console.log('🚀 Starting WordPress plugin packaging...');

// Update version if requested
let finalVersion = version;
let finalZipName = zipName;

if (shouldUpdateVersion) {
    const newVersion = incrementVersion(versionType);
    console.log(`📈 Incrementing version: ${version} → ${newVersion}`);
    
    // Update package.json
    packageJson.version = newVersion;
    fs.writeFileSync('package.json', JSON.stringify(packageJson, null, 2) + '\n');
    console.log('✅ Updated package.json version');
    
    // Update plugin files
    updateAllVersions(newVersion);
    
    // Update the version variables for this script
    finalVersion = newVersion;
    finalZipName = `${pluginName}-v${newVersion}.zip`;
    
    console.log(`🔄 Using updated version: ${finalVersion}`);
} else {
    console.log(`📋 Using current version: ${finalVersion}`);
}

// Create output directory
if (!fs.existsSync(outputDir)) {
    fs.mkdirSync(outputDir, { recursive: true });
    console.log(`✅ Created output directory: ${outputDir}`);
}

// Copy files to dist directory
console.log('📁 Copying plugin files...');

// Copy main plugin file (correct filename)
if (fs.existsSync('stock-images-by-indietech.php')) {
    fs.copyFileSync('stock-images-by-indietech.php', path.join(outputDir, 'stock-images-by-indietech.php'));
    console.log('✅ Copied stock-images-by-indietech.php');
} else {
    console.error('❌ Main plugin file stock-images-by-indietech.php not found!');
    process.exit(1);
}

// Copy readme.txt
if (fs.existsSync('readme.txt')) {
    fs.copyFileSync('readme.txt', path.join(outputDir, 'readme.txt'));
    console.log('✅ Copied readme.txt');
}

// Copy index.php (security file)
if (fs.existsSync('index.php')) {
    fs.copyFileSync('index.php', path.join(outputDir, 'index.php'));
    console.log('✅ Copied index.php');
}

// Copy assets directory completely
if (fs.existsSync('assets')) {
    copyDirectory('assets', path.join(outputDir, 'assets'));
    console.log('✅ Copied assets directory');
}

// Copy templates directory completely
if (fs.existsSync('templates')) {
    copyDirectory('templates', path.join(outputDir, 'templates'));
    console.log('✅ Copied templates directory');
}

// Copy languages directory completely
if (fs.existsSync('languages')) {
    copyDirectory('languages', path.join(outputDir, 'languages'));
    console.log('✅ Copied languages directory');
}

// Copy screenshots directory completely
if (fs.existsSync('screenshots')) {
    copyDirectory('screenshots', path.join(outputDir, 'screenshots'));
    console.log('✅ Copied screenshots directory');
}

// Copy scripts directory completely
if (fs.existsSync('scripts')) {
    copyDirectory('scripts', path.join(outputDir, 'scripts'));
    console.log('✅ Copied scripts directory');
}

// Create zip file
console.log('📦 Creating zip file...');

try {
    // Remove existing zip if it exists
    if (fs.existsSync(finalZipName)) {
        fs.unlinkSync(finalZipName);
        console.log('🗑️  Removed existing zip file');
    }
    
    // Create zip using system zip command
    const zipCommand = `cd ${outputDir} && zip -r ../${finalZipName} .`;
    execSync(zipCommand, { stdio: 'inherit' });
    
    console.log(`✅ Successfully created: ${finalZipName}`);
    console.log(`📁 Plugin ready for upload: ${path.resolve(finalZipName)}`);
    
    // Clean up dist directory
    fs.rmSync(outputDir, { recursive: true, force: true });
    console.log('🧹 Cleaned up temporary files');
    
} catch (error) {
    console.error('❌ Error creating zip file:', error.message);
    console.log('💡 Make sure you have zip command available on your system');
    console.log('   On macOS/Linux: zip should be pre-installed');
    console.log('   On Windows: Install 7-Zip or use WSL');
    process.exit(1);
}

console.log('\n🎉 WordPress plugin packaging complete!');
console.log(`📤 You can now upload ${finalZipName} to your WordPress site`);

// Show usage information
if (args.includes('--help') || args.includes('-h')) {
    console.log('\n📖 Usage:');
    console.log('  npm run package                    - Package with current version');
    console.log('  npm run package -- --version       - Package with incremented patch version');
    console.log('  npm run package -- --version patch - Package with incremented patch version');
    console.log('  npm run package -- --version minor - Package with incremented minor version');
    console.log('  npm run package -- --version major - Package with incremented major version');
} 