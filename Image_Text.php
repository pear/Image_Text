<?php

    define("IMAGE_TEXT_REGEX_HTMLCOLOR", "/^.*([a-fA-F0-9]{2})([a-fA-F0-9]{2})([a-fA-F0-9]{2})$/", true);

    define("IMAGE_TEXT_ALIGN_LEFT", "left", true);
    define("IMAGE_TEXT_ALIGN_RIGHT", "right", true);
    define("IMAGE_TEXT_ALIGN_CENTER", "center", true);

    require_once 'Image/Image_Tools.php';
    require_once 'Image/Image_Text/Image_Text_Line.php';

    /**
    * Image_Text
    * A generic class for text operations on images
    * 
    * @access public
    * @package Image_Text
    * @author Tobias Schlitt <tobias@schlitt.info>
    * @categorie Image
    * @license http://www.php.net/license/3_0.txt The PHP License, version 3.0
    */


    class Image_Text {

        /**
        * The lines of the text
        *
        * @access public
        * @var array object ImageText
        */

        var $lines = array();

        // Main settings

        /**
        * Options array, these options can be set through the constructor, fontFile and fontPath, width and height are obligatory
        *
        * @access public
        * @var array
        */

        var $options = array(
            'fontPath'          => "./",            // OBLIGATORY: Path to your fonts
            'fontFile'          => "",              // OBLIGATORY: Font file to use
            'width'             => 0,               // OBLIGATORY: Width of the text box
            'height'            => 0,               // OBLIGATORY: Height of the text box
            'fontSize'          => 1,               // OPTIONAL: Fontsize to use for rendering
            'color'             => array('r' => 255, 'g' => 255, 'b' => 255), // OPTIONAL: Colorcode @see Image_Text::colorize()
            'bgcolor'           => false,           // OPTIONAL: Colorcode for background color of the box @see Image_Text::colorize() (needed for addBorder)
            'minFontSize'       => 1,               // OPTIONAL: Measurizing, min fontSize @see Image_Text::measurize()
            'maxFontSize'       => 100,             // OPTIONAL: Measurizing, max fontSize @see Image_Text::measurize()
            'maxLines'          => 100,             // OPTIONAL: Measurizing, max number of lines
            'align'             => IMAGE_TEXT_ALIGN_LEFT,       // OPTIONAL: Standard alignement (has to be of ALIGNMENT constants)
            'borderWidth'       => 0,               // OPTIONAL: Set the border width
            'borderColor'       => false,           // OPTIONAL: Set the border color
            'borderRoundWidth'  => 0,               // OPTIONAL: Round the border edges
            'borderSpacing'     => 0,               // OPTIONAL: Space between text box and sourinding (use negative values for inner border)
            'shadowX'           => false,           // OPTIONAL: X adjustment for a shadow
            'shadowY'           => false,           // OPTIONAL: Y adjustment for a shadow
            'shadowColor'       => false,           // OPTIONAL: Color for a shadow @see Image_Text::colorize()
            'imageType'         => 'png');          // Type of image to generate

        /**
        * The image generated
        *
        * @var resource image
        */

        var $img;

        /**
        * Stores the current line key (in lack of array_funtions returning references
        *
        * @var int
        */

        var $_current = 0;

        /**
        * Constructor
        * Constructor
        *
        * @access public
        * @param string $text The text to render as string, array of strings or array of ImageTextLine objects
        * @param array $options Array of options (see public attributes). fontPath, fontFile, width and height are neccessary
        */

        function Image_Text ( $text, $options ) {
            foreach ($options as $key => $value) {
                if (isset($this->options[$key])) {
                    $this->options[$key] = $value;
                }
            }
            if (is_array($text)) {
                foreach ($text as $key => $item) {
                    if (is_object($item) && is_a($item, "Image_Text_Line")) {
                        $this->lines[] = $item;
                    } else {
                        $this->lines[] = new Image_Text_Line($item, $this->options);
                    }
                }
            } else {
                $this->lines[] = new Image_Text_Line($text, $this->options);
            }
        }

        /**
        * Render the text to an image
        * Render the text to an image
        *
        * @access public
        * @param int $x The x position of the text box
        * @param int $y The y position of the text box
        * @param resource image $img The image to render to (optional, give false to create new)
        */

        function renderImage ( $x = 0, $y = 0, &$img ) {
            $size = $this->getSize();
            $border = (2 * $this->options['borderSpacing']);
            if (!$img) {
                if (!$this->image) {
                    $this->img =& $this->_newImage( $x, $y );
                }
                $img =& $this->img;
            }
            if ($this->options['bgcolor']) {
                $bgCol = imagecolorallocate($img, $this->options['bgcolor']['r'], $this->options['bgcolor']['g'], $this->options['bgcolor']['b']);
                imagefilledrectangle($img, $x, $y, $x + $this->options['width'], $y + $this->options['height'], $bgCol);
            }
            if ($this->options['borderColor']) {
                Image_Tools::addBorder ( $img,
                                         $x + $this->options['borderWidth'],
                                         $y + $this->options['borderWidth'],
                                         $this->options['width'] + $border,
                                         $this->options['height'] + $border - $this->options['borderWidth'],
                                         $this->options['borderColor'],
                                         $this->options['bgcolor'],
                                         $this->options['borderWidth'],
                                         $this->options['borderRoundWidth'] );
            }
            $actX = $x + $this->options['borderSpacing'] + $this->options['borderWidth'];
            $actY = $y + $this->options['borderSpacing'] + $this->options['borderWidth'];
            foreach ($this->lines as $key => $line) {
                $size = $this->lines[$key]->getSize($this->options['fontPath'].$this->options['fontFile']);
                $actY += $size['height'] + round($this->_getLineSpace / 2);
                $this->lines[$key]->toImage($img, $actX, $actY, $this->options['fontPath'].$this->options['fontFile']);
                $actY += round($this->_getLineSpace / 2);
            }
        }

        /**
        * Output image to stdout (normaly browser)
        * Output image to stdout (normaly browser)
        *
        * @param string $imageType a valig image-type (optional)
        * @return bool True on success, otherwise failure
        */

        function outImage ( $imageType = null ) {
            if (!isset($imageType)) {
                $imageType = $this->options['imageType'];
            }
            $this->_prepareImage($imageType);
            header("Content-type: image/".strtolower($imageType));
            $function = "image".$imageType;
            return $function($this->img);
        }

        /**
        * Return the image resource (reference)
        * Return the image resource (reference)
        *
        * @return mixed Resource image on success, otherwise false
        */

        function &getImage ( ) {
            if (!$this->_prepareImage()) {
                return false;
            }
            return $this->img;
        }

        /**
        * Get the size of the text box
        * Get the size of the text box
        *
        * @access public
        * @param array $lines The lines in the text box (optional, takes the owned lines)
        * @param int $fontsize The font size to check with (optional, takes the own)
        * @return array The size of the box
        */

        function getSize ( $lines = null, $fontsize = null ) {
            if (!isset($lines)) {
                $lines = $this->lines;
            }
            if (!isset($fontsize)) {
                $fontsize = $this->options['fontSize'];
            }
            $size['width'] = 0; $size['height'] = 0;
            foreach ($lines as $key => $line) {
                $lineSize = $lines[$key]->getSize($this->options['fontPath'].$this->options['fontFile'], $fontsize);
                $size['width'] += $lineSize['width'];
                $size['height'] += $lineSize['height'];
            }
            return $size;
        }

        /**
        * Measure the text in the box
        * This function trys to find out the best measures for a given text. It determines the
        * optimal font size and the lines and their content in the box.
        *
        * @access public
        * @return bool True on success, otherwise false
        */

        function measurize ( ) {
            $tokens = $this->getAllTokens();
            $lines = false;
            for ($i = $this->options['maxFontSize']; $i > 1; $i--) {
                $tmpTokens = $tokens;
                $lines = false;

                $lines = $this->_testMeasure( $lines, $tmpTokens, $this->$options['fontPath'].$this->options['fontFile'], $i);

                if ($lines) {
                    break;
                }
            }
            if ($lines) {
                $this->options['fontSize'] = $i;
                $this->lines = $lines;
                return true;
            }
            return false;
        }

        /**
        * Align all lines in the textbox
        * Align all lines in the textbox
        *
        * @access public
        * @param const $align The align to set (optional, takes the own)
        */

        function align ( $align = null ) {
            if (!isset($align)) {
                $align = $this->options['align'];
            }
            foreach ($this->lines as $key => $line) {
                $this->lines[$key]->align($align, $this->options['width'], $this->options['fontPath'].$this->options['fontFile']);
            }
        }

        /**
        * Colorize the text box
        * Colorize the lines in the text box. This method can take 2 color formats. It
        * either gets an array with keys "r", "g" and "b" asigned to a decimal number or
        * a HTML style hex code triple representing the color. It is althought possible to
        * give this method an array of the mentioned color types to alternate the color of the lines.
        * If there are more lines than colors, the colors are cycled.
        *
        * @access public
        * @param mixed $color The color(s) to set
        */

        function colorize ( $color = null, $bgcolor = null ) {
            if (!isset($color)) {
                $color = $this->options['color'];
            }
            if (!is_array($color) || isset($color['r'])) {
                $color = array($color);
            }
            if (isset($bgcolor)) {
                $this->options['bgcolor'] = $this->_translateColor($bgcolor);
            }
            $i = 0;
            foreach ($this->lines as $key => $array) {
                $color[$i] = $this->_translateColor($color[$i]);
                $this->lines[$key]->colorize($color[$i++]);
                if ($i >= count($color)) {
                    $i = 0;
                }
            }
            $this->options['color'] =& $color;
        }

        /**
        * Add a border to your text box
        * This method adds a border with the given settings to your text box.
        * The border is outlined for standard, if you want to have the border inlined,
        * use a negative spacing value. Beware, that if you place a border inside an
        * image, eveything under the textbox will be overwriten. If you use rounding,
        * the border background has to have the same color as your image background.
        *
        * @access public
        * @param int $lineWidth Width of the line for the border
        * @param mixed color $fgColor Color of the border @see Imaget_Text::colorize() for color types
        * @param mixed color $bgColor Background color of your @see Imaget_Text::colorize() for color types
        * @param int $spacing Space between text and border
        * @param int $roundWidth If you like rounded edges, set this to a positive value
        */
        
        function addBorder ( $lineWidth, $fgColor, $bgColor, $spacing = 0, $roundWidth = 0 ) {

            $this->options['borderWidth'] = $lineWidth;
            $this->options['borderColor'] = $this->_translateColor($fgColor);
            $this->options['bgcolor'] = $this->_translateColor($bgColor);
            $this->options['borderSpacing'] = $spacing;
            $this->options['borderRoundWidth'] = $roundWidth;

        }

        function addShadow ( $xAdjust, $yAdjust, $color = false ) {
            foreach ($this->lines as $key => $line) {
                $this->lines[$key]->addShadow($xAdjust, $yAdjust, $this->_translateColor($color));
            }
        }

        /**
        * NOT CORRECT IMPLEMENTED YET!!!
        */

        function rotate ( $angle ) {
            foreach ($this->lines as $key => $line) {
                $this->lines[$key]->rotate($angle);
            }
            $width = $this->options['width'];
            $this->options['width'] = $this->options['height'];
            $this->options['height'] = $width;
        }

        /**
        * Get the current selected line (reference)
        * Get the current selected line (reference)
        *
        * @return Image_Text_Line The current line as refernce
        */


        function &currentLine ( ) {
            return $this->lines[$this->_current];
        }

        /**
        * Get the next line and select it (reference)
        * Get the next line and select it (reference)
        *
        * @return Image_Text_Line The next line as refernce
        */

        function &nextLine ( ) {
            if (isset($this->_current)) {
                return $this->currentLine();
            } else {
                $this->_curent = 0;
            }
            return false;
        }

        /**
        * Get the first line and select it (reference)
        * Get the first line and select it (reference)
        *
        * @return Image_Text_Line The first line as refernce
        */

        function &firstLine ( ) {
            $this->_curent = 0;
            return $this->currentLine();
        }

        /**
        * Get number of lines in this object
        * Get number of lines in this object
        *
        * @return int Number of lines
        */

        function countLines ( ) {
            return count($this->lines);
        }

        /**
        * Get all lines
        * Get all lines
        *
        * @access public
        * @return array string Array of Image_Text_Line objects
        */

        function &getAllLines ( ) {
            return $this->lines;
        }

        /**
        * Get all tokens of your text in one array
        * Get all tokens of your text in one array
        *
        * @access public
        * @return array string Array of tokens
        */

        function getAllTokens ( ) {
            $tokens = array();
            foreach ($this->lines as $key => $line) {
                $tokens = array_merge($tokens, $line->getTokens());
            }
            return $tokens;
        }

        /**
        * Check the options array
        * Check the options array
        *
        * @param array $options The array of options
        * @return bool True on success, otherwise failure
        */

        function _checkOptions ( $options, $initial = true ) {
            if ($initial && (!isset($options['fontPath']) ||
                    !isset($options['fontFile']) ||
                    !isset($options['width']) ||
                    !isset($options['height']))) {
                return false;
            }
            if (!is_file($options['fontPath'].$options['fontFile'])) {
                return false;
            }
            if (($options['width'] <= 0) || ($options['height'] <= 0)) {
                return false;
            }
            return true;
        }

        /**
        * Create a new image
        * Create a new image
        *
        * @param int $x The x adjustment of the text box
        * @param int $y The y adjustment of the text box
        * @return resource image The new image resource
        */

        function &_newImage ( $x, $y ) {
            $size = $this->getSize();
            $border = (2 * $this->options['borderSpacing']) + $this->options['borderWidth'];
            $x += $this->options['width'] + $border;
            $y += $this->options['height'] + $border;
            return imagecreate($x, $y);
        }

        /**
        * Prepare the image for output
        * Prepare the image for output
        *
        * @param string $imageType The type of image (see options, optional)
        * @return bool True on succes, otherwise failure
        */

        function _prepareImage ( $imageType = null ) {
            if (!isset($imageType)) {
                if (!$this->_checkImageType($imageType)) {
                    return false;
                }
                $imageType = $this->options['imageType'];
            }
            $this->imageType = $imageType;
            if (!isset($this->img)) {
                If (!$this->renderImage(0,0)) {
                    return false;
                }
            }
            return true;
        }

        function _checkImageType ( $imageType )  {
            switch ($imageType) {
                case 'png':
                case 'jpeg':
                case 'xpm':
                    return true;
                    break;
                default:
                    return false;
                    break;
            }
        }

        /**
        * Get the linespace for rendering
        * Get the linespace for rendering.
        *
        * @todo The linespace is currently determined by the half of the fontsize. This is
        * neccessary, because lines may be differnet hight. Has to be changed when possible.
        *
        * @access private
        * @return int The linespace
        */

        function _getLineSpace ( ) {
            return (int)round($this->options['fontSize'] / 2);
        }

        /**
        * Translate HTML colorformat to array
        * Translate HTML colorformat to array
        *
        * @access private
        * @param string $color A single color value
        * @returns array The translated color code
        */

        function _translateColor ( $color ) {

            if (is_array($color) && isset($color['r']) && isset($color['g']) && isset($color['b'])) {
                return $color;
            }

            if (!is_array($color) && preg_match(IMAGE_TEXT_REGEX_HTMLCOLOR, $color, $matches)) {
                $color = array(
                               'r' => hexdec($matches[1]),
                               'g' => hexdec($matches[2]),
                               'b' => hexdec($matches[3])
                               );
                return $color;
            }
            return false;
        }

        /**
        * The actual measurizer process
        * The actual measurizer process, called recursively for a special font and font size
        * from attribute maxFontSize until it finds a fitting solution or attribute minFontSize
        * is reached.
        *
        * @todo This could be optimized I think for performance and line division issues.
        *
        * @param bool $lines Has to be false (becomes the new lines array while recursing, has to be a reverence)
        * @param array $tokens All current text tokens (copy of the actual but a reverence, because recursion)
        * @param string $font The complete fontpath + file
        * @param int $fontsize The fontsize to test with
        */

        function &_testMeasure ( &$lines, &$tokens, $font, $fontsize ) {
            if (!$lines[0]) {
                $lines[0] =& new Image_Text_Line(array_shift($tokens), $this->options);
                $lines[0]->options['fontSize'] = $fontsize;
                $line =& $lines[0];
            } else {
                $line =& $lines[(count($lines) - 1)];
                $line->pushToken(array_shift($tokens));
            }
            $lineSize = $line->getSize($font, $fontsize);
            if ($lineSize['width'] > $this->options['width']) {
                $lines[count($lines)] =& new Image_Text_Line($line->popToken(), $this->options);
                $lines[(count($lines) - 1)]->options['fontSize'] = $fontsize;
            }
            $size = $this->getSize($lines, $fontsize);
            if (($size['height'] + (count($lines) * ($fontsize / 2)) + ($fontsize / 2)) > $this->options['height']) {
                return false;
            }
            if (count($tokens) == 0) {
                return $lines;
            } else {
                return $this->_testMeasure( $lines, $tokens, $font, $fontsize );
            }
        }

    }
?>