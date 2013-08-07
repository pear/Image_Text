<?php
/**
 * Image_Text.
 *
 * This is a simple example script, showing Image_Text's facilities.
 *
 * PHP version 5
 *
 * @category  Image
 * @package   Image_Text
 * @author    Tobias Schlitt <toby@php.net>
 * @copyright 1997-2005 The PHP Group
 * @license   http://www.php.net/license/3_01.txt PHP License
 * @link      http://pear.php.net/package/Image_Text
 * @since     File available since Release 0.0.1
 */
require_once 'Image/Text.php';

$colors = array(
    0 => '#0d54e2',
    1 => '#e8ce7a',
    2 => '#7ae8ad'
);

$text = "EXTERIOR: DAGOBAH -- DAY\nWith Yoda\nstrapped to\n\nhis back, Luke climbs" .
    " up one of the many thick vines that grow in the swamp until he reaches the" .
    " Dagobah statistics lab. Panting heavily, he continues his exercises --" .
    " grepping, installing new packages, logging in as root, and writing" .
    " replacements for two-year-old shell scripts in PHP.\nYODA: Code! Yes. A" .
    " programmer's strength flows from code maintainability. But beware of Perl." .
    " Terse syntax... more than one way to do it... default variables. The dark" .
    " side of code maintainability are they. Easily they flow, quick to join you" .
    " when code you write. If once you start down the dark path, forever will it" .
    " dominate your destiny, consume you it will.\nLUKE: Is Perl better than" .
    " PHP?\nYODA: No... no... no. Orderless, dirtier, more seductive.\nLUKE: But" .
    " how will I know why PHP is better than Perl?\nYODA: You will know. When your" .
    " code you try to read six months from now...";

$options = array(
    'canvas' => array(
        'width' => 600,
        'height' => 600
    ), // Generate a new image 600x600 pixel
    'cx' => 300, // Set center to the middle of the canvas
    'cy' => 300,
    'width' => 300, // Set text box size
    'height' => 300,
    'line_spacing' => 1, // Normal linespacing
    'angle' => 45, // Text rotated by 45
    'color' => $colors, // Predefined colors
    'background_color' => '#FF0000', //red background
    'max_lines' => 100, // Maximum lines to render
    'min_font_size' => 2, // Minimal/Maximal font size (for automeasurize)
    'max_font_size' => 50,
    'font_path' => './', // Settings for the font file
    'font_file' => 'Vera.ttf',
    'antialias' => true, // Antialiase font rendering
    'halign' => Image_Text::IMAGE_TEXT_ALIGN_RIGHT, // Alignment to the right
    'valign' => Image_Text::IMAGE_TEXT_ALIGN_MIDDLE // Alignment to the middle
);

// Generate a new Image_Text object
$itext = new Image_Text($text, $options);

// Initialize and check the settings
$itext->init();

// Automatically determine optimal font size
$itext->autoMeasurize();

// Render the image
$itext->render();

// Display it
$itext->display();
