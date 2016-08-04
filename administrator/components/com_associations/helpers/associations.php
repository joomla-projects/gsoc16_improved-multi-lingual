<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_associations
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * Associations component helper.
 *
 * @since  __DEPLOY_VERSION__
 */
class AssociationsHelper extends JHelperContent
{
	public static $extension = 'com_associations';

	/**
	 * Get component properties based on a string.
	 *
	 * @param   string  $key  The component/item/extension identifier.
	 *
	 * @return  JRegistry  The component properties.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public static function getComponentProperties($key = '')
	{
		static $cp = array();

		if (empty($key))
		{
			return null;
		}

		if (!isset($cp[$key]))
		{
			$cp[$key] = new Registry;

			// Get component info from key.
			$matches = preg_split("#[\.\|]+#", $key);

			$cp[$key]->component                        = $matches[0];
			$cp[$key]->item                             = isset($matches[1]) ? $matches[1] : null;
			$cp[$key]->extension                        = isset($matches[2]) ? $matches[2] : null;
			$cp[$key]->realcomponent                    = !is_null($cp[$key]->extension) ? $cp[$key]->extension : $cp[$key]->component;
			$cp[$key]->sitePath                         = JPATH_SITE . '/components/' . $cp[$key]->realcomponent;
			$cp[$key]->adminPath                        = JPATH_ADMINISTRATOR . '/components/' . $cp[$key]->component;
			$cp[$key]->associations                     = new Registry;
			$cp[$key]->associations->support            = true;
			$cp[$key]->associations->supportItem        = false;
			$cp[$key]->associations->supportCategories  = false;

			// Check for component model and his properties.
			$componentName = ucfirst(substr($cp[$key]->component, 4));
			$modelsPath    = $cp[$key]->adminPath . '/models';

			// If component models path does not exist, component does not support associations.
			if (!is_dir($modelsPath))
			{
				$cp[$key]->associations->support = false;

				return $cp[$key];
			}

			// Association get items Helper.
			$cp[$key]->associations->gethelper = new Registry;

			if (file_exists($cp[$key]->sitePath . '/helpers/association.php'))
			{
				$cp[$key]->associations->gethelper->file   = $cp[$key]->sitePath . '/helpers/association.php';

				// For items.
				if (is_null($cp[$key]->extension))
				{
					$cp[$key]->associations->gethelper->class  = $componentName . 'HelperAssociation';
					$cp[$key]->associations->gethelper->method = 'getAssociations';

				}
				// For categories.
				else
				{
					$cp[$key]->associations->gethelper->class  = ucfirst(substr($cp[$key]->realcomponent, 4)) . 'HelperAssociation';
					$cp[$key]->associations->gethelper->method = 'getCategoryAssociations';
				}
			}
			// Exclusive for com_menus. @todo menus should be uniformized.
			elseif ($cp[$key]->component === 'com_menus')
			{
				$cp[$key]->associations->gethelper->class  = 'MenusHelper';
				$cp[$key]->associations->gethelper->file   = $cp[$key]->adminPath . '/helpers/menus.php';
				$cp[$key]->associations->gethelper->method = 'getAssociations';
			}

			// If association get items helper class does not exists, component does not support associations.
			if (!isset($cp[$key]->associations->gethelper->class))
			{
				$cp[$key]->associations->support = false;

				return $cp[$key];
			}

			// Load the association get items helper class.
			JLoader::register($cp[$key]->associations->gethelper->class, $cp[$key]->associations->gethelper->file);

			// If association get items helper class cannot loaded, component does not support associations.
			if (!class_exists($cp[$key]->associations->gethelper->class))
			{
				$cp[$key]->associations->support = false;

				return $cp[$key];
			}

			// If association get items helper class cannot be called, component does not support associations.
			if (!class_exists($cp[$key]->associations->gethelper->class))
			{
				$cp[$key]->associations->support = false;

				return $cp[$key];
			}

			// Association JHtml Helper.
			$cp[$key]->associations->htmlhelper = new Registry;

			foreach (glob($cp[$key]->adminPath . '/helpers/html/*.php', GLOB_NOSORT) as $htmlHelperFile)
			{
				// Using JHtml Override.
				$className = 'JHtml' . ucfirst(basename($htmlHelperFile, '.php'));
				JLoader::register($className, $htmlHelperFile);

				if (class_exists($className) && is_callable(array($className, 'association')))
				{
					$cp[$key]->associations->htmlhelper->key   = str_replace('JHtml', '', $className) . '.association';
					$cp[$key]->associations->htmlhelper->class = $className;
					$cp[$key]->associations->htmlhelper->file  = $htmlHelperFile;
				}
				// Using Legacy (ex: com_menus). @todo menus should be uniformized.
				else
				{
					$className = ucfirst(substr($cp[$key]->component, 4)) . 'Html' . ucfirst(basename($htmlHelperFile, '.php'));
					JLoader::register($className, $htmlHelperFile);

					if (class_exists($className) && is_callable(array($className, 'association')))
					{
						$cp[$key]->associations->htmlhelper->key   = str_replace('Html', 'Html.', $className) . '.association';
						$cp[$key]->associations->htmlhelper->class = $className;
						$cp[$key]->associations->htmlhelper->file  = $htmlHelperFile;
					}
				}
			}

			// Get component title.
			$lang = JFactory::getLanguage();
			$lang->load($cp[$key]->component . '.sys', JPATH_ADMINISTRATOR) || $lang->load($cp[$key]->component . '.sys', $cp[$key]->adminPath);
			$lang->load($cp[$key]->component, JPATH_ADMINISTRATOR) || $lang->load($cp[$key]->component, $cp[$key]->adminPath);

			$cp[$key]->title = JText::_($cp[$key]->component);

			// Check if component support categories associations.
			if ($cp[$key]->component !== 'com_categories')
			{
				$cp[$key]->associations->supportCategories = is_callable(array($cp[$key]->associations->gethelper->class, 'getCategoryAssociations'));
			}

			// If we are fetching only the main component info don't do anything else.
			if (is_null($cp[$key]->item))
			{
				return $cp[$key];
			}

			// If association html helper cannot loaded, component items does not support associations.
			if (!isset($cp[$key]->associations->htmlhelper->class))
			{
				$cp[$key]->associations->support     = false;
				$cp[$key]->associations->supportItem = false;

				return $cp[$key];
			}

			// Check if component item supports associations.
			$itemName = ucfirst($cp[$key]->item);

			JLoader::register($componentName . 'Model' . $itemName, $modelsPath . '/' . $cp[$key]->item . '.php');
			$cp[$key]->model = JModelLegacy::getInstance($itemName, $componentName . 'Model', array('ignore_request' => true));

			// If component item model cannot loaded, or associations properties does not exist, component item does not support associations.
			$cp[$key]->associations->supportItem = $cp[$key]->model && $cp[$key]->model->get('associationsContext');

			// If item does not support associations don't do anything else and free model form memory.
			if (!$cp[$key]->associations->supportItem)
			{
				unset($cp[$key]->model);
				return $cp[$key];
			}

			// Get Item type alias, Asset column key and Associations context key.
			$cp[$key]->typeAlias             = !is_null($cp[$key]->extension) ? 'com_categories.category' : $cp[$key]->model->get('typeAlias');
			$cp[$key]->assetKey              = $cp[$key]->typeAlias;
			$cp[$key]->associations->context = $cp[$key]->model->get('associationsContext');

			// Get the database table.
			$cp[$key]->model->addTablePath($cp[$key]->adminPath . '/tables');
			$cp[$key]->table   = $cp[$key]->model->getTable();
			$cp[$key]->dbtable = $cp[$key]->table->get('_tbl');

			// Get the table fields.
			$cp[$key]->tableFields = $cp[$key]->table->getFields();

			// Component fields
			// @todo This need should be checked hardcoding.
			$cp[$key]->fields                   = new Registry;
			$cp[$key]->fields->title            = isset($cp[$key]->tableFields['name']) ? 'name' : null;
			$cp[$key]->fields->title            = isset($cp[$key]->tableFields['title']) ? 'title' : $cp[$key]->fields->title;
			$cp[$key]->fields->alias            = isset($cp[$key]->tableFields['alias']) ? 'alias' : null;
			$cp[$key]->fields->ordering         = isset($cp[$key]->tableFields['ordering']) ? 'ordering' : null;
			$cp[$key]->fields->ordering         = isset($cp[$key]->tableFields['lft']) ? 'lft' : $cp[$key]->fields->ordering;
			$cp[$key]->fields->menutype         = isset($cp[$key]->tableFields['menutype']) ? 'menutype' : null;
			$cp[$key]->fields->level            = isset($cp[$key]->tableFields['level']) ? 'level' : null;
			$cp[$key]->fields->catid            = isset($cp[$key]->tableFields['catid']) ? 'catid' : null;
			$cp[$key]->fields->language         = isset($cp[$key]->tableFields['language']) ? 'language' : null;
			$cp[$key]->fields->access           = isset($cp[$key]->tableFields['access']) ? 'access' : null;
			$cp[$key]->fields->published        = isset($cp[$key]->tableFields['state']) ? 'state' : null;
			$cp[$key]->fields->published        = isset($cp[$key]->tableFields['published']) ? 'published' : $cp[$key]->fields->published;
			$cp[$key]->fields->created_by       = isset($cp[$key]->tableFields['created_user_id']) ? 'created_user_id' : null;
			$cp[$key]->fields->created_by       = isset($cp[$key]->tableFields['created_by']) ? 'created_by' : $cp[$key]->fields->created_by;
			$cp[$key]->fields->checked_out      = isset($cp[$key]->tableFields['checked_out']) ? 'checked_out' : null;
			$cp[$key]->fields->checked_out_time = isset($cp[$key]->tableFields['checked_out_time']) ? 'checked_out_time' : null;

			// Disallow ordering according to component.
			$cp[$key]->excludeOrdering = array();

			if (is_null($cp[$key]->fields->catid))
			{
				array_push($cp[$key]->excludeOrdering, 'category_title');
			}
			if (is_null($cp[$key]->fields->menutype))
			{
				array_push($cp[$key]->excludeOrdering, 'menutype_title');
			}
			if (is_null($cp[$key]->fields->access))
			{
				array_push($cp[$key]->excludeOrdering, 'access_level');
			}
			if (is_null($cp[$key]->fields->ordering))
			{
				array_push($cp[$key]->excludeOrdering, 'ordering');
			}

			// Check the default ordering (ordering is the default, is component does not support, fallback to title).
			$cp[$key]->defaultOrdering = is_null($cp[$key]->fields->ordering) ? array('title', 'ASC') : array('ordering', 'ASC');

			// Flag that indicates if the component allow modal layout and so have a custom target button.
			$cp[$key]->customTarget = (int) file_exists($cp[$key]->adminPath . '/views/' . $cp[$key]->item . 's/tmpl/modal.php');
		}

		return $cp[$key];
	}

	/**
	 * Check if user is allowed to edit own item
	 *
	 * @param   string  $componentKey  The component properties.
	 * @param   JTable  $item  Database row from the component.
	 *
	 * @return  boolean.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public static function allowEditOwn($componentKey = '', $item = null)
	{
		$user  = JFactory::getUser();

		if (isset($item->{$componentKey->fields->created_by}))
		{
			return $user->authorise(
					'core.edit.own', $componentKey->realcomponent . '.' . $item->id
				) && $item->{$componentKey->fields->created_by} == $user->id;
		}

		return false;
	}

	/**
	 * Check if user is allowed to edit item
	 *
	 * @param   string  $componentKey  The component properties.
	 * @param   JTable  $item          Database row from the component.
	 *
	 * @return  boolean.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public static function allowEdit($componentKey = '', $item = null)
	{
		$user = JFactory::getUser();

		// Different case for menu items
		if ($componentKey->realcomponent == 'com_menus')
		{
			if (isset($item->menutypeid))
			{
				return $user->authorise('core.edit', 'com_menus.menu.' . $item->menutypeid);
			}
			else
			{
				$table = JTable::getInstance('MenuType');
				$table->load(array('menutype' => $item->menutype));
				
				return $user->authorise('core.edit', 'com_menus.menu.' . $table->id);
			}
			
		}

		return $user->authorise('core.edit', $componentKey->realcomponent . '.' . $item->id) || self::allowEditOwn($componentKey, $item);
	}

	/**
	 * Check if user is allowed to create item
	 *
	 * @param   string  $componentKey  The component properties.
	 *
	 * @return  boolean.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public static function allowCreate($componentKey = '')
	{
		$user = JFactory::getUser();
		
		return $user->authorise('core.create', $componentKey->realcomponent);
	}

	/**
	 * Check if user is allowed to edit checkout item
	 *
	 * @param   string  $componentKey  The component properties.
	 * @param   JTable  $item          Database row from the component.
	 *
	 * @return  boolean.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public static function allowCheckout($componentKey = '', $item = null)
	{
		if (!is_null($componentKey->fields->checked_out))
		{
			$user = JFactory::getUser();

			// Check if user checked out this item
			return in_array($item->{$componentKey->fields->checked_out}, array($user->id, 0));
		}
	}
}
