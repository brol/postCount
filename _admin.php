<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of postCount, a plugin for Dotclear 2.
#
# Copyright (c) 2007-2010 Olivier Le Bris
# http://phoenix.cybride.net/
# Contributor: Pierre Van Glabeke
#
# Licensed under the Creative Commons by-nc-sa license.
# See LICENSE file or
# http://creativecommons.org/licenses/by-nc-sa/3.0/deed.fr_CA
# -- END LICENSE BLOCK ------------------------------------
#
# 01-01-2014

/**
* rights management
*/
if (!defined('DC_CONTEXT_ADMIN')) { return; }

/**
* admin menu integration
*/
$_menu['Blog']->addItem(__('postCount'),
	'plugin.php?p=postCount',
	'index.php?pf=postCount/icon.png',
	preg_match('/plugin.php\?p='.'postCount'.'(&.*)?$/', $_SERVER['REQUEST_URI']),
	$core->auth->check('usage,admin', $core->blog->id)
	);
