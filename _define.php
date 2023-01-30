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
    return null;
}

//require_once dirname(__FILE__).'/_debug.php';

$this->registerModule(
    'postCount',
    'Post read counter',
    'Olivier Le Bris, Pierre Van Glabeke and Contributors',
    '1.8-dev',
    [
        'requires'    => [['core', '2.24']],
        'permissions' => dcCore::app()->auth->makePermissions([
            dcAuth::PERMISSION_USAGE,
            dcAuth::PERMISSION_ADMIN,
        ]),
        'type'       => 'plugin',
        'support'    => 'http://forum.dotclear.org/viewtopic.php?pid=326250#p326250',
        'details'    => 'http://plugins.dotaddict.org/dc2/details/' . basename(__DIR__),
    ]
);
