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
/**
 * define template helper codes
 */
dcCore::app()->tpl->addValue('postCountIncrement', array('tpl_postCount', 'postCountIncrement'));
dcCore::app()->tpl->addValue('postCountGet', array('tpl_postCount', 'postCountGet'));

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
