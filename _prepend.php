<?php
/**
 * @brief postCount, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugin
 *
 * @author Olivier Le Bris (http://phoenix.cybride.net/)
 *
 * @Contributors Pierre Van Glabeke
 * @copyright Creative Commons by-nc-sa license https://creativecommons.org/licenses/by-nc-sa/3.0/deed.fr_CA
 */
if (!defined('DC_RC_PATH')) { return; }
/**
 * auto-load working class
 */

Clearbricks::lib()->autoload(['postCount' => __DIR__ . '/class.postcount.php']);
dcCore::app()->blog->postCount = new postCount();
