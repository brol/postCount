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

dcPage::check('usage,admin');

$page_title = __('Post read counter');

/* get plugin operation */
$p_op = (!empty($_POST['op']))?(string)$_POST['op']:'none';
$p_tab='tab_settings';

/* get message to display */
if (!empty($_GET['msg'])) $msg = (string) rawurldecode($_GET['msg']);
else if (!empty($_POST['msg'])) $msg = (string) rawurldecode($_POST['msg']);

if (!empty($_GET['m'])) {
	switch ($_GET['m']) {
		case '1':
			$m = __('Settings saved.');
			$p_tab='tab_settings';
			break;
		case '2':
			$m = __('Settings reseted.');
			$p_tab='tab_settings';
			break;
		case '3':
			$m = __('Counters reseted.');
			$p_tab='tab_settings';
			break;
		default:
			break;
	}
	if (empty($msg)) $msg = $m;
	else $msg.=' - '.$m;
}

/* what operation to do */
switch ($p_op) {
	case 'settings': {
		try {
			$m=1;
			$plugin_defaults = (empty($_POST['plugin_defaults']))?false:true;
			dcCore::app()->blog->postCount->enabled = (boolean) (empty($_POST['plugin_enabled']))?false:true;
			dcCore::app()->blog->postCount->synchronize = (boolean) (empty($_POST['plugin_synchronize']))?false:true;
			dcCore::app()->blog->postCount->countlock = (boolean) (empty($_POST['plugin_countlock']))?false:true;
			dcCore::app()->blog->postCount->local = (boolean) (empty($_POST['plugin_local']))?false:true;
			if (empty($_POST['plugin_locals'])) {
				dcCore::app()->blog->postCount->locals = explode(',','127.0.0.1');
			}
			else {
				// sanitize input text + remove white spaces
				$IPs = preg_replace('/\s\s+/', '', html::escapeHTML( (string) $_POST['plugin_locals'] ) );			
				dcCore::app()->blog->postCount->locals = explode(',',$IPs);
			}
			if ($plugin_defaults) {
				$m=2;
				dcCore::app()->blog->postCount->defaultSettings();
			}
			dcCore::app()->blog->postCount->saveSettings();			
			if (empty($msg)) {
				http::redirect('plugin.php?p=postCount&m='.$m);
			}
		}
		catch (Exception $ex) { dcCore::app()->error->add($ex->getMessage()); }
	}
	break;	
	
	case 'reset': {
		try {
			$m=3;
			dcCore::app()->blog->postCount->reset();			
			if (empty($msg)) {
				http::redirect('plugin.php?p=postCount&m='.$m);
			}
		}
		catch (Exception $ex) { dcCore::app()->error->add($ex->getMessage()); }
	}
	break;	
	
	case 'none':
	default:
		break;
}

?>
<html>
<head>
<title><?php echo $page_title; ?></title>
<link rel="stylesheet" type="text/css" href="index.php?pf=postCount/style.css" />
  <?php echo
  dcPage::jsDatePicker().
  //dcPage::jsToolBar().
  dcPage::jsModal().
  /*dcPage::jsLoad('js/_post.js').*/
  /*dcPage::jsConfirmClose('entry-form','comment-form').*/
  # --BEHAVIOR-- adminPageHeaders
  dcCore::app()->callBehavior('adminPageHeaders');/*.*/
  /*dcPage::jsPageTabs($default_tab).*/
  /*$next_headlink."\n".$prev_headlink;*/
  ?>
<?php
	echo dcPage::jsPageTabs($p_tab);
?>
</head>
<body>

<?php

	echo dcPage::breadcrumb(
		array(
			html::escapeHTML(dcCore::app()->blog->name) => '',
			'<span class="page-title">'.$page_title.'</span>' => ''
		));
if (!empty($msg)) {
  dcPage::success($msg);
}
?>

<div class='multi-part' id='tab_integration' title='<?php echo __('Integration') ?>'>
	<div class="fieldset">
		<h4><?php echo __('Themes integration'); ?></h4>
		<p>
			<?php echo __('Add this code in your post.html theme file where to update post count:'); ?></p>
			<pre>{{tpl:postCountIncrement}}</pre>
		<p>
			<?php echo __('Add this code in your themes file (post.html or home.html) where to display post count:'); ?></p>
			<pre>{{tpl:postCountGet}}</pre>
	</div>
</div>

<div class='multi-part' id='tab_settings' title='<?php echo __('Settings') ?>'>
	<form action="plugin.php" method="post" id="state">
    <div class="fieldset">
      <h4><?php echo __('Settings'); ?></h4>
			<p>
				<?php echo form::checkbox('plugin_defaults', 1, (boolean) false) ?>
				<label for="plugin_defaults" class="classic"><?php echo __('Reset to default settings') ?></label>
			</p>
			<p>
				<?php echo form::checkbox('plugin_enabled', 1, (boolean) dcCore::app()->blog->settings->postCount->enabled) ?>
				<label for="plugin_enabled" class="classic"><?php echo __('Plugin activation') ?></label>
			</p>
			<p>
				<?php echo form::checkbox('plugin_synchronize', 1, (boolean) dcCore::app()->blog->settings->postCount->synchronize) ?>
				<label for="plugin_synchronize" class="classic"><?php echo __('Synchronize blog') ?></label>
			</p>
			<p>
				<?php echo form::checkbox('plugin_countlock', 1, (boolean) dcCore::app()->blog->settings->postCount->countlock) ?>
				<label for="plugin_countlock" class="classic"><?php echo __('Lock counters') ?></label>
			</p>
			<p>
				<?php echo form::checkbox('plugin_local', 1, (boolean) dcCore::app()->blog->settings->postCount->local) ?>
				<label for="plugin_local" class="classic"><?php echo __('Count local counts') ?></label>
			<p class="info">
				<?php echo __('Your IP:') ?> <?php echo dcCore::app()->blog->postCount->getIP(); ?>
			</p>
			</p>
			<p>
				<label for="plugin_locals" class="classic"><?php echo __('Local IPs (comma separated)') ?></label>
				<?php echo form::field('plugin_locals',50,255,html::escapeHTML(dcCore::app()->blog->settings->postCount->locals)) ?>
			</p>
    </div>
			<p>
				<input type="submit" value="<?php echo __('Save') ?>" />
			</p>
			<p>
        <?php
				  echo form::hidden(array('p'),'postCount');
					echo form::hidden(array('op'),'settings');
					echo dcCore::app()->formNonce();
        ?>
		  </p>
	</form>

  <form action="plugin.php" method="post" id="reset">
    <div class="fieldset">
		  <h4><?php echo __('Reset counters'); ?></h4>
			<p><?php echo __('Permanant reset post read counters.') ?></p>
			<p class="warn"><?php echo __('BEWARE: this cannot be undo!') ?></p>
			<p><?php echo __('This option remove all counters to reset them.') ?></p>
    </div>
      <p>
				<input type="submit" value="<?php echo __('Reset') ?>" /> 
				<?php
					echo form::hidden(array('p'),'postCount');
					echo form::hidden(array('op'),'reset');
					echo dcCore::app()->formNonce();
				?>
      </p>
	</form>
</div>

<?php dcPage::helpBlock('postCount'); ?>

</body>
</html>