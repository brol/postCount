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

if (!defined('DC_RC_PATH')) {
  return null;
}
//require_once dirname(__FILE__).'/_debug.php';

/**
 * register this module for Dotclear
 */
$this->registerModule(
	/* Name */			"postCount",
	/* Description*/	"Post read counter / Compteur de lecture de billet",
	/* Author */		"Olivier Le Bris, Pierre Van Glabeke",
	/* Version */		"1.7",
	/* Properties */
	array(
		'permissions' => 'usage,contentadmin',
		'type' => 'plugin',
		'dc_min' => '2.6',
		'support' => 'http://forum.dotclear.org/viewtopic.php?pid=326250#p326250',
		'details' => 'http://plugins.dotaddict.org/dc2/details/postCount'
		)
);
