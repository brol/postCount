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
        return;
}
/**
 * define template helper codes
 */
$core->tpl->addValue('postCountIncrement', array('tpl_postCount', 'postCountIncrement'));
$core->tpl->addValue('postCountGet', array('tpl_postCount', 'postCountGet'));

/**
 * postCount template helper
 */
class tpl_postCount {

	/**
	 * template helper to increment post counter
	 */
    public static function postCountIncrement($attr) {
		return '<?php postCount::postCountIncrement() ?>';
	}

	/**
	 * template helper to get post counter
	 */
    public static function postCountGet($attr) {
		return '<?php echo postCount::postCountGet() ?>';
	}
}
