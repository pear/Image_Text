<?php

    require_once 'PEAR.php';

define("IMAGE_TEXT_REGEX_HTMLCOLOR", "/^[#|]([a-fA-F0-9]{2})?([a-fA-F0-9]{2})([a-fA-F0-9]{2})([a-fA-F0-9]{2})$/", true);
define("IMAGE_TEXT_ALIGN_LEFT", "left", true);
define("IMAGE_TEXT_ALIGN_RIGHT", "right", true);
define("IMAGE_TEXT_ALIGN_CENTER", "center", true);
define("IMAGE_TEXT_ALIGN_JUSTIFY", "justify", true);

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
     * - font_path: Path to your fonts
     * - font_file: Font filename to use
     * - width: width of the text block
     * - height: height of the text block
     * Optional options:
     * - font_size: font_size to use for rendering
     * - color: Colorcode (default white) see {@link Image_Text::colorize()}
     * - bgcolor: Background color of the block
     * - align: alignement (one of the alignement constants)
     * - min_font_size: min font size see {@link Image_Text::colorize()}
     * - max_font_size: max font size see {@link Image_Text::colorize()}
     * - max_lines: Maximum amount of lines
     * - border_width: Border width
     * - border_color: Border color
     * - borderRoundWith: Round the border edges
     * - border_spacing: Space between border and texts, use negative values
     *                  for inner border
     * - image_type: Type of image to output/store (see php image_type constanst)
     * - dest_file: File path see (@link Image_Text::display())
     *
     * @access public
     * @var array
     */
    var $options = array(
            // orientation
            'x'                 => 0,
            'y'                 => 0,
                // maybe youi better like center coordinates
                // instead of usual top left corner
            'cx'         => false,
            'cy'         => false,
            
            // surface
                // canvas = image resource or array with 'width' and 'height' as keys
            'canvas'            => null,
                // swith on/off antialiasing
            'antialias'         => true,
            
            // text clipping
                // width and height determine the text clipping
                // leaving this as is will cause to make the text clipping same size with the
                // canvas
            'width'             => 0,
            'height'            => 0,
                // text alignement inside the clipping
            'halign'             => IMAGE_TEXT_ALIGN_LEFT,
            //'valign'             => IMAGE_TEXT_ALIGN_TOP,
                // angle to rotate the text clipping
            'angle'             => 0,
                // color values can either be hex translated strings or array's of rgb + alpha
                // you can define either 1 color value or an array of color values
                // to be rotated as defined by 'color_mode'
            'color'             => array(
                                    'r' => 255, 'g' => 255, 'b' => 255, 'a'=>0
                                   ),
            'bgcolor'           => false,
                // define the color rotation mode (either per line or per paragraph) using this two 
                // strings
            'color_mode'        => 'line',
            
            // font settings
            'font_path'         => "./",
            'font_file'         => "",
            'font_size'         => 1,
            'line_spacing'      => 1,

            // automasurizing settings
                // auto masurize enables you to make a given text fit into a given clipping
                // with the best fitting font size (or in other ways: it determines the greatest
                // possible font size a text can have in a given clipping
                
                // set the minimal and maximal value to test the measure
            'min_font_size'     => 1,
            'max_font_size'     => 100,
            
            // border
                // image text enables you to set a border
                // 
            'border_width'      => 0,
            'border_color'      => false,
            'border_round_width' => 0,
            'border_spacing'    => 0,
            'image_type'        => IMAGETYPE_PNG,
            'dest_file'         => ''
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
            $this->options['halign'] = $mode;
        } else {
            return PEAR::raiseError('Invalid halignment');
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
                return PEAR::raiseError('Invalid color');
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
    
    function getOffset ( ) {
        $width = $this->options['width'];
        $height = $this->options['height'];
        $angle = $this->options['angle'];
        $x = $this->options['x'];
        $y = $this->options['y'];
        if (!empty($this->options['cx']) && !empty($this->options['cy'])) {
            $cx = $this->options['cx'];
            $cy = $this->options['cy'];
            $x = $cx - ($width / 2);
            $y = $cy - ($height / 2);
            if ($angle) {
                $ang = deg2rad($angle);
                // vektor from the top left cornern ponting to the middle point
                $vA = array( ($cx - $x), ($cy - $y) );
                // var_dump($vA);
                // matrix to rotate vektor
                $mRot = array( 
                    round(cos($ang), 14),   round((sin($ang) * -1), 10),
                    round(sin($ang), 14),   round(cos($ang), 10)
                );
                // var_dump($mRot);
                // multiply vektor with matrix to get the rotated vector
                // this results in the location of the center point after rotation
                $vB = array ( 
                    ($mRot[0] * $vA[0] + $mRot[2] * $vA[0]),
                    ($mRot[1] * $vA[1] + $mRot[3] * $vA[1])
                );
                // var_dump($vB);
                // to get the movement vector, we subtract the original middle 
                $vC = array (
                    ($vA[0] - $vB[0]),
                    ($vA[1] - $vB[1])
                );
                // var_dump($vC);
                // finally we move the top left corner coords there
                $x += $vC[0];
                $y += $vC[1];
            }
        }
        return array ('x' => $x, 'y' => $y);
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
        if (!is_file($this->options['font_path'].$this->options['font_file'])) {
            return PEAR::raiseError('Font not found');
        } else {
            $this->_font = $this->options['font_path'].$this->options['font_file'];
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
        
        $this->options['canvas']['height'] = imagesx($this->_img);
        $this->options['canvas']['width'] = imagesy($this->_img);

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
        $start = (empty($start)) ? $this->options['min_font_size'] : $start;
        $end = (empty($end)) ? $this->options['max_font_size'] : $end;
        $res = false;
        for ($i = $start; $i <= $end; $i++) {
            $this->options['font_size'] = $i;
            $res = $this->measurize();
            if ($res === false) {
                if ($start == $i) {
                    $this->options['font_size'] = -1;
                    return PEAR::raiseError("No possible font size found");
                }
                $this->options['font_size'] -= 1;
                break;
            }
        }
        return $this->options['font_size'];
    }

    function measurize ( $force = false ) {
        if(!$this->_init) {
            return false;
        }

        $this->_processText();

        $font = $this->_font;
        $size = $this->options['font_size'];

        if(($size<2) && !$force) {
            return false;
        }

        $line_spacing = $this->options['line_spacing'];

        $max_lines = (int)$this->options['max_lines'];
        if (($max_lines<1) && !$force) {
            return false;
        }

        $block_width = $this->options['width'];
        $block_height = $this->options['height'];

        $colors_cnt = sizeof($this->colors);
        $c = $this->colors[0];

        $space = $this->options['line_spacing'] * $this->options['font_size'];
        
        $text_line = '';
        $lines_cnt = 0;
        $tokens_cnt = sizeof($this->_tokens);
        $lines = array();
        $lineHeight = $space;
        $sizes = array();

        $i = 0;
        $para_cnt = 1;
       
        foreach($this->_tokens as $token) {
            if ($token=="\n") {
                // New paragraph
                $bounds = imagettfbbox($size, 0, $font, $text_line);
                if ((++$lines_cnt>=$max_lines) && !$force) {
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
                if ((int)($bounds[1]-$bounds[7]) == 0) {
                    $lineHeight += ($lineHeight / $lines_cnt);
                } else {
                    $lineHeight += (int)(($bounds[1]-$bounds[7]) + $space);
                }
                if (($lineHeight >= $block_height) && !$force) {
                    return false;
                }
                $text_line = '';
                if ($this->options['color_mode']=='paragraph') {
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
                if ((++$lines_cnt>=$max_lines) && !$force) {
                    return false;
                }
                if ($this->options['color_mode']=='line') {
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
                $lineHeight += (int)(($bounds[1]-$bounds[7]) + $space);
                if (($lineHeight >= $block_height) && !$force) {
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
        $lineHeight += (int)(($bounds[1]-$bounds[7]) + $space);
        if (($lineHeight >= $block_height) && !$force) {
            return false;
        }
                    
        return $lines;
        
    }
    
    /**
     * Render the text in the canvas using the given options.
     *
     * @access public
     */
     
    function render( $force = false )
    {
        $this->_processText();
        
        $lines = $this->measurize( $force );
        if (!$lines || !$this->_init) {
            return false;
        }
        
        $block_width = $this->options['width'];
        $block_height = $this->options['height'];
        
        $max_lines = $this->options['max_lines'];
        
        $angle = $this->options['angle'];
        $radians = round(deg2rad($angle), 3);
        
        $font = $this->_font;
        $size = (int)$this->options['font_size'];
        
        $line_spacing = $this->options['line_spacing'];
        
        $align = $this->options['halign'];
        
        $im = $this->_img;
        
        $cosX = cos(deg2rad($this->options['angle']));
        $sinX = sin(deg2rad($this->options['angle']));

        $offset = $this->getOffset();
        
        // var_dump($offset);
        
        $start_x = $offset['x'];
        $start_y = $offset['y'];
        $end_x = $start_x + $block_width;
        $end_y = $start_y + $block_height;
        
        $new_posx = $start_x;
        $new_posy = $start_y;
        
        $lines_cnt = min($max_lines,sizeof($lines));

        $sinR = sin($radians);
        $cosR = cos($radians);

        for($i=0; $i<$lines_cnt; $i++){
            /*
             * Calc the new start X and Y (only for line>0)
             * the distance between the line above is used
             */
            // if($i>0){
                $space = $line_spacing * $size*2;
                $new_posx += $sinR * $space;
                $new_posy += $cosR * $space;
            // }

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
                // case IMAGE_TEXT_ALIGN_JUSTIFY:
                //    break;
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
            header("Content-type: " .image_type_to_mime_type($this->options['image_type']));
        } else {
            PEAR::raiseError('header already sent');
        }
        switch ($this->options['image_type']) {
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
            $imgout($this->_img,$this->options['dest_file']);
        } else {
           $imgout($this->_img);
        }
        if ($free) {
            imagedestroy($this->image);
        }
    }
}

?>
