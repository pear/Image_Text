<?php

/**
 * Image_Text - Advanced text maipulations
 * 
 * Image_Text provides advanced text manipulation facilities for GD2
 * image generation with PHP. Simply add text clippings to your images,
 * let the class automatically determine lines, rotate text boxes around
 * their center or top left corner. These are only a couple of features
 * Image_Text provides. 
 * @package Image_Text
 * @license The PHP License, version 3.0
 * @author Tobias Schlitt <toby@php.net>
 * @category images
 */


require_once 'PEAR.php';

// Regex to match HTML style hex triples    
define("IMAGE_TEXT_REGEX_HTMLCOLOR", "/^[#|]([a-f0-9]{2})?([a-f0-9]{2})([a-f0-9]{2})([a-f0-9]{2})$/i", true);

// Alignment settings for vertical alignement

define("IMAGE_TEXT_ALIGN_LEFT", "left", true);
define("IMAGE_TEXT_ALIGN_RIGHT", "right", true);
define("IMAGE_TEXT_ALIGN_CENTER", "center", true);

// Alignment settings for horizontal alignement
define("IMAGE_TEXT_ALIGN_TOP", "top", true);
define("IMAGE_TEXT_ALIGN_MIDDLE", "middle", true);
define("IMAGE_TEXT_ALIGN_BOTTOM", "bottom", true);

// Not implemented yet
/**
 * @todo This constant is useless until now, since justified alignment does not work yet
 */
define("IMAGE_TEXT_ALIGN_JUSTIFY", "justify", true);


/**
 * Image_Text - Advanced text maipulations
 * 
 * Image_Text provides advanced text manipulation facilities for GD2
 * image generation with PHP. Simply add text clippings to your images,
 * let the class automatically determine lines, rotate text boxes around
 * their center or top left corner. These are only a couple of features
 * Image_Text provides. 
 */

class Image_Text {

    /**
     * Options array. these options can be set through the constructor or the set() method.
     *
     * Possible options to set are:
     * <pre>
     *
     *      'x'                 | This sets the top left coordinates (using x/y) or the center point
     *      'y'                 | coordinates (using cx/cy) for your text box. The values from
     *      'cx'                | cx/cy will overwrite x/y.
     *      'cy'                |
     *
     *      'canvas'            | You can set different values as a canvas:
     *                          |   - A gd image resource
     *                          |   - An array with 'width' and 'height'
     *                          |   - Nothing (the canvas will be measured after the real text size)
     *
     *      'antialias'         | This is usually true. Set it to false to switch antialiasing off.
     *
     *      'width'             | The width and height for your text box-
     *      'height'            |
     *
     *      'halign'            | Alignment of your text inside the textbox. Use alignmnet constants to define    
     *      'valign'            | vertical and horizontal alignment.
     *
     *      'angle'             | The angle to rotate your text box.
     *
     *      'color'             | An array of color values. Colors will be rotated in the mode you choose (linewise 
     *                          | or paragraphwise). Can be in the following formats:
     *                          |   - String representing HTML style hex couples (+ unusual alpha couple in the first place, optional)
     *                          |   - Array of int values using 'r', 'g', 'b' and optionally 'a' as keys
     *
     *      'color_mode'        | The color rotation mode for your color sets. Does only apply if you
     *                          | defined multiple colors. Use 'line' or 'paragraph'.
     *
     *      'font_path'         | Location of the font to use.
     *      'font_file'         |
     *
     *      'font_size'         | The font size to render text in (will be overwriten, if you use automeasurize).
     *
     *      'line_spacing'      | Measure for the line spacing to use. Default is 1.
     *
     *      'min_font_size'     | Automeasurize settings. Try to keep this area as small as possible to get better
     *      'max_font_size'     | performance.
     *
     *      'image_type'        | The type of image (use image type constants). Is default set to PNG.
     *
     *      'dest_file'         | The destination to (optionally) save your file.
     * </pre>
     *
     * @access public
     * @var array
     * @see Image_Text::Image_Text(), Image_Text::set()
     */
     
    var $options = array(
            // orientation
            'x'                 => 0,
            'y'                 => 0,
            
            // surface
            'canvas'            => null,
            'antialias'         => true,
            
            // text clipping
            'width'             => 0,
            'height'            => 0,

            // text alignement inside the clipping
            'halign'             => IMAGE_TEXT_ALIGN_LEFT,
            'valign'             => IMAGE_TEXT_ALIGN_TOP,
            
            // angle to rotate the text clipping
            'angle'             => 0,
            
            // color settings
            'color'             => array(
                                    'r' => 0, 'g' => 0, 'b' => 0, 'a'=>0
                                   ),

            'color_mode'        => 'line',
            
            // font settings
            'font_path'         => "./",
            'font_file'         => null,
            'font_size'         => 2,
            'line_spacing'      => 1,

            // automasurizing settings
            'min_font_size'     => 1,
            'max_font_size'     => 100,
            
            // misc settings
            'image_type'        => IMAGETYPE_PNG,
            'dest_file'         => ''
        );
    
    /**
     * The text you want to render.
     *
     * @access private
     * @var string
     */
        
    var $_text;
        
    /**
     * Resource ID of the image canvas
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
     * @access private
     * @var array
     */
     
    var $bbox = array();

    /**
     * Defines in which mode the canvas has be set
     *
     * @access private
     * @var array
     */
     
    var $_mode = '';    

    /**
     * Color indeces returned by imagecolorallocatealpha
     *
     * @access private
     * @var array
     */
     
    var $_colors = array();
    
    /**
     * Width and height of the (rendered) text
     *
     * @access private
     * @var array
     */
    
    var $_realTextSize = array('width' => false, 'height' => false);

    /**
     * Measurized lines
     *
     * @access private
     * @var array
     */
    
    var $_lines = false;
    
    /**
     * Fontsize for which the last measuring process was done
     *
     * @access private
     * @var array
     */
    
    var $_measurizedSize = false;
       
    /**
     * Is the text object initialized?
     *
     * @access private
     * @var bool
     */
    
    var $_init = false;

    /**
     * Constructor
     *
     * Set the text and options
     *
     * @param   string  $text       Text to print
     * @param   array   $options    Options
     * @access public
     * @see Image_Text::$options, Image_Text::set()
     */
     
    function Image_Text($text, $options)
    {
        $this->_text = $text;
        $this->options = array_merge($this->options, $options);
    }
    
    /**
     * Set options
     *
     * Set a single or multiple options. It may happen that you have to reinitialize the Image_Text
     * object after changing options.
     *
     * @param   mixed   $option     A single option name or the options array
     * @param   mixed   $value      Option value if $option is string
     * @return  bool                True on success, otherwise PEAR::Error
     * @access public
     * @see Image_Text::Image_Text(), Image_Text::$options
     */
    
    function set ( $option, $value = null ) {
        $reInits = array_flip($this->_reInits);
        if (!is_array($option)) {
            if (!isset($value)) {
                return PEAR::raiseError('No value given.');
            }
            $option = array($option => $value);
        }
        foreach ($option as $opt => $val) {
            if ($opt == 'color') {
                $this->setColors($val);
            } else {
                $this->options[$opt] = $val;
            }
            if (isset($reInits[$opt])) {
                $this->_init = false;
            }
        }
        return true;
    }
    
    /**
     * Set the color-set
     *
     * Using this method you can set one or more colors for your text.
     * If you set multiple colors, use a simple numeric array to determine
     * their order and give it to this function. Multiple colors will be 
     * cycled by the options specified 'color_mode' option.
     *
     * The following colors syntaxes are understood by this method:
     * - "#ffff00" hexadecimal format (HTML style), with and without #
     * - "#08ffff00" hexadecimal format (HTML style) with alpha channel (08), with and without #
     * - array with 'r','g','b' and (optionally) 'a' keys, using int values
     * - a GD color special color (tiled,...)
     *
     * A single color or an array of colors are allowed here.
     *
     * @param   mixed  $colors       Single color or array of colors
     * @return  bool                 True on success, otherwise PEAR::Error
     * @access  public
     * @see Image_Text::setColor(), Image_Text::$options
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
        return true;
    }

    /**
     * Set a color
     *
     * This method is used to set a color at a specific color ID inside the
     * color cycle.
     *
     * The following colors syntaxes are understood by this method:
     * - "#ffff00" hexadecimal format (HTML style), with and without #
     * - "#08ffff00" hexadecimal format (HTML style) with alpha channel (08), with and without #
     * - array with 'r','g','b' and (optionally) 'a' keys, using int values
     * - a GD color special color (tiled,...)
     *
     * @param   mixed    $color        Color value
     * @param   mixed    $id           ID (in the color array) to set color to
     * @return  bool                True on success, otherwise PEAR::Error
     * @access  public
     * @see Image_Text::setColors(), Image_Text::$options
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
        return true;
    }
        
    /**
     * Initialiaze the Image_Text object
     *
     * This method has to be called after setting the options for your Image_Text object.
     * It initializes the canvas, normalizes some data and checks important options.
     * Be shure to check the initialization after you switched some options. The
     * {@see Image_Text::set()} method may force you to reinitialize the object.
     *
     * @access  public
     * @return  bool  True on success, otherwise PEAR::Error
     */
     
    function init()
    {
        // Does the fontfile exist and is readable?
        if (!is_file($this->options['font_path'].$this->options['font_file']) || !is_readable($this->options['font_path'].$this->options['font_file'])) {
            return PEAR::raiseError('Fontfile not found or not readable.');
        } else {
            $this->_font = $this->options['font_path'].$this->options['font_file'];
        }
        
        // Is the font size to small?
        if ($this->options['width'] < 1) {
            return PEAR::raiseError('Font size to small. Has to be > 1.');
        }
    
        // Check and create canvas
        if(empty($this->options['canvas'])) {
            // Create new image from width && height of the clipping
            $this->_img = imagecreatetruecolor(
                        $this->options['width'], $this->options['width']);
            if (!$this->_img) {
                return PEAR::raiseError('Could not create image canvas.');
            }
        } elseif ( is_resource($this->options['canvas']) &&
            get_resource_type($this->options['canvas'])=='gd'
        ) {
            // The canvas is an image resource
            $this->_img = $this->options['canvas'];
        } elseif (is_array($this->options['canvas'])) {
            // Canvas must be a width and height measure
            $this->_img = imagecreatetruecolor(
                        $this->options['canvas']['width'],
                        $this->options['canvas']['height']
                    );
            if (!$this->_img) {
                return PEAR::raiseError('Could not create image cabvas.');
            }
        } elseif ($this->options['canvas']=='auto') {
            $this->_mode = 'auto';
        }
        
        $this->options['canvas']['height'] = imagesx($this->_img);
        $this->options['canvas']['width'] = imagesy($this->_img);
        
        // Save and repair angle
        $angle = $this->options['angle'];
        while($angle < 0) {
            $angle += 360;
        }
        if($angle > 359){
            $angle = $angle % 360;
        }
        $this->options['angle'] = $angle;
        
        // Set the color values
        $this->setColors($this->options['color']);
        if (PEAR::isError($this->options['color'])) {
            return $this->options['color'];
        }
        
        // Initialization is complete
        $this->_init = true;
        return true;
    }
    
    /**
     * Auto measurize text
     *
     * Automatically determines the greatest possible font size to
     * fit the text into the text box. This method may be very resource
     * intensive on your webserver. A good tweaking point are the $start
     * and $end parameters, which specify the range of font sizes to search 
     * through. Anyway, the results should be cached if possible.
     *
     * @access public
     * @param  int      $start  Fontsize to start testing with
     * @param  int      $end    Fontsize to end testing with
     * @return int              Fontsize measured or PEAR::Error
     * @see Image_Text::measurize()
     */
   
    function autoMeasurize ( $start = false, $end = false) {
        if (!$this->_init) {
            return PEAR::raiseError('Not initialized. Call ->init() first!');
        }
        $this->_processText();
        
        $start = (empty($start)) ? $this->options['min_font_size'] : $start;
        $end = (empty($end)) ? $this->options['max_font_size'] : $end;
        
        $res = false;
        // Run through all possible font sizes until a measurize fails
        // Not the optimal way. This can be tweaked!
        for ($i = $start; $i <= $end; $i++) {
            $this->options['font_size'] = $i;
            $res = $this->measurize();
            if ($res === false) {
                if ($start == $i) {
                    $this->options['font_size'] = -1;
                    return PEAR::raiseError("No possible font size found");
                }
                $this->options['font_size'] -= 1;
                $this->_measurizedSize = $this->options['font_size'];
                break;
            }
            // Allways the last couple of lines is stored here.
            $this->_lines = $res;
        }
        return $this->options['font_size'];
    }
    
    /**
     * Measurize text into the text box
     *
     * This method makes your text fit into the defined textbox by measurizing the
     * lines for your given font-size. You can do this manually before rendering (or use
     * even {@see Image_Text::autoMeasurize()}) or the renderer will do measurizing 
     * automatically.
     *
     * @access public
     * @param  bool  $force  Optionally, default is false, set true to force measurizing
     * @return array         Array of measured lines or PEAR::Error
     * @see Image_Text::autoMeasurize()
     */
    
    function measurize ( $force = false ) {
        if (!$this->_init) {
            return PEAR::raiseError('Not initialized. Call ->init() first!');
        }

        // Precaching options
        $font = $this->_font;
        $size = $this->options['font_size'];

        $line_spacing = $this->options['line_spacing'];
        $space = $this->options['line_spacing'] * $this->options['font_size'] * 1.5;
        
        $max_lines = (int)$this->options['max_lines'];

        if (($max_lines<1) && !$force) {
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
        
        $text_height = 0;
        $sizes = array();
        
        $i = 0;
        $para_cnt = 0;
        
        // Run through tokens and order them in lines       
        foreach($this->_tokens as $token) {
            // Handle new paragraphs
            if ($token=="\n") {
                $bounds = imagettfbbox($size, 0, $font, $text_line);
                if ((++$lines_cnt>=$max_lines) && !$force) {
                    return false;
                }
                if ($this->options['color_mode']=='paragraph') {
                    $c = $this->colors[$para_cnt%$colors_cnt];
                    $i++;
                } else {
                    $c = $this->colors[$i++%$colors_cnt];
                }
                $lines[]  = array(
                                'string'        => $text_line,
                                'width'         => $bounds[2]-$bounds[0],
                                'height'        => $bounds[1]-$bounds[7],
                                'bottom_margin' => $bounds[1],
                                'left_margin'   => $bounds[0],
                                'color'         => $c
                            );
                $text_height += (int)$space;
                if (($text_height >= $block_height) && !$force) {
                    return false;
                }
                $para_cnt++;
                $text_line = '';
                continue;
            }

            // Usual lining up
            
            $bounds = imagettfbbox($size, 0, $font,
                    $text_line.(!empty($text_line)?' ':'').$token);
            $prev_width = $i>0?$width:0;
            $width = $bounds[2]-$bounds[0];
            
            // Handling of automatic new lines
            if ($width>$block_width) {
                if ((++$lines_cnt>=$max_lines) && !$force) {
                    return false;
                }
                if ($this->options['color_mode']=='line') {
                    $c = $this->colors[$i++%$colors_cnt];
                } else {
                    $c = $this->colors[$para_cnt%$colors_cnt];
                    $i++;
                }   
                $lines[]  = array(
                                'string'    => $text_line,
                                'width'     => $prev_width,
                                'height'    => $bounds[1]-$bounds[7],
                                'bottom_margin' => $bounds[1],
                                'left_margin'   => $bounds[0],
                                'color'         => $c
                            );
                            
                $text_height += (int)$space;
                if (($text_height >= $block_height) && !$force) {
                    return false;
                }

                $text_line = $token;
            } else {
                $text_line .= ($text_line!=''?' ':'').$token;
            }
        }
        // Store reminding line
        $bounds = imagettfbbox($size, 0, $font,$text_line);
        if ($this->options['color_mode']=='line') {
            $c = $this->colors[$i++%$colors_cnt];
        }
        $lines[]  = array(
                        'string'=> $text_line,
                        'width' => $bounds[2]-$bounds[0],
                        'height'=> $bounds[1]-$bounds[7],
                        'bottom_margin' => $bounds[1],
                        'left_margin'   => $bounds[0],
                        'color'         => $c
                    );
        // If non empty line, add last line height
        if ($text_line !== "") {
            $text_height += (int)$space;
        }
        
        if (($text_height >= $block_height) && !$force) {
            return false;
        }
                
        $this->_realTextSize = array('width' => $this->options['width'], 'height' => $text_height);        
        return $lines;
    }
    
    /**
     * Render the text in the canvas using the given options.
     *
     * This renders the measurized text or automatically measures it first. The $force
     * can be used to switch of measurizing problems (this may cause your text being rendered
     * outside a given text box.
     *
     * @access public
     * @param  bool     $force  Optional, initially false, set true to silence measurize errors
     * @return bool             True on success, otherwise PEAR::Error
     */
     
    function render( $force = false )
    {
        if (!$this->_init) {
            return PEAR::raiseError('Not initialized. Call ->init() first!');
        }
        
        if (!$this->_tokens) {
            $this->_processText();
        }
        
        if (empty($this->_lines) || ($this->_measurizedSize != $this->options['font_size'])) {
            $this->_lines = $this->measurize( $force );
        }
        $lines = $this->_lines;
        
        if (PEAR::isError($this->_lines)) {
            return $this->_lines;
        }
        
        if ($this->_mode === 'auto') {
            $this->_img = imagecreatetruecolor(
                        $this->_realTextSize['width'],
                        $this->_realTextSize['height']
                    );
            if (!$this->_img) {
                return PEAR::raiseError('Could not create image cabvas.');
            }
            $this->setColors($this->_options['color']);
        }
        
        $block_width = $this->options['width'];
        $block_height = $this->options['height'];
        
        $max_lines = $this->options['max_lines'];
        
        $angle = $this->options['angle'];
        $radians = round(deg2rad($angle), 3);
        
        $font = $this->_font;
        $size = $this->options['font_size'];
        
        $line_spacing = $this->options['line_spacing'];
        
        $align = $this->options['halign'];
        
        $im = $this->_img;
        
        $offset = $this->_getOffset();
        
        $start_x = $offset['x'];
        $start_y = $offset['y'];
        
        $end_x = $start_x + $block_width;
        $end_y = $start_y + $block_height;
        
        $sinR = sin($radians);
        $cosR = cos($radians);
        
        switch ($this->options['valign']) {
            case IMAGE_TEXT_ALIGN_TOP:
                $valign_space = 0;
                break;
            case IMAGE_TEXT_ALIGN_MIDDLE:
                $valign_space = ($this->options['height'] - $this->_realTextSize['height']) / 2;
                break;
            case IMAGE_TEXT_ALIGN_BOTTOM:
                $valign_space = $this->options['height'] - $this->_realTextSize['height'];
                break;
            default:
                $valign_space = 0;
        }
        
        $space = $line_spacing * $size * 1.5;
        
        // Adjustment of align + translation of top-left-corner to bottom-left-corner of first line
        $new_posx = $start_x + ($sinR * ($valign_space + $lines[0]['height']));
        $new_posy = $start_y + ($cosR * ($valign_space + $lines[0]['height']));
                
        $lines_cnt = min($max_lines,sizeof($lines));
        
        // Go thorugh lines for rendering
        for($i=0; $i<$lines_cnt; $i++){
            
            // Calc the new start X and Y (only for line>0)
            // the distance between the line above is used
            if($i > 0){
                $new_posx += $sinR * $space;
                $new_posy += $cosR * $space;
            }

            // Calc the position of the 1st letter. We can then get the left and bottom margins
            // 'i' is really not the same than 'j' or 'g'
            $bottom_margin  = $lines[$i]['bottom_margin'];
            $left_margin    = $lines[$i]['left_margin'];
            $line_width     = $lines[$i]['width'];

            // Calc the position using the block width, the current line width and obviously
            // the angle. That gives us the offset to slide the line
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
                default:
                    $hyp = -1;
                    break;
            }

            $posx = $new_posx + $cosR * $hyp;
            $posy = $new_posy - $sinR * $hyp;
            
            $c = $lines[$i]['color'];

            // Render textline
            $bboxes[] = imagettftext ($im, $size, $angle, $posx, $posy, $c, $font, $lines[$i]['string']);
        }
        $this->bbox = $bboxes;
    }

    /**
     * Return the image ressource
     *
     * Get the image canvas
     *
     * @access public
     * @return resource Used image resource
     */
     
    function &getImg()
    {
        return $this->_img;
    }

    /**
     * Display the image (send it to the browser)
     *
     * This will output the image to the users browser. It automatically determines the correct
     * header type and stuff.
     *
     * @param   bool  $save  Save or not the image on printout
     * @param   bool  $free  Free the image on exit
     * @return  bool         True on success, otherwise PEAR::Error
     * @access public
     * @see Image_Text::save()
     */

    function display($save = false, $free = false)
    {
        if (!headers_sent()) {
            header("Content-type: " .image_type_to_mime_type($this->options['image_type']));
        } else {
            PEAR::raiseError('Header already sent.');
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
                return PEAR::raiseError('Unsupported image type.');
                break;
        }
        if ($save) {
            $imgout($this->_img);
            $res = $this->save();
            if (PEAR::isError($res)) {
                return $res;
            }            
        } else {
           $imgout($this->_img);
        }
        
        if ($free) {
            $res = imagedestroy($this->image);
            if (!$res) {
                PEAR::raiseError('Destroying image failed.');
            }
        }
        return true;
    }
    
    /**
     * Save image canvas
     *
     * Saves the image to a given destination. You can leave out the destination file path,
     * if you have the option for that set correctly. Saving is possible with the 
     * {@see Image_Text::display()} method, too.
     *
     * @param   string  $destFile   The destination to save to (optional, uses options value else)
     * @return  bool                True on success, otherwise PEAR::Error
     */
    
    function save ( $destFile = false ) {
        if (!$dest_file) {
            $dest_file = $this->options['dest_file'];
        }
        if (!$dest_file) {
            return PEAR::raiseError("Invalid desitination file.");
        }
        $res = $imgout($this->_img, $this->options['dest_file']);
        if (!$res) {
            PEAR::raiseError('Saving file failed.');
        }
        return true;
    }
    
    /**
     * Get completely translated offset for text rendering
     * Get completely translated offset for text rendering. Important
     * for usage of center coords and angles
     *
     * @access private
     * @return array    Array of x/y coordinates
     */
    
    function _getOffset ( ) {
        // Presaving data
        $width = $this->options['width'];
        $height = $this->options['height'];
        $angle = $this->options['angle'];
        $x = $this->options['x'];
        $y = $this->options['y'];
        // Using center coordinates
        if (!empty($this->options['cx']) && !empty($this->options['cy'])) {
            $cx = $this->options['cx'];
            $cy = $this->options['cy'];
            // Calculation top left corner
            $x = $cx - ($width / 2);
            $y = $cy - ($height / 2);
            // Calculating movement to keep the center point on himslf after rotation
            if ($angle) {
                $ang = deg2rad($angle);
                // Vector from the top left cornern ponting to the middle point
                $vA = array( ($cx - $x), ($cy - $y) );
                // Matrix to rotate vector
                // sinus and cosinus
                $sin = round(sin($ang), 14);
                $cos = round(cos($ang), 14);
                // matrix
                $mRot = array(
                    $cos, (-$sin),
                    $sin, $cos
                );
                // Multiply vector with matrix to get the rotated vector
                // This results in the location of the center point after rotation
                $vB = array ( 
                    ($mRot[0] * $vA[0] + $mRot[2] * $vA[0]),
                    ($mRot[1] * $vA[1] + $mRot[3] * $vA[1])
                );
                // To get the movement vector, we subtract the original middle 
                $vC = array (
                    ($vA[0] - $vB[0]),
                    ($vA[1] - $vB[1])
                );
                // Finally we move the top left corner coords there
                $x += $vC[0];
                $y += $vC[1];
            }
        }
        return array ('x' => $x, 'y' => $y);
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
     * Extract the tokens from the text.
     *
     * @access private
     */
    function _processText()
    {
        if (empty($this->_text)) {
            return false;
        }
        // Normalize linebreak to "\n"
        $this->_text = preg_replace("[\r\n]", "\n", $this->_text);

        // Get each paragraph
        $paras = explode("\n",$this->_text);

        // loop though the paragraphs
        // and get each word (token)
        foreach($paras as $para) {
            $words = explode(' ',$para);
            foreach($words as $word) {
                $this->_tokens[] = empty($word)?"\n":$word;
            }
            // add a "\n" to mark the end of a paragraph
            $this->_tokens[] = "\n";
        }
        // we do not need an end paragraph as the last token
        unset($this->_tokens[sizeof($this->_tokens)-1]);
    }
}

?>