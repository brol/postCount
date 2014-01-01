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
if (!defined('DC_CONTEXT_ADMIN')) exit;

/**
 * get previous and actual module version
 */
$v_new = $core->plugins->moduleInfo('postCount', 'version');
$v_old = $core->getVersion('postCount');

try {
	if (version_compare($v_old, $v_new, '>=')) {
		/**
		 * module is up to date
		 */
		return;
	} else {

		/**
		 * module is to be installed or updates
		 */
	
		// setup default settings and new plugin version
		$core->blog->postCount->initSettings();
		$core->setVersion('postCount', $v_new);
	
		if ($v_old != '') {
		
			/**
			 * module is to be updated
			 */

			/**
			 * convert meta_type count to count|'default blog language'
			 * only if plugin version is < 1.6
			 */
			if (version_compare($v_old, '1.6', '<')) {
				try {
					$cur = $this->core->con->openCursor($this->core->prefix.'meta');
					$cur->meta_type = 'count|'.(string) $this->blog->settings->postCount->lang;
					$cur->update("WHERE meta_type='count'");
					$this->core->blog->triggerBlog();
				}
				catch (Exception $ex) { $this->core->error->add($ex->getMessage()); }
			}		
		} else {
			/**
			 * module is to be installed
			 */
		}
		
		unset($v_new, $v_old);
		return true;		
	}
} catch (Exception $e) {
	$core->error->add(__('Unable to install or update the plugin postCount'));
	$core->error->add($e->getMessage());
	return false;
}
