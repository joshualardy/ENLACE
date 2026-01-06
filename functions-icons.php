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
    // Remove 'Icon' suffix if present and convert to lowercase
    $icon_name = strtolower(str_replace('icon', '', $icon_name));
    
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
        $searched_name = strtolower(str_replace('icon', '', $icon_name));
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
