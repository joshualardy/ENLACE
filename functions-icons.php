<?php
/**
 * Heroicons Helper Functions
 * 
 * Helper functions to easily include Heroicons SVG icons in WordPress templates
 */

/**
 * Get the path to an icon file
 * 
 * @param string $icon_name The name of the icon (e.g., 'BeakerIcon' or 'beaker')
 * @return string|false The path to the icon file or false if not found
 */
function get_icon_path($icon_name) {
    // Remove 'Icon' or 'icon' suffix if present (case-insensitive) and convert to lowercase
    $icon_name = preg_replace('/icon$/i', '', $icon_name);
    $icon_name = strtolower($icon_name);
    
    $icon_file = get_template_directory() . '/assets/icons/heroicons/24/solid/' . $icon_name . '.svg';
    
    if (file_exists($icon_file)) {
        return $icon_file;
    }
    
    // Try with original case if lowercase doesn't work
    $icon_file_original = get_template_directory() . '/assets/icons/heroicons/24/solid/' . $icon_name . '.svg';
    if (file_exists($icon_file_original)) {
        return $icon_file_original;
    }
    
    return false;
}

/**
 * Get icon SVG content
 * 
 * @param string $icon_name The name of the icon
 * @param array $attributes Optional attributes to add to the SVG (e.g., ['class' => 'icon', 'width' => '24'])
 * @return string The SVG HTML or empty string if icon not found
 */
function get_icon($icon_name, $attributes = array()) {
    $icon_path = get_icon_path($icon_name);
    
    if (!$icon_path) {
        // Debug: log missing icon
        $searched_name = preg_replace('/icon$/i', '', $icon_name);
        $searched_name = strtolower($searched_name);
        $expected_path = get_template_directory() . '/assets/icons/heroicons/24/solid/' . $searched_name . '.svg';
        
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("Icon not found: $icon_name");
            error_log("Searched for: $searched_name.svg");
            error_log("Expected path: $expected_path");
            error_log("Path exists: " . (file_exists($expected_path) ? 'YES' : 'NO'));
        }
        
        // Return empty or a placeholder for debugging
        if (defined('WP_DEBUG') && WP_DEBUG && WP_DEBUG_DISPLAY) {
            return '<!-- Icon not found: ' . esc_html($icon_name) . ' -->';
        }
        
        return '';
    }
    
    // Read SVG content
    $svg_content = file_get_contents($icon_path);
    
    if (!$svg_content) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("Could not read icon file: $icon_path");
        }
        return '';
    }
    
    // Force color replacement - replace currentColor everywhere with actual hex color
    // This ensures icons are visible even if CSS currentColor doesn't resolve correctly
    $fill_color = '#0F1623'; // var(--text-main) color
    
    // Step 1: Replace fill="currentColor" or fill='currentColor' in SVG tag
    $svg_content = preg_replace('/(<svg[^>]*)\s+fill\s*=\s*["\']currentColor["\']/i', '${1}', $svg_content);
    
    // Step 2: Add explicit fill to ALL path elements (even if they don't have one)
    // This ensures paths are visible regardless of inheritance
    $svg_content = preg_replace_callback(
        '/<path(\s+[^>]*)>/i',
        function($matches) use ($fill_color) {
            $attrs = $matches[1];
            // Remove any existing fill attribute first
            $attrs = preg_replace('/\s+fill\s*=\s*["\'][^"\']*["\']/i', '', $attrs);
            // Add our fill color at the beginning of attributes
            return '<path fill="' . $fill_color . '" style="fill: ' . $fill_color . ' !important;"' . $attrs . '>';
        },
        $svg_content
    );
    
    // Step 3: Replace any remaining currentColor references
    $svg_content = preg_replace('/fill\s*=\s*["\']currentColor["\']/i', 'fill="' . $fill_color . '"', $svg_content);
    
    // Step 4: Add style attribute to SVG tag to force color
    if (preg_match('/<svg([^>]*)>/i', $svg_content, $matches)) {
        $svg_attrs = $matches[1];
        // Remove existing style if present and add our style
        $svg_attrs = preg_replace('/\s+style\s*=\s*["\'][^"\']*["\']/i', '', $svg_attrs);
        $svg_attrs .= ' style="fill: ' . $fill_color . ' !important; color: ' . $fill_color . ' !important;"';
        $svg_content = preg_replace('/<svg[^>]*>/i', '<svg' . $svg_attrs . '>', $svg_content, 1);
    }
    
    // Parse SVG to add attributes
    if (!empty($attributes)) {
        // Replace or add attributes to SVG tag
        if (preg_match('/<svg\s+([^>]*)>/', $svg_content, $matches)) {
            $existing_attrs = $matches[1];
            $updated_attrs = $existing_attrs;
            
            // Update or add each attribute
            foreach ($attributes as $key => $value) {
                // Remove existing attribute if present
                $pattern = '/' . preg_quote($key, '/') . '\s*=\s*["\'][^"\']*["\']/';
                $updated_attrs = preg_replace($pattern, '', $updated_attrs);
                // Add new attribute
                $updated_attrs .= ' ' . esc_attr($key) . '="' . esc_attr($value) . '"';
            }
            
            // Clean up extra spaces
            $updated_attrs = preg_replace('/\s+/', ' ', trim($updated_attrs));
            
            $svg_content = preg_replace(
                '/<svg\s+[^>]*>/',
                '<svg ' . $updated_attrs . '>',
                $svg_content,
                1
            );
        } else {
            // If no attributes found, add them after <svg
            $attrs_string = '';
            foreach ($attributes as $key => $value) {
                $attrs_string .= ' ' . esc_attr($key) . '="' . esc_attr($value) . '"';
            }
            $svg_content = preg_replace(
                '/<svg>/',
                '<svg' . $attrs_string . '>',
                $svg_content
            );
        }
    }
    
    return $svg_content;
}

/**
 * Display icon (echo)
 * 
 * @param string $icon_name The name of the icon
 * @param array $attributes Optional attributes
 */
function the_icon($icon_name, $attributes = array()) {
    echo get_icon($icon_name, $attributes);
}

/**
 * Get icon with default WordPress styling
 * 
 * @param string $icon_name The name of the icon
 * @param string $size Size class (e.g., 'size-6', 'w-5 h-5')
 * @param string $color Color class (e.g., 'text-blue-500')
 * @return string The SVG HTML
 */
function get_icon_styled($icon_name, $size = 'size-6', $color = '') {
    $classes = array('icon', 'heroicon');
    
    if ($size) {
        $classes[] = $size;
    }
    
    if ($color) {
        $classes[] = $color;
    }
    
    return get_icon($icon_name, array(
        'class' => implode(' ', $classes)
    ));
}

/**
 * Display styled icon
 * 
 * @param string $icon_name The name of the icon
 * @param string $size Size class
 * @param string $color Color class
 */
function the_icon_styled($icon_name, $size = 'size-6', $color = '') {
    echo get_icon_styled($icon_name, $size, $color);
}
