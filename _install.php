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

if (!defined('DC_CONTEXT_ADMIN')) exit;

/**
 * get previous and actual module version
 */
$v_new = dcCore::app()->plugins->moduleInfo('postCount', 'version');
$v_old = dcCore::app()->getVersion('postCount');

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
		dcCore::app()->blog->postCount->initSettings();
		dcCore::app()->setVersion('postCount', $v_new);
	
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
					$cur = dcCore::app()->con->openCursor(dcCore::app()->prefix.'meta');
					$cur->meta_type = 'count|'.(string) $this->blog->settings->postCount->lang;
					$cur->update("WHERE meta_type='count'");
					dcCore::app()->blog->triggerBlog();
				}
				catch (Exception $ex) { dcCore::app()->error->add($ex->getMessage()); }
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
	dcCore::app()->error->add(__('Unable to install or update the plugin postCount'));
	dcCore::app()->error->add($e->getMessage());
	return false;
}
