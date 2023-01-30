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
if (!defined('DC_RC_PATH')) {return;}

/**
 * manage post read counter
 */
class postCount {

	/**
	 * class variables
	 */
	protected $installed=true;
	public $enabled;
	public $synchronize;
	protected $lang;
	public $countlock;
	public $local;
	public $locals=array();

	/**
	 * class constructor
	 */
	public function __construct() {
		dcCore::app()->blog->settings->addNamespace('postCount');		
		$this->readSettings();
	}

	/**
	 * read settings
	 */
	protected function readSettings() {
		if ($this->installed) {
			$this->enabled = (boolean) dcCore::app()->blog->settings->postCount->enabled;
			$this->synchronize = (boolean) dcCore::app()->blog->settings->postCount->synchronize;
			$this->lang = (string) dcCore::app()->blog->settings->postCount->lang;
			$this->countlock = (boolean) dcCore::app()->blog->settings->postCount->countlock;
			$this->local = (boolean) dcCore::app()->blog->settings->postCount->local;
			$this->locals = explode(',',dcCore::app()->blog->settings->postCount->locals);
		}
	}
	
	/**
	 * set default settings
	 */
	public function initSettings() {
		$this->defaultSettings();
		$this->saveSettings();
	}

	/**
	 * set default settings
	 */
	public function defaultSettings() {
		$this->enabled = (boolean) false;
		$this->synchronize = (boolean) true;
		$this->lang = (string) dcCore::app()->blog->settings->postCount->lang;
		$this->countlock = (boolean) false;
		$this->local = (boolean) false;
		$this->locals = explode(',','127.0.0.1');
	}

	/**
	 * save all settings
	 */
	public function saveSettings() {
		dcCore::app()->blog->settings->postCount->put('enabled',$this->enabled,'boolean',__('Enable plugin'));
		dcCore::app()->blog->settings->postCount->put('synchronize',$this->synchronize,'boolean',__('Synchronize blog'));
		dcCore::app()->blog->settings->postCount->put('lang',$this->lang,'string',__('Blog language'));
		dcCore::app()->blog->settings->postCount->put('countlock',$this->countlock,'boolean',__('Lock counters'));
		dcCore::app()->blog->settings->postCount->put('local',$this->local,'boolean',__('Count local counts'));
		dcCore::app()->blog->settings->postCount->put('locals',implode(',',$this->locals),'string',__('Local IPs'));
		if ($this->synchronize)
			dcCore::app()->blog->triggerBlog();
	}

	/**
	 * get current post id
	 */
    protected function post_id() {
		if (isset(dcCore::app()->ctx) && is_object(dcCore::app()->ctx->posts) && dcCore::app()->ctx->posts->exists('post_id'))
			return (integer)dcCore::app()->ctx->posts->post_id;
		else
			return -1;
    }

	/**
	 * get current post lang
	 */
    protected function post_lang() {
		if (isset(dcCore::app()->ctx) && is_object(dcCore::app()->ctx->posts) && dcCore::app()->ctx->posts->exists('post_lang'))
			return (string) dcCore::app()->ctx->posts->post_lang;
		else {
			$this->readSettings();
			return (string) $this->lang;
		}
    }

	/**
	 * get current post read counter
	 */
	public function count()	{
		$id = $this->post_id();
		if (!is_numeric($id) || $id < 0)
			return -1;
		else {
			try {
			if (dcCore::app()->con->driver() == 'mysql' || dcCore::app()->con->driver() == 'mysqli') {
        $cast_type = 'UNSIGNED';
      } else {
        $cast_type='INTEGER';
      }
	            $req =
				'SELECT CAST(M.meta_id AS '.$cast_type.') '.
				'FROM '.dcCore::app()->prefix.'meta M '.
				"WHERE M.meta_type='count|".(string)$this->post_lang()."' ".
				"AND M.post_id=".(integer) $id;
	            $rs = dcCore::app()->con->select($req);
	            if ($rs->isEmpty())
					return (integer)0;
				else
					return ((integer)$rs->f(0) < 0) ? (integer)0 : (integer)$rs->f(0);
			}
		    catch (Exception $ex) { dcCore::app()->error->add($ex->getMessage()); }
		}
	}

	/**
	 * get client internet ip address
	 */
	public function getIP() {
		foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key) {
			if (array_key_exists($key, $_SERVER) === true) {
				foreach (explode(',', $_SERVER[$key]) as $ip) {
					if (filter_var($ip, FILTER_VALIDATE_IP) !== false) {
						return $ip;
					}
				}
			}
		}
	}
	
	/**
	 * increment post read counter
	 */
	public function increment() {
		$id = $this->post_id();
		if (!is_numeric($id) || $id < 0)
			return;
		else {
			$this->readSettings();
			if ($this->countlock) return;

			if (!$this->local) {			
				// disallow incerment counters if client is same ip as server
				$ServerIP = isset($_SERVER['SERVER_ADDR'])?$_SERVER['SERVER_ADDR']:0;
				$RemoteIP = $this->getIP();
				if ($RemoteIP == $ServerIP) return;
				// disallow if remote ip is listed in restricted ip
				if (in_array($RemoteIP, $this->locals)) return;
			}

			try {
				$cur = dcCore::app()->con->openCursor(dcCore::app()->prefix.'meta');
				$count = (integer) $this->count();
				if ($count <= 0) {
					$cur->meta_type = 'count|'.(string)$this->post_lang();
					$cur->post_id = (integer) $id;
					$cur->meta_id = (integer) 1;
					$cur->insert();
					if ($this->synchronize) {
						dcCore::app()->blog->triggerBlog();
					}
				}
				else {
					$cur->meta_id = (integer)( $count +1 );
					$cur->update(
						'WHERE post_id='.(integer) $id.
						" AND meta_type='count|".(string)$this->post_lang()."' "
					);
					if ($this->synchronize) {
						dcCore::app()->blog->triggerBlog();
					}
				}
			}
		    catch (Exception $ex) { dcCore::app()->error->add($ex->getMessage()); }
		}
	}

	/**
	 * reset post read counter
	 */
	public function reset() {
		try {
			$req =
			'DELETE '.
			'FROM '.dcCore::app()->prefix.'meta '.
			"WHERE meta_type LIKE 'count|%'";
			$rs = dcCore::app()->con->select($req);
		}
		catch (Exception $ex) { dcCore::app()->error->add($ex->getMessage()); }
	}

	/**
	 * increment post read counter
	 */
    public static function postCountIncrement() {
		if (!isset(dcCore::app()->blog->postCount) || !dcCore::app()->blog->settings->postCount->enabled)
			return;
		dcCore::app()->blog->postCount->increment();
	}

	/**
	 * get post counter
	 */
    public static function postCountGet() {
		if (!isset(dcCore::app()->blog->postCount) || !dcCore::app()->blog->settings->postCount->enabled)
			return;

		$count = (integer) dcCore::app()->blog->postCount->count();
		if ($count <= 0)
			$msg = __('Unread');
		else if ($count == 1)
			$msg = __('One read');
		else
			$msg = str_replace('#c', $count, __('#c reads'));
		unset($count);

		return $msg;
	}
}
