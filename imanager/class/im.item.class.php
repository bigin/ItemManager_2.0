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
class ImItem
{
	/**
	 * @var array of the objects of type Item
	 */
	public $items;
	/**
	 * @var string filter by node
	 */
	private $filterby;
	/**
	 * @var boolean indicates to searchig field values
	 */
	private $fieldflag = false;

	public function __construct(){$this->items = array();}


	/**
	 * Initializes all the items of a category and made them available in ImItem::$items
	 */
	public function init($catid)
	{
		// nitialize the fields class
		$fc = new ImFields();
		$fc->init($catid);
		$this->items = array();
		foreach(glob(IM_ITEM_DIR.'*.'.$catid.IM_ITEM_FILE_SUFFIX) as $file)
		{

			$base = basename($file, IM_ITEM_FILE_SUFFIX);
			$strp = strpos($base, '.');
			$id = substr($base, 0, $strp);
			$category = substr($base, $strp+1);

			$xml = getXML($file);

			$item = new Item($category);

			$item->set('categoryid', $category);
			$item->set('id', $id);
			$item->set('file', $file);
			$item->set('filename',$base.IM_ITEM_FILE_SUFFIX);

			$item->name = (string) $xml->name;
			$item->label = (string) $xml->label;
			$item->position = (int) $xml->position;
			$item->active = (int) $xml->active;

			$item->created = (int) $xml->created;
			$item->updated = (int) $xml->updated;

			foreach($fc->fields as $name => $obj)
			{
				$new_field = new Field($category);
				// clone object because otherwise we'll lose the value data
				$new_field = clone $obj;

				foreach($xml->field as $fieldkey => $field)
				{
					if( $new_field->get('id') == $field->id)
					{
						$inputClassName = 'Input'.$new_field->type;
						$InputType = new $inputClassName($fc->fields[$name]);
						$output = $InputType->prepareOutput();

						foreach($output as $outputkey => $outputvalue)
						{
							if(is_array($outputvalue))
							{
								$new_field->$outputkey = array();
								$counter = 0;
								foreach($field->$outputkey as $arrkey => $arrval)
								{
									$new_field->{$outputkey}[] = (string) $field->{$outputkey}[$counter];
									$counter++;
								}
							} else
							{
								$new_field->$outputkey = '';
								$new_field->$outputkey = (string) $field->$outputkey;
							}
						}
						if(empty($new_field->value) && !empty($new_field->default))
						{
							$new_field->value = (string) $new_field->default;
						}
					}
				}

				$item->fields->$name = $new_field;
			}

			$this->items[$item->get('id')] = $item;
		}
	}

	/**
	 * Initializes all items and made them available in ImItem::$items array
	 */
	public function initAll()
	{
		// initialize categories
		$c = new ImCategory();
		$c->init();
		$this->items = array();
		foreach($c->categories as $catid => $catvalue)
		{
			// nitialize the fields class
			$fc = new ImFields();
			$fc->init($catid);
			foreach(glob(IM_ITEM_DIR.'*.'.$catid.IM_ITEM_FILE_SUFFIX) as $file)
			{
				$base = basename($file, IM_ITEM_FILE_SUFFIX);
				$strp = strpos($base, '.');
				$id = substr($base, 0, $strp);
				$category = substr($base, $strp+1);

				$xml = getXML($file);

				$item = new Item($category);

				$item->set('categoryid', $category);
				$item->set('id', $id);
				$item->set('file', $file);
				$item->set('filename',$base.IM_ITEM_FILE_SUFFIX);

				$item->name = (string) $xml->name;
				$item->label = (string) $xml->label;
				$item->position = (int) $xml->position;
				$item->active = (int) $xml->active;

				$item->created = (int) $xml->created;
				$item->updated = (int) $xml->updated;

				foreach($fc->fields as $name => $obj)
				{
					$new_field = new Field($category);
					// clone object because otherwise we'll lose the value data
					$new_field = clone $obj;

					foreach($xml->field as $fieldkey => $field)
					{
						if( $new_field->get('id') == $field->id)
						{
							$inputClassName = 'Input'.$new_field->type;
							$InputType = new $inputClassName($fc->fields[$name]);
							$output = $InputType->prepareOutput();

							foreach($output as $outputkey => $outputvalue)
							{
								if(is_array($outputvalue))
								{
									$new_field->$outputkey = array();
									$counter = 0;
									foreach($field->$outputkey as $arrkey => $arrval)
									{
										$new_field->{$outputkey}[] = (string) $field->{$outputkey}[$counter];
										$counter++;
									}
								} else
								{
									$new_field->$outputkey = '';
									$new_field->$outputkey = (string) $field->$outputkey;
								}
							}

							if(empty($new_field->value) && !empty($new_field->default))
							{
								$new_field->value = (string) $new_field->default;
							}
						}
					}

					$item->fields->$name = $new_field;
				}

				$this->items[$catid][$item->get('id')] = $item;
			}
		}
	}


	public function countItems(array $items=array())
		{$locitems = !empty($items) ? $items : $this->items; return count($locitems);}



	public function getItem($stat, array $items=array())
	{
		$locitems = !empty($items) ? $items : $this->items;

		// nothing to select
		if(empty($items))
		{
			if(!$this->countItems() || $this->countItems() <= 0)
				return false;
		}

		// just id was entered
		if(is_numeric($stat))
			return !empty($locitems[(int) $stat]) ? $locitems[(int) $stat] : false;

		// all parameter have to match the data
		$treads = array();
		if(false !== strpos($stat, '&&'))
		{
			$treads = explode('&&', $stat, 2);
			$parts[] = trim($treads[0]);
			$parts[] = trim($treads[1]);

			$sepitems = array();
			foreach($parts as $part)
			{
				$sepitems[] = $this->separateItems($locitems, $part);
			}

			if(!empty($sepitems[0]) && !empty($sepitems[1]))
			{
				$arr = array_map('unserialize', array_intersect(array_map('serialize', $sepitems[0]), array_map('serialize', $sepitems[1])));

				return !empty($arr) ? reset($arr) : false;
			}
		// only one parameter have to match the data
		} elseif(false !== strpos($stat, '||'))
		{
			$treads = explode('||', $stat, 2);
			$parts[] = trim($treads[0]);
			$parts[] = trim($treads[1]);

			$sepitems = array();
			foreach($parts as $part)
			{
				if($res = $this->separateItem($locitems, $part))
					return $res;
			}
		// $stat contains just one command
		} else
		{
			return $this->separateItem($locitems, $stat);
		}
		return false;
	}


	public function getItems($stat, $offset=0, $length=0, array $items=array())
	{
		// reset offset
		$offset = ($offset > 0) ? $offset-1 : $offset;

		if($offset > 0 && $length > 0 && $offset >= $length)
			return false;

		$locitems = !empty($items) ? $items : $this->items;

		// nothing to select
		if(empty($items))
		{
			if(!$this->countItems() || $this->countItems() <= 0)
				return false;
		}

		// just id was entered
		if(is_numeric($stat))
			return !empty($locitems[(int) $stat]) ? $locitems[(int) $stat] : false;


		// all parameter have to match the data
		$treads = array();

		if(false !== strpos($stat, '&&'))
		{
			$treads = explode('&&', $stat, 2);
			$parts[] = trim($treads[0]);
			$parts[] = trim($treads[1]);

			$sepitems = array();
			foreach($parts as $part)
			{
				$sepitems[] = $this->separateItems($locitems, $part);
			}
			if(!empty($sepitems[0]) && !empty($sepitems[1]))
			{
				$arr = array_map('unserialize', array_intersect(array_map('serialize', $sepitems[0]), array_map('serialize', $sepitems[1])));

				// limited output
				if(!empty($arr) && ((int) $offset > 0 || (int) $length > 0))
				{
					if((int) $length == 0) $len = null;
					$arr = array_slice($arr, (int) $offset, (int) $length, true);
				}
				return $arr;
			}
		// only one parameter have to match the data
		} elseif(false !== strpos($stat, '||'))
		{
			$treads = explode('||', $stat, 2);
			$parts[] = trim($treads[0]);
			$parts[] = trim($treads[1]);

			$sepitems = array();
			foreach($parts as $part)
			{
				$sepitems[] = $this->separateItems($locitems, $part);
			}
			if(!empty($sepitems[0]) || !empty($sepitems[1]))
			{
				if(is_array($sepitems[0]) && is_array($sepitems[1]))
				{
					// limited output
					if(!empty($sepitems[0]) && ((int) $offset > 0 || (int) $length > 0))
					{
						if((int) $length == 0) $len = null;
						$sepitems[0] = array_slice($sepitems[0], (int) $offset, (int) $length, true);
						$sepitems[1] = array_slice($sepitems[1], (int) $offset, (int) $length, true);
						$return = array_merge($sepitems[0], $sepitems[1]);
						return array_slice($return, (int) $offset, (int) $length, true);
					}
					return array_merge($sepitems[0], $sepitems[1]);

				} elseif(is_array($sepitems[0]) && !is_array($sepitems[1]))
				{
					// limited output
					if(!empty($sepitems[0]) && ((int) $offset > 0 || (int) $length > 0))
					{
						if((int) $length == 0) $len = null;
						$sepitems[0] = array_slice($sepitems[0], (int) $offset, (int) $length, true);
					}
					return $sepitems[0];
				} else
				{
					// limited output
					if(!empty($sepitems[1]) && ((int) $offset > 0 || (int) $length > 0))
					{
						if((int) $length == 0) $len = null;
						$sepitems[1] = array_slice($sepitems[1], (int) $offset, (int) $length, true);
					}
					return $sepitems[1];
				}
			}

		// run this if $stat contains just one command
		} else
		{
			$arr = $this->separateItems($locitems, $stat);

			// limited output
			if(!empty($arr) && ((int) $offset > 0 || (int) $length > 0))
			{
				if((int) $length == 0) $len = null;
				$arr = array_slice($arr, (int) $offset, (int) $length, true);
			}

			return $arr;

		}
		return false;
	}


	/**
	 * Returns the array of objects of the type Item, sorted by any node your choice
	 * NOTE: However if no $items argument is passed to the function, the fields
	 * must already be in the buffer: ImItem::$items. Call the ImItem::init($category_id)
	 * method before to assign the fields to the buffer.
	 *
	 * You can sort items by using any node
	 * Sample sortng by "position":
	 * ImItem::filterItems('position', 'DESC', $your_items_array)
	 *
	 * @param string $filterby
	 * @param string $key
	 * @param array $items
	 * @return boolean|array of objects of type Item
	 */
	public function filterItems($filterby, $option,  $offset=0, $length=0, array $items=array())
	{
		// reset offset
		$offset = ($offset > 0) ? $offset-1 : $offset;

		$locitems = !empty($items) ? $items : $this->items;
		if(empty($locitems))
		{
			if(!$this->countItems() || $this->countItems() <= 0)
				return false;
		}

		$itemcontainer = array();

		if($filterby == 'position' || $filterby == 'name' || $filterby == 'label' || $filterby == 'active'
			|| $filterby == 'created' || $filterby == 'updated')
		{
			if(empty($locitems)) return false;

			foreach($locitems as $item_id => $i)
			{
				if(!isset($i->$filterby)) continue;
				$itemcontainer[$item_id] = $locitems[$item_id];
			}
		} else
		{
			// filtering for complex value types
			foreach($locitems as $itemkey => $item)
			{
				foreach($item->fields as $fieldkey => $fieldval)
				{
					if($fieldkey != $filterby) continue;
					$itemcontainer[$itemkey] = $locitems[$itemkey];
					$this->fieldflag = true;
					break;
				}
			}
		}

		if(!empty($itemcontainer))
		{
			$this->filterby = $filterby;
			usort($itemcontainer, array($this, 'sortObjects'));
			// sorte DESCENDING
			if(strtolower($option) != 'asc') $itemcontainer = $this->reverseItems($itemcontainer);
			$itemcontainer = $this->reviseItemIds($itemcontainer);

			// limited output
			if(!empty($itemcontainer) && ((int) $offset > 0 || (int) $length > 0))
			{
				if((int) $length == 0) $len = null;
				$itemcontainer = array_slice($itemcontainer, (int) $offset, (int) $length, true);
			}

			if(!empty($items))
				return $itemcontainer;
			$this->items = $itemcontainer;
			return $this->items;
		}

		return false;
	}



	/**
	 * Deletes an Item
	 *
	 * @param Item $item
	 * @param reinitialize flag $re
	 * @return bool
	 */
	public function destroyItem(Item $item, $re = false)
	{
		if(file_exists(IM_ITEM_DIR.$item->get('id').'.'.$item->get('categoryid').IM_ITEM_FILE_SUFFIX))
		{
			unlink(IM_ITEM_DIR.$item->get('id').'.'.$item->get('categoryid').IM_ITEM_FILE_SUFFIX);
			// reinitialize items
			if($re) $this->init($item->get('categoryid'));
			return true;
		}
		return false;
	}



	protected function separateItem(array $items, $stat)
	{
		if (false !== strpos($stat, '='))
		{
			$data = explode('=', $stat, 2);
			$key = strtolower(trim($data[0]));
			$val = trim($data[1]);


			$num = substr_count($val, '%');

			$pat = false;
			if($num == 1)
			{
				$pos = strpos($val, '%');
				if($pos == 0)
				{
					$pat = '/'.strtolower(trim(str_replace('%', '', $val))).'$/';
				} elseif($pos == strlen($val))
				{
					$pat = '/^'.strtolower(trim(str_replace('%', '', $val))).'/';
				}

			} elseif($num == 2)
			{
				$pat = '/'.strtolower(trim(str_replace('%', '', $val))).'/';
			}

			if(false !== strpos($key, ' '))
				return false;

			// searching for the name and other simple attributs
			if($key == 'name' || $key == 'label' || $key == 'position' || $key == 'active'
				|| $key == 'created' || $key == 'updated')
			{
				foreach($items as $itemkey => $item)
				{
					if(strtolower($item->$key) == strtolower($val) && !$pat)
						return $item;
					elseif($pat && preg_match($pat, strtolower($item->$key)))
						return $item;
				}

				return false;
			}
			// searching for field in complex value types
			foreach($items as $itemkey => $item)
			{
				foreach($item->fields as $fieldkey => $fieldval)
				{
					if(!empty($fieldval->value) && $fieldkey == $key && $fieldval->value == $val)
						return $item;
					elseif(!empty($fieldval->value) && $pat && preg_match($pat, strtolower($fieldval->value)))
						return $item;
				}
			}
		}
		return false;
	}


	protected function separateItems(array $items, $stat)
	{
		$res = array();

		$pattern = array(0 => '>=', 1 => '<=', 2 => '!=', 3 => '>', 4 => '<', 5 => '=');

		foreach($pattern as $pkey => $pval)
		{
			if(false !== strpos($stat, $pval))
			{
				$data = explode($pval, $stat, 2);
				$key = strtolower(trim($data[0]));
				$val = trim($data[1]);
				if(false !== strpos($key, ' '))
					return false;

				$num = substr_count($val, '%');
				$pat = false;
				if($num == 1)
				{
					$pos = strpos($val, '%');
					if($pos == 0)
					{
						$pat = '/'.strtolower(trim(str_replace('%', '', $val))).'$/';
					} elseif($pos == (strlen($val)-1))
					{
						$pat = '/^'.strtolower(trim(str_replace('%', '', $val))).'/';
					}
				} elseif($num == 2)
				{
					$pat = '/'.strtolower(trim(str_replace('%', '', $val))).'/';

				}

				// searching for the name and other simple attributs
				if($key == 'name' || $key == 'label' || $key == 'position' || $key == 'active'
					|| $key == 'created' || $key == 'updated')
				{
					foreach($items as $itemkey => $item)
					{
						if(!isset($item->$key)) continue;

						if($pkey == 0)
						{
							if($item->$key < $val) continue;
						} elseif($pkey == 1)
						{
							if($item->$key > $val) continue;
						} elseif($pkey == 2)
						{
							if($item->$key == $val) continue;
						} elseif($pkey == 3)
						{
							if($item->$key <= $val) continue;
						} elseif($pkey == 4)
						{
							if($item->$key >= $val) continue;
						} elseif($pkey == 5)
						{
							if($item->$key != $val && !$pat) {

								continue;
							}
							elseif($pat && !preg_match($pat, strtolower($item->$key))){
								continue;
							}
						}


						$res[$item->get('id')] = $item;
					}

				// searching for fields in complex value types
				} else
				{
					foreach($items as $itemkey => $item)
					{
						foreach($item->fields as $fieldkey => $fieldval)
						{
							if(!isset($item->fields->$key->value)) continue;

							if($pkey == 0)
							{
								if($item->fields->$key->value < $val) continue;
							} elseif($pkey == 1)
							{
								if($item->fields->$key->value > $val) continue;
							} elseif($pkey == 2)
							{
								if($item->fields->$key->value == $val) continue;
							} elseif($pkey == 3)
							{
								if($item->fields->$key->value <= $val) continue;
							} elseif($pkey == 4)
							{
								if($item->fields->$key->value >= $val) continue;
							}elseif($pkey == 5)
							{
								if($item->fields->$key->value != $val && !$pat)
									continue;
								elseif($pat && !preg_match($pat, strtolower($item->fields->$key->value)))
									continue;
							}

							$res[$item->get('id')] = $item;

						}
					}
				}
				if(!empty($res)) return $res;

				return false;
			}
		}

		return false;
	}


	/**
	 * Sorts the objects
	 *
	 * @param $a $b objects to be sorted
	 * @return boolean
	 */
	private function sortObjects($a, $b)
	{
		if(!$this->fieldflag)
		{
			$a = $a->{$this->filterby};
			$b = $b->{$this->filterby};
			if(is_numeric($a))
			{
				if($a == $b) {return 0;}
				else
				{
					if($b > $a) {return -1;}
					else {return 1;}
				}
			} else {return strcasecmp($a, $b);}

		} else
		{
			$a = $a->fields->{$this->filterby}->value;
			$b = $b->fields->{$this->filterby}->value;
			if(is_numeric($a))
			{
				if($a == $b) {return 0;}
				else
				{
					if($b > $a) {return -1;}
					else {return 1;}
				}
			} else {return strcasecmp($a, $b);}
		}
	}


	/**
	 * Reverse the array of items
	 *
	 * @param array $itemcontainer An array of objects
	 * @return boolean|array
	 */
	public function reverseItems($itemcontainer)
	{
		if(!is_array($itemcontainer)) return false;
		return array_reverse($itemcontainer);
	}


	/**
	 * Revise keys of the array of items and changes these into real item id's
	 *
	 * @param array $itemcontainer An array of objects
	 * @return boolean|array
	 */
	public function reviseItemIds($itemcontainer)
	{
		if(!is_array($itemcontainer)) return false;
		$result = array();
		foreach($itemcontainer as $val)
			$result[$val->get('id')] = $val;
		return $result;
	}


}