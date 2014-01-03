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
# 03-01-2014
if (!defined('DC_RC_PATH')) {return;}

/**
 * manage post read counter
 */
class postCount {

	/**
	 * class variables
	 */
	protected $core;
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
	public function __construct($core) {
		$this->core	=& $core;
		$this->core->blog->settings->addNamespace('postCount');		
		$this->readSettings();
	}

	/**
	 * read settings
	 */
	protected function readSettings() {
		if ($this->installed) {
			$this->enabled = (boolean) $this->core->blog->settings->postCount->enabled;
			$this->synchronize = (boolean) $this->core->blog->settings->postCount->synchronize;
			$this->lang = (string) $this->core->blog->settings->postCount->lang;
			$this->countlock = (boolean) $this->core->blog->settings->postCount->countlock;
			$this->local = (boolean) $this->core->blog->settings->postCount->local;
			$this->locals = explode(',',$this->core->blog->settings->postCount->locals);
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
		$this->lang = (string) $this->core->blog->settings->lang;
		$this->countlock = (boolean) false;
		$this->local = (boolean) false;
		$this->locals = explode(',','127.0.0.1');
	}

	/**
	 * save all settings
	 */
	public function saveSettings() {
		$this->core->blog->settings->postCount->put('enabled',$this->enabled,'boolean',__('Enable plugin'));
		$this->core->blog->settings->postCount->put('synchronize',$this->synchronize,'boolean',__('Synchronize blog'));
		$this->core->blog->settings->postCount->put('lang',$this->lang,'string',__('Blog language'));
		$this->core->blog->settings->postCount->put('countlock',$this->countlock,'boolean',__('Lock counters'));
		$this->core->blog->settings->postCount->put('local',$this->local,'boolean',__('Count local counts'));
		$this->core->blog->settings->postCount->put('locals',implode(',',$this->locals),'string',__('Local IPs'));
		if ($this->synchronize)
			$this->core->blog->triggerBlog();
	}

	/**
	 * get current post id
	 */
    protected function post_id() {
        global $_ctx;
		if (isset($_ctx) && is_object($_ctx->posts) && $_ctx->posts->exists('post_id'))
			return (integer)$_ctx->posts->post_id;
		else
			return -1;
    }

	/**
	 * get current post lang
	 */
    protected function post_lang() {
        global $_ctx;
		if (isset($_ctx) && is_object($_ctx->posts) && $_ctx->posts->exists('post_lang'))
			return (string) $_ctx->posts->post_lang;
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
			if ($this->core->con->driver() == 'mysql' || $this->core->con->driver() == 'mysqli') {
        $cast_type = 'UNSIGNED';
      } else {
        $cast_type='INTEGER';
      }
	            $req =
				'SELECT CAST(M.meta_id AS '.$cast_type.') '.
				'FROM '.$this->core->prefix.'meta M '.
				"WHERE M.meta_type='count|".(string)$this->post_lang()."' ".
				"AND M.post_id=".(integer) $id;
	            $rs = $this->core->con->select($req);
	            if ($rs->isEmpty())
					return (integer)0;
				else
					return ((integer)$rs->f(0) < 0) ? (integer)0 : (integer)$rs->f(0);
			}
		    catch (Exception $ex) { $this->core->error->add($ex->getMessage()); }
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
				$cur = $this->core->con->openCursor($this->core->prefix.'meta');
				$count = (integer) $this->count();
				if ($count <= 0) {
					$cur->meta_type = 'count|'.(string)$this->post_lang();
					$cur->post_id = (integer) $id;
					$cur->meta_id = (integer) 1;
					$cur->insert();
					if ($this->synchronize) {
						$this->core->blog->triggerBlog();
					}
				}
				else {
					$cur->meta_id = (integer)( $count +1 );
					$cur->update(
						'WHERE post_id='.(integer) $id.
						" AND meta_type='count|".(string)$this->post_lang()."' "
					);
					if ($this->synchronize) {
						$this->core->blog->triggerBlog();
					}
				}
			}
		    catch (Exception $ex) { $this->core->error->add($ex->getMessage()); }
		}
	}

	/**
	 * reset post read counter
	 */
	public function reset() {
		try {
			$req =
			'DELETE '.
			'FROM '.$this->core->prefix.'meta '.
			"WHERE meta_type LIKE 'count|%'";
			$rs = $this->core->con->select($req);
		}
		catch (Exception $ex) { $this->core->error->add($ex->getMessage()); }
	}

	/**
	 * increment post read counter
	 */
    public static function postCountIncrement() {
		global $core;
		if (!isset($core->blog->postCount) || !$core->blog->settings->postCount->enabled)
			return;
		$core->blog->postCount->increment();
	}

	/**
	 * get post counter
	 */
    public static function postCountGet() {
		global $core;
		if (!isset($core->blog->postCount) || !$core->blog->settings->postCount->enabled)
			return;

		$count = (integer) $core->blog->postCount->count();
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
