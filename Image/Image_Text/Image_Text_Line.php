<?php

    /**
    * Image_Text_Line
    * Class that represents a line in the Image_Text object
    * 
    * @access public
    * @package Image_Text
    * @author Tobias Schlitt <tobias@schlitt.info>
    * @categorie Image
    * @license http://www.php.net/license/3_0.txt The PHP License, version 3.0
    */

    class Image_Text_Line {

        /**
        * Array of string tokens, building the line.
        *
        * @access public
        * @var array string
        */

        var $tokens = array();

        /**
        * Fontsize of the line.
        *
        * @access public
        * @var int
        */

        var $options = array(
            'fontSize'          => 1,
            'color'             => array('r' => 255, 'g' => 255, 'b' => 255),
            'align'             => IMAGE_TEXT_ALIGN_LEFT,
            'angle'             => 0,
            'antiAliasing'      => true,
            'shadowX'           => false,
            'shadowY'           => false,
            'shadowColor'       => false);

        /**
        * X position.
        *
        * @access private
        * @var array string
        */

        var $_x = 0;

        /**
        * Constructor
        * Constructor
        *
        * @access public
        * @param string $text The text of the line.
        */

        function Image_Text_Line ( $text, $options = null ) {
            if (is_array($text)) {
                $this->tokens = $text;
            } else {
                $this->tokens = explode(" ", $text);
            }
            foreach ($options as $key => $value) {
                if (isset($this->options[$key])) {
                    $this->options[$key] = $value;
                }
            }
        }

        /**
        * Render the line to an image
        * Render the line to an image
        *
        * @access public
        * @param resource image $img The image to render to.
        * @param int $x X posotion to add.
        * @param int $y Y posotion of the line.
        * @param string $font The fontfile to use for rendering.
        */

        function toImage ( &$img, $x, $y, $font ) {
            $antiAliasing = ($this->options['antiAliasing']) ? 1 : -1;
            $x += $this->_x;
            if ($this->options['shadowX'] && $this->options['shadowX']) {
                if (!$this->options['shadowColor']) {
                    $shadowCol = imagecolorclosestalpha( $img, $this->options['color']['r'], $this->options['color']['g'], $this->options['color']['b'], 50);
                } else {
                    $shadowCol = imagecolorallocate( $img, $this->options['shadowColor']['r'], $this->options['shadowColor']['g'], $this->options['shadowColor']['b']);
                }
                imagettftext($img, $this->options['fontSize'], $this->options['angle'], $x + $this->options['shadowX'], $y + $this->options['shadowX'], $antiAliasing * $shadowCol, $font, $this->getText());
            }
            $col = imagecolorallocate($img, $this->options['color']['r'], $this->options['color']['g'], $this->options['color']['b']);
            imagettftext($img, $this->options['fontSize'], $this->options['angle'], $x, $y, $antiAliasing * $col, $font, $this->getText());
            
        }

        /**
        * Align the line in a given box width.
        * Align the line in a given box width.
        *
        * @access public
        * @param const $alignment A constant which determines the alignement.
        * @param int $width The width of the box to align in.
        * @param string $font The fontfile to use for rendering.
        */

        function align ( $alignment, $width, $font ) {

            switch ($alignment) {

                case IMAGE_TEXT_ALIGN_LEFT:
                    $this->_x = 0;
                    break;

                case IMAGE_TEXT_ALIGN_RIGHT:
                    $size = $this->getSize($font);
                    $lineWidth = $size['width'];
                    $this->_x = (int)floor($width - $lineWidth);
                    break;

                case IMAGE_TEXT_ALIGN_CENTER:
                    $size = $this->getSize($font);
                    $lineWidth = $size['width'];
                    $this->_x = (int)floor(($width - $lineWidth) / 2);
                    break;
            }

        }

        /**
        * Set color of the line.
        * Set color of the line.
        *
        * @access public
        * @param array $color The color to set.
        */

        function colorize  ( $color ) {
            $this->options['color'] = $color;
        }

        /**
        * NOT CORRECT IMPLEMENTED YET!!!
        */

        function rotate ( $angle ) {
            $this->options['angle'] = $angle;
        }
    
        /**
        * Add a shadow to your text.
        * Add a shadow to your text.
        *
        * @access public
        * @param int $xAdjust Adjustment in x direction
        * @param int $yAdjust Adjustment in y direction
        * @param array $color The color to set.
        */

        function addShadow ( $xAdjust, $yAdjust, $color ) {
            $this->options['shadowX'] = $xAdjust;
            $this->options['shadowY'] = $yAdjust;
            $this->options['shadowColor'] = $color;
        }

        /**
        * Returns the line text
        * Returns the line text
        *
        * @access public
        * @returns string The text of the line
        */

        function getText ( ) {
            return implode(" ", $this->tokens);
        }

        /**
        * Returns the lines tokens
        * Returns the lines tokens
        *
        * @access public
        * @returns array The text of the line in tokens
        */

        function getTokens ( ) {
            return $this->tokens;
        }

        /**
        * Determine the size of the line in a given font and size
        * Determine the size of the line in a given font and size
        *
        * @access public
        * @param string $font The fontfile to use
        * @param int $fontsize The fontsize to use (optional)
        * @returns array Width and height of the line
        */

        function getSize ( $font, $fontsize = null ) {
            $fontsize = (isset($fontsize)) ? $fontsize : $this->options['fontSize'];
            $bbox = imagettfbbox( $fontsize, 0, $font, $this->getText());
            $size['width'] = max($bbox[2], $bbox[4]) - min($bbox[0], $bbox[6]);
            $size['height'] = max($bbox[1], $bbox[3]) - min($bbox[5], $bbox[7]);
            return $size;
        }

        /**
        * Add a token to the line
        * Add a token to the line
        *
        * @access public
        * @param string $token The token to add
        */

        function pushToken ( $token ) {
            array_push($this->tokens, $token);
        }

        /**
        * Pop a token from the line
        * Pop a token from the line
        *
        * @access public
        * @returns string $token The token poped away
        */

        function popToken ( ) {
            return array_pop($this->tokens);
        }

        /**
        * Return the current selected token
        * Return the current selected token
        *
        * @access public
        * @returns string $token The current token
        */

        function currentToken ( ) {
            return current($this->tokens);
        }

        /**
        * Return the next token and select it
        * Return the next token and select it
        *
        * @access public
        * @returns string $token The next token
        */

        function nextToken ( ) {
            return next($this->tokens);
        }

        /**
        * Return the first token and select it
        * Return the first token and select it
        *
        * @access public
        * @returns string $token The first token
        */

        function firstToken ( ) {
            reset($this->tokens);
            return $this->current();
        }

    }

?>