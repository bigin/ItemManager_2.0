<?php 
/**
* ~Model~
*/
class ImModel
{
	public $is_admin_panel;
	private $setup;

	// new
	public $config;

	protected $categories;
	// category processor
	public $cp;

	public static $installed;

	public $item;
	public $category;
	public $field;

	public $hooks;

	public static $action = array();
	//use ImObserver;


	public function __construct()
	{

		self::$installed = false;

		// get all categories
		$this->category = new ImCategory($this);
		$this->category->init();

		// category controller
		$this->cp = new ImCategoryProcessor($this->category);
		// alle categorien einlesen

		// field
		$field = new ImFields();



		// initialize settup class
		$this->config = new ImSetup();

		// check if user inside admin panel
		$this->is_admin_panel = (!defined('IN_GS')) ? false : true;

		// start SETUP Procedure
		if (!file_exists(ITEMDATA))
		{
			if($this->config->setup())
				if(!file_exists(IM_CONFIG_FILE))
				{
					if($this->config->setupConfig())
					{
						$this->preferencesRefresh();
						self::$installed = true;
					}
				}
		} else
		{
			self::$installed = true;
		}

		// if categories have not yet been created
		if(!$this->cp->is_cat_exist && !self::$installed)
		{
			if(isset($this->input['view']))
			{
				ImMsgReporter::setClause('no_items_yet');
			} elseif(isset($this->input['edit']))
			{
				ImMsgReporter::setClause('no_category_created',
					array('noun' => ImMsgReporter::getClause('elements')));
			} elseif(isset($this->input['fields']))
			{
				ImMsgReporter::setClause('no_fields_yet');
			}
		}

		// initialise our classes
		$this->item = new ImItem();
		$this->backend = new ImBackend($this);

		global $plugins;

		$actions = array(
			'ImManagerBeforeMethods',
			'ImBackendBeforeDisplay',
			'ImBackendBeforeRenderFieldEditor',
			'ImBackendBeforeRenderItemRows',
			'ImBackendBeforeRenderItemList',
			'ImBackendBeforeRenderItemEditor',
			'ImBeforeItemSave',
			'ImAfterItemSave',
		);

		foreach($plugins as $key => $plugin)
		{
			if(in_array($plugin['hook'], $actions))
			{
				$plugins[$key]['args']['imanager'] = & $this;
			}
		}

		exec_action('ImActivated');

	}


	// refresh process preferencess FUNKTION BITTE NOCH ÄNDERN ODER LÖSCHEN
	public function preferencesRefresh() { self::$config = getXML(IM_CONFIG_FILE);}


	/**
	 * Deletes the category and ther fields and items
	 *
	 * @param integer $cat, category id
	 * @param bool $refresh
	 * @return bool
	 */
	public function deleteCategory($cat, $refresh=false)
	{
		if(empty($cat)) return false;

		if(!is_numeric($cat)) return false;

		$cat = $this->category->getCategory($cat);

		if(!$cat)
		{
			ImMsgReporter::setClause('err_deleting_category', array(
					'errormsg' => ImMsgReporter::getClause('err_category_not_exists', array()))
			);
			return false;
		}


		$fc = $this->field;
		$fc->init($cat->get('id'));

		// try to create fields backup of the category to be deleted
		if(intval($this->config->backend->fieldbackup) == 1)
		{
			if(!$fc->fieldsExists($cat->get('id')))
				if(!$fc->createFields($cat->get('id')))
				{
					ImMsgReporter::setClause('save_failure', array(), true);
					return false;
				}

			if(!$this->config->createBackup(IM_FIELDS_DIR, $cat->get('id'), IM_FIELDS_FILE_SUFFIX))
			{
				ImMsgReporter::setClause('err_backup', array('backup' => $this->config->backend->fieldbackupdir), true);
				return false;
			}
		}

		// create category backup
		if(intval($this->config->backend->catbackup) == 1)
		{
			if(!$this->config->createBackup(IM_CATEGORY_DIR, $cat->get('id'), IM_CATEGORY_FILE_SUFFIX))
			{
				ImMsgReporter::setClause('err_backup', array('backup' => $this->config->backend->catbackupdir), true);
				return false;
			}
		}

		$ic = new ImItem();
		$ic->init($cat->get('id'));

		// backup items before delete category
		if(intval($this->config->backend->itembackup) == 1 && !empty($ic->items))
		{
			foreach($ic->items as $item_id => $item)
			{
				if(!$this->config->createBackup(IM_ITEM_DIR, $item_id.'.'.$item->get('categoryid'),
					IM_ITEM_FILE_SUFFIX))
				{
					ImMsgReporter::setClause('err_backup', array(
						'backup' => $this->config->backend->itembackupdir), true
					);
					return false;
				}

				// get image directory to delete
				$imagedir = IM_IMAGE_UPLOAD_DIR.$item_id.'.'.$item->get('categoryid');

				if(!$ic->destroyItem($item))
				{
					ImMsgReporter::setClause('err_item_delete', array(), true);
					return false;
				}
				/* Item has been successfully deleted, now we have to clean up the image uploads */
				$this->delTree($imagedir);
			}
		}
		// destroy category file
		if(!$this->category->destroyCategory($cat))
		{
			ImMsgReporter::setClause('err_deleting_category', array(
					'errormsg' => ImMsgReporter::getClause('err_category_file_writable', array()))
			);
			return false;
		}
		// destroy fields file
		if(!$fc->destroyFieldsFile($cat))
		{
			ImMsgReporter::setClause('err_delete_fields_file', array(), true);
			return false;
		}

		// reinitialize the categories
		if($refresh) $this->category->init();

		// reselect current category if its deleted

		ImMsgReporter::setClause('category_deleted', array('category' => $cat->name));
		return true;
	}



	public function createCategoryByName($cat, $refresh=false)
	{
		if(empty($cat)) return false;

		if(!is_string($cat)) return false;

		if(false !== strpos($cat, '='))
		{
			$data = explode('=', $cat, 2);
			$key = strtolower(trim($data[0]));
			$val = trim($data[1]);
			if(false !== strpos($key, ' '))
				return false;

			if($key != 'name')
				return false;

			$cat = $val;
		}

		if(strlen($cat) > $this->config->common->maxcatname)
		{
			ImMsgReporter::setClause('err_category_name_length', array('count' => $this->config->common->maxcatname));
			return false;
		}
		// CHECK here category name
		$new_cat = new Category();
		$new_cat->set('name', str_replace('"', '\'', $cat));

		$new_cat->slug = self::toAscii($cat);

		// do not save category if name already exists
		if(!$this->category->getCategory('name='.safe_slash_html($new_cat->name)))
			$new_cat->save();
		else
		{
			ImMsgReporter::setClause('err_category_name_exists', array());
			return false;
		}

		// reinitialize categories
		if($refresh) $this->category->init();

		ImMsgReporter::setClause('successfull_category_created', array(
			'category' => safe_slash_html($new_cat->name))
		);
		return true;
	}


	public function updateCategory($input, $refresh=false)
	{
		if(empty($input)) return false;
		if(empty($input['id']) || !is_numeric($input['id'])) return false;

		$cat = $this->category->getCategory($input['id']);

		if(!$cat)
		{
			ImMsgReporter::setClause('err_updating_category', array(
					'errormsg' => ImMsgReporter::getClause('err_category_not_exists', array()))
			);
			return false;
		}

		$flag = true;

		if(strlen($input['name']) > $this->config->common->maxcatname)
		{
			ImMsgReporter::setClause('err_category_name_length', array('count' => $this->config->common->maxcatname));
			return false;
		}
		if(str_replace('"', '\'', $input['name']) != $cat->get('name'))
		{
			if(!$this->category->getCategory('name='.safe_slash_html(str_replace('"', '\'', $input['name']))))
			{
				$cat->set('name', safe_slash_html(str_replace('"', '\'', $input['name'])));
				$flag = true;
			} else
			{
				ImMsgReporter::setClause('err_category_name_exists', array());
				return false;
			}
		}
		if(!is_numeric($input['position']))
		{
			ImMsgReporter::setClause('err_category_position', array());
			return false;
		}


		// set slug
		$cat->slug =  !empty($input['slug']) ? self::toAscii($input['slug']) : self::toAscii($input['name']);

		if(!empty($input['position']))
		{
			if(intval($input['position']) != intval($cat->get('position')))
			{
				$cat->set('position', intval($input['position']));
				$flag = true;
			}
		}


		if($flag)
		{
			$cat->save();
			// reinitialize categories
			if($refresh) $this->category->init();

			ImMsgReporter::setClause('successfull_category_updated', array(
					'category' => safe_slash_html($cat->name))
			);
			return true;
		}

	}




	public function createFields(array $input)
	{
		if(empty($input))
			return false;

		// Check category first
		if(!$this->cp->isCategoryValid($input['cat']))
		{
			ImMsgReporter::setClause('invalid_category', array(), true);
			return false;
		}


		$ids = array();
		$names = array();
		$labels = array();
		$types = array();
		$options = array();
		$defaults = array();
		// walk through input
		for($i=0; isset($input['cf_'.$i.'_key']); $i++)
		{
			// check the max name length
			if(!empty($input['cf_'.$i.'_key']) && $this->config->common->maxfieldname > $input['cf_'.$i.'_key'])
			{
				ImMsgReporter::setClause('err_save_fields_maxlength', array(
						'count' => intval($this->config->common->maxfieldname), true
					)
				);
				continue;
			}

			if(!empty($input['cf_'.$i.'_key']))
			{
				$ids[] = !empty($input['cf_'.$i.'_id']) ? intval($input['cf_'.$i.'_id']) : null;
				$names[] = $input['cf_'.$i.'_key'];
				$labels[] = !empty($input['cf_'.$i.'_label']) ? $input['cf_'.$i.'_label'] : '';
				$types[] = !empty($input['cf_'.$i.'_type']) ? $input['cf_'.$i.'_type'] : '';
				$options[] = !empty($input['cf_'.$i.'_options']) ? $input['cf_'.$i.'_options'] : '';
				$defaults[] = !empty($input['cf_'.$i.'_value']) ? $input['cf_'.$i.'_value'] : '';
			}
		}

		// show message when duplicate values exist, but save correctly entered names
		if(count($names) != count(array_unique($names)))
		{
			//$names = array_unique($names);
			// remove duplicate keys in other arrays
			$dupl = $this->getDuplicate($names);
			if(!empty($dupl))
			{
				foreach($dupl as $val)
				{
					unset($ids[$val]);
					unset($names[$val]);
					unset($labels[$val]);
					unset($types[$val]);
					unset($options[$val]);
					unset($defaults[$val]);
				}
			}

			ImMsgReporter::setClause('err_save_fields_unique');
		}

		$fc = $this->field;
		$fc->init($input['cat']);

		// backup fields?
		if(intval($this->config->backend->fieldbackup) == 1)
		{
			if(!$fc->fieldsExists($input['cat']))
				if(!$fc->createFields($input['cat']))
				{
					ImMsgReporter::setClause('save_failure', array(), true);
					return false;
				}

			if(!$this->config->createBackup(IM_FIELDS_DIR, $input['cat'], IM_FIELDS_FILE_SUFFIX))
			{
				ImMsgReporter::setClause('err_backup', array('backup' => $this->config->backend->fieldbackupdir), true);
				return false;
			}
		}

		// Update the field data or create new
		foreach($ids as $key => $id)
		{
			// check if fields already exists
			$field = $fc->getField($id);

			if($field)
			{
				$field->name = self::toAscii($names[$key]);
				$field->label = str_replace('"', '\'', $labels[$key]);
				$field->type = self::toAscii($types[$key]);
				$field->position = $key+1;
				$field->default = str_replace('"', '\'', $defaults[$key]);
				$field->options = array();
				if(!empty($options[$key]))
				{
					$split = preg_split("/\r?\n/", rtrim(stripslashes(str_replace('"', '\'', $options[$key]))));
					foreach($split as $option)
						$field->options[] = $option;
				}

			// field does not exist, create new field
			} else
			{
				$field = new Field($input['cat']);
				$field->name = self::toAscii($names[$key]);
				$field->label = str_replace('"', '\'', $labels[$key]);
				$field->type = self::toAscii($types[$key]);
				$field->position = $key+1;
				$field->default = str_replace('"', '\'', $defaults[$key]);
				$field->options = array();
				if(!empty($options[$key]))
				{
					$split = preg_split("/\r?\n/", rtrim(stripslashes(str_replace('"', '\'', $options[$key]))));
					foreach($split as $option)
						$field->options[] = $option;
				}
			}

			$field->save();
		}

		// remove deleted fieds
		$data = ImFields::getFieldsSaveInfo($input['cat']);
		$result = array_diff($data['ids'], $ids);
		foreach($result as $fieldkey)
		{
			$deletion = $fc->getField($fieldkey);
			if(is_object($deletion) && !$deletion->delete())
			{
				ImMsgReporter::setClause('err_delete_field', array('fieldname' => $deletion->name), true);
				return false;
			}
		}

		ImMsgReporter::setClause('save_success');
		return true;
	}





	public function saveFieldDetails($input)
	{
		$cf = $this->field;
		$cf->init($this->cp->currentCategory());
		// get current field by id
		$currfield = $cf->getField(intval($input['field']));

		if(!$currfield)
		{
			// todo: korrigieren die echte Fehlermeldung
			ImMsgReporter::setClause('err_field_id', array(), true);
			return false;
		}

		$currfield->default = !empty($input['default']) ? str_replace('"', '\'', $input['default']) : '';
		$currfield->info = !empty($input['info']) ? str_replace('"', '\'', $input['info']) : '';
		$currfield->required = (isset($input['required']) && $input['required'] > 0) ? 1 : null;
		$currfield->minimum = (isset($input['min_field_input']) && intval($input['min_field_input']) > 0)
			? intval($input['min_field_input']) : null;
		$currfield->maximum = (isset($input['max_field_input']) && intval($input['max_field_input']) > 0)
			? intval($input['max_field_input']) : null;
		$currfield->areacss = !empty($input['areacss']) ? str_replace('"', '\'', $input['areacss']) : '';
		$currfield->labelcss = !empty($input['labelcss']) ? str_replace('"', '\'', $input['labelcss']) : '';
		$currfield->fieldcss = !empty($input['fieldcss']) ? str_replace('"', '\'', $input['fieldcss']) : '';

		$currfield->save();

		ImMsgReporter::setClause('save_success');
		return true;

	}

	public static function deleteSearchIndex()
	{
		if(function_exists('delete_i18n_search_index'))
			delete_i18n_search_index();
		/*require_once(GSPLUGINPATH.'i18n_search/indexer.class.php');
		I18nSearchIndexer::deleteIndex();*/
	}


	protected function getDuplicate($arr, $clean=false)
	{
		if($clean) {
			return array_unique($arr);
		}
		$new_arr = array();
		$dups = array();
		foreach ($arr as $key => $val) {
			if (!isset($new_arr[$val])) {
				$new_arr[$val] = $key;
			} else {
				$dups[] = $key;
			}
		}
		return $dups;
	}




 	public function buildPagination(array $tpls, array $params)
	{
		$maxitemperpage = ((int) $this->config->backend->maxitemperpage > 0) ?
			$this->config->backend->maxitemperpage : 20;
		$limit = !empty($params['limit']) ? $params['limit'] : $this->config->backend->maxitemperpage;
		$adjacents = !empty($params['adjacents']) ? $params['adjacents'] : 3;
		$lastpage = !empty($params['lastpage']) ? $params['lastpage'] : ceil($params['items'] / $maxitemperpage);

		$page = !empty($params['page']) ? $params['page'] : 1;
		$start = !empty($params['start']) ? $params['start'] : 1;
		$pageurl = $params['pageurl'];
		$next = ($page+1);
		$prev = ($page-1);


		$tpl = new ImTplEngine();
		$tpl->init();
		// only one page to show
		if($lastpage <= 1)
			return $tpl->render($tpls['wrapper'], array('value' => ''), true);


		$output = new Template();

		if($page > 1)
			$output->push($tpl->render($tpls['prev'], array('href' => $pageurl . $prev), true));
		else
			$output->push($tpl->render($tpls['prev_inactive'], array(), true));

		// not enough pages to bother breaking it up
		if($lastpage < 7 + ($adjacents * 2))
		{
			for($counter = 1; $counter <= $lastpage; $counter++)
			{
				if($counter == $page)
				{
					$output->push($tpl->render($tpls['central_inactive'], array('counter' => $counter), true));
				} else
				{
					$output->push($tpl->render($tpls['central'], array(
						'href' => $pageurl . $counter, 'counter' => $counter), true)
					);
				}
			}
		// enough pages to hide some
		} elseif($lastpage > 5 + ($adjacents * 2))
		{
			// vclose to beginning; only hide later pages
			if($page < 1 + ($adjacents * 2))
			{
				for($counter = 1; $counter < 4 + ($adjacents * 2); $counter++)
				{
					if($counter == $page)
					{
						$output->push($tpl->render($tpls['central_inactive'], array('counter' => $counter), true));
					} else
					{
						$output->push($tpl->render($tpls['central'], array('href' => $pageurl . $counter,
							'counter' => $counter), true));
					}
				}
				// ...
				$output->push($tpl->render($tpls['ellipsis']));
				// sec last
				$output->push($tpl->render($tpls['secondlast'], array('href' => $pageurl . ($lastpage - 1),
					'counter' => ($lastpage - 1)), true));
				// last
				$output->push($tpl->render($tpls['last'], array('href' => $pageurl . $lastpage,
					'counter' => $lastpage), true));
			}
			// middle pos; hide some front and some back
			elseif($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2))
			{
				// first
				$output->push($tpl->render($tpls['first'], array('href' => $pageurl . '1'), true));
				// second
				$output->push($tpl->render($tpls['second'], array('href' => $pageurl . '2', 'counter' => '2'), true));
				// ...
				$output->push($tpl->render($tpls['ellipsis']));

				for($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++)
				{
					if($counter == $page)
					{
						$output->push($tpl->render($tpls['central_inactive'], array('counter' => $counter), true));
					} else
					{
						$output->push($tpl->render($tpls['central'], array('href' => $pageurl . $counter,
							'counter' => $counter), true));
					}
				}
				// ...
				$output->push($tpl->render($tpls['ellipsis']));
				// sec last
				$output->push($tpl->render($tpls['secondlast'], array('href' => $pageurl . ($lastpage - 1),
					'counter' => ($lastpage - 1)), true));
				// last
				$output->push($tpl->render($tpls['last'], array('href' => $pageurl . $lastpage,
					'counter' => $lastpage), true));
			}
			//close to end; only hide early pages
			else
			{
				// first
				$output->push($tpl->render($tpls['first'], array('href' => $pageurl . '1'), true));
				// second
				$output->push($tpl->render($tpls['second'], array('href' => $pageurl . '2', 'counter' => '2'), true));
				// ...
				$output->push($tpl->render($tpls['ellipsis']));

				for($counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter++)
				{
					if($counter == $page)
					{
						$output->push($tpl->render($tpls['central_inactive'], array('counter' => $counter), true));
					} else
					{
						$output->push($tpl->render($tpls['central'], array('href' => $pageurl . $counter,
							'counter' => $counter), true));
					}
				}
			}
		}
		//next link
		if($page < $counter - 1)
			$output->push($tpl->render($tpls['next'], array('href' => $pageurl . $next), true));
		else
			$output->push($tpl->render($tpls['next_inactive'], array(), true));

		return $tpl->render($tpls['wrapper'], array('value' => $output->content), true);

	}


	public function saveItem(&$input)
	{
		exec_action('ImBeforeItemSave');

		/* check there the user errors: If the user tried to compromise the script, we'll
		reset field values to empty and display an error message */

		// timestamp or item id required
		if(empty($input['timestamp']) && empty($input['id']))
		{
			ImMsgReporter::setClause('err_save_item_timestamp_id', array(), true);
			return false;
		}

		// check the timestamp first
		if(!empty($input['timestamp']))
		{
			if(!$this->isTimestamp($input['timestamp']))
			{
				ImMsgReporter::setClause('err_timestamp', array(), true);
				return false;
			}
		}

		$id = !empty($input['id']) ? intval($input['id']) : null;
		$categoryid = !empty($input['categoryid']) ? intval($input['categoryid']) : null;

		// is category valid?
		if(!$this->cp->isCategoryValid($categoryid))
		{
			ImMsgReporter::setClause('invalid_category', array(), true);
			return false;
		}

		// Initialize items of the passed category
		$ic = new ImItem();
		$ic->init($categoryid);

		$curitem = $ic->getItem($id);

		// new item
		if(!$curitem)
			$curitem = new Item($categoryid);

		// check required item name
		if(empty($input['name']))
		{
			ImMsgReporter::setClause('err_by_empty_field', array(
				'field' => ImMsgReporter::getClause('title', array())), true
			);
			return false;
		}

		// should the item name to be unique
		if($this->config->backend->unique_itemname == 1)
		{
			// check if item name already exist and is not the same item
			$item_by_name = $ic->getItem('name='.str_replace('"', '\'', $input['name']));
			if($item_by_name && $id != $item_by_name->get('id'))
			{
				ImMsgReporter::setClause('err_item_exists', array('name' => safe_slash_html_input(str_replace('"', '\'', $input['name']))), true);
				return false;
			}
		}

		// check item name length
		if(strlen($input['name']) > $this->config->common->maxitemname)
		{
			ImMsgReporter::setClause('err_item_name_length', array('count' => intval($this->config->common->maxitemname)));
			return false;
		}


		/* Ok, the standard procedure is completed and now we want to make the next step
		and loop through the fields of the item to save these values */

		$curitem->name = str_replace('"', '\'', $input['name']);
		$curitem->active = isset($input['active']) ? 1 : 0;

		$tmp_image_dir = '';

		foreach($curitem->fields as $fieldname => $fieldvalue)
		{

			$fieldinput = !empty($input[$fieldname]) ? str_replace('"', '\'', $input[$fieldname]) : '';

			$inputClassName = 'Input'.$fieldvalue->type;
			$InputType = new $inputClassName($curitem->fields->$fieldname);

			// handle our special fields

			// imageupload
			if($fieldvalue->type == 'imageupload')
			{
				// new item
				if(empty($input['id']) && !empty($input['timestamp']))
				{
					// pass temporary image directory
					$tmp_image_dir = IM_IMAGE_UPLOAD_DIR.'tmp_'.$input['timestamp'].'_'.$categoryid.'/';
					$fieldinput = $tmp_image_dir;
				} else
				{
					// pass image directory
					$fieldinput = IM_IMAGE_UPLOAD_DIR.intval($input['id']).'.'.$categoryid.'/';
				}

				// position is send
				if(isset($input['position']) && is_array($input['position']))
				{
					$InputType->positions = $input['position'];

					if(!file_exists($fieldinput.'config.xml'))
					{
						$xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><params></params>');
						foreach($InputType->positions as $filepos => $filename)
						{
							$xml->image[$filepos]->name = $filename;
							$xml->image[$filepos]->position = $filepos;
						}

					} else
					{
						$xml = simplexml_load_file($fieldinput.'config.xml');
						unset($xml->image);
						foreach($InputType->positions as $filepos => $filename)
						{
							$xml->image[$filepos]->name = $filename;
							$xml->image[$filepos]->position = $filepos;
						}
					}
					$xml->asXml($fieldinput.'config.xml');
				}
			} elseif($fieldvalue->type == 'password')
			{
				$InputType->confirm = !empty($input['password_confirm']) ? $input['password_confirm'] : '';
				// refill password field values if empty
				$InputType->password = !empty($curitem->fields->$fieldname->value)
					? $curitem->fields->$fieldname->value : '';
				$InputType->salt = !empty($curitem->fields->$fieldname->salt)
					? $curitem->fields->$fieldname->salt : '';
				$fieldinput = $input['password'];
			}

			$resultinput = $InputType->prepareInput($fieldinput);

			if(!isset($resultinput) || empty($resultinput) || is_int($resultinput))
			{
				// parse error
				switch ($resultinput)
				{
					case 1:
						ImMsgReporter::setClause('err_required_field', array('fieldname' => $fieldvalue->label), true);
						return false;
					case 2:
						ImMsgReporter::setClause('err_input_min_length', array('fieldname' => $fieldvalue->label,
								'count' => $fieldvalue->minimum), true
						);
						return false;
					case 3:
						ImMsgReporter::setClause('err_input_max_length', array('fieldname' => $fieldvalue->label,
								'count' => $fieldvalue->maximum), true
						);
						return false;
					case 5:
						ImMsgReporter::setClause('err_input_incomplete',
							array('fieldname' => $fieldvalue->label), true);
						return false;
					case 7:
						ImMsgReporter::setClause('err_input_comparison',
							array('fieldname' => $fieldvalue->label), true);
						return false;
				}

				// todo: error log

			}

			foreach($resultinput as $inputputkey => $inputvalue)
				$curitem->fields->$fieldname->$inputputkey = $inputvalue;
		}


		if(!$curitem->save())
		{
			ImMsgReporter::setClause('err_save_item', array(), true);
			return false;
		}

		$input['id'] = $curitem->get('id');

		/* Congratulation, we have just came through some checkpoints well.
		   Item has been successfully saved, now we still have to take several
		   steps to clean up the system from dated data. */

		/* Check if it's a new item as we have not had the standard item-ID
		   and temporary image directory should be renamed */
		if(!empty($tmp_image_dir) && file_exists($tmp_image_dir))
		{
			if(!$this->renameTmpDir($curitem))
			{
				ImMsgReporter::setClause('err_rename_directory', array('name' => $tmp_image_dir), true);
				return false;
			}
			// clean up the older data
			$this->cleanUpTempContainers('imageupload');
		}

		// delete search index (i18n search)
		$this->deleteSearchIndex();

		ImMsgReporter::setClause('item_successfully_saved', array('name' => safe_slash_html_input(str_replace('"', '\'', $input['name']))));

		exec_action('ImAfterItemSave');
		return true;
	}



	public function deleteItem($id, $catid)
	{
		// timestamp or item id required
		if(!is_numeric($id))
		{
			ImMsgReporter::setClause('err_unknow_itemid', array(), true);
			return false;
		}

		// is current category valid
		if(!$this->cp->isCategoryValid($catid))
		{
			ImMsgReporter::setClause('invalid_category', array(), true);
			return false;
		}

		// Initialize items of the current category
		$ic = new ImItem();
		$ic->init($catid);

		$item = $ic->getItem($id);

		// item does not exist
		if(!$item)
		{
			ImMsgReporter::setClause('err_item_not_exist', array(), true);
			return false;
		}

		// backup item before delete
		if(intval($this->config->backend->itembackup) == 1)
		{
			if(!$this->config->createBackup(IM_ITEM_DIR, $item->get('id').'.'.$item->get('categoryid'),
				IM_ITEM_FILE_SUFFIX))
			{
				ImMsgReporter::setClause('err_backup', array('backup' => $this->config->backend->itembackupdir), true);
				return false;
			}
		}

		// get image directory to delete
		$imagedir = IM_IMAGE_UPLOAD_DIR.$item->get('id').'.'.$item->get('categoryid');
		// get image name to display
		$itemname = $item->name;

		if(!$ic->destroyItem($item))
		{
			ImMsgReporter::setClause('err_item_delete', array(), true);
			return false;
		}

		/* Item has been successfully deleted, now we have to clean up the image uploads */
		$this->delTree($imagedir);
		// delete search index (i18n search)
		$this->deleteSearchIndex();

		// The deletion was successful, show a message
		ImMsgReporter::setClause('item_deleted', array('item' => $itemname));
		return true;

	}


	protected function isTimestamp($string){return (1 === preg_match( '~^[1-9][0-9]*$~', $string ));}


	public function getSiteUrl()
	{
		$https = !empty($_SERVER['HTTPS']) && strcasecmp($_SERVER['HTTPS'], 'on') === 0 ||
			!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
			strcasecmp($_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') === 0;
		return
			($https ? 'https://' : 'http://').
			(!empty($_SERVER['REMOTE_USER']) ? $_SERVER['REMOTE_USER'].'@' : '').
			(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ($_SERVER['SERVER_NAME'].
				($https && $_SERVER['SERVER_PORT'] === 443 ||
				$_SERVER['SERVER_PORT'] === 80 ? '' : ':'.$_SERVER['SERVER_PORT'])));
	}


	protected function renameTmpDir($item)
	{
		$err = false;
		foreach($item->fields as $fieldname => $fieldvalue)
		{
			if($fieldvalue->type != 'imageupload')
				continue;

			$inputClassName = 'Input'.$fieldvalue->type;
			$InputType = new $inputClassName($item->fields->$fieldname);


			// try to rename file directory
			$newpath = IM_IMAGE_UPLOAD_DIR.$item->get('id').'.'.$item->get('categoryid').'/';
			if(!rename($fieldvalue->value, $newpath))
				return false;

			$resultinput = $InputType->prepareInput($newpath);

			if(!isset($resultinput) || empty($resultinput))
				return false;

			foreach($resultinput as $inputputkey => $inputvalue)
				$item->fields->$fieldname->$inputputkey = $inputvalue;
		}

		if($item->save() && !$err) return true;

		return false;
	}


	private function cleanUpTempContainers($datatyp)
	{
		if($datatyp == 'imageupload')
		{
			if(!file_exists(IM_IMAGE_UPLOAD_DIR))
				return false;

			foreach(glob(IM_IMAGE_UPLOAD_DIR.'tmp_*_*') as $file)
			{
				$base = basename($file);
				$strp = explode('_', $base);

				// wrong file name, continue
				if(count($strp) < 3)
					continue;

				if(!$this->cp->isCategoryValid($strp[2]))
					$this->delTree($file);

				$min_days = intval($this->config->backend->min_tmpimage_days);
				$storagetime =  time() - (60 * 60 * 24 * $min_days);

				if($strp[1] < $storagetime && $storagetime > 0)
					$this->delTree($file);
			}
			return true;
		}
	}


	protected function delTree($dir)
	{
		if(!file_exists($dir))
			return false;
		$files = array_diff(scandir($dir), array('.','..'));
		foreach ($files as $file)
		{
			(is_dir("$dir/$file") && !is_link($dir)) ? $this->delTree("$dir/$file") : unlink("$dir/$file");
		}
		return rmdir($dir);
	}


	public static function toAscii($str, $replace = array(), $delimiter = '-')
	{
		if(!empty($replace))
			$str = str_replace((array)$replace, ' ', $str);

		$clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
		$clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
		$clean = strtolower(trim($clean, '-'));
		$clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);

		return $clean;
	}

	public function runAction($action)
	{
		exec_action($action);
		return !empty(self::$action[$action]) ? self::$action[$action] : '';
	}

}




if(!function_exists('return_page_slug')) {
   function return_page_slug() { 
	    return isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
   }
}

/** returns current url  [[ todo: DAS HIER NOCH LÖSCHEN function bereits vorhanden siehe ImModel::getFullUrl() Method oben  ]]*/
function curPageURL() 
{
    $isHTTPS = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on');
    $port = (isset($_SERVER['SERVER_PORT']) && ((!$isHTTPS && $_SERVER['SERVER_PORT'] != '80') || ($isHTTPS && $_SERVER['SERVER_PORT'] != '443')));
    $port = ($port) ? ':'.$_SERVER['SERVER_PORT'] : '';
    return ($isHTTPS ? 'https://' : 'http://').$_SERVER['SERVER_NAME'].$port.$_SERVER['REQUEST_URI'];
}

function reparse_url($parsed_url, $imcat)
{ 
    $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'].'://' : ''; 
    $host     = isset($parsed_url['host'])   ? $parsed_url['host'] : '';  
    $path     = isset($parsed_url['path'])   ? $parsed_url['path'] : ''; 
    $query    = isset($parsed_url['query'])  ? '?'.$parsed_url['query'] : '';
    $pairs = explode('&', $query);
    foreach($pairs as $pair) 
    {
        $part = explode('=', $pair);
        if($part[0] == 'page')
        {
            return ($scheme.$host.$path.'?id=imanager&cat='. $imcat->current_category().'&page=');
        }
    }
  return ; 
}

if(!function_exists('to7bits')) {
    function to7bits($text,$from_enc='UTF-8') {
	    if (function_exists('mb_convert_encoding')) {
		    $text = mb_convert_encoding($text,'HTML-ENTITIES',$from_enc);
	    }
	    $text = preg_replace(
	    array('/&szlig;/','/&(..)lig;/','/&([aouAOU])uml;/','/&(.)[^;]*;/'),array('ss',"$1","$1".'e',"$1"),$text);
	    return $text;
    }
}

// Function to clean posted content
function safe_slash_html_input($text) {
    if (get_magic_quotes_gpc()==0) 
    {		
        $text = addslashes(htmlspecialchars($text, ENT_QUOTES, 'UTF-8', false));
    } else 
    {		
	    $text = htmlentities($text, ENT_QUOTES, 'UTF-8', false);	
    }
        return $text;
}

function find_array_key($array, $key) {
    foreach($array as $index => $val) {
        if($val['slug'] == $key) return $index;
    }
    return false;
}
?>
