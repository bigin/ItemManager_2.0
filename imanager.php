<?php
if(!isset($_SESSION)){session_start();}
if(!isset($_SESSION['cat']) || is_null($_SESSION['cat'])) $_SESSION['cat'] = null;

// get correct id for the plugin
$thisfile = basename(__FILE__, '.php');
// include path & file constants
include($thisfile.'/lib/inc/_def.php');

register_plugin(
	$thisfile,
	'ItemManager',
	IM_VERSION_GS,
	'Juri Ehret',
	'http://ehret-studio.com',
	'A simple flat-file framework for GetSimple-CMS',
	'imanager',
	'im_render_backend'
);

// activate actions
add_action('admin-pre-header', 'ajaxGetLists');
add_action('nav-tab', 'createNavTab', array($thisfile, $thisfile, 'Manager'));
//add_action($thisfile.'-sidebar', 'im_render_backend', array('sidebar'));
register_style('jqui', IM_SITE_URL.'plugins/'.$thisfile.'/upload/js/jquery-ui/jquery-ui.css',
	GSVERSION, 'screen');
register_style('imstyle', IM_SITE_URL.'plugins/'.$thisfile.'/css/im-styles.css',
	GSVERSION, 'screen');
register_style('blueimp',  IM_SITE_URL.'plugins/'.$thisfile.'/css/blueimp-gallery.min.css',
	GSVERSION, 'screen');
register_style('imstylefonts', IM_SITE_URL.'plugins/'.$thisfile
	.'/css/fonts/font-awesome/css/font-awesome.min.css', GSVERSION, 'screen');
queue_style('jqui', GSBACK);
queue_style('imstyle', GSBACK);
queue_style('imstylefonts', GSBACK);
queue_style('blueimp', GSBACK);

// ItemManager
include(GSPLUGINPATH.'imanager/lib/ItemManager.php');

/**
 * Core ItemManager's function, we use it to create an ItemManager instance
 *
 * @param string $name
 *
 * @return Im\ItemManager instance
 */
function imanager($name='')
{
	global $im;
	if($im === null) $im = new ItemManager();
	return !empty($name) ? $im->$name : $im;
}

/**
 * Loads and renders ItemManager's backend (inside admin panel only)
 *
 */
function im_render_backend($arg=null)
{
	global $im;
	if(is_null($arg))
	{
		// check whether the user inside admin panel
		if(defined('IN_GS') && !empty($_GET['id']) && $_GET['id'] == IM_NAME) {
			define('IS_ADMIN_PANEL', true);
		}
		if($im === null) $im = imanager();
		if(defined('IS_ADMIN_PANEL'))
		{
			(!$im->config->injectActions) or $im->setActions();
			if($im->config->hiddeAdmin) {
				echo $im->config->adminDisabledMsg;
			} else
			{
				$im->admin->init();
				echo $im->admin->display();
			}
		}
	} else
	{
		if(defined('IS_ADMIN_PANEL')) echo $im->admin->display($arg);
	}
}

/**
 * Ajax call before rendering the admin headers
 */
function ajaxGetLists()
{
	global $im;
	if(isset($_GET['getcatlist']) || isset($_GET['getitemlist']))
	{
		// check whether the user inside admin panel
		if(defined('IN_GS') && !empty($_GET['id']) && $_GET['id'] == IM_NAME) {
			define('IS_ADMIN_PANEL', true);
		}
		if($im === null) $im = imanager();
		if(defined('IS_ADMIN_PANEL'))
		{
			(!$im->config->injectActions) or $im->setActions();
			if(!$im->config->hiddeAdmin)
			{
				$im->admin->init();
				echo $im->admin->display();
			}
			exit();
		}
	}
}

/**
 * Deprecated ItemManager's call for backward compatibility only
 */
class IManager extends ItemManager { public function __construct() { parent::__construct(); } }
?>
