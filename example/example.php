<?php

    /**
     * Example for the usage of Image_Text.
     *
     * This is an example file for the Image_Text package. This script is called from a browser
     * and manipulations are possible through GET parameters.
     *
     * @package Image_Text
     * @license The PHP License, version 3.0
     * @author Tobias Schlitt <toby@php.net>
     * @category images
     * @todo Comment.
     * @todo Add more GET options.
     * @todo Implement with HTML formular.
     */

	ini_set('include_path', '.:/cvs/pear/Image_Text:/usr/share/pear');

    require_once 'Image/Text.php';

    function getmicrotime()
    {
        list($usec,$sec) = explode(' ', microtime());
        return ((float)$usec+(float)$sec);
    }
    
    $colors = array(
        0 => array('r' => 255, 'g' => 255, 'b' => 255, 'a'=>0),
        1 => '#ff0000',
        2 => '#00ff00',
        3 => array('r' => 0, 'g' => 100, 'b' => 100, 'a'=>50),
        4 => array('r' => 255, 'g' => 100, 'b' => 100),
        5 => array('r' => 150, 'g' => 150, 'b' => 150),
        6 => array('r' => 200, 'g' => 200, 'b' => 200)
    );
    
    $canvas = array(
        0 => array('width'=>600,'height'=>700)
    );
    
    
    $img = imagecreatetruecolor(800, 700);
    imagefilledrectangle($img, 20,20,400,400,0xff00ff);
    
    $texts[] = "a passion for php"; // Short Text
    $texts[] = "a good computer is like a tipi - no windows, no gates and an apache inside"; // Normal Text
    $texts[] = "What is PEAR?\nThe fleshy pome, or fruit, of a rosaceous tree (Pyrus communis), cultivated in many varieties in temperate climates.\nPEAR is a framework and distribution system for reusable PHP components. PECL, being a subset of PEAR, is the complement for C extensions for PHP. See the FAQ and manual for more information"; // Long Text
    $texts[] = "EXTERIOR: DAGOBAH -- DAY\nWith Yoda strapped to his back, Luke climbs up one of the many thick vines that grow in the swamp until he reaches the Dagobah statistics lab. Panting heavily, he continues his exercises -- grepping, installing new packages, logging in as root, and writing replacements for two-year-old shell scripts in PHP.\nYODA: Code! Yes. A programmer's strength flows from code maintainability. But beware of Perl. Terse syntax... more than one way to do it... default variables. The dark side of code maintainability are they. Easily they flow, quick to join you when code you write. If once you start down the dark path, forever will it dominate your destiny, consume you it will.\nLUKE: Is Perl better than PHP?\nYODA: No... no... no. Orderless, dirtier, more seductive.\nLUKE: But how will I know why PHP is better than Perl?\nYODA: You will know. When your code you try to read six months from now...";

    $options = array(
                'x'             => 0,
                'y'             => 0,
                
                'canvas'        => array('width'=> 800,'height'=> 600),
                /*
                'canvas'        => &$img,
                */
                //'canvas'      => 'auto',
                'width'         => 800,
                'height'        => 600,
                'fontSize'      => 34,
                'linespacing'   => 1,
                // 'angle'       => 45,
                'angle'         => 0,
                'color'         => $colors,
                'maxLines'      => 100,
                'minFontSize'   => 2,
                'maxFontSize'   => 100,
                'fontPath'      => './',
                'antialias'     => true,
                'fontFile'      => 'Vera.ttf',
                'align'         => IMAGE_TEXT_ALIGN_LEFT
            );

    $start = getmicrotime();
    
    $itext = new Image_Text($texts[2], $options);
    
    $itext->init();
    
    // $itext->render();
    
    $itext->autoMeasurize();
    
    // var_dump($itext->render());
    $itext->render();
    
    // var_dump($itext);
    
    $end = getmicrotime();
    
    $time = $end - $start;
    
    if (isset($_GET['benchmark'])) {
        echo $time." sec";
    } else {
        $itext->display();
    }

?>