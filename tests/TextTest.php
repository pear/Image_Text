<?php
/**
 * Tests for Image_Text
 *
 * PHP version 5
 *
 * @category Image
 * @package  Image_Text
 * @author   Michael Cramer <michael@bigmichi1.de>
 * @license  http://www.php.net/license/3_01.txt PHP License
 * @link     http://pear.php.net/package/Image_Text
 */
require_once 'Image/Text.php';
require_once dirname(__FILE__) . '/helper/imageisthesame.php';

/**
 * Class Image_Text_Test
 *
 * @category Image
 * @package  Image_Text
 * @author   Michael Cramer <michael@bigmichi1.de>
 * @license  http://www.php.net/license/3_01.txt PHP License
 * @link     http://pear.php.net/package/Image_Text
 */
class Image_Text_Test extends PHPUnit_Framework_TestCase
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
        if (!function_exists("createbmp")) {
            require_once dirname(__FILE__) . '/helper/imagebmp.function.php';
        }
        $this->_dir = dirname(__FILE__) . '/testimages/';
    }

    /**
     * initialize the testee
     *
     * @param string $text Text for Image
     * @param array $color text color
     *
     * @return Image_Text testee
     */
    protected function initInstance($text, $color = array('#FFFFFF'))
    {
        $i = new Image_Text($text);
        $i->set(
            array(
                'font_path' => dirname(__FILE__) . '/data/',
                'font_file' => 'Vera.ttf',
                'font_size' => 20,
                'canvas' => array('width' => 200, 'height' => 100),
                'width' => 200,
                'height' => 200,
                'color' => $color,
                'image_type' => IMAGETYPE_BMP
            )
        );
        return $i;
    }

    /**
     * test construction
     *
     * @return void
     */
    public function testConstruct()
    {
        $i = $this->initInstance('Hello World');
        $this->assertSame('Image_Text', get_class($i));
        $i->init();
        $i->render();
        $this->assertTrue(
            imageisthesame(
                $this->_dir . 'test-construct.bmp',
                $i->getImg()
            )
        );
    }

    /**
     * test background color
     *
     * @return void
     */
    public function testBackgroundColor()
    {
        //default background is black
        $i = $this->initInstance('Hello World');
        $this->assertSame('Image_Text', get_class($i));
        $i->init();
        $i->render();
        $this->assertTrue(
            imageisthesame(
                $this->_dir . 'test-background-black.bmp',
                $i->getImg()
            )
        );

        //red background
        $i = $this->initInstance('Hello World');
        $this->assertSame('Image_Text', get_class($i));
        $i->set(array('background_color' => '#FF0000'));
        $i->init();
        $i->render();
        $this->assertTrue(
            imageisthesame(
                $this->_dir . 'test-background-red.bmp',
                $i->getImg()
            )
        );
    }

    /**
     * @todo Implement testSet().
     */
    public function testSet()
    {
        // Remove the following line when you implement this test.
        $this->markTestIncomplete(
            "This test has not been implemented yet."
        );
    }

    /**
     * @todo Implement testSetColors().
     */
    public function testSetColors()
    {
        // Remove the following line when you implement this test.
        $this->markTestIncomplete(
            "This test has not been implemented yet."
        );
    }

    /**
     * @todo Implement testSetColor().
     */
    public function testSetColor()
    {
        // Remove the following line when you implement this test.
        $this->markTestIncomplete(
            "This test has not been implemented yet."
        );
    }

    /**
     * @todo Implement testInit().
     */
    public function testInit()
    {
        // Remove the following line when you implement this test.
        $this->markTestIncomplete(
            "This test has not been implemented yet."
        );
    }

    /**
     * @todo Implement testAutoMeasurize().
     */
    public function testAutoMeasurize()
    {
        // Remove the following line when you implement this test.
        $this->markTestIncomplete(
            "This test has not been implemented yet."
        );
    }

    /**
     * @todo Implement testMeasurize().
     */
    public function testMeasurize()
    {
        // Remove the following line when you implement this test.
        $this->markTestIncomplete(
            "This test has not been implemented yet."
        );
    }

    /**
     * @todo Implement testRender().
     */
    public function testRender()
    {
        // Remove the following line when you implement this test.
        $this->markTestIncomplete(
            "This test has not been implemented yet."
        );
    }

    /**
     * @todo Implement testGetImg().
     */
    public function testGetImg()
    {
        // Remove the following line when you implement this test.
        $this->markTestIncomplete(
            "This test has not been implemented yet."
        );
    }

    /**
     * @todo Implement testDisplay().
     */
    public function testDisplay()
    {
        // Remove the following line when you implement this test.
        $this->markTestIncomplete(
            "This test has not been implemented yet."
        );
    }

    /**
     * @todo Implement testSave().
     */
    public function testSave()
    {
        // Remove the following line when you implement this test.
        $this->markTestIncomplete(
            "This test has not been implemented yet."
        );
    }

    /**
     * @todo Implement test_getOffset().
     */
    public function test_getOffset()
    {
        // Remove the following line when you implement this test.
        $this->markTestIncomplete(
            "This test has not been implemented yet."
        );
    }

    /**
     * test convert routine
     *
     * @return void
     */
    public function test_convertString2RGB()
    {
        $this->assertEquals(
            array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 0),
            Image_Text::convertString2RGB('#FFFFFF')
        );
        $this->assertEquals(
            array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 0),
            Image_Text::convertString2RGB('#00FFFFFF')
        );
        $this->assertEquals(
            array('r' => 0, 'g' => 0, 'b' => 0, 'a' => 0),
            Image_Text::convertString2RGB('#000000')
        );
        $this->assertEquals(
            array('r' => 0, 'g' => 0, 'b' => 0, 'a' => 255),
            Image_Text::convertString2RGB('#FF000000')
        );
    }

    /**
     * @todo Implement test_processText().
     */
    public function test_processText()
    {
        // Remove the following line when you implement this test.
        $this->markTestIncomplete(
            "This test has not been implemented yet."
        );
    }
}
