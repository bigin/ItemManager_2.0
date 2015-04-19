<?php
/**
 * Plugin Name: ItemManager
 * Description: Full-featured  ItemManager.
 * Version: 1.0
 * Author: Juri Ehret
 * Author URI: http://ehret-studio.com
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
class ImCategory
{
	/**
	 * @var array of the object of type Category
	 */
	public  $categories;
	/**
	 * @var string filter by node
	 */
	private $filterby;


	public function __construct(){$this->categories = array();}


	/**
	 * Initializes all the categories and made them available in ImCategory::$categories buffer
	 */
	public function init()
	{
		$this->categories = array();
		foreach (glob(IM_CATEGORY_DIR . '*' . IM_CATEGORY_FILE_SUFFIX) as $file)
		{
			$cat = new Category();

			$base = basename($file);
			$strp = strpos($base, '.');
			$id = substr($base, 0, $strp);
			$xml = getXML($file);

			if(!$cat->setProtectedParams((int) $id))
				continue;

			$cat->name = (string) $xml->name;
			$cat->position = (int) $xml->position;
			$cat->created = (int) $xml->created;
			$cat->updated = (int) $xml->updated;

			$this->categories[$cat->get('id')] = $cat;
		}
	}


	/**
	 * Returns the number of categories
	 *
	 * @param array $categories
	 * @return int
	 */
	public function countCategories(array $categories=array())
		{return count($cat = !empty($categories) ? $categories : $this->categories);}


	/**
	 * Returns the object of type Category
	 * NOTE: However if no $categories argument is passed to the function, the categories
	 * must already be in the buffer: ImCategory::$categories. Call the ImCategory::init()
	 * method before to assign the categories to the buffer.
	 *
	 * You can search for category by ID: ImCategory::getCategory(2) or similar to ImCategory::getCategory('id=2')
	 * or by category name ImCategory::getCategory('name=My category name')
	 *
	 * @param string/integer $stat
	 * @param array $categories
	 * @return boolean|object of the type Category
	 */
	public function getCategory($stat, array $categories=array())
	{

		$loccat = !empty($categories) ? $categories : $this->categories;
		// nothing to select
		if(empty($categories))
		{
			if(!$this->countCategories() || $this->countCategories() <= 0)
				return false;
		}

		// stat is an id
		if(is_numeric($stat))
		{
			// id not found
			if(!isset($loccat[(int) $stat]) || empty($loccat[(int) $stat]->get('id')))
				return false;

			return $loccat[(int) $stat];

		// stat is a string
		} elseif (false !== strpos($stat, '='))
		{
			$data = explode('=', $stat, 2);
			$key = strtolower(trim($data[0]));
			$val = trim($data[1]);
			if(false !== strpos($key, ' '))
				return false;

			foreach($loccat as $catid => $c)
			{
				if(!isset($c->$key) || $c->$key != $val) continue;

				return $loccat[$catid];
			}
		}
		return false;
	}


	/**
	 * Returns the array of objects of the type Category, by a comparison of values
	 * NOTE: However if no $categories argument is passed to the function, the categories
	 * must already be in the buffer: ImCategory::$categories. Call the ImCategory::init()
	 * method before to assign the categories to the buffer.
	 *
	 * You can sort categories by using any node
	 * Sample sortng by "position":
	 * ImCategory::filterCategories('position', 'DESC', $your_categories_array)
	 *
	 * @param string $filterby
	 * @param string $key
	 * @param array $categories
	 * @return boolean|array
	 */
	public function getCategories($stat, $offset=0, $length=0, array $categories=array())
	{
		// reset offset
		$offset = ($offset > 0) ? $offset-1 : $offset;

		$loccat = !empty($categories) ? $categories : $this->categories;
		// nothing to select
		if(empty($categories))
		{
			if(!$this->countCategories() || $this->countCategories() <= 0)
				return false;
		}

		$catcontainer = array();

		$pattern = array(0 => '>=', 1 => '<=', 2 => '!=', 3 => '>', 4 => '<', 5 => '=');

		foreach($pattern as $pkey => $pval)
		{
			if(false !== strpos($stat, $pval))
			{

				$data = explode($pval, $stat, 2);
				$key = strtolower(trim($data[0]));
				if($pkey != 5)
					$val = (int) trim($data[1]);
				else
					$val = trim($data[1]);

				if(false !== strpos($key, ' '))
					return false;

				foreach($loccat as $cat_id => $c)
				{
					if($pkey == 0)
					{
						if(!isset($c->$key) || $c->$key < $val) continue;
					} elseif($pkey == 1)
					{
						if(!isset($c->$key) || $c->$key > $val) continue;
					} elseif($pkey == 2)
					{
						if(!isset($c->$key) || $c->$key == $val) continue;
					} elseif($pkey == 3)
					{
						if(!isset($c->$key) || $c->$key <= $val) continue;
					} elseif($pkey == 4)
					{
						if(!isset($c->$key) || $c->$key >= $val) continue;
					} elseif($pkey == 5)
					{
						if(!isset($c->$key) || $c->$key != $val) continue;
					}

					$catcontainer[$cat_id] = $loccat[$cat_id];
				}
				if(!empty($catcontainer))
				{
					// limited output
					if((int) $offset > 0 || (int) $length > 0)
					{
						if((int) $length == 0) $len = null;
						$catcontainer = array_slice($catcontainer, (int) $offset, (int) $length, true);
					}
					return $catcontainer;
				}
				return false;
			}
		}
		return false;
	}


	/**
	 * Returns the array of objects of the type Category, sorted by any node
	 * NOTE: However if no $categories argument is passed to the function, the categories
	 * must already be in the buffer: ImCategory::$categories. Call the ImCategory::init()
	 * method before to assign the categories to the buffer.
	 *
	 * You can sort categories by using any node
	 * Sample sortng by "position":
	 * ImCategory::filterCategories('position', 'DESC', $your_categories_array)
	 *
	 * @param string $filterby
	 * @param string $key
	 * @param array $categories
	 * @return boolean|array of objects of the type Category
	 */
	public function filterCategories($filterby, $key, $offset=0, $length=0, array $categories=array())
	{
		// reset offset
		$offset = ($offset > 0) ? $offset-1 : $offset;

		$loccat = !empty($categories) ? $categories : $this->categories;
		if(empty($categories))
		{
			if(!$this->countCategories() || $this->countCategories() <= 0)
				return false;
		}

		$catcontainer = array();

		foreach($loccat as $cat_id => $c)
		{
			if(!isset($c->$filterby)) continue;

			$catcontainer[$cat_id] = $loccat[$cat_id];
		}

		if(!empty($catcontainer))
		{
			$this->filterby = $filterby;
			usort($catcontainer, array($this, 'sortObjects'));
			// sorte DESCENDING
			if(strtolower($key) != 'asc') $catcontainer = $this->reverseCategories($catcontainer);
			$catcontainer = $this->reviseCatIds($catcontainer);

			// limited output
			if((int) $offset > 0 || (int) $length > 0)
			{
				if((int) $length == 0) $len = null;
				$catcontainer = array_slice($catcontainer, (int) $offset, (int) $length, true);
			}
			return $catcontainer;
		}

		return false;
	}


	/**
	 * Deletes the category
	 *
	 * @param Category $cat
	 * @return bool
	 */
	public function destroyCategory(Category $cat)
	{
		if(file_exists(IM_CATEGORY_DIR . $cat->get('id') . IM_CATEGORY_FILE_SUFFIX))
			return unlink(IM_CATEGORY_DIR . $cat->get('id') . IM_CATEGORY_FILE_SUFFIX);
		return false;
	}


	/**
	 * Reverse the array of categoriies
	 *
	 * @param array $catcontainer An array of objects
	 * @return boolean|array
	 */
	public function reverseCategories($catcontainer)
	{
		if(!is_array($catcontainer)) return false;
		return array_reverse($catcontainer);
	}


	/**
	 * Revise keys of the array of categories and changes these into real category Ids
	 *
	 * @param array $catcontainer An array of objects
	 * @return boolean|array
	 */
	public function reviseCatIds($catcontainer)
	{
		if(!is_array($catcontainer)) return false;
		$result = array();
		foreach($catcontainer as $val)
			$result[$val->get('id')] = $val;
		return $result;
	}


	/**
	 * Sorts the objects
	 *
	 * @param $a $b objects to be sorted
	 * @return boolean
	 */
	private function sortObjects($a, $b)
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
	}

}
?>