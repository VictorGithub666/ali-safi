// Run with: node resources/js/generate-icons.js
// Requires sharp: npm install sharp

const sharp = require('sharp');
const fs = require('fs');
const path = require('path');

const sizes = [72, 96, 128, 144, 152, 192, 384, 512];
const inputImage = path.join(__dirname, '../../public/logo-1000.png');
const outputDir = path.join(__dirname, '../../public/icons');

// Create output directory if it doesn't exist
if (!fs.existsSync(outputDir)) {
    fs.mkdirSync(outputDir, { recursive: true });
}

async function generateIcons() {
    console.log('Generating PWA icons...');
    
    for (const size of sizes) {
        const outputPath = path.join(outputDir, `icon-${size}x${size}.png`);
        
        await sharp(inputImage)
            .resize(size, size, {
                fit: 'contain',
                background: { r: 5, g: 187, b: 20, alpha: 1 }
            })
            .png()
            .toFile(outputPath);
        
        console.log(`Generated: icon-${size}x${size}.png`);
    }
    
    // Generate maskable icon (with padding for safe area)
    const maskablePath = path.join(outputDir, 'icon-maskable-512x512.png');
    await sharp(inputImage)
        .resize(512, 512, {
            fit: 'contain',
            background: { r: 5, g: 187, b: 20, alpha: 1 }
        })
        .extend({
            top: 64,
            bottom: 64,
            left: 64,
            right: 64,
            background: { r: 5, g: 187, b: 20, alpha: 1 }
        })
        .png()
        .toFile(maskablePath);
    
    console.log('Generated: icon-maskable-512x512.png');
    console.log('Icon generation complete!');
}

generateIcons().catch(console.error);