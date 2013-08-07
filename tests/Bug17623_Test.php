<?php
/**
 * Image_Text.
 *
 * This is a simple example script, showing Image_Text's facilities.
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
/**
 * Class Bug17623_Test
 *
 * @category Image
 * @package  Image_Text
 * @author   Michael Cramer <michael@bigmichi1.de>
 * @license  http://www.php.net/license/3_01.txt PHP License
 * @link     http://pear.php.net/package/Image_Text
 * @link     http://pear.php.net/bugs/bug.php?id=17623
 */
class Bug17623_Test extends PHPUnit_Framework_TestCase
{
    /**
     * testee
     *
     * @var Image_Text
     */
    private $_testee;

    /**
     * setup test instance
     *
     * @return void
     */
    protected function setUp()
    {
        $this->_testee = new Image_Text("test");
    }

    /**
     * testcase for Bug 17623
     *
     * @return void
     */
    public function testBug17623()
    {
        $this->_testee->set('font_path', dirname(__FILE__) . '/data/');
        $this->_testee->set('font_file', 'Vera.ttf');
        $this->_testee->set('width', 200);
        $this->_testee->set('height', 200);
        $this->_testee->set('color', '#000000');
        $this->_testee->init();
        $this->assertNotNull($this->_testee->getImg());
    }
}
