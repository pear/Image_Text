<?php
/**
 * Tests for Image_Text
 *
 * PHP version 5
 *
 * @category Image
 * @package  Image_Text
 * @author   Michael Cramer <michael@bigmichi1.de>
 * @license  BSD License
 * @link     http://pear.php.net/package/Text_CAPTCHA
 */
require_once dirname(__FILE__) . '/imageisthesame.php';
/**
 * Class for testing colorization of the text
 *
 * @category Text
 * @package  Text_CAPTCHA
 * @author   Michael Cramer <michael@bigmichi1.de>
 * @license  BSD License
 * @link     http://pear.php.net/package/Text_CAPTCHA
 */
class Colorize_Test extends PHPUnit_Framework_TestCase
{
    /**
     * directory with images for comparison
     *
     * @var
     */
    private $_dir;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp()
    {
        if (!extension_loaded('gd')) {
            $this->markTestSkipped("Requires the gd extension");
        }
        $this->_dir = dirname(__FILE__) . '/testimages/color/';
    }

    /**
     * initialize the testee
     *
     * @param string $text Text for Image
     *
     * @return Image_Text testee
     */
    protected function initInstance($text)
    {
        $i = new Image_Text($text);
        $i->set(
            array(
                'font_path' => dirname(__FILE__) . '/data/',
                'font_file' => 'Vera.ttf',
                'font_size' => 20,
                'canvas' => array('width' => 400, 'height' => 200),
                'width' => 400,
                'height' => 400,
                'background_color' => '#FFFFFF',
                'color' => array('#00FF00', '#FF0000', '#0000FF'),
                'shadow_offset' => 1,
                'shadow_color' => '#000000'
            )
        );
        return $i;
    }

    /**
     * test word colorization
     *
     * @return void
     */
    public function testWord()
    {
        $i = $this->initInstance(
            "one\none two three four five six seven eight nine ten\none two"
        );
        $this->assertSame('Image_Text', get_class($i));
        $i->set('color_mode', Image_Text_Colormode::WORD);
        $i->init()->render();$i->save($this->_dir . 'word.png');
        $this->assertTrue(
            imageisthesame(
                $this->_dir . 'word.png',
                $i->getImg()
            )
        );
    }

    /**
     * test line colorization
     *
     * @return void
     */
    public function testLine()
    {
        $i = $this->initInstance(
            "one\none two three four five six seven eight nine ten\none two"
        );
        $this->assertSame('Image_Text', get_class($i));
        $i->set('color_mode', Image_Text_Colormode::LINE);
        $i->init()->render();$i->save($this->_dir . 'line.png');
        $this->assertTrue(
            imageisthesame(
                $this->_dir . 'line.png',
                $i->getImg()
            )
        );
    }

    /**
     * test paragraph colorization
     *
     * @return void
     */
    public function testParagraph()
    {
        $i = $this->initInstance(
            "one\none two three four five six seven eight nine ten\none two"
        );
        $this->assertSame('Image_Text', get_class($i));
        $i->set('color_mode', Image_Text_Colormode::PARAGRAPH);
        $i->init()->render();$i->save($this->_dir . 'paragraph.png');
        $this->assertTrue(
            imageisthesame(
                $this->_dir . 'paragraph.png',
                $i->getImg()
            )
        );
    }
}
