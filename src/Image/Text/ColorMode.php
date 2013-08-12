<?php
/**
 * Image_Text.
 *
 * This is the main file of the Image_Text package. This file has to be included for
 * usage of Image_Text.
 *
 * This is a simple example script, showing Image_Text's facilities.
 *
 * PHP version 5
 *
 * @category Image
 * @package  Image_Text
 * @author   Michael Cramer <michael@bigmichi1.de>
 * @license  http://www.php.net/license/3_01.txt PHP License
 * @version  GIT: @package_version@
 * @link     http://pear.php.net/package/Image_Text
 * @since    File available since Release 0.7.1
 */
/**
 * Color modes for Image_Text
 *
 * @category Image
 * @package  Image_Text
 * @author   Michael Cramer <michael@bigmichi1.de>
 * @license  http://www.php.net/license/3_01.txt PHP License
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/Image_Text
 * @since    Class available since Release 0.7.1
 */
final class ColorMode
{
    /**
     * rotate colors for every line
     */
    const LINE = 'line';
    /**
     * rotate colors for every paragraph
     */
    const PARAGRAPH = 'paragraph';
    /**
     * rotate colors for every word
     *
     * @since 0.7.1
     */
    const WORD = 'word';

    /**
     * don't allow instantiation
     */
    private function __construct()
    {
    }
}