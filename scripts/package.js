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
        
        // Update STOCK_IMAGES_VERSION constant
        const constantRegex = /define\('STOCK_IMAGES_VERSION',\s*'([^']+)'\);/;
        if (constantRegex.test(content)) {
            content = content.replace(constantRegex, `define('STOCK_IMAGES_VERSION', '${newVersion}');`);
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
        'stock-images.php',
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

console.log('�� Starting WordPress plugin packaging...');

// Update version if requested
if (shouldUpdateVersion) {
    const newVersion = incrementVersion(versionType);
    console.log(`📈 Incrementing version: ${version} → ${newVersion}`);
    
    // Update package.json
    packageJson.version = newVersion;
    fs.writeFileSync('package.json', JSON.stringify(packageJson, null, 2) + '\n');
    console.log('✅ Updated package.json version');
    
    // Update plugin files
    updateAllVersions(newVersion);
    
    // Update the version variable for this script
    const updatedVersion = newVersion;
    const updatedZipName = `${pluginName}-v${updatedVersion}.zip`;
    
    console.log(`🔄 Using updated version: ${updatedVersion}`);
} else {
    console.log(`📋 Using current version: ${version}`);
}

// Create output directory
if (!fs.existsSync(outputDir)) {
    fs.mkdirSync(outputDir, { recursive: true });
    console.log(`✅ Created output directory: ${outputDir}`);
}

// Copy files to dist directory
console.log('📁 Copying plugin files...');

// Copy main plugin file
if (fs.existsSync('stock-images.php')) {
    fs.copyFileSync('stock-images.php', path.join(outputDir, 'stock-images.php'));
    console.log('✅ Copied stock-images.php');
}

// Copy readme.txt
if (fs.existsSync('readme.txt')) {
    fs.copyFileSync('readme.txt', path.join(outputDir, 'readme.txt'));
    console.log('✅ Copied readme.txt');
}

// Copy assets directory
if (fs.existsSync('assets')) {
    if (!fs.existsSync(path.join(outputDir, 'assets'))) {
        fs.mkdirSync(path.join(outputDir, 'assets'), { recursive: true });
    }
    
    // Copy CSS and JS files
    if (fs.existsSync('assets/css')) {
        if (!fs.existsSync(path.join(outputDir, 'assets/css'))) {
            fs.mkdirSync(path.join(outputDir, 'assets/css'), { recursive: true });
        }
        const cssFiles = fs.readdirSync('assets/css');
        cssFiles.forEach(file => {
            if (file.endsWith('.css')) {
                fs.copyFileSync(
                    path.join('assets/css', file),
                    path.join(outputDir, 'assets/css', file)
                );
                console.log(`✅ Copied assets/css/${file}`);
            }
        });
    }
    
    if (fs.existsSync('assets/js')) {
        if (!fs.existsSync(path.join(outputDir, 'assets/js'))) {
            fs.mkdirSync(path.join(outputDir, 'assets/js'), { recursive: true });
        }
        const jsFiles = fs.readdirSync('assets/js');
        jsFiles.forEach(file => {
            if (file.endsWith('.js')) {
                fs.copyFileSync(
                    path.join('assets/js', file),
                    path.join(outputDir, 'assets/js', file)
                );
                console.log(`✅ Copied assets/js/${file}`);
            }
        });
    }
}

// Copy templates directory
if (fs.existsSync('templates')) {
    if (!fs.existsSync(path.join(outputDir, 'templates'))) {
        fs.mkdirSync(path.join(outputDir, 'templates'), { recursive: true });
    }
    
    const templateFiles = fs.readdirSync('templates');
    templateFiles.forEach(file => {
        if (file.endsWith('.php')) {
            fs.copyFileSync(
                path.join('templates', file),
                path.join(outputDir, 'templates', file)
            );
            console.log(`✅ Copied templates/${file}`);
        }
    });
}

// Copy languages directory
if (fs.existsSync('languages')) {
    if (!fs.existsSync(path.join(outputDir, 'languages'))) {
        fs.mkdirSync(path.join(outputDir, 'languages'), { recursive: true });
    }
    
    const langFiles = fs.readdirSync('languages');
    langFiles.forEach(file => {
        if (file.endsWith('.pot') || file.endsWith('.po') || file.endsWith('.mo')) {
            fs.copyFileSync(
                path.join('languages', file),
                path.join(outputDir, 'languages', file)
            );
            console.log(`✅ Copied languages/${file}`);
        }
    });
}

// Copy screenshots directory (for WordPress.org)
if (fs.existsSync('screenshots')) {
    if (!fs.existsSync(path.join(outputDir, 'screenshots'))) {
        fs.mkdirSync(path.join(outputDir, 'screenshots'), { recursive: true });
    }
    
    const screenshotFiles = fs.readdirSync('screenshots');
    screenshotFiles.forEach(file => {
        if (file.endsWith('.png') || file.endsWith('.jpg') || file.endsWith('.jpeg')) {
            fs.copyFileSync(
                path.join('screenshots', file),
                path.join(outputDir, 'screenshots', file)
            );
            console.log(`✅ Copied screenshots/${file}`);
        }
    });
}

// Create zip file
console.log('📦 Creating zip file...');

try {
    // Remove existing zip if it exists
    if (fs.existsSync(zipName)) {
        fs.unlinkSync(zipName);
    }
    
    // Create zip using system zip command
    const zipCommand = `cd ${outputDir} && zip -r ../${zipName} .`;
    execSync(zipCommand, { stdio: 'inherit' });
    
    console.log(`✅ Successfully created: ${zipName}`);
    console.log(`📁 Plugin ready for upload: ${path.resolve(zipName)}`);
    
    // Clean up dist directory
    fs.rmSync(outputDir, { recursive: true, force: true });
    console.log('🧹 Cleaned up temporary files');
    
} catch (error) {
    console.error('❌ Error creating zip file:', error.message);
    console.log('�� Make sure you have zip command available on your system');
    console.log('   On macOS/Linux: zip should be pre-installed');
    console.log('   On Windows: Install 7-Zip or use WSL');
    process.exit(1);
}

console.log('\n🎉 WordPress plugin packaging complete!');
console.log(`�� You can now upload ${zipName} to your WordPress site`);

// Show usage information
if (args.includes('--help') || args.includes('-h')) {
    console.log('\n📖 Usage:');
    console.log('  npm run package                    - Package with current version');
    console.log('  npm run package -- --version       - Package with incremented patch version');
    console.log('  npm run package -- --version patch - Package with incremented patch version');
    console.log('  npm run package -- --version minor - Package with incremented minor version');
    console.log('  npm run package -- --version major - Package with incremented major version');
} 