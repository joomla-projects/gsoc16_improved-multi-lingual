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
			$matches = preg_split("#[\.\:]+#", $key);

			$cp[$key]->key                              = $key;
			$cp[$key]->component                        = $matches[0];
			$cp[$key]->item                             = isset($matches[1]) ? $matches[1] : null;
			$cp[$key]->extension                        = isset($matches[2]) ? $matches[2] : null;
			$cp[$key]->realcomponent                    = !is_null($cp[$key]->extension) ? $cp[$key]->extension : $cp[$key]->component;
			$cp[$key]->sitePath                         = JPATH_SITE . '/components/' . $cp[$key]->realcomponent;
			$cp[$key]->adminPath                        = JPATH_ADMINISTRATOR . '/components/' . $cp[$key]->component;
			$cp[$key]->enabled                          = true;
			$cp[$key]->associations                     = new Registry;
			$cp[$key]->associations->support            = true;
			$cp[$key]->associations->supportItem        = false;
			$cp[$key]->associations->supportCategories  = false;

			// If component is disabled or not installed, component does not support associations.
			$db = JFactory::getDbo();

			$query = $db->getQuery(true)
				->select($db->quoteName('extension_id'))
				->from($db->quoteName('#__extensions'))
				->where($db->quoteName('element') . ' = ' . $db->quote($cp[$key]->realcomponent))
				->where($db->quoteName('type') . ' = ' . $db->quote('component'))
				->where($db->quoteName('enabled') . ' = 1');

			if (!$db->setQuery($query)->loadResult())
			{
				$cp[$key]->enabled = false;

				return $cp[$key];
			}

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

			// Get the translate title for the component.
			$cp[$key]->title = JText::_($cp[$key]->component);

			// Get the translate title for the component item.
			$languageKey = strtoupper($cp[$key]->realcomponent) . '_' . strtoupper($cp[$key]->item) . 'S';
			$cp[$key]->itemsTitle = $lang->hasKey($languageKey) ? JText::_($languageKey) : JText::_($cp[$key]->component);

			// Check if component support categories associations.
			if ($cp[$key]->component !== 'com_categories')
			{
				$cp[$key]->associations->supportCategories = is_callable(array($cp[$key]->associations->gethelper->class, 'getCategoryAssociations'));

				// Get the translate title for the component category item.
				if ($cp[$key]->associations->supportCategories)
				{
					$languageKey = strtoupper($cp[$key]->realcomponent) . '_CATEGORIES';
					$cp[$key]->categoriesTitle = $lang->hasKey($languageKey) ? JText::_($languageKey) : JText::_('JCATEGORIES');
				}
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
			$cp[$key]->assetKey              = !is_null($cp[$key]->extension) ? $cp[$key]->realcomponent . '.category' : $cp[$key]->typeAlias;
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
			$cp[$key]->fields->id               = isset($cp[$key]->tableFields['id']) ? 'id' : null;
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
	 * Get the associated language edit links Html.
	 *
	 * @param   JRegistry  $component     Component properties.
	 * @param   integer    $itemId        Item id.
	 * @param   string     $itemLanguage  Item language code.
	 * @param   boolean    $addLink       True for adding edit links. False for just text.
	 *
	 * @return  string  The language HTML
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public static function getAssociationHtmlList($component, $itemId, $itemLanguage, $addLink = true)
	{
		$db    = JFactory::getDbo();
		$items = array();

		// Get the associations.
		$associations = JLanguageAssociations::getAssociations(
			$component->realcomponent,
			$component->dbtable,
			$component->associations->context,
			$itemId,
			$component->fields->id,
			$component->fields->alias,
			$component->fields->catid
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
				->select($db->quoteName('a.' . $component->fields->id, 'id'))
				->select($db->quoteName('a.' . $component->fields->language, 'language'))
				->select($db->quoteName('a.' . $component->fields->title, 'title'))
				->from($db->quoteName($component->dbtable, 'a'))
				->where($db->quoteName('a.' . $component->fields->id) . ' IN (' . implode(', ', array_values($associations)) . ')');

			if (!is_null($component->fields->catid))
			{
				$query->select($db->quoteName('c.title', 'category_title'))
					->join('LEFT', $db->quoteName('#__categories', 'c') . ' ON ' . $db->qn('c.id') . ' = ' . $db->qn('a.' . $component->fields->catid));
			}

			if (!is_null($component->fields->menutype))
			{
				$query->select($db->quoteName('mt.title', 'menu_title'))
					->join('LEFT', $db->quoteName('#__menu_types', 'mt') . ' ON ' . $db->qn('mt.menutype') . ' = ' . $db->qn('a.' . $component->fields->menutype));
			}

			$db->setQuery($query);

			$items = $db->loadObjectList($component->fields->language);
		}

		// Get all content languages.
		$query = $db->getQuery(true)
			->select($db->quoteName(array('sef', 'lang_code', 'image', 'title')))
			->from($db->quoteName('#__languages'))
			->order($db->quoteName('ordering') . ' ASC');

		$db->setQuery($query);

		$languages = $db->loadObjectList('lang_code');

		// Load item table for ACL checks.
		$table = clone $component->table;
		$table->load($itemId);

		$canEditReference = self::allowEdit($component, $table);
		$canCreate        = self::allowAdd($component);

		// Create associated items list.
		foreach ($languages as $langCode => $language)
		{
			// Don't do for the reference language.
			if ($langCode == $itemLanguage)
			{
				continue;
			}

			// Get html parameters.
			if (isset($items[$langCode]))
			{
				$title      = $items[$langCode]->title;
				$additional = '';

				if (isset($items[$langCode]->category_title))
				{
					$additional = '<br/>' . JText::_('JCATEGORY') . ': ' . $items[$langCode]->category_title;
				}
				elseif (isset($items[$langCode]->menu_title))
				{
					$additional = '<br/>' . JText::_('COM_ASSOCIATIONS_HEADING_MENUTYPE') . ': ' . $items[$langCode]->menu_title;
				}

				$labelClass = 'label label-success'; 
				$target     = $langCode . ':' . $items[$langCode]->id . ':edit';
				$table->load($items[$langCode]->id);
				$allow      = $canEditReference && self::allowEdit($component, $table);
			}
			else
			{
				$items[$langCode] = new stdClass;
				$title      = JText::_('COM_ASSOCIATIONS_ADD_NEW_ASSOCIATION');
				$additional = '';
				$labelClass = 'label'; 
				$target     = $langCode . ':0:add';
				$allow      = $canCreate;
			}

			// Generate item Html.
			$options   = array(
				'option'    => 'com_associations',
				'view'      => 'association',
				'layout'    => 'edit',
				'component' => $component->key,
				'task'      => 'association.edit',
				'id'        => $itemId,
				'target'    => $target,
			);
			$url       = JRoute::_('index.php?' . http_build_query($options));
			$text      = strtoupper($language->sef);
			$langImage = JHtml::_('image', 'mod_languages/' . $language->image . '.gif', $language->title, array('title' => $language->title), true);
			$tooltip   = implode(' ', array($langImage, $title, $additional));

			$items[$langCode]->link = JHtml::_('tooltip', $tooltip, null, null, $text, $allow && $addLink ? $url : '', null, 'hasTooltip ' . $labelClass);
		}

		return JLayoutHelper::render('joomla.content.associations', $items);
	}

	/**
	 * Get a existing asset key using the item parents.
	 *
	 * @param   JRegistry  $component  Component properties.
	 * @param   object     $item       Item db row.
	 *
	 * @return  string  The asset key.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected static function getAssetKey(JRegistry $component, $item = null)
	{
		// Get the item asset.
		$asset = JTable::getInstance('Asset');
		$asset->loadByName($component->assetKey . '.' . $item->id);

		// If the item asset does not exist (ex: com_menus, com_contact, com_newsfeeds).
		if (is_null($asset->id))
		{
			// For menus component, if item asset does not exist, fallback to menu asset.
			if (!is_null($component->fields->menutype))
			{
				// If the menu type id is unknown get it from MenuType table.
				if (!isset($item->menutypeid))
				{
					$table = JTable::getInstance('MenuType');
					$table->load(array('menutype' => $item->{$component->fields->menutype}));
					$item->menutypeid = $table->id;
				}

				$asset->loadByName($component->realcomponent . '.menu.' . $item->menutypeid);
			}
			// For all other components, if item asset does not exist, fallback to category asset (if component supports).
			elseif (!is_null($component->fields->catid))
			{
				$asset->loadByName($component->realcomponent . '.category.' . $item->{$component->fields->catid});
			}
		}

		// If item asset, category/menu asset does not exist, fallback to component asset.
		if (is_null($asset->id))
		{
			$asset->loadByName($component->realcomponent);
		}

		return $asset->name;
	}

	/**
	 * Check if user is allowed to edit items.
	 *
	 * @param   JRegistry  $component  Component properties.
	 * @param   object     $item       Item db row.
	 *
	 * @return  boolean  True on allowed.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public static function allowEdit(JRegistry $component, $item = null)
	{
		$user = JFactory::getUser();

		// If no item properties return the component permissions for core.edit.
		if (is_null($item))
		{
			return $user->authorise('core.edit', $component->realcomponent);
		}

		// Get the asset key.
		$assetKey = self::getAssetKey($component, $item);

		// Check if can edit own.
		$canEditOwn = false;

		if (!is_null($component->fields->created_by))
		{
			$canEditOwn = $user->authorise('core.edit.own', $assetKey) && $item->{$component->fields->created_by} == $user->id;
		}

		// Check also core.edit permissions.
		return $canEditOwn || $user->authorise('core.edit', $assetKey);
	}

	/**
	 * Check if user is allowed to create items.
	 *
	 * @param   JRegistry  $component  Component properties.
	 * @param   object     $item       Item db row.
	 *
	 * @return  boolean  True on allowed.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public static function allowAdd(JRegistry $component, $item = null)
	{
		$user = JFactory::getUser();

		// If no item properties return the component permissions for core.edit.
		if (is_null($item))
		{
			return $user->authorise('core.create', $component->realcomponent);
		}


		// Check core.create permissions.
		return $user->authorise('core.create', self::getAssetKey($component, $item));
	}

	/**
	 * Check if user is allowed to perform check actions (checkin/checkout) on a item.
	 *
	 * @param   JRegistry  $component  Component properties.
	 * @param   object     $item       Item db row.
	 *
	 * @return  boolean  True on allowed.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public static function allowCheckActions(JRegistry $component, $item = null)
	{
		// If no item properties or component doesn't have checked_out field, doesn't support checkin/checkout.
		if (is_null($item) || is_null($component->fields->checked_out))
		{
			return false;
		}

		// All other cases. Check if user checked out this item.
		return in_array($item->{$component->fields->checked_out}, array(JFactory::getUser()->id, 0));
	}
}
