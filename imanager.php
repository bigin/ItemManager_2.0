<?php
/**
 * Plugin Name: ItemManager
 * Description: A simple flat-file Framework.
 * Version: 2.0
 * Author: Juri Ehret
 * Author URL: http://ehret-studio.com
 *
 * This file is part of ItemManager.
 *
 * ItemManager is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or any
 * later version.
 *
 * ItemManager is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with ItemManager.  If not, see <http://www.gnu.org/licenses/>.
 *
 */
if(session_status() == PHP_SESSION_NONE) {session_start();}
if(!isset($_SESSION['cat']) || is_null($_SESSION['cat'])) $_SESSION['cat'] = null;

# get correct id for plugin
$thisfile = basename(__FILE__, '.php');

# path & file constants definitions
define('ITEMDATA', GSDATAPATH.'imanager/'); // wird verwendet
define('IM_CATEGORY_DIR', ITEMDATA.'categories/');
define('IM_ITEM_DIR', ITEMDATA.'items/');
define('IM_SETTINGS_DIR', ITEMDATA.'settings/');
define('IM_FIELDS_DIR', ITEMDATA.'fields/');
define('IM_UPLOAD_DIR', GSDATAUPLOADPATH.'imanager/');
define('IM_TEMPLATE_DIR', GSPLUGINPATH.'imanager/tpl/');
define('IM_IMAGE_UPLOAD_DIR', GSDATAPATH.'uploads/imanager/');
define('IM_BACKUP_DIR', GSBACKUPSPATH.'other/imanager/');
define('IM_CONFIG_FILE', IM_SETTINGS_DIR.'config.im.xml');
define('IM_FIELDS_FILE_SUFFIX', '.im.fields.xml');
define('IM_CATEGORY_FILE_SUFFIX', '.im.cat.xml');
define('IM_TEMPLATE_FILE_SUFFIX', '.im.tpl');
define('IM_ITEM_FILE_SUFFIX', '.im.item.xml');
define('IM_SITE_URL', $SITEURL);
define('IM_LANGUAGE', $LANG);

//define('IM_ACTION_IMANAGER', 'imanager');




// bestimmte bereiche im Admin deaktivieren
//define('IM_HIDE_SETTINGS', 1);
//define('IM_HIDE_FIELDS', 1 );


// Initialize software name
define('IMTITLE', 'PettyShop');

// register plugin
register_plugin(
	$thisfile,
	'Item Manager',
	'2.0',
	'Juri Ehret 10.04.2015',
	'http://ehret-studio.com',
	'A simple flat-file Framework',
	'imanager',
	'imanager'
);

// activate actions
add_action('admin-pre-header', 'ajaxGetLists');
add_action('nav-tab', 'createNavTab', array('imanager', $thisfile, IMTITLE, 'view'));
/* i18n search stuff */
add_action('search-index', 'i18nSearchImIndex');
add_filter('search-item', 'i18nSearchImItem');
add_filter('search-display', 'i18nSearchImDisplay');
/* include your own CSS for beautiful manager style */
register_style('imstyle', IM_SITE_URL.'plugins/'.$thisfile.'/css/im-styles.css', GSVERSION, 'screen');
register_style('blueimp',  IM_SITE_URL.'plugins/'.$thisfile.'/css/blueimp-gallery.min.css', GSVERSION, 'screen');
register_style('imstylefonts', IM_SITE_URL.'plugins/'.$thisfile.'/css/fonts/font-awesome/css/font-awesome.min.css', GSVERSION, 'screen');
queue_style('imstyle', GSBOTH);
queue_style('imstylefonts', GSBOTH);
queue_style('blueimp', GSBOTH);


// model
include(GSPLUGINPATH.'imanager/class/im.model.class.php');
// settup
include(GSPLUGINPATH.'imanager/class/im.setup.class.php');
// manager
include(GSPLUGINPATH.'imanager/class/im.class.php');
// backend controller
include(GSPLUGINPATH.'imanager/class/im.backend.controller.class.php');
// category
include(GSPLUGINPATH.'imanager/class/im.category.class.php');
// category object type
include(GSPLUGINPATH.'imanager/class/im.category.object.php');
// category processor
include(GSPLUGINPATH.'imanager/class/im.category.processor.class.php');

// template object type
include(GSPLUGINPATH.'imanager/class/im.template.object.php');
// item object type
include(GSPLUGINPATH.'imanager/class/im.item.object.php');
// item class type
include(GSPLUGINPATH.'imanager/class/im.item.class.php');
// output
include(GSPLUGINPATH.'imanager/class/im.template.class.php');
// reporter
include(GSPLUGINPATH.'imanager/class/im.msg.reporter.class.php');

// fields controller
include(GSPLUGINPATH.'imanager/class/im.fields.class.php');
// fields object type
include(GSPLUGINPATH.'imanager/class/im.field.object.php');


/* INTERFACES */

// input interface
include(GSPLUGINPATH.'imanager/class/im.input.interface.php');
// field interface
include(GSPLUGINPATH.'imanager/class/im.field.interface.php');


/* FIELDS */

// field type long
include(GSPLUGINPATH.'imanager/class/processors/fields/im.field.longtext.php');
// field type text
include(GSPLUGINPATH.'imanager/class/processors/fields/im.field.text.php');
// field type hidden
include(GSPLUGINPATH.'imanager/class/processors/fields/im.field.hidden.php');
// field type checkbox
include(GSPLUGINPATH.'imanager/class/processors/fields/im.field.checkbox.php');
// field type long
include(GSPLUGINPATH.'imanager/class/processors/fields/im.field.dropdown.php');
// field type password
include(GSPLUGINPATH.'imanager/class/processors/fields/im.field.password.php');
// field type editor
include(GSPLUGINPATH.'imanager/class/processors/fields/im.field.editor.php');
// field type bild upload
include(GSPLUGINPATH.'imanager/class/processors/fields/im.field.imageupload.php');


/* INPUTS */

// input type longtext
include(GSPLUGINPATH.'imanager/class/processors/inputs/im.input.longtext.php');
// input type text
include(GSPLUGINPATH.'imanager/class/processors/inputs/im.input.text.php');
// input type hidden
include(GSPLUGINPATH.'imanager/class/processors/inputs/im.input.hidden.php');
// input type checkbox
include(GSPLUGINPATH.'imanager/class/processors/inputs/im.input.checkbox.php');
// input type dropdown
include(GSPLUGINPATH.'imanager/class/processors/inputs/im.input.dropdown.php');
// input type password
include(GSPLUGINPATH.'imanager/class/processors/inputs/im.input.password.php');
// input type editor
include(GSPLUGINPATH.'imanager/class/processors/inputs/im.input.editor.php');
// input type bild upload
include(GSPLUGINPATH.'imanager/class/processors/inputs/im.input.imageupload.php');

// backend
function imanager()
{
	$request = array_merge($_GET, $_POST);
	$manager = new IManager();
	// run
	$backend = $manager->backend->display($request);
	echo $backend->content;
}

function ajaxGetLists()
{
	if(isset($_GET['getcatlist']))
	{
		$request = array_merge($_GET, $_POST);
		$manager = new IManager();
		if($manager->is_admin_panel)
			echo $manager->backend->display($request);
		exit();
	} elseif (isset($_GET['getitemlist']))
	{
		$request = array_merge($_GET, $_POST);
		$manager = new IManager();
		if($manager->is_admin_panel)
			echo $manager->backend->display($request);
		exit();
	}
}

function i18nSearchImIndex()
{

	$manager = new IManager();
	$manager->item->initAll();
	$items = $manager->item->items;

	// get the array excludes
	$excludes = array_map('trim', explode(',', $manager->config->common->i18nsearchexcludes));
	$sfield = $manager->config->common->i18nsearchfield;
	$fc = new ImFields();

	foreach($items as $categoryid => $items)
	{
		if(in_array($categoryid, $excludes)) continue;
		if(!$fc->fieldsExists($categoryid)) continue;

		foreach($items as $itemid => $itemdata)
		{
			if(empty($itemdata->fields->$sfield->value)) continue;
			$id = $itemid .'.'. $categoryid;
			$title = strip_tags($itemdata->name);
			$content = html_entity_decode(strip_tags(htmlspecialchars_decode($itemdata->fields->$sfield->value)), ENT_QUOTES, 'UTF-8');
			i18n_search_index_item('im:'.$id, null, $itemdata->created, $itemdata->created, null, $title, $content);
		}
	}
}

function i18nSearchImItem($id, $language, $creDate, $pubDate, $score)
{
	if (!class_exists('I18nSearchImResult'))
	{
		global $manager;
		$strp = strpos($id, '.');
		$category = substr($id, $strp+1);

		$manager = new IManager();
		$manager->item->init($category);


		class I18nSearchImResult extends I18nSearchResultItem
		{
			protected $data = null;

			protected function get($input)
			{
				if (!$this->data)
				{
					// really ugly solution
					global $manager;

					$strp = strpos($this->id, '.');
					$item_id = str_replace('im:', '', substr($this->id, 0, $strp));
					$item = $manager->item->getItem($item_id);
					$raw_url = $manager->config->common->i18nsearch_url;

					$fieldname = !empty($manager->config->common->i18nsearch_segment) ?
						$manager->config->common->i18nsearch_segment : '';
					$slug = !empty($item->fields->$fieldname->value) ? $item->fields->$fieldname->value : $item->name;
					$url  = $manager->getSiteUrl().$raw_url.$slug;

					$fieldname = !empty($manager->config->common->i18nsearch_content) ?
						$manager->config->common->i18nsearch_content : '';
					$content = !empty($item->fields->$fieldname->value) ?
						htmlspecialchars($item->fields->$fieldname->value) : '';
				}
				switch ($input)
				{
					case 'title': return $item->name;
					case 'content': return $content;
					case 'link':  return $url;
					default: return $item->name;
				}
			}
		}
	}

	if(substr($id,0,3) == 'im:')
	{
		return new I18nSearchImResult($id, $language, $creDate, $pubDate, $score);
	}
	return null;
}

function i18nSearchImDisplay($item, $showLanguage, $showDate, $dateFormat, $numWords)
{
	if (substr($item->id, 0, 3) == 'im:')
	{
		echo '<h3><a href="'.$item->link.'" >'.htmlspecialchars($item->title). '</a></h3>';
		echo '<p>'.htmlspecialchars($item->content) . '</p>';
		return true;
	}
	return false;
}
?>
