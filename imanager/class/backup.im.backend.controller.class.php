<?php
/**
 * Plugin Name: ItemManager
 * Description: full featured  Framework.
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
class ImBackend
{
	private $im;
	protected $input;
	private $tpl;
	public $break;

	public function __construct(ImModel &$im)
	{
		if(!$im->is_admin_panel) return false;
		$this->im = $im;
	}

	/*  */
	public function display($input)
	{
		$this->input = $input;
		// there is the basic structure of the backend view
		$o = array('head' => '', 'msg' => '', 'content' => '' );
		// display category selector flag

		// first check if the category and select one to be current
		if(isset($this->input['reloader']) && isset($this->input['post-category']))
			$this->im->cp->setCategory($this->input['post-category']);
		elseif(!empty($this->input['catsender']) && !empty($this->input['cat']))
			$this->im->cp->setCategory($this->input['cat']);

		// Initialize templates now, we'll need them to build our backend
		$this->tpl = $this->im->getTemplateEngine();
		$this->tpl->init();

		$result = $this->im->runAction('ImBackendBeforeDisplay');
		if(isset($result['action']) && $result['action'] == 'replace')
			return !empty($result['value']) ? $result['value'] : '';




		// TEMPLATES ****

		/* Templates initialisieren */
		//$tpl = new ImTplEngine();
		//$tpl->init();

		/* Beispiel template nach Namen */
		//$tpl = $tpl->getTemplate('testfile');
		//$tpl = $tpl->getTemplate('name=testfile');

		/* Beispiel Template "testfile" holen, Platzhalter und language Platzhalter ersetzen */
		//$test_template = $tpl->getTemplate('testfile');
		//$test_template = $tpl->render($test_template, array('world' => 'Welt'), true);

		/* Beispiel alle Templates des Bündels "generals" holen, danach Template mit dem Namen "testfile" zeigen */
		//$test_template = $this->tpl->getTemplates('general');
		//print_r($this->tpl->getTemplate('testfile', $test_template));

		/*$test_template = $tpl->getTemplates('general');
		$my_tpl = $tpl->getTemplate('testfile', $test_template);

		$i=2;
		for($a=0; $a<$i; $a++)
		{

			$mytpl = $tpl->render(clone $my_tpl, array('bla' => $a));


			echo '<pre>';
			//print_r($mytpl);
			echo '</pre>';
		}*/

		// build tab panel template
		$o['head'] = $this->buildTabpanel();

		// errors already send?
		$msg = ImMsgReporter::msgs();



		// build edit item menu
		if(isset($this->input['edit']))
		{

			// save item
			if(isset($this->input['submit']) && !empty($this->input['categoryid']))
			{
				if(!empty($this->input['timestamp']) || !empty($this->input['id']))
				{
					$this->im->saveItem($this->input);
				}
			}

			if($this->im->cp->is_cat_exist)
				$o['content'] = $this->buildItemEditor();
		}

		// save category & settings
		elseif (isset($this->input['category_edit']))
		{
			// create new category. true for refresh
			$this->im->createCategoryByName($this->input['new_category'], true);
			$o['content'] = $this->buildCategoryEditor();
		}
		// show details of category
		elseif (isset($this->input['categorydetails']))
		{
			$o['content'] = $this->buildCategoryDetailsEditor();
		}
		// update_category
		elseif(isset($this->input['categoryupdate']))
		{
			if($this->im->updateCategory($this->input, true))
				$o['content'] = $this->buildCategoryEditor();
			else
				$o['content'] = $this->buildCategoryDetailsEditor();
		}
		// delete category
		elseif (isset($this->input['deletecategory']))
		{
			$this->im->deleteCategory($this->input['deletecategory'], true);
			$o['content'] = $this->buildCategoryEditor();
		}
		// category menu
		elseif (isset($this->input['category']))
		{
			// ajax
			if(isset($this->input['getcatlist']))
			{
				return $this->buildCategoryList();
			}
			$o['content'] = $this->buildCategoryEditor();
		}
		elseif (isset($this->input['settings_edit']))
		{
			$this->im->config->setupConfig($this->input);
			$o['content'] = $this->buildSettingsSection();
		}
		elseif (isset($this->input['settings']))
		{
			$o['content'] = $this->buildSettingsSection();
		}

		// configure custom fields
		elseif (isset($this->input['fields']))
		{
			if(isset($this->input['save']))
			{
				$this->im->createFields($this->input);
			}

			if(isset($this->input['submit']))
			{
				$this->im->saveFieldDetails($this->input);
			}


			if(isset($this->input['field']) &&
				is_numeric($this->input['field']))
			{
				$o['content'] = $this->buildFieldDetails();
			} else
			{
				$o['content'] = $this->buildFieldEditor();
			}
		}

		// show item list menu
		elseif(ImModel::$installed && !$msg)
		{
			// ajax
			if(isset($this->input['getitemlist']))
			{
				return $this->buildItemRows();
			}
			// delete item
			elseif (isset($this->input['delete']))
			{
				$this->im->deleteItem($this->input['delete'],
					$this->im->cp->currentCategory());
			}

			$o['content'] = $this->buildItemList();
		}


		$o['msg'] = $this->buildMsg();

		return $this->buildBackend($o);
	}


	public function &__get($name) { return $this->{$name};}

	//public function __set($name, $value){$this->{$name} = $value;}


	/**
	 * Displays admin tab panel
	 *
	 * @return Template object
	 */
	private function buildTabpanel()
	{
		// get temple with the name tabpanel
		$tabpanel = $this->tpl->getTemplate('tabpanel');

		// build template variables
		$tvs = array();

		// initialize the current css class to the tab-panel buttons
		$labels = array('settings', 'fields', 'category', 'edit', 'view');
		$activekey = '';
		if(!empty($this->input))
		{
			foreach($this->input as $key => $val)
			{
				if(in_array($key, $labels))
				{
					$tvs[$key] = 'class="current"';
					$activekey = $key;
				}
			}
		}
		if(empty($activekey)) $tvs['view'] = 'class="current"';

		// initialize ItemManager title
		$tvs['itemmanager-title'] = IMTITLE;

		// ok, all tvs were initialized – render template and return it to controller
		return $this->tpl->render($tabpanel, $tvs, true, array(), true);
	}


	private function buildCategorySelector()
	{
		// get temples of type "catselector" then get the form
		$form = $this->tpl->getTemplate('form', $this->tpl->getTemplates('catselector'));
		// get the copy of the option template
		$option = $this->tpl->getTemplate('option');

		$category = $this->im->category;

		$category->categories = $category->filterCategories('position', 'ASC');

		$tvs = '';
		if(!$category->countCategories() || empty($form) || empty($option))
			return false;

		// render option template
		foreach($category->categories as $key => $cat)
		{
			$tag = ($this->im->cp->currentCategory() == $cat->get('id')) ? 'selected' : '';
			$tploption = $this->tpl->render($option, array('selected' => $tag, 'value' => $cat->get('id'), 'name' => $cat->name));
			$tvs .= $tploption->content;
		}
		// render category selector form
		return $this->tpl->render($form,  array('options' => $tvs,
			'uri-add' => isset($this->input['fields']) ? '&fields' : ''), true, array(), true);
	}


	private function buildMsg()
	{
		$o = '';
		$msg = ImMsgReporter::msgs();
		if(!empty($msg))
			foreach($msg as $val) $o .= $val;
		return $o;
	}


	private function buildBackend($values)
	{
		// get "backend" temple
		$backend = $this->tpl->getTemplate('backend');
		return $this->tpl->render($backend, $values);
	}


	private function buildCategoryEditor()
	{
		// get some templates of categorylist bundle
		$categorylist = $this->tpl->getTemplates('categorylist');
		$form = $this->tpl->getTemplate('form', $categorylist);
		$row = $this->tpl->getTemplate('row', $categorylist);
		$filter = $this->tpl->getTemplate('filter', $categorylist);

		$category = $this->im->category;

		$catcount = $this->im->category->countCategories($category->categories);

		// get settings
		$configs = $this->im->config;

		if(!$category->countCategories())
			return $this->tpl->render($form,  array(
						'value' => ''), true, array(), true
			);

		$order = ($configs->backend->catorder == 'acs') ? 'ASC' : 'DESC';
		$attribut = isset($configs->backend->catorderby) ?
			safe_slash_html_input($configs->backend->catorderby) : 'position';
		$page = !empty($this->input['page']) ? $this->input['page'] : 1;
		$perpage = !empty($this->input['getcatlist']) ? intval($this->input['getcatlist']) :
			$configs->backend->maxcatperpage;

		// start item
		$start = (($page -1) * $perpage +1);

		// filter categories
		$category->categories = $category->filterCategories($attribut, $order, $start, $perpage);

		//category rows
		$tplrow = '';
		foreach($category->categories as $cat)
		{
			$ic = new ImItem();
			$ic->init($cat->get('id'));
			$count = $ic->countItems();

			$buff = $this->tpl->render($row, array(
				'cat-position' => $cat->position,
				'categoryname' => $cat->name,
				'category' => $cat->get('id'), 'count' => $count), true, array()
			);
			$tplrow .= $buff->content;
		}

		// display category filter?
		if((int) $configs->backend->catfilter == 1)
		{
			$filter = $this->tpl->render($filter, array(
					'catnumber' => $configs->backend->maxcatperpage,
					'asc' => ($configs->backend->catorder == 'asc') ? 'selected' : '',
					'desc' => ($configs->backend->catorder == 'desc') ? 'selected' : '',
					'position' => ($attribut == 'position') ? 'selected' : '',
					'name' => ($attribut == 'name') ? 'selected' : '',
					'created' => ($attribut == 'created') ? 'selected' : '',
					'updated' => ($attribut == 'updated') ? 'selected' : '',
					'nswitch' => $this->buildNumberSwitch('category')
				), true, array()
			);
		}

		// load pagination templates
		$pagination = $this->tpl->getTemplates('pagination');
		$tpls['wrapper'] = $this->tpl->getTemplate('wrapper', $pagination);
		$tpls['prev'] = $this->tpl->getTemplate('prev', $pagination);
		$tpls['prev_inactive'] = $this->tpl->getTemplate('prev', $pagination);
		$tpls['central'] = $this->tpl->getTemplate('central', $pagination);
		$tpls['central_inactive'] = $this->tpl->getTemplate('central_inactive', $pagination);
		$tpls['next'] = $this->tpl->getTemplate('next', $pagination);
		$tpls['next_inactive'] = $this->tpl->getTemplate('next_inactive', $pagination);
		$tpls['ellipsis'] = $this->tpl->getTemplate('ellipsis', $pagination);
		$tpls['secondlast'] = $this->tpl->getTemplate('secondlast', $pagination);
		$tpls['second'] = $this->tpl->getTemplate('second', $pagination);
		$tpls['last'] = $this->tpl->getTemplate('last', $pagination);
		$tpls['first'] = $this->tpl->getTemplate('first', $pagination);

		$params['page'] = $page;
		$params['items'] = $catcount;
		$params['lastpage'] = ($catcount / $configs->backend->maxcatperpage);
		$params['limit'] = $configs->backend->maxcatperpage;
		$params['pageurl'] = 'load.php?id=imanager&category&page=';

		$pagination = $this->im->buildPagination($tpls, $params);

		return $this->tpl->render($form,  array(
			'filter' => ((int) $configs->backend->catfilter == 1) ? $filter->content : '',
			'page' => $page,
			'value' => $tplrow,
			'pagination' => $pagination->content), true, array(), true
		);
	}


	// Ajax stuff
	private function buildCategoryList()
	{
		$categorylist = $this->tpl->getTemplates('categorylist');
		$row = $this->tpl->getTemplate('row', $categorylist);

		$category = $this->im->category;
		if(!$category->countCategories())
			return false;

		// get config
		$config = $this->im->config;

		$option = !empty($this->input['option']) ? safe_slash_html_input($this->input['option']) : 'ASC';
		$filterby = !empty($this->input['filterby']) ? safe_slash_html_input(strtolower($this->input['filterby'])) : 'position';
		$perpage = !empty($this->input['getcatlist']) ? intval($this->input['getcatlist']) : $config->backend->maxcatperpage;
		$page = !empty($this->input['page']) ? $this->input['page'] : 1;

		// change config properties
		$config->backend->maxcatperpage = $perpage;
		$config->backend->catorderby = $filterby;
		$config->backend->catorder = $option;
		$config->save();

		// start item
		$start = (($page -1) * $perpage +1);

		// filter category
		$category->categories = $category->filterCategories($filterby, $option, $start, $perpage);


		if(empty($category->categories))
			return false;

		if(!empty($this->input['positions']))
		{
			foreach($this->input['positions'] as $element)
			{
				if(!isset($category->categories[$element['id']]->position) || empty($element['position']))
					continue;
				if((int)$category->categories[$element['id']]->position != $element['position'])
				{
					$category->categories[$element['id']]->position = $element['position'];
					$category->categories[$element['id']]->save();
					/*$handle = fopen(dirname(__FILE__)."/log.txt", "a");
					fwrite($handle, print_r($element['position'], true));
					fclose($handle);*/
				}
			}
			// refilter output
			$category->categories = $category->filterCategories($filterby, $option, $start, $perpage, $category->categories);
		}


		//category rows
		$tplrow = '';
		foreach($category->categories as $cat)
		{
			$ic = new ImItem();
			$ic->init($cat->get('id'));
			$count = $ic->countItems();

			$buff = $this->tpl->render($row, array(
					'cat-position' => $cat->position,
					'categoryname' => $cat->name,
					'category' => $cat->get('id'), 'count' => $count), true, array());
			$tplrow .= $buff->content;
		}

		return $tplrow;

	}


	private function buildSettingsSection()
	{
		$settings = $this->tpl->getTemplates('settings');
		$form = $this->tpl->getTemplate('form', $settings);

		// get settings
		$configs = $this->im->config;

		// category
		$attribut = isset($configs->backend->catorderby) ?
			safe_slash_html_input($configs->backend->catorderby) : 'position';

		// item
		$i_attribut = isset($configs->backend->itemorderby) ?
			safe_slash_html_input($configs->backend->itemorderby) : 'i_position';

		/*// true to refresh settings
		$configs = $this->im->config;*/
		return $this->tpl->render($form,  array('maxcatname' => $configs->common->maxcatname,
				'maxfieldname' => $configs->common->maxfieldname,
				'maxitemname' => $configs->common->maxitemname,
				'catbackup' => ($configs->backend->catbackup == 1) ? ' checked ' : '',
				'fieldbackup' => ($configs->backend->fieldbackup == 1) ? ' checked ' : '',
				'catbackupdir' => $configs->backend->catbackupdir,
				'min_catbackup_days' => (intval($configs->backend->min_catbackup_days) > 0)
						? intval($configs->backend->min_catbackup_days) : 0,
				'fieldbackupdir' => $configs->backend->fieldbackupdir,
				'min_fieldbackup_days' => (intval($configs->backend->min_fieldbackup_days) > 0)
						? intval($configs->backend->min_fieldbackup_days) : 0,
				'i18nsearch' => ($configs->common->i18nsearch == 1) ? ' checked ' : '',
				'i18nsearch_field' => $configs->common->i18nsearchfield,
				'exclude_categories' => $configs->common->i18nsearchexcludes,
				'i18nsearch_url' => $configs->common->i18nsearch_url,
				'i18nsearch_segment' => $configs->common->i18nsearch_segment,
				'i18nsearch_content' => $configs->common->i18nsearch_content,


				'catfilter' => ($configs->backend->catfilter == 1) ? ' checked ' : '',
				'ten' => ($configs->backend->maxcatperpage == 10) ? 'selected' : '',
				'twenty' => ($configs->backend->maxcatperpage == 20) ? 'selected' : '',
				'thirty' => ($configs->backend->maxcatperpage == 30) ? 'selected' : '',
				'forty' => ($configs->backend->maxcatperpage == 40) ? 'selected' : '',
				'fifty' => ($configs->backend->maxcatperpage == 50) ? 'selected' : '',
				'catorder' => $configs->backend->catorder,
				'asc' => ($configs->backend->catorder == 'acs') ? 'selected' : '',
				'desc' => ($configs->backend->catorder == 'desc') ? 'selected' : '',
				'position' => ($attribut == 'position') ? 'selected' : '',
				'name' => ($attribut == 'name') ? 'selected' : '',
				'created' => ($attribut == 'created') ? 'selected' : '',
				'updated' => ($attribut == 'updated') ? 'selected' : '',

				'timeformat' => $configs->backend->timeformat,
				'itemfilter' => ($configs->backend->itemfilter == 1) ? ' checked ' : '',
				'i_ten' => ($configs->backend->maxitemperpage == 10) ? 'selected' : '',
				'i_twenty' => ($configs->backend->maxitemperpage == 20) ? 'selected' : '',
				'i_thirty' => ($configs->backend->maxitemperpage == 30) ? 'selected' : '',
				'i_forty' => ($configs->backend->maxitemperpage == 40) ? 'selected' : '',
				'i_fifty' => ($configs->backend->maxitemperpage == 50) ? 'selected' : '',
				'itemorder' => $configs->backend->itemorder,
				'i_asc' => ($configs->backend->itemorder == 'acs') ? 'selected' : '',
				'i_desc' => ($configs->backend->itemorder == 'desc') ? 'selected' : '',
				'i_position' => ($i_attribut == 'position') ? 'selected' : '',
				'i_name' => ($i_attribut == 'name') ? 'selected' : '',
				'i_created' => ($i_attribut == 'created') ? 'selected' : '',
				'i_updated' => ($i_attribut == 'updated') ? 'selected' : '',
				'i_label' => ($i_attribut == 'label') ? 'selected' : '',
				'i_active' => ($i_attribut == 'active') ? 'selected' : '',

				'itemactive' => ($configs->backend->itemactive == 1) ? ' checked ' : '',
				'uniqueitemname' => ($configs->backend->unique_itemname == 1) ? ' checked ' : '',
				'min_tmpimage_days' => (intval($configs->backend->min_tmpimage_days) > 0) ? intval($configs->backend->min_tmpimage_days) : 0,
				'itembackup' =>  ($configs->backend->itembackup == 1) ? ' checked ' : '',
				'min_itembackup_days' => (intval($configs->backend->min_itembackup_days) > 0) ? intval($configs->backend->min_itembackup_days) : 0,
				'itembackupdir' => $configs->backend->itembackupdir,


			), true, array(), true

		);
	}


	private function buildCategoryDetailsEditor()
	{
		$settings = $this->tpl->getTemplates('detailscategory');
		$form = $this->tpl->getTemplate('form', $settings);

		// is category id available
		$id = isset($this->input['categorydetails']) ? $this->input['categorydetails'] : false;
		if(!$id) $id = isset($this->input['id']) ? $this->input['id'] : false;
		if(!$id) return false;

		$cat = $this->im->category->getCategory($id);

		if(!$cat)
		{
			ImMsgReporter::setClause('err_category_id_unknown', array());
			return false;
		}

		// prepare some values
		$id = $cat->get('id');
		$name = !empty($this->input['name']) ? $this->input['name'] : $cat->get('name');
		$slug = !empty($this->input['slug']) ? $this->input['slug'] : $cat->get('slug');
		$positon = !empty($this->input['position']) ? $this->input['position'] : $cat->get('position');
		$position = !empty($position) ? $position : $cat->get('id');

		// get settings
		$configs = $this->im->config;

		return $this->tpl->render($form,  array('catid' => $id,
				'catname' => $name,
				'catslug' => $slug,
				'catposition' => $position,
				'created' => date((string) $configs->backend->timeformat, (int) $cat->get('created')),
				'updated' => ((int) $cat->get('updated') > 0) ?
						date((string) $configs->backend->timeformat, (int) $cat->get('updated')) : '',
				'categoryid' => $id), true, array(), true

		);
	}



	private function buildFieldEditor()
	{
		$result = $this->im->runAction('ImBackendBeforeRenderFieldEditor');
		if(isset($result['action']) && $result['action'] == 'replace')
			return !empty($result['value']) ? $result['value'] : '';

		// load fied editor templates
		$fields = $this->tpl->getTemplates('fields');
		$form = $this->tpl->getTemplate('form', $fields);
		$row = $this->tpl->getTemplate('row', $fields);
		$js = $this->tpl->getTemplate('js', $fields);
		$link = $this->tpl->getTemplate('link', $fields);
		$details = $this->tpl->getTemplate('details', $fields);

		// check is the current category valid
		if(!$this->im->cp->isCategoryValid()) return;

		// field file already exist
		$cf = new ImFields();

		if(!$cf->fieldsExists($this->im->cp->currentCategory()))
			if(!$cf->createFields($this->im->cp->currentCategory()))
				return false;

		/* initialize all fields of the current category. After this, they are
		   stored in $cf->fields array */
		$cf->init($this->im->cp->currentCategory());

		// order fields by position
		$cf->fields = $cf->filterFields('position', 'ASC');

		// define template buffers
		$tplrow = '';
		$rowbuffer = null;
		$tploptions = null;
		$catselector = null;

		// render category selector template
		$catselector = $this->buildCategorySelector();

		// Ok, there are no fields available for this category, so just show the hiden stuff
		if(!$cf->fields)
		{
			// render row template
			$rowbuffer = $this->tpl->render($row, array('tr-class' => 'hidden',
					'i' => 0,
					'id' => '',
					'key' => '',
					'label' => '',
					'area-display' => 'display: none',
					'text-options' => ''), true
			);
			$tplrow .= $rowbuffer->content;
			// replace the form placeholders and return
			return $this->tpl->render($form,  array('catselector' => $catselector, 'categorie_items' => $tplrow,
					'cat' => $this->im->cp->currentCategory()), true, array(), true
			);
		}

		// Hmmm Ok, some fields seems to be there, try to display them
		$i = 0;
		foreach($cf->fields as $f)
		{
			$options = '';
			$isdropdown = false;
			if(isset($f->type) && $f->type == 'dropdown')
				$isdropdown = true;
			//$options = "\r\n";
			if ($isdropdown && count($f->options) > 0)
			{
				foreach ($f->options as $option)
					$options .= $option . "\r\n";
			}

			// render details link
			$tpldetails = $this->tpl->render($details, array('field-id' => $f->get('id')), true, array());

			$rowbuffer = $this->tpl->render($row, array('selected-'.$f->type => ' selected="selected" '));
			$rowbuffer = $this->tpl->render($rowbuffer, array('tr-class' => 'sortable',
					'i' => $i,
					'field-details' => $tpldetails->content,
					'id' => $f->get('id'),
					'key' => isset($f->name) ? $f->name : '',
					'label' => isset($f->label) ? $f->label : '',
					'area-display' => !$isdropdown ? 'display:none' : '',
					'text-options' => isset($f->default) ? $f->default : '',
					'area-options' => $options), true
			);

			$tplrow .= $rowbuffer->content;
		}
		// render hiden stuff
		$rowbuffer = $this->tpl->render($row, array('tr-class' => 'hidden',
				'i' => 0,
				'id' => '',
				'key' => '',
				'label' => '',
				'area-display' => 'display: none',
				'text-options' => ''), true
		);
		$tplrow .= $rowbuffer->content;

		// replace the form placeholders
		return $this->tpl->render($form,  array(
				'catselector' => $catselector,
				'categorie_items' => $tplrow,
				'cat' => $this->im->cp->currentCategory()), true, array(), true
		);
	}


	private function buildFieldDetails()
	{
		// get fielddetail template templates
		$fielddetails = $this->tpl->getTemplates('fielddetails');
		$form = $this->tpl->getTemplate('form', $fielddetails);

		$cf = new ImFields();
		$cf->init($this->im->cp->currentCategory());
		// get current field by id
		$currfield = $cf->getField(intval($this->input['field']));


		if(!$currfield)
		{
			// ERROR FIELD NOT FOUND
			return false;
		}

		// replace the form placeholders and return
		return $this->tpl->render($form,  array(
				'field-id' => $currfield->get('id'),
				'field_name' => !empty($currfield->name) ? $currfield->name : '',
				'field_label' => !empty($currfield->label) ? $currfield->label : '',
				'field_type' => !empty($currfield->type) ? $currfield->type : '',
				'fielddefault' => !empty($currfield->default) ? $currfield->default : '',
				'fieldinfo' => !empty($currfield->info) ? $currfield->info : '',
				'fieldrequired' => ($currfield->required == 1) ? 'checked' : '',
				'min_field_input' => !empty($currfield->minimum) ? intval($currfield->minimum) : '',
				'max_field_input' => !empty($currfield->maximum) ? intval($currfield->maximum) : '',
				'area_css' => !empty($currfield->areacss) ? $currfield->areacss : '',
				'label_css' => !empty($currfield->labelcss) ? $currfield->labelcss : '',
				'field_css' => !empty($currfield->fieldcss) ? $currfield->fieldcss : '',
			), true, array(), true
		);
	}


	private function buildNumberSwitch($obj)
	{
		// get numberswitch templates
		$nrswitch = $this->tpl->getTemplates('numberswitch');
		$container = $this->tpl->getTemplate('container', $nrswitch);
		$inactive = $this->tpl->getTemplate('inactive', $nrswitch);
		$active = $this->tpl->getTemplate('active', $nrswitch);

		// get settings
		$configs = $this->im->config;

		// there are the default navigation points
		$defauls = array(10, 20, 30, 40, 50);


		if($obj == 'category')
		{
			// and max per page parameter
			$maxperpage = empty($configs->backend->maxcatperpage) ? 10 : intval($configs->backend->maxcatperpage);

			// noting entered by user, build standard template
			if(in_array($maxperpage, $defauls))
			{
				$tplbuffer[0] = ($maxperpage == $defauls[0]) ?
					$this->tpl->render($active, array('href' => '#', 'number' => $maxperpage), true, array(), true) :
					$this->tpl->render($inactive, array('href' => '#', 'number' => $defauls[0]), true, array(), true);

				$tplbuffer[1] = ($maxperpage == $defauls[1]) ?
					$this->tpl->render($active, array('href' => '#', 'number' => $defauls[1]), true, array(), true) :
					$this->tpl->render($inactive, array('href' => '#', 'number' => $defauls[1]), true, array(), true);

				$tplbuffer[2] = ($maxperpage == $defauls[2]) ?
					$this->tpl->render($active, array('href' => '#', 'number' => $defauls[2]), true, array(), true) :
					$this->tpl->render($inactive, array('href' => '#', 'number' => $defauls[2]), true, array(), true);

				$tplbuffer[3] = ($maxperpage == $defauls[3]) ?
					$this->tpl->render($active, array('href' => '#', 'number' => $defauls[3]), true, array(), true) :
					$this->tpl->render($inactive, array('href' => '#', 'number' => $defauls[3]), true, array(), true);

				$tplbuffer[4] = ($maxperpage == $defauls[4]) ?
					$this->tpl->render($active, array('href' => '#', 'number' => $defauls[4]), true, array(), true) :
					$this->tpl->render($inactive, array('href' => '#', 'number' => $defauls[4]), true, array(), true);


				// replaze tvs with real valies (<li> tags)
				return $this->tpl->render($container, array(
						'1' => $tplbuffer[0],
						'2' => $tplbuffer[1],
						'3' => $tplbuffer[2],
						'4' => $tplbuffer[3],
						'5' => $tplbuffer[4]
					), true, array(), true
				);
			}
		} elseif($obj == 'item')
		{
			// and max per page parameter
			$maxperpage = empty($configs->backend->maxitemperpage) ? 20 : intval($configs->backend->maxitemperpage);

			// noting entered by user, build standard template
			if(in_array($maxperpage, $defauls))
			{
				$tplbuffer[0] = ($maxperpage == $defauls[0]) ?
					$this->tpl->render($active, array('href' => '#', 'number' => $maxperpage), true, array(), true) :
					$this->tpl->render($inactive, array('href' => '#', 'number' => $defauls[0]), true, array(), true);

				$tplbuffer[1] = ($maxperpage == $defauls[1]) ?
					$this->tpl->render($active, array('href' => '#', 'number' => $defauls[1]), true, array(), true) :
					$this->tpl->render($inactive, array('href' => '#', 'number' => $defauls[1]), true, array(), true);

				$tplbuffer[2] = ($maxperpage == $defauls[2]) ?
					$this->tpl->render($active, array('href' => '#', 'number' => $defauls[2]), true, array(), true) :
					$this->tpl->render($inactive, array('href' => '#', 'number' => $defauls[2]), true, array(), true);

				$tplbuffer[3] = ($maxperpage == $defauls[3]) ?
					$this->tpl->render($active, array('href' => '#', 'number' => $defauls[3]), true, array(), true) :
					$this->tpl->render($inactive, array('href' => '#', 'number' => $defauls[3]), true, array(), true);

				$tplbuffer[4] = ($maxperpage == $defauls[4]) ?
					$this->tpl->render($active, array('href' => '#', 'number' => $defauls[4]), true, array(), true) :
					$this->tpl->render($inactive, array('href' => '#', 'number' => $defauls[4]), true, array(), true);


				// replaze tvs with real valies (<li> tags)
				return $this->tpl->render($container, array(
						'1' => $tplbuffer[0],
						'2' => $tplbuffer[1],
						'3' => $tplbuffer[2],
						'4' => $tplbuffer[3],
						'5' => $tplbuffer[4]
					), true, array(), true
				);
			}
		}
	}




	private function buildItemList()
	{
		$result = $this->im->runAction('ImBackendBeforeRenderItemList');
		if(isset($result['action']) && $result['action'] == 'replace')
			return !empty($result['value']) ? $result['value'] : '';

		// get numberswitch templates
		$itemlist = $this->tpl->getTemplates('itemlist');
		$form = $this->tpl->getTemplate('form', $itemlist);
		$row = $this->tpl->getTemplate('row', $itemlist);
		$filter = $this->tpl->getTemplate('filter', $itemlist);
		$active = $this->tpl->getTemplate('active', $itemlist);
		$inactive = $this->tpl->getTemplate('inactive', $itemlist);
		$noitems = $this->tpl->getTemplate('noitemsrow', $itemlist);
		$filteroptiontpl = $this->tpl->getTemplate('filteroption', $itemlist);

		// get settings
		$configs = $this->im->config;

		// get categories
		$cat = $this->im->category->categories;
		// check if object exist
		if(!$cat) return false;
		// get curren category object
		$curcat = $cat[$this->im->cp->currentCategory()];

		// Initialize items of current category
		$ic = new ImItem();
		$ic->init($this->im->cp->currentCategory());

		$count = $ic->countItems();

		$filterby = !empty($configs->backend->itemorderby) ? $configs->backend->itemorderby : 'position';
		$filteroption = !empty($configs->backend->itemorder) ? $configs->backend->itemorder : 'desc';
		$page = !empty($this->input['page']) ? $this->input['page'] : 1;
		$maxitemperpage = !empty($configs->backend->maxitemperpage) ? $configs->backend->maxitemperpage : 20;

		// start item
		$start = (($page -1) * $maxitemperpage +1);
		// order items
		$ic->filterItems($filterby, $filteroption, $start, $maxitemperpage);

		// define template buffers
		$catselector = '';
		$lines = '';

		// render category selector template
		$catselector = $this->buildCategorySelector();

		// activate / deactivate items
		if(!empty($this->input['activate']))
		{
			$id = intval($this->input['activate']);
			$item = $ic->getItem($id);
			if($item)
			{
				$item->active = ($item->active == 1) ? '' : 1;
				$item->save();
			}
		}

		// display item filter?
		if((int) $configs->backend->itemfilter == 1)
		{

			// build field options for items of current category
			$fieldoptions = new Template();
			$fieldoptions->push($this->tpl->render($filteroptiontpl, array(
					'fieldname' => 'name',
					'selected' => (!empty($configs->backend->filterbyfield) &&
							(string) $configs->backend->filterbyfield == 'name') ? 'selected' : '',
					'fieldlabel' => 'Item name')
			));
			// initialize fields
			$fc = new ImFields();
			if($fc->fieldsExists($curcat->get('id')))
			{
				$fc->init($curcat->get('id'));
				foreach($fc->fields as $fname => $fval)
				{
					$fieldoptions->push($this->tpl->render($filteroptiontpl, array(
						'fieldname' => $fname,
						'selected' => (!empty($configs->backend->filterbyfield) &&
						(string) $configs->backend->filterbyfield == (string) $fname) ? 'selected' : '',
						'fieldlabel' => !empty($fval->label) ? $fval->label : $fname)
					));
					//$fieldoptions .= $optionsbuff->content;
				}
			}

			$filter = $this->tpl->render($filter, array(
					'itemnumber' => $maxitemperpage,
					'asc' => ($filteroption == 'asc') ? 'selected' : '',
					'desc' => ($filteroption == 'desc') ? 'selected' : '',
					'position' => ($filterby == 'position') ? 'selected' : '',
					'name' => ($filterby == 'name') ? 'selected' : '',
					'label' => ($filterby == 'label') ? 'selected' : '',
					'active' => ($filterby == 'active') ? 'selected' : '',
					'created' => ($filterby == 'created') ? 'selected' : '',
					'updated' => ($filterby == 'updated') ? 'selected' : '',
					'nswitch' => $this->buildNumberSwitch('item'),
					'fieldoptions' => $fieldoptions->content,//$fieldoptions,
					'eq' => (!empty($configs->backend->filter) &&
							(string) $configs->backend->filter == 'eq') ? 'selected' : '',
					'geq' => (!empty($configs->backend->filter) &&
							(string) $configs->backend->filter == 'geq') ? 'selected' : '',
					'leq' => (!empty($configs->backend->filter) &&
							(string) $configs->backend->filter == 'leq') ? 'selected' : '',
					'g' => (!empty($configs->backend->filter) &&
							(string) $configs->backend->filter == 'g') ? 'selected' : '',
					'l' => (!empty($configs->backend->filter) &&
							(string) $configs->backend->filter == 'l') ? 'selected' : '',
					'filtervalue' => !empty($configs->backend->filtervalue) ? $configs->backend->filtervalue : ''
				), true, array()
			);
		} else
			$filter = false;

		if(empty($ic->items))
		{
			$rowbuff = $this->tpl->render($noitems, array(), true);
			$lines .= $rowbuff->content;
		} else
		{
			// build item rows
			foreach($ic->items as $itemkey => $itemvalue)
			{
				$rowbuff = $this->tpl->render($row, array(
					'page' => $page,
					'item-id' => $itemkey,
					'item-position' => !empty($itemvalue->position) ? $itemvalue->position : $itemkey,
					'item-name' => !empty($itemvalue->name) ? $itemvalue->name : '',
					'item-created' =>  !empty($itemvalue->created)?date((string) $configs->backend->timeformat, (int) $itemvalue->created):'',
					'item-updated' =>  !empty($itemvalue->updated)?date((string) $configs->backend->timeformat, (int) $itemvalue->updated):'',
					'item-checkuncheck' => ($itemvalue->active == 1) ? $active->content : $inactive->content
				), true);

				$lines .= $rowbuff->content;
			}
		}

		// build pagination
		// load pagination templates
		$pagination = $this->tpl->getTemplates('pagination');
		$tpls['wrapper'] = $this->tpl->getTemplate('wrapper', $pagination);
		$tpls['prev'] = $this->tpl->getTemplate('prev', $pagination);
		$tpls['prev_inactive'] = $this->tpl->getTemplate('prev', $pagination);
		$tpls['central'] = $this->tpl->getTemplate('central', $pagination);
		$tpls['central_inactive'] = $this->tpl->getTemplate('central_inactive', $pagination);
		$tpls['next'] = $this->tpl->getTemplate('next', $pagination);
		$tpls['next_inactive'] = $this->tpl->getTemplate('next_inactive', $pagination);
		$tpls['ellipsis'] = $this->tpl->getTemplate('ellipsis', $pagination);
		$tpls['secondlast'] = $this->tpl->getTemplate('secondlast', $pagination);
		$tpls['second'] = $this->tpl->getTemplate('second', $pagination);
		$tpls['last'] = $this->tpl->getTemplate('last', $pagination);
		$tpls['first'] = $this->tpl->getTemplate('first', $pagination);

		$params['page'] = $page;
		$params['items'] = $count;
		$params['pageurl'] = 'load.php?id=imanager&page=';

		$pagination = $this->im->buildPagination($tpls, $params);




		return $this->tpl->render($form, array(
			'catselector' => $catselector,
			'page' => $page,
			'itemfilter' => ($filter) ? $filter->content : '',
			'content' => $lines,
			'count' => $count,
			'category' => $curcat->name,
			'pagination' => $pagination->content), true, array(), true
		);
	}


	// Ajax stuff
	private function buildItemRows()
	{

		$result = $this->im->runAction('ImBackendBeforeRenderItemRows');
		if(isset($result['action']) && $result['action'] == 'replace')
			return !empty($result['value']) ? $result['value'] : '';

		$itemlist = $this->tpl->getTemplates('itemlist');
		$row = $this->tpl->getTemplate('row', $itemlist);
		$active = $this->tpl->getTemplate('active', $itemlist);
		$inactive = $this->tpl->getTemplate('inactive', $itemlist);

		// get settings
		$configs = $this->im->config;

		// Initialize items of current category
		$ic = new ImItem();
		$ic->init($this->im->cp->currentCategory());

		$count = $ic->countItems();

		$filteroption = !empty($this->input['option']) ? safe_slash_html_input($this->input['option']) : 'desc';
		$filterby = !empty($this->input['filterby']) ? safe_slash_html_input(strtolower($this->input['filterby'])) : 'position';
		$filterbyfield = !empty($this->input['filterbyfield']) ? safe_slash_html_input(strtolower($this->input['filterbyfield'])) : '';
		$maxitemperpage = !empty($this->input['getitemlist']) ? intval($this->input['getitemlist']) : $config->backend->maxitemperpage;
		$filter = !empty($this->input['filter']) ? safe_slash_html_input(strtolower($this->input['filter'])) : '';
		$filtervalue = !empty($this->input['filtervalue']) ? safe_slash_html_input($this->input['filtervalue']) : '';
		$page = !empty($this->input['page']) ? $this->input['page'] : 1;

		// change config properties
		$configs->backend->maxitemperpage = $maxitemperpage;
		$configs->backend->itemorderby = $filterby;
		$configs->backend->itemorder = $filteroption;
		$configs->backend->filterbyfield = $filterbyfield;
		$configs->backend->filter = $filter;
		$configs->backend->filtervalue = $filtervalue;
		$configs->save();

		// template
		$lines = '';

		// start item
		$start = (($page -1) * $maxitemperpage +1);

		$fc = new ImFields();
		if($fc->fieldsExists($this->im->cp->currentCategory()))
		{
			$fc->init($this->im->cp->currentCategory());

			if(!empty($configs->backend->filterbyfield) && ($fc->fieldNameExists((string) $configs->backend->filterbyfield)
				|| $configs->backend->filterbyfield == 'name') &&
				!empty($configs->backend->filter) && !empty($configs->backend->filtervalue))
			{
				if($configs->backend->filter == 'eq') $filter = '=';
				elseif($configs->backend->filter == 'geq') $filter = '>=';
				elseif($configs->backend->filter == 'leq') $filter = '<=';
				elseif($configs->backend->filter == 'l') $filter = '<';
				elseif($configs->backend->filter == 'g') $filter = '>';

				$query = $configs->backend->filterbyfield.$filter.$configs->backend->filtervalue;

				//echo $query; exit();

				$ic->items = $ic->getItems((string)$query, $start, (int) $maxitemperpage);

			}
		}

		// change position of items
		$ic->filterItems($filterby, $filteroption, $start, $maxitemperpage);

		if(!empty($this->input['positions']) && !empty($ic->items))
		{
			/*$handle = fopen(dirname(__FILE__)."/log.txt", "a");
			fwrite($handle, print_r($this->input, true));
			fclose($handle);*/

			foreach($this->input['positions'] as $element)
			{
					if(!isset($ic->items[$element['id']]->position) || !isset($element['position']))
						continue;
					if($ic->items[$element['id']]->position != $element['position'])
					{
						$ic->items[$element['id']]->position = $element['position'];
						$ic->items[$element['id']]->save();
					}
			}
			// refilter output
			$ic->filterItems($filterby, $filteroption);
		}


		// nothing was found
		if(empty($ic->items))
			return '&nbsp;';

		// build item rows
		foreach($ic->items as $itemkey => $itemvalue)
		{
			$rowbuff = $this->tpl->render($row, array(
				'page' => $page,
				'item-id' => $itemkey,
				'item-position' => !empty($itemvalue->position) ? $itemvalue->position : $itemkey,
				'item-name' => !empty($itemvalue->name) ? $itemvalue->name : '',
				'item-created' =>  !empty($itemvalue->created)?date((string) $configs->backend->timeformat, (int) $itemvalue->created):'',
				'item-updated' =>  !empty($itemvalue->updated)?date((string) $configs->backend->timeformat, (int) $itemvalue->updated):'',
				'item-checkuncheck' => ($itemvalue->active == 1) ? $active->content : $inactive->content
			), true);

			$lines .= $rowbuff->content;
		}

		return $lines;
	}


	private function buildItemEditor()
	{
		$result = $this->im->runAction('ImBackendBeforeRenderItemEditor');
		if(isset($result['action']) && $result['action'] == 'replace')
			return !empty($result['value']) ? $result['value'] : '';

		$itemeditor = $this->tpl->getTemplates('itemeditor');
		$form = $this->tpl->getTemplate('form', $itemeditor);
		$fieldarea = $this->tpl->getTemplate('fieldarea', $itemeditor);
		$infotext = $this->tpl->getTemplate('infotext', $itemeditor);
		$required = $this->tpl->getTemplate('required', $itemeditor);

		$id = !empty($this->input['edit']) ? intval($this->input['edit']) : null;
		if(is_null($id))
			$id = !empty($this->input['id']) ? intval($this->input['id']) : null;
		if(ImMsgReporter::isError())
			$id = !empty($this->input['id']) ? intval($this->input['id']) : null;

		$categoryid = $this->im->cp->currentCategory();

		// try to get the current page for our back link
		$backpage = !empty($this->input['page']) ? intval($this->input['page']) : 1;
		$stamp = !empty($this->input['timestamp']) ? $this->input['timestamp'] : time();


		// Initialize items of current category
		$ic = new ImItem();
		$ic->init($this->im->cp->currentCategory());

		$curitem = $ic->getItem($id);

		// new item
		if(!$curitem)
		{
			$curitem = new Item($this->im->cp->currentCategory());
			$active = $this->im->config->backend->itemactive;
		} else
			$active = $curitem->active;

		// Initialize fields
		$fc = new ImFields();
		$fc->init($this->im->cp->currentCategory());

		$fields = $fc->filterFields('position', 'asc');

		$tplfields = new Template();
		if($fields)
		{
			foreach($fields as $fieldname => $field)
			{
				// Input
				$inputClassName = 'Input'.$field->type;
				$InputType = new $inputClassName($fields[$fieldname]);
				$output = $InputType->prepareOutput();

				// Field
				$fieldClassName = 'Field'.$field->type;
				$fieldType = new $fieldClassName($this->tpl);
				$fieldType->name = $fieldname;
				$fieldType->id = $fieldType->name;


				// hidden
				if($field->type == 'hidden')
					continue;

				// dropdown
				if($field->type == 'dropdown')
					$fieldType->options = $field->options;

				// image upload
				if($field->type == 'imageupload')
				{
					$fieldType->categoryid = $this->im->cp->currentCategory();
					$fieldType->itemid = $id;
					$fieldType->timestamp = $stamp;
				}

				foreach($output as $outputkey => $outputvalue)
				{
					$fieldType->class = $field->type.'-field';

					if(!empty($field->fieldcss))
						$fieldType->style = $field->fieldcss;

					if(is_array($outputvalue))
					{
						$fieldType->$outputkey = array();
						$counter = 0;
						if(!isset($curitem->fields->$fieldname->$outputkey))
							continue;
						foreach($curitem->fields->$fieldname->$outputkey as $arrkey => $arrval)
						{
							$fieldType->{$outputkey}[] = (string) $curitem->fields->$fieldname->{$outputkey}[$counter];
							$counter++;
						}
					} else
						$fieldType->$outputkey = !empty($curitem->fields->$fieldname->$outputkey) ? (string) $curitem->fields->$fieldname->$outputkey : '';

					if(ImMsgReporter::isError())
						$fieldType->$outputkey = !empty($this->input[$fieldType->name]) ? $this->input[$fieldType->name] : '';
				}

				// set default field values
				if(empty($fieldType->value) && !empty($field->default))
					$fieldType->value = (string) $field->default;

				$tplinfotext = new Template();
				if(!empty($field->info))
					$tplinfotext = $this->tpl->render($infotext, array('infotext' => $field->info));

				$tplrequired = new Template();
				if(!empty($field->required) && $field->required == 1)
					$tplrequired = $this->tpl->render($required, array());

				if($field->type != 'chunk')
				{
					$tplfields->push($this->tpl->render($fieldarea, array(
						'fieldid' =>  $field->name,
						'label' => $field->label,
						'infotext' => $tplinfotext->content,
						'area-style' => !empty($field->areacss) ? ' style="'.$field->areacss.'"' : '',
						'label-style' => !empty($field->labelcss) ? ' style="'.$field->labelcss.'"' : '',
						'required' => $tplrequired->content,
						'field' => $fieldType->render()->content)
						)
					);
				} else
				{
					$tplfields->push($fieldType->render());
				}
			}
		}

		$form = $this->tpl->render($form, array(
				'item-id' => $id,
				'position' => !empty($curitem->position) ? $curitem->position : '',
				'checked' => ($active > 0) ? ' checked ' : '',
				'back-page' => $backpage,
				'timestamp' => $stamp,
				'currentcategory' => $this->im->cp->currentCategory(),
				'itemname' => !empty($curitem->name) ? $curitem->name : '',
				'custom-fields' => $tplfields->content,
				'created' => ((int) $curitem->created > 0) ?
						date((string) $this->im->config->backend->timeformat, (int) $curitem->created) : '',
				'updated' => ($curitem->updated > 0) ?
						date((string) $this->im->config->backend->timeformat, (int) $curitem->updated) : ''
			)
		);

		return $this->tpl->render($form, array(), true, array(), true);

	}




}
?>