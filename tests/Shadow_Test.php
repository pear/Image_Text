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
 * Class for testing shadowing of the text
 *
 * @category Text
 * @package  Text_CAPTCHA
 * @author   Michael Cramer <michael@bigmichi1.de>
 * @license  BSD License
 * @link     http://pear.php.net/package/Text_CAPTCHA
 */
class Shadow_Test extends PHPUnit_Framework_TestCase
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
        $this->_dir = dirname(__FILE__) . '/testimages/';
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
                'font_size' => 12,
                'canvas' => array('width' => 200, 'height' => 100),
                'width' => 200,
                'height' => 200,
                'color' => array('#FFFFFF')
            )
        );
        return $i;
    }

    /**
     * test shodowing the text
     *
     * @return void
     */
    public function testShadow()
    {
        $i = $this->initInstance("test\ntest");
        $this->assertSame('Image_Text', get_class($i));
        $i->set('background_color', '#FFFFFF');
        $i->set('color', '#00FF00');
        $i->set(array('shadow_offset' => 1, 'shadow_color' => '#000000'));
        $i->init();
        $i->render();
        $i->save("C:\\temp\\test-shadow.png");
        $this->assertTrue(
            imageisthesame(
                $this->_dir . 'test-shadow.png',
                $i->getImg()
            )
        );
    }
}
