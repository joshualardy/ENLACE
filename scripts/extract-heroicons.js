const fs = require('fs-extra');
const path = require('path');

// Paths
const heroiconsPath = path.join(__dirname, '../node_modules/@heroicons/react/24/solid');
const outputPath = path.join(__dirname, '../assets/icons/heroicons/24/solid');

async function extractIcons() {
    try {
        // Check if heroicons are installed
        if (!await fs.pathExists(heroiconsPath)) {
            console.error('❌ Heroicons not found. Please run: npm install');
            process.exit(1);
        }
        
        // Ensure output directory exists
        await fs.ensureDir(outputPath);
        
        // Read all icon files from heroicons
        const iconFiles = await fs.readdir(heroiconsPath);
        
        let extractedCount = 0;
        
        for (const file of iconFiles) {
            if (file.endsWith('.js') && file !== 'index.js') {
                const iconName = path.basename(file, '.js');
                const filePath = path.join(heroiconsPath, file);
                
                // Read the icon file
                const content = await fs.readFile(filePath, 'utf8');
                
                // Extract viewBox from React.createElement("svg", ...)
                const viewBoxMatch = content.match(/viewBox:\s*["']([^"']+)["']/);
                const viewBox = viewBoxMatch ? viewBoxMatch[1] : '0 0 24 24';
                
                // Extract fill from React.createElement("svg", ...)
                const fillMatch = content.match(/fill:\s*["']([^"']+)["']/);
                const fill = fillMatch ? fillMatch[1] : 'currentColor';
                
                // Extract path elements - look for React.createElement("path", ...)
                const pathMatches = content.matchAll(/React\.createElement\("path",\s*\{([^}]+)\}\)/g);
                const paths = [];
                
                for (const match of pathMatches) {
                    const pathAttrs = match[1];
                    const dMatch = pathAttrs.match(/d:\s*["']([^"']+)["']/);
                    const fillRuleMatch = pathAttrs.match(/fillRule:\s*["']([^"']+)["']/);
                    const clipRuleMatch = pathAttrs.match(/clipRule:\s*["']([^"']+)["']/);
                    
                    if (dMatch) {
                        let pathElement = `<path d="${dMatch[1]}"`;
                        if (fillRuleMatch) {
                            pathElement += ` fill-rule="${fillRuleMatch[1]}"`;
                        }
                        if (clipRuleMatch) {
                            pathElement += ` clip-rule="${clipRuleMatch[1]}"`;
                        }
                        pathElement += '/>';
                        paths.push(pathElement);
                    }
                }
                
                if (paths.length > 0) {
                    // Create SVG file
                    const svg = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="${viewBox}" fill="${fill}">
${paths.join('\n')}
</svg>`;
                    
                    // Convert icon name: BeakerIcon -> beaker
                    const fileName = iconName.replace(/Icon$/, '').toLowerCase();
                    
                    // Save as SVG file
                    await fs.writeFile(
                        path.join(outputPath, `${fileName}.svg`),
                        svg,
                        'utf8'
                    );
                    
                    extractedCount++;
                    console.log(`✓ Extracted: ${fileName}.svg (from ${iconName})`);
                } else {
                    console.warn(`⚠ Could not extract paths from: ${iconName}`);
                }
            }
        }
        
        console.log(`\n✅ Successfully extracted ${extractedCount} icons to ${outputPath}`);
    } catch (error) {
        console.error('❌ Error extracting icons:', error.message);
        process.exit(1);
    }
}

extractIcons();
