<?php
/**
 * Test file to verify icons are working
 * Access this file directly in browser: /wp-content/themes/ENLACE/test-icons.php
 */

// Load WordPress
require_once('../../../wp-load.php');

// Include icon functions
require_once('functions-icons.php');

?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Icons</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #1A2332;
            color: white;
        }
        .test-icon {
            display: inline-block;
            margin: 20px;
            padding: 20px;
            background: rgba(255,255,255,0.1);
            border-radius: 8px;
        }
        .test-icon svg {
            display: block;
            margin: 10px auto;
        }
        .test-label {
            text-align: center;
            margin-top: 10px;
            font-size: 12px;
        }
        .error {
            color: red;
            background: rgba(255,0,0,0.2);
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .success {
            color: green;
            background: rgba(0,255,0,0.2);
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <h1>Test des icônes Heroicons</h1>
    
    <?php
    $icon_path = get_template_directory() . '/assets/icons/heroicons/24/solid/user.svg';
    $exists = file_exists($icon_path);
    ?>
    
    <div class="<?php echo $exists ? 'success' : 'error'; ?>">
        <strong>Chemin de l'icône UserIcon:</strong><br>
        <?php echo esc_html($icon_path); ?><br>
        <strong>Fichier existe:</strong> <?php echo $exists ? 'OUI ✓' : 'NON ✗'; ?>
    </div>
    
    <h2>Test UserIcon (16x16)</h2>
    <div class="test-icon">
        <?php the_icon('UserIcon', array('width' => '16', 'height' => '16', 'style' => 'color: white;')); ?>
        <div class="test-label">UserIcon 16x16</div>
    </div>
    
    <h2>Test ArrowRightOnRectangleIcon (16x16)</h2>
    <div class="test-icon">
        <?php the_icon('ArrowRightOnRectangleIcon', array('width' => '16', 'height' => '16', 'style' => 'color: white;')); ?>
        <div class="test-label">ArrowRightOnRectangleIcon 16x16</div>
    </div>
    
    <h2>Test UserIcon (24x24)</h2>
    <div class="test-icon">
        <?php the_icon('UserIcon', array('width' => '24', 'height' => '24', 'style' => 'color: white;')); ?>
        <div class="test-label">UserIcon 24x24</div>
    </div>
    
    <h2>Test avec get_icon() (retourne HTML)</h2>
    <div class="test-icon">
        <?php echo get_icon('UserIcon', array('width' => '32', 'height' => '32', 'style' => 'color: #4A90E2;')); ?>
        <div class="test-label">UserIcon 32x32 (bleu)</div>
    </div>
    
    <h2>Debug: Contenu brut du fichier user.svg</h2>
    <pre style="background: rgba(0,0,0,0.3); padding: 10px; border-radius: 4px; overflow: auto;">
<?php 
if ($exists) {
    echo esc_html(file_get_contents($icon_path));
} else {
    echo "Fichier non trouvé";
}
?>
    </pre>
</body>
</html>
