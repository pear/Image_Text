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
require_once 'Image/Text.php';
require_once dirname(__FILE__) . '/imageisthesame.php';
/**
 * Class for testing alignment of the text
 *
 * @category Text
 * @package  Text_CAPTCHA
 * @author   Michael Cramer <michael@bigmichi1.de>
 * @license  BSD License
 * @link     http://pear.php.net/package/Text_CAPTCHA
 */
class Alignment_Test extends PHPUnit_Framework_TestCase
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
        $this->_dir = dirname(__FILE__) . '/testimages/align/';
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
                'color' => array('#000000'),
                'background_color' => '#FFFFFF'
            )
        );
        return $i;
    }

    /**
     * test left alignment of the text
     *
     * @return void
     */
    public function testLeft()
    {
        $i = $this->initInstance(
            "test\n" .
            "test test\n" .
            "test test test\n" .
            "test test test test\n" .
            "test test test test test"
        );
        $this->assertInstanceOf('Image_Text', $i);
        $i->set('halign', Image_Text::IMAGE_TEXT_ALIGN_LEFT);
        $i->init()->render();
        $this->assertTrue(
            imageisthesame(
                $this->_dir . 'left.png',
                $i->getImg()
            )
        );
    }

    /**
     * test right alignment of the text
     *
     * @return void
     */
    public function testRight()
    {
        $i = $this->initInstance(
            "test\n" .
            "test test\n" .
            "test test test\n" .
            "test test test test\n" .
            "test test test test test"
        );
        $this->assertInstanceOf('Image_Text', $i);
        $i->set('halign', Image_Text::IMAGE_TEXT_ALIGN_RIGHT);
        $i->init()->render();
        $this->assertTrue(
            imageisthesame(
                $this->_dir . 'right.png',
                $i->getImg()
            )
        );
    }

    /**
     * test center alignment of the text
     *
     * @return void
     */
    public function testCenter()
    {
        $i = $this->initInstance(
            "test\n" .
            "test test\n" .
            "test test test\n" .
            "test test test test\n" .
            "test test test test test"
        );
        $this->assertInstanceOf('Image_Text', $i);
        $i->set('halign', Image_Text::IMAGE_TEXT_ALIGN_CENTER);
        $i->init()->render();
        $this->assertTrue(
            imageisthesame(
                $this->_dir . 'center.png',
                $i->getImg()
            )
        );
    }

    /**
     * test justify alignment of the text
     *
     * @return void
     */
    public function testJustify()
    {
        $i = $this->initInstance(
            "test\n" .
            "test test\n" .
            "test test test\n" .
            "test test test test\n" .
            "test test test test test"
        );
        $this->assertInstanceOf('Image_Text', $i);
        $i->set('halign', Image_Text::IMAGE_TEXT_ALIGN_JUSTIFY);
        $i->init()->render();
        $this->assertTrue(
            imageisthesame(
                $this->_dir . 'justify.png',
                $i->getImg()
            )
        );
    }
}