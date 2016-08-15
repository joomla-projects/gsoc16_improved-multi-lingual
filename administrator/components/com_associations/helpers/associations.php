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
	 * Get item context based on the item key.
	 *
	 * @param   string  $key  The item identifier.
	 *
	 * @return  JRegistry  The item properties.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public static function getItemTypeProperties($key = '')
	{
		static $it = array();

		if (empty($key))
		{
			return null;
		}

		if (!isset($it[$key]))
		{
			$it[$key] = new Registry;

			// Get component item type info from key.
			$matches = preg_split("#[\.]+#", $key);

			$it[$key]->key                             = trim($key);
			$it[$key]->item                            = $matches[count($matches) - 1];
			$it[$key]->realcomponent                   = $matches[0];
			$it[$key]->component                       = $it[$key]->item === 'category' ? 'com_categories' : $it[$key]->realcomponent;
			$it[$key]->extension                       = $it[$key]->item === 'category' ? preg_replace('#\.' . $it[$key]->item . '$#', '', $key) : null;
			$it[$key]->adminPath                       = JPATH_ADMINISTRATOR . '/components/' . $it[$key]->component;
			$it[$key]->componentEnabled                = true;
			$it[$key]->associations                    = new Registry;
			$it[$key]->associations->support           = false;
			$it[$key]->associations->supportCategories = false;

			// If component of the item type is disabled or not installed, item type does not support associations.
			$db = JFactory::getDbo();

			$query = $db->getQuery(true)
				->select($db->quoteName('extension_id'))
				->from($db->quoteName('#__extensions'))
				->where($db->quoteName('element') . ' = ' . $db->quote($it[$key]->realcomponent))
				->where($db->quoteName('type') . ' = ' . $db->quote('component'))
				->where($db->quoteName('enabled') . ' = 1');

			if (!$db->setQuery($query)->loadResult())
			{
				$it[$key]->componentEnabled = false;

				return $it[$key];
			}

			// Check for component item type model and his properties.
			$componentName = ucfirst(substr($it[$key]->component, 4));
			$modelsPath    = $it[$key]->adminPath . '/models';

			// If component item type models path does not exist, item type does not support associations.
			if (!is_dir($modelsPath))
			{
				return $it[$key];
			}

			// Check if component item type supports associations.
			$itemName = ucfirst($it[$key]->item);

			JLoader::register($componentName . 'Model' . $itemName, $modelsPath . '/' . $it[$key]->item . '.php');
			$it[$key]->model = JModelLegacy::getInstance($itemName, $componentName . 'Model', array('ignore_request' => true));

			// If component item type model cannot loaded, or associations properties does not exist, item type does not support associations.
			$it[$key]->associations->support = $it[$key]->model && $it[$key]->model->get('associationsContext');

			// Get item type alias and asset column key.
			$it[$key]->assetKey  = $it[$key]->item === 'category' ? $it[$key]->extension . '.category' : $it[$key]->model->get('typeAlias');

			if ($it[$key]->item !== 'category')
			{
				$it[$key]->categoryContext = $it[$key]->model->get('categoryContext') ? $it[$key]->model->get('categoryContext') : $it[$key]->realcomponent;
			}

			// Get the associations context key.
			$it[$key]->associations->context = $it[$key]->model->get('associationsContext');

			// Get the database table.
			$it[$key]->model->addTablePath($it[$key]->adminPath . '/tables');
			try
			{
				$it[$key]->table = $it[$key]->model->getTable();
			}
			catch (Exception $e)
			{
				unset($it[$key]->model);

				return $it[$key];
			}
			$it[$key]->dbtable = $it[$key]->table->get('_tbl');

			// Get the table fields.
			$it[$key]->tableFields = $it[$key]->table->getFields();

			// Component fields
			// @todo This need should be checked hardcoding.
			$it[$key]->fields                   = new Registry;
			$it[$key]->fields->id               = isset($it[$key]->tableFields['id']) ? 'id' : null;
			$it[$key]->fields->title            = isset($it[$key]->tableFields['name']) ? 'name' : null;
			$it[$key]->fields->title            = isset($it[$key]->tableFields['title']) ? 'title' : $it[$key]->fields->title;
			$it[$key]->fields->alias            = isset($it[$key]->tableFields['alias']) ? 'alias' : null;
			$it[$key]->fields->ordering         = isset($it[$key]->tableFields['ordering']) ? 'ordering' : null;
			$it[$key]->fields->ordering         = isset($it[$key]->tableFields['lft']) ? 'lft' : $it[$key]->fields->ordering;
			$it[$key]->fields->menutype         = isset($it[$key]->tableFields['menutype']) ? 'menutype' : null;
			$it[$key]->fields->level            = isset($it[$key]->tableFields['level']) ? 'level' : null;
			$it[$key]->fields->catid            = isset($it[$key]->tableFields['catid']) ? 'catid' : null;
			$it[$key]->fields->language         = isset($it[$key]->tableFields['language']) ? 'language' : null;
			$it[$key]->fields->access           = isset($it[$key]->tableFields['access']) ? 'access' : null;
			$it[$key]->fields->state            = isset($it[$key]->tableFields['published']) ? 'published' : null;
			$it[$key]->fields->state            = isset($it[$key]->tableFields['state']) ? 'state' : $it[$key]->fields->state;
			$it[$key]->fields->created_user_id  = isset($it[$key]->tableFields['created_by']) ? 'created_by' : null;
			$it[$key]->fields->created_user_id  = isset($it[$key]->tableFields['created_user_id']) ? 'created_user_id' : $it[$key]->fields->created_user_id;
			$it[$key]->fields->checked_out      = isset($it[$key]->tableFields['checked_out']) ? 'checked_out' : null;
			$it[$key]->fields->checked_out_time = isset($it[$key]->tableFields['checked_out_time']) ? 'checked_out_time' : null;

			// Disallow ordering according to component.
			$it[$key]->excludeOrdering = array();

			if (is_null($it[$key]->fields->catid))
			{
				array_push($it[$key]->excludeOrdering, 'category_title');
			}
			if (is_null($it[$key]->fields->menutype))
			{
				array_push($it[$key]->excludeOrdering, 'menutype_title');
			}
			if (is_null($it[$key]->fields->access))
			{
				array_push($it[$key]->excludeOrdering, 'access_level');
			}
			if (is_null($it[$key]->fields->ordering))
			{
				array_push($it[$key]->excludeOrdering, 'ordering');
			}

			// Check the default ordering (ordering is the default, is component does not support, fallback to title).
			$it[$key]->defaultOrdering = is_null($it[$key]->fields->ordering) ? array('title', 'ASC') : array('ordering', 'ASC');

			// If item does not have id, title, alias and language cannot support associations.
			if (in_array(null, array($it[$key]->fields->id, $it[$key]->fields->title, $it[$key]->fields->alias, $it[$key]->fields->language)))
			{
				$it[$key]->associations->support = false;
			}

			// Check the helpers.
			$it[$key] = self::getItemHelpers($it[$key]);
			if (!isset($it[$key]->associations->gethelper) || !isset($it[$key]->associations->htmlhelper))
			{
				$it[$key]->associations->support = false;
			}

			// If component item type does not support associations no need to proceed.
			if (!$it[$key]->associations->support)
			{
				unset($it[$key]->model);
				unset($it[$key]->table);

				return $it[$key];
			}

			// Get the translated titles.
			$languagePath = JPATH_ADMINISTRATOR . '/components/' . $it[$key]->realcomponent;
			$lang         = JFactory::getLanguage();
			$lang->load($it[$key]->realcomponent . '.sys', JPATH_ADMINISTRATOR) || $lang->load($it[$key]->realcomponent . '.sys', $languagePath);
			$lang->load($it[$key]->realcomponent, JPATH_ADMINISTRATOR) || $lang->load($it[$key]->realcomponent, $languagePath);

			// For the component of the item type.
			$it[$key]->componentTitle = JText::_(strtoupper($it[$key]->realcomponent));

			// If the item type is a category.
			if ($it[$key]->item === 'category')
			{
				$languageKey = strtoupper(str_replace('.', '_', $it[$key]->extension)) . '_CATEGORIES';
				$it[$key]->title = $lang->hasKey($languageKey) ? JText::_($languageKey) : JText::_('JCATEGORIES');
			}
			// For all other item types.
			else
			{
				$languageKey = strtoupper($it[$key]->realcomponent) . '_' . strtoupper($it[$key]->item) . 'S';
				$it[$key]->title = $lang->hasKey($languageKey) ? JText::_($languageKey) : JText::_($it[$key]->realcomponent);
			}
		}

		return $it[$key];
	}

	/**
	 * Check for existing item association helpers.
	 *
	 * @param   JRegistry  $itemType  Item type properties.
	 *
	 * @return  JRegistry  The item properties. For chaining.
	 *
	 * @since  __DEPLOY_VERSION__
	 * @deprecated  4.0  Association Helpers will be removed.
	 */
	protected static function getItemHelpers($itemType)
	{
		$itemType->sitePath = JPATH_SITE . '/components/' . $itemType->realcomponent;

		// Association get items Helper.
		$getHelperFile = $itemType->sitePath . '/helpers/association.php';

		if (file_exists($getHelperFile))
		{
			// For items.
			if (is_null($itemType->extension))
			{
				$className   = ucfirst(substr($itemType->realcomponent, 4)) . 'HelperAssociation';
				$classMethod = 'getAssociations';
			}
			// For categories.
			else
			{
				$className   = ucfirst(substr($itemType->realcomponent, 4)) . 'HelperAssociation';
				$classMethod = 'getCategoryAssociations';
			}
		}
		// Exclusive for com_menus. @todo menus should be uniformized.
		elseif ($itemType->component === 'com_menus')
		{
			$getHelperFile = $itemType->adminPath . '/helpers/menus.php';
			$className     = 'MenusHelper';
			$classMethod   = 'getAssociations';
		}

		// Load the association get items helper class and check if helper class exits and it's callable.
		if (isset($className))
		{
			JLoader::register($className, $getHelperFile);

			if (class_exists($className) && is_callable(array($className, $classMethod)))
			{
				$itemType->associations->gethelper         = new Registry;
				$itemType->associations->gethelper->class  = $className;
				$itemType->associations->gethelper->file   = $getHelperFile;
				$itemType->associations->gethelper->method = $classMethod;
			}
		}

		// Association JHtml Helper.
		foreach (glob($itemType->adminPath . '/helpers/html/*.php', GLOB_NOSORT) as $htmlHelperFile)
		{
			// Using JHtml Override.
			$className = 'JHtml' . ucfirst(basename($htmlHelperFile, '.php'));
			JLoader::register($className, $htmlHelperFile);

			if (class_exists($className) && is_callable(array($className, 'association')))
			{
				$itemType->associations->htmlhelper        = new Registry;
				$itemType->associations->htmlhelper->key   = str_replace('JHtml', '', $className) . '.association';
				$itemType->associations->htmlhelper->class = $className;
				$itemType->associations->htmlhelper->file  = $htmlHelperFile;
			}
			// Using Legacy (ex: com_menus). @todo menus should be uniformized.
			else
			{
				$className = ucfirst(substr($itemType->component, 4)) . 'Html' . ucfirst(basename($htmlHelperFile, '.php'));
				JLoader::register($className, $htmlHelperFile);

				if (class_exists($className) && is_callable(array($className, 'association')))
				{
					$itemType->associations->htmlhelper         = new Registry;
					$itemType->associations->htmlhelper->key    = str_replace('Html', 'Html.', $className) . '.association';
					$itemType->associations->htmlhelper->class  = $className;
					$itemType->associations->htmlhelper->file   = $htmlHelperFile;
				}
			}
		}

		// Router helper.
		$routerHelperFile = $itemType->sitePath . '/helpers/route.php';

		if (file_exists($routerHelperFile))
		{
			$className   = ucfirst(substr($itemType->realcomponent, 4)) . 'HelperRoute';
			$classMethod = is_null($itemType->extension) ? 'get' . ucfirst(substr($itemType->item, 4)) . 'Route' : 'getCategoryRoute';

			JLoader::register($className, $routerHelperFile);

			if (class_exists($className) && is_callable(array($className, $classMethod)))
			{
				$itemType->associations->routerhelper         = new Registry;
				$itemType->associations->routerhelper->file   = $routerHelperFile;
				$itemType->associations->routerhelper->class  = $className;
				$itemType->associations->routerhelper->method = $classMethod;
			}
		}

		return $itemType;
	}

	/**
	 * Get all the content languages.
	 *
	 * @return  array  Array of objects all content languages by language code.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public static function getContentLanguages()
	{
		$db = JFactory::getDbo();

		// Get all content languages.
		$query = $db->getQuery(true)
			->select($db->quoteName(array('sef', 'lang_code', 'image', 'title', 'published')))
			->from($db->quoteName('#__languages'))
			->order($db->quoteName('ordering') . ' ASC');

		$db->setQuery($query);

		return $db->loadObjectList('lang_code');
	}

	/**
	 * Get the associated language links.
	 *
	 * @param   JRegistry  $itemType  Item type properties.
	 * @param   integer    $itemId    Item id.
	 *
	 * @return  array  Array of objects all associated elements by language code.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public static function getAssociationList($itemType, $itemId)
	{
		$db    = JFactory::getDbo();
		$items = array();

		// Get the associations.
		$associations = JLanguageAssociations::getAssociations(
			$itemType->realcomponent,
			$itemType->dbtable,
			$itemType->associations->context,
			$itemId,
			$itemType->fields->id,
			$itemType->fields->alias,
			$itemType->fields->catid
		);

		// If associations exist get their data.
		if ($associations)
		{
			foreach ($associations as $tag => $associated)
			{
				$associations[$tag] = (int) $associated->id;
			}

			// Get the associated items.
			$query = $db->getQuery(true)
				->select($db->quoteName('a.' . $itemType->fields->id, 'id'))
				->select($db->quoteName('a.' . $itemType->fields->title, 'title'))
				->select($db->quoteName('a.' . $itemType->fields->language, 'language'))
				->from($db->quoteName($itemType->dbtable, 'a'))
				->where($db->quoteName('a.' . $itemType->fields->id) . ' IN (' . implode(', ', array_values($associations)) . ')');

			// Prepare the group by clause.
			$groupby = array(
				'a.' . $itemType->fields->id,
				'a.' . $itemType->fields->title,
				'a.' . $itemType->fields->language,
			);

			// Join over the category.
			if (!is_null($itemType->fields->catid))
			{
				$query->select($db->quoteName('c.title', 'category_title'))
					->join('LEFT', $db->quoteName('#__categories', 'c') . ' ON ' . $db->qn('c.id') . ' = ' . $db->qn('a.' . $itemType->fields->catid));
				
				$groupby[] = 'c.title';
			}

			// Join over the menu type.
			if (!is_null($itemType->fields->menutype))
			{
				$query->select($db->quoteName('mt.title', 'menu_title'))
					->join('LEFT', $db->quoteName('#__menu_types', 'mt') . ' ON ' . $db->qn('mt.menutype') . ' = ' . $db->qn('a.' . $itemType->fields->menutype));
				
				$groupby[] = 'mt.title';
			}

			// Add the group by clause
			$query->group($db->quoteName($groupby));

			$db->setQuery($query);

			$items = $db->loadObjectList($itemType->fields->language);
		}

		return $items;
	}

	/**
	 * Get the associated language edit links Html.
	 *
	 * @param   JRegistry  $itemType      Item type properties.
	 * @param   integer    $itemId        Item id.
	 * @param   string     $itemLanguage  Item language code.
	 * @param   boolean    $addLink       True for adding edit links. False for just text.
	 * @param   boolean    $allLanguages  True for showing all content languages. False only languages with associations.
	 *
	 * @return  string  The language HTML
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public static function getAssociationHtmlList($itemType, $itemId, $itemLanguage, $addLink = true, $allLanguages = true)
	{
		$db    = JFactory::getDbo();

		// Get the associations list for this item.
		$items = self::getAssociationList($itemType, $itemId);

		// Get all content languages.
		$languages = self::getContentLanguages();

		// Load item table for ACL checks.
		$table = clone $itemType->table;
		$table->load($itemId);

		$canEditReference = self::allowEdit($itemType, $table);
		$canCreate        = self::allowAdd($itemType);

		// Create associated items list.
		foreach ($languages as $langCode => $language)
		{
			// Don't do for the reference language.
			if ($langCode == $itemLanguage)
			{
				continue;
			}

			// Don't show languages without associations, if we don't want to show them.
			if (!$allLanguages && !isset($items[$langCode]))
			{
				continue;
			}

			// Get html parameters.
			if (isset($items[$langCode]))
			{
				$title       = '<br/><br/>' . $items[$langCode]->title;
				$additional  = '';

				if (isset($items[$langCode]->category_title))
				{
					$additional = '<br/>' . JText::_('JCATEGORY') . ': ' . $items[$langCode]->category_title;
				}
				elseif (isset($items[$langCode]->menu_title))
				{
					$additional = '<br/>' . JText::_('COM_ASSOCIATIONS_HEADING_MENUTYPE') . ': ' . $items[$langCode]->menu_title;
				}

				$additional .= $addLink ? '<br/><br/>' . JText::_('COM_ASSOCIATIONS_EDIT_ASSOCIATION') : '';
				$labelClass  = 'label';
				$target      = $langCode . ':' . $items[$langCode]->id . ':edit';
				$table->load($items[$langCode]->id);
				$allow       = $canEditReference && self::allowEdit($itemType, $table);
			}
			else
			{
				$items[$langCode] = new stdClass;
				$title      = '<br/><br/>' . JText::_('COM_ASSOCIATIONS_NO_ASSOCIATION');
				$additional = $addLink ? '<br/><br/>' . JText::_('COM_ASSOCIATIONS_ADD_NEW_ASSOCIATION') : '';
				$labelClass = 'label label-warning';
				$target     = $langCode . ':0:add';
				$allow      = $canCreate;
			}

			// Generate item Html.
			$options   = array(
				'option'   => 'com_associations',
				'view'     => 'association',
				'layout'   => 'edit',
				'itemtype' => $itemType->key,
				'task'     => 'association.edit',
				'id'       => $itemId,
				'target'   => $target,
			);
			$url       = JRoute::_('index.php?' . http_build_query($options));
			$text      = strtoupper($language->sef);
			$langImage = JHtml::_('image', 'mod_languages/' . $language->image . '.gif', $language->title, array('title' => $language->title), true);
			$tooltip   = implode(' ', array($langImage, $language->title, $title, $additional));

			$items[$langCode]->link = JHtml::_('tooltip', $tooltip, null, null, $text, $allow && $addLink ? $url : '', null, 'hasTooltip ' . $labelClass);
		}

		return JLayoutHelper::render('joomla.content.associations', $items);
	}

	/**
	 * Get a existing asset key using the item parents.
	 *
	 * @param   JRegistry  $itemType  Item type properties.
	 * @param   object     $item      Item db row.
	 *
	 * @return  string  The asset key.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected static function getAssetKey(JRegistry $itemType, $item = null)
	{
		// Get the item asset.
		$asset = JTable::getInstance('Asset');
		$asset->loadByName($itemType->assetKey . '.' . $item->id);

		// If the item asset does not exist (ex: com_menus, com_contact, com_newsfeeds).
		if (is_null($asset->id))
		{
			// For menus component, if item asset does not exist, fallback to menu asset.
			if (!is_null($itemType->fields->menutype))
			{
				// If the menu type id is unknown get it from MenuType table.
				if (!isset($item->menutypeid))
				{
					$table = JTable::getInstance('MenuType');
					$table->load(array('menutype' => $item->{$itemType->fields->menutype}));
					$item->menutypeid = $table->id;
				}

				$asset->loadByName($itemType->realcomponent . '.menu.' . $item->menutypeid);
			}
			// For all other components, if item asset does not exist, fallback to category asset (if component supports).
			elseif (!is_null($itemType->fields->catid))
			{
				$asset->loadByName($itemType->realcomponent . '.category.' . $item->{$itemType->fields->catid});
			}
		}

		// If item asset, category/menu asset does not exist, fallback to component asset.
		if (is_null($asset->id))
		{
			$asset->loadByName($itemType->realcomponent);
		}

		return $asset->name;
	}

	/**
	 * Check if user is allowed to edit items.
	 *
	 * @param   JRegistry  $itemType  Item type properties.
	 * @param   object     $item      Item db row.
	 *
	 * @return  boolean  True on allowed.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public static function allowEdit(JRegistry $itemType, $item = null)
	{
		$user = JFactory::getUser();

		// If no item properties return the component permissions for core.edit.
		if (is_null($item))
		{
			return $user->authorise('core.edit', $itemType->realcomponent);
		}

		// Get the asset key.
		$assetKey = self::getAssetKey($itemType, $item);

		// Check if can edit own.
		$canEditOwn = false;

		if (!is_null($itemType->fields->created_user_id))
		{
			$canEditOwn = $user->authorise('core.edit.own', $assetKey) && $item->{$itemType->fields->created_user_id} == $user->id;
		}

		// Check also core.edit permissions.
		return $canEditOwn || $user->authorise('core.edit', $assetKey);
	}

	/**
	 * Check if user is allowed to create items.
	 *
	 * @param   JRegistry  $itemType  Item type properties.
	 * @param   object     $item      Item db row.
	 *
	 * @return  boolean  True on allowed.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public static function allowAdd(JRegistry $itemType, $item = null)
	{
		$user = JFactory::getUser();

		// If no item properties return the component permissions for core.edit.
		if (is_null($item))
		{
			return $user->authorise('core.create', $itemType->realcomponent);
		}


		// Check core.create permissions.
		return $user->authorise('core.create', self::getAssetKey($itemType, $item));
	}

	/**
	 * Check if user is allowed to perform check actions (checkin/checkout) on a item.
	 *
	 * @param   JRegistry  $itemType  Item type properties.
	 * @param   object     $item      Item db row.
	 *
	 * @return  boolean  True on allowed.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public static function allowCheckActions(JRegistry $itemType, $item = null)
	{
		// If no item properties or component doesn't have checked_out field, doesn't support checkin/checkout.
		if (is_null($item) || is_null($itemType->fields->checked_out))
		{
			return false;
		}

		// All other cases. Check if user checked out this item.
		return in_array($item->{$itemType->fields->checked_out}, array(JFactory::getUser()->id, 0));
	}
}
