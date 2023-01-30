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
if (!defined('DC_RC_PATH')) {
    return;
}

dcCore::app()->resources['help']['postCount'] = __DIR__ . '/help/postCount.html';
