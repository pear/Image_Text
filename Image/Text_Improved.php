<?php

    require_once 'PEAR.php';

define("IMAGE_TEXT_REGEX_HTMLCOLOR", "/^[#|]([a-fA-F0-9]{2})?([a-fA-F0-9]{2})([a-fA-F0-9]{2})([a-fA-F0-9]{2})$/", true);
define("IMAGE_TEXT_ALIGN_LEFT", "left", true);
define("IMAGE_TEXT_ALIGN_RIGHT", "right", true);
define("IMAGE_TEXT_ALIGN_CENTER", "center", true);

class Image_Text {

    var $text;

    var $aligns = array('left','right','center');
    /**
     * Options array, these options can be set through the constructor,
     *
     * Structure:
     * Required options:
     * canvas: Either an image ressource or an array with 'width' and 'height'
     *         keys
     * - fontPath: Path to your fonts
     * - fontFile: Font filename to use
     * - width: width of the text block
     * - height: height of the text block
     * Optional options:
     * - fontSize: Fontsize to use for rendering
     * - color: Colorcode (default white) see {@link Image_Text::colorize()}
     * - bgcolor: Background color of the block
     * - align: alignement (one of the alignement constants)
     * - minFontSize: min font size see {@link Image_Text::colorize()}
     * - maxFontSize: max font size see {@link Image_Text::colorize()}
     * - maxLines: Maximum amount of lines
     * - borderWidth: Border width
     * - borderColor: Border color
     * - borderRoundWith: Round the border edges
     * - borderSpacing: Space between border and texts, use negative values
     *                  for inner border
     * - imageType: Type of image to output/store (see php IMAGETYPE constanst)
     * - destFile: File path see (@link Image_Text::display())
     *
     * @access public
     * @var array
     */
    var $options = array(
            // Surface
            'canvas'            => null,
            'fontPath'          => "./",
            'fontFile'          => "",
            'width'             => 0,
            'height'            => 0,
            'fontSize'          => 1,
            'angle'             => 0,
            'antialias'         => true,
            'linespacing'       => 1,
            'color'             => array(
                                    'r' => 255, 'g' => 255, 'b' => 255, 'a'=>0
                                   ),
            'bgcolor'           => false,
            'align'             => IMAGE_TEXT_ALIGN_LEFT,
            'minFontSize'       => 1,
            'maxFontSize'       => 100,
            'maxLines'          => 100,
            'borderWidth'       => 0,
            'borderColor'       => false,
            'borderRoundWidth'  => 0,
            'borderSpacing'     => 0,
            'imageType'         => IMAGETYPE_PNG,
            'destFile'          => ''
        );

    /**
     * Image Canvas
     *
     * @access private
     * @var ressource
     */
    var $_img;

    /**
     * Tokens (each word)
     *
     * @access private
     * @var array
     */
    var $_tokens = array();

    /**
     * Fullpath to the font
     *
     * @access private
     * @var string
     */
    var $_font;


    /**
     * Contains the bbox of each rendered lines
     *
     * @access public
     * @var array
     */
    var $bbox = array();

    /**
     * Defines in which mode the canvas has be set
     *
     * @access public
     * @var array
     */
    var $_mode = '';


    /**
     * Color indeces returned by imagecolorallocatealpha
     *
     * @access public
     * @var array
     */
    var $colors = array();

    /**
     * Define the mode to used for colors
     * 'lines' switches the color on each line
     * 'paragraph' switches the color on each paragraph
     *
     * @access public
     * @var array
     */
    var $colorMode = 'paragraph';

    
    var $_init = false;

    /**
     * Constructor
     *
     * Set the text and options
     *
     * @param   string  $text       Text to print
     * @param   array   $options    Options (@link Image_Text::options)
     * @access public
     */
    function Image_Text($text, $options)
    {
        $this->text = $text;
        $this->options = array_merge($this->options,$options);
    }

    /**
     * Set the alignement mode
     *
     * The following constants should be used:
     * - IMAGE_TEXT_ALIGN_LEFT
     * - IMAGE_TEXT_ALIGN_RIGHT
     * - IMAGE_TEXT_ALIGN_CENTER
     *
     * @param   string  $mode       Alignenment mode
     * @access public
     */
    function setAlign($mode)
    {
        if (!in_array($mode,$this->aligns)) {
            $this->options['align'] = $mode;
        } else {
            PEAR::raiseError('Invalid alignment');
        }
    }

    /**
     * Set the colors
     *
     * The following colors syntax must be used:
     * "#08ffff00" hexadecimal format with alpha channel (08)
     * array with 'r','g','b','a'(optionnal) keys
     * A GD color special color (tiled,...)
     * A single color or an array of colors are allowed
     *
     * @param   mixed  $colors       Array of colors
     * @access public
     */
    function setColors($colors)
    {
        if (is_array($colors) && !isset($colors['r'])) {
            $i = 0;
            foreach ($colors as $color) {
                $this->setColor($color,$i++);
            }
        } else {
            $this->setColor($colors,$i);
        }
        return;
    }

    /**
     * Set the colors
     *
     * The following colors syntax must be used:
     * "#08ffff00" hexadecimal format with alpha channel (08)
     * array with 'r','g','b','a'(optionnal) keys
     * A GD color special color (tiled,...)
     * Only one color is allowed
     * If $id is given, the color index $id is used
     *
     * @param   mixed  $colors       Array of colors
     * @param   mixed  $id           Array of colors
     * @access public
     */
    function setColor($color, $id=0)
    {
        if(is_array($color)) {
            if (isset($color['r']) &&
                isset($color['g']) &&
                isset($color['b'])
            ) {
                if (!isset($color['a'])) {
                    $color['a'] = 0;
                }
                $this->options['colors'][$id] = $color;
            }
        } elseif (is_string($color)) {
            $color = $this->_convertString2RGB($color);
            if ($color) {
                $this->options['colors'][$id] = $color;
            } else {
                PEAR::raiseError('Invalid color');
                return false;
            }
        } else {
            $this->options['colors'][$id] = $color;
        }
        $color = $this->options['colors'][$id];
        if ($this->options['antialias']) {
            $this->colors[] = imagecolorallocatealpha($this->_img,
                            $color['r'],$color['g'],$color['b'],$color['a']);
        } else {
            $this->colors[] = -imagecolorallocatealpha($this->_img,
                            $color['r'],$color['g'],$color['b'],$color['a']);
        }
    }


    /**
     * Convert a color to an array
     *
     * The following colors syntax must be used:
     * "#08ffff00" hexadecimal format with alpha channel (08)
     * array with 'r','g','b','a'(optionnal) keys
     * A GD color special color (tiled,...)
     * Only one color is allowed
     * If $id is given, the color index $id is used
     *
     * @param   mixed  $colors       Array of colors
     * @param   mixed  $id           Array of colors
     * @access private
     */
    function _convertString2RGB($scolor)
    {
        if (preg_match(IMAGE_TEXT_REGEX_HTMLCOLOR, $scolor, $matches)) {
            return array(
                           'r' => hexdec($matches[2]),
                           'g' => hexdec($matches[3]),
                           'b' => hexdec($matches[4]),
                           'a' => hexdec(!empty($matches[1])?$matches[1]:0),
                           );
        }
        return false;
    }

    /**
     * Initialiaze the datas
     *
     * This method must be called before Image_Text::render()
     *
     * @access public
     */
    function init()
    {
        if (!is_file($this->options['fontPath'].$this->options['fontFile'])) {
            PEAR::raiseError('Font not found');
        } else {
            $this->_font = $this->options['fontPath'].$this->options['fontFile'];
        }

        if ($this->options['width'] < 1) {
            return false;
        }

        if(empty($this->options['canvas'])) {
            $this->_img = imagecreatetruecolor(
                        $this->options['width'], $this->options['width']);
            if (!$this->_img) {
                return PEAR::raiseError('Could not create image');
            }
        } elseif ( is_resource($this->options['canvas']) &&
            get_resource_type($this->options['canvas'])=='gd'
        ) {
            $this->_img = $this->options['canvas'];
        } elseif (is_array($this->options['canvas'])) {
            $this->_img = imagecreatetruecolor(
                        $this->options['canvas']['width'],
                        $this->options['canvas']['height']
                    );
            if (!$this->_img) {
                return PEAR::raiseError('Could not create image');
            }
        } elseif ($this->options['canvas']=='auto') {
            $this->_mode = 'auto';
        }

        $angle = $this->options['angle'];
        while($angle < 0) {
            $angle += 360;
        }
        if($angle > 359){
            $angle = $angle % 360;
        }
        $this->options['angle'] = $angle;
        $this->setColors($this->options['color']);
        $this->_init = true;
        return true;
    }


    /**
     * Extract the tokens from the text.
     *
     * @access private
     */
    function _processText()
    {
        if (empty($this->text)) {
            return false;
        }
        $this->_tokens = array();
        $this->text = preg_replace("[\r\n]", "\n", $this->text);
        $lines = explode("\n",$this->text);
        foreach($lines as $line) {
            $words = explode(' ',$line);
            foreach($words as $word) {
                $this->_tokens[] = empty($word)?"\n":$word;
            }
            $this->_tokens[] = "\n";
        }
        unset($this->_tokens[sizeof($this->_tokens)-1]);
    }

    function autoMeasurize ( $start = false, $end = false) {
        if (!$this->_init) {
            return PEAR::raiseError('Not initialized');
        }
        $start = (empty($start)) ? $this->options['minFontSize'] : $start;
        $end = (empty($end)) ? $this->options['maxFontSize'] : $end;
        $res = false;
        for ($i = $start; $i <= $end; $i++) {
            $this->options['fontSize'] = $i;
            $res = $this->measurize();
            if ($res === false) {
                if ($start == $i) {
                    $this->options['fontSize'] = -1;
                    return PEAR::raiseError("No possible font size found");
                }
                $this->options['fontSize'] -= 1;
                break;
            }
        }
        return $this->options['fontSize'];
    }

    function measurize ( ) {
        if(!$this->_init) {
            return false;
        }

        $this->_processText();

        $font = $this->_font;
        $size = $this->options['fontSize'];

        if($size<2) {
            return false;
        }

        $linespacing = $this->options['linespacing'];

        $max_lines = (int)$this->options['maxLines'];
        if ($max_lines<1) {
            return false;
        }

        $block_width = $this->options['width'];
        $block_height = $this->options['height'];

        $colors_cnt = sizeof($this->colors);
        $c = $this->colors[0];

        $text_line = '';
        $lines_cnt = 0;
        $tokens_cnt = sizeof($this->_tokens);
        $lines = array();
        $lineHeight = 0;
        $sizes = array();

        $i = 0;
        $para_cnt = 1;
       
        foreach($this->_tokens as $token) {
            if ($token=="\n") {
                // New paragraph
                $bounds = imagettfbbox($size, 0, $font, $text_line);
                if (++$lines_cnt>=$max_lines) {
                    echo "TO MANY LINES!";
                    return false;
                }
                $lines[]  = array(
                                'string'        => $text_line,
                                'width'         => $bounds[2]-$bounds[0],
                                'height'        => $bounds[1]-$bounds[7],
                                'bottom_margin' => $bounds[1],
                                'left_margin'   => $bounds[0],
                                'color'         => $c
                            );
                $lineHeight += (int)(($bounds[1]-$bounds[7]) + $bounds[1]);
                if ($lineHeight > $block_height) {
                    return false;
                }
                $text_line = '';
                if ($this->colorMode=='paragraph') {
                    $c = $this->colors[$para_cnt%$colors_cnt];
                    $t = $para_cnt++%$colors_cnt;
                }
                $i++;
                continue;
            }

            $bounds = imagettfbbox($size, 0, $font,
                    $text_line.(!empty($text_line)?' ':'').$token);
            $prev_width = $i>0?$width:0;
            $width = $bounds[2]-$bounds[0];
            if ($width>$block_width) {
                // New Line
                if (++$lines_cnt>=$max_lines) {
                    return false;
                }
                if ($this->colorMode=='line') {
                    $c = $this->colors[$i%$colors_cnt];
                    $t = $para_cnt++%$colors_cnt;
                    //echo "$t-$para_cnt-$text_line<br>";
                }
                $lines[]  = array(
                                'string'    => $text_line,
                                'width'     => $prev_width,
                                'height'    => $bounds[1]-$bounds[7],
                                'bottom_margin' => $bounds[1],
                                'left_margin'   => $bounds[0],
                                'color'         => $c
                            );
                $lineHeight += (int)(($bounds[1]-$bounds[7]) + $bounds[1]);
                if ($lineHeight > $block_height) {
                    return false;
                }
                $text_line = $token;
            } else {
                $text_line .= ($text_line!=''?' ':'').$token;
            }
            $i++;
        }
        // store reminding line
        $bounds = imagettfbbox($size, 0, $font,$text_line);
        $lines[]  = array(
                        'string'=> $text_line,
                        'width' => $bounds[2]-$bounds[0],
                        'height'=> $bounds[1]-$bounds[7],
                        'bottom_margin' => $bounds[1],
                        'left_margin'   => $bounds[0],
                        'color'         => $c
                    );
        return $lines;
        
    }
    
    /**
     * Render the text in the canvas using the given options.
     *
     * @access public
     */
     
    function render( )
    {
        $this->_processText();
        
        $lines = $this->measurize();
        if (!$lines || !$this->_init) {
            return false;
        }
        
        $block_width = $this->options['width'];
        $block_height = $this->options['height'];
        
        $max_lines = $this->options['maxLines'];
        
        $angle = $this->options['angle'];
        $radians = deg2rad($angle);
        
        $font = $this->_font;
        $size = (int)$this->options['fontSize'];
        
        $linespacing = $this->options['linespacing'];
        
        $align = $this->options['align'];
        
        $im = $this->_img;

        $new_posx = $this->options['x'];
        $new_posy = $this->options['y'];

        $start_x = $this->options['x'];
        $start_y = $this->options['y'];
        $end_x = $start_x + $block_width;
        $end_y = $start_y + $block_height;

        $lines_cnt = min($max_lines,sizeof($lines));

        $sinR = sin($radians);
        $cosR = cos($radians);

        for($i=0; $i<$lines_cnt; $i++){
            /*
             * Calc the new start X and Y (only for line>0)
             * the distance between the line above is used
             */
            if($i>0){
                $space = $linespacing * $size*2;
                $new_posx += $sinR * $space;
                $new_posy += $cosR * $space;
            }

            /*
             * Calc the position of the 1st letter. We can then get the left and bottom margins
             * 'i' is really not the same than 'j' or 'g'
             */
            $bottom_margin  = $lines[$i]['bottom_margin'];
            $left_margin    = $lines[$i]['left_margin'];
            $line_width     = $lines[$i]['width'];

            /*
             * Calc the position using the block width, the current line width and obviously
             * the angle. That gives us the offset to slide the line
             */
            switch($align) {
                case IMAGE_TEXT_ALIGN_LEFT:
                    $hyp = -1;
                    break;
                case IMAGE_TEXT_ALIGN_RIGHT:
                    $hyp = $block_width - $line_width - $left_margin -2;
                    break;
                case IMAGE_TEXT_ALIGN_CENTER:
                    $hyp = ($block_width-$line_width)/2 - $left_margin -2;
                    break;
                case IMAGE_TEXT_ALIGN_JUSTIFY:
                    break;
            }

            $posx = $new_posx + $cosR * $hyp;
            $posy = $new_posy - $sinR * $hyp;

            /*
             * Adjust the positions using the margins processed above
             */
            $posx -= sin($radians) * $bottom_margin;
            $posy -= cos($radians) * $bottom_margin;

            $c = $lines[$i]['color'];

            $bboxes[] = imagettftext ($im, $size, $angle, $posx, $posy, $c, $font, $lines[$i]['string']);
        }
        $this->bbox = $bboxes;
    }

    /**
     * Return the image ressource
     *
     * @access public
     */
    function getImg()
    {
        return $this->_img;
    }

    /**
     * Print out the images using the given image format
     *
     * @param   boolean  $save  Save or not the image on printout
     * @param   boolean  $free  Free the image on exit
     * @access public
     */
    function display($save=false, $free=false)
    {
        if (!headers_sent()) {
            header("Content-type: " .image_type_to_mime_type($this->options['imageType']));
        } else {
            PEAR::raiseError('header already sent');
        }
        switch ($this->options['imageType']) {
            case IMAGETYPE_PNG:
                $imgout = 'imagepng';
                break;
            case IMAGETYPE_JPEG:
                $imgout = 'imagejpeg';
                break;
            case IMAGETYPE_BMP:
                $imgout = 'imagebmp';
                break;
            default:
                return PEAR::raiseError('unsupported image type');
                break;
        }
        if ($save) {
            $imgout($this->_img);
            $imgout($this->_img,$this->options['destFile']);
        } else {
           $imgout($this->_img);
        }
        if ($free) {
            imagedestroy($this->image);
        }
    }
}

?>
