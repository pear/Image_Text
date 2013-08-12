<?php
/**
 * Image_Text_Colormode.
 *
 * This is the main file for the Image_Text_Colormode. This file is included in
 * Image_Text natively.
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
final class Image_Text_Colormode
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