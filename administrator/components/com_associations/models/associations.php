<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_associations
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Methods supporting a list of article records.
 *
 * @since  __DEPLOY_VERSION__
 */
class AssociationsModelAssociations extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since  __DEPLOY_VERSION__
	 * @see     JController
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'title',
				'ordering',
				'component',
				'language',
				'association',
				'menutype', 'menutype_title',
				'level',
				'published',
				'category_id', 'category_title',
				'access', 'access_level',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected function populateState($ordering = 'ordering', $direction = 'asc')
	{
		$this->setState('component', $this->getUserStateFromRequest($this->context . '.component', 'component', '', 'string'));
		$this->setState('language', $this->getUserStateFromRequest($this->context . '.language', 'language', '', 'string'));

		$this->setState('filter.search', $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string'));
		$this->setState('filter.published', $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '', 'cmd'));
		$this->setState('filter.category_id', $this->getUserStateFromRequest($this->context . '.filter.category_id', 'filter_category_id', '', 'cmd'));
		$this->setState('filter.menutype', $this->getUserStateFromRequest($this->context . '.filter.menutype', 'filter_menutype', '', 'string'));
		$this->setState('filter.access', $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access', '', 'string'));
		$this->setState('filter.level', $this->getUserStateFromRequest($this->context . '.filter.level', 'filter_level', '', 'cmd'));

		// List state information.
		parent::populateState($ordering, $direction);
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('component');
		$id .= ':' . $this->getState('language');
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.published');
		$id .= ':' . $this->getState('filter.category_id');
		$id .= ':' . $this->getState('filter.menutype');
		$id .= ':' . $this->getState('filter.access');
		$id .= ':' . $this->getState('filter.level');

		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$user      = JFactory::getUser();
		$db        = $this->getDbo();
		$query     = $db->getQuery(true);
		$component = AssociationsHelper::getComponentProperties($this->getState('component'));

		// Main query.
		$query->select($db->quoteName('a.id'))
			->select($db->quoteName('a.' . $component->fields->title, 'title'))
			->select($db->quoteName('a.' . $component->fields->alias, 'alias'))
			->from($db->quoteName($component->dbtable, 'a'));

		// Select author for ACL checks
		if (!is_null($component->fields->created_by))
		{
			$query->select($db->quoteName('a.' . $component->fields->created_by));
		}

		// Select checked out data for check in checkins.
		if (!is_null($component->fields->checked_out) && !is_null($component->fields->checked_out_time))
		{
			$query->select($db->quoteName('a.' . $component->fields->checked_out))
				->select($db->quoteName('a.' . $component->fields->checked_out_time));

			// Join over the users.
			$query->select($db->quoteName('u.name', 'editor'))
				->join('LEFT', $db->quoteName('#__users', 'u') . ' ON ' . $db->qn('u.id') . ' = ' . $db->qn('a.' . $component->fields->checked_out));
		}

		// Join over the language
		$query->select($db->quoteName('a.' . $component->fields->language, 'language'))
			->select($db->quoteName('l.title', 'language_title'))
			->select($db->quoteName('l.image', 'language_image'))
			->join('LEFT', $db->quoteName('#__languages', 'l') . ' ON ' . $db->qn('l.lang_code') . ' = ' . $db->qn('a.' . $component->fields->language));

		// Join over the associations.
		$query->select('COUNT(' . $db->quoteName('asso2.id') . ') > 1 AS ' . $db->quoteName('association'))
			->join(
				'LEFT',
				$db->quoteName('#__associations', 'asso') . ' ON ' . $db->quoteName('asso.id') . ' = ' . $db->quoteName('a.id')
				. ' AND ' . $db->quoteName('asso.context') . ' = ' . $db->quote($component->associations->context)
			)
			->join('LEFT', $db->quoteName('#__associations', 'asso2') . ' ON ' . $db->quoteName('asso2.key') . ' = ' . $db->quoteName('asso.key'))
			->group($db->quoteName(array('a.id', 'title', 'language')));

		// If component supports ordering, select the ordering also.
		if (!is_null($component->fields->ordering))
		{
			$query->select($db->quoteName('a.' . $component->fields->ordering, 'ordering'));
		}

		// If component supports state, select the published state also.
		if (!is_null($component->fields->published))
		{
			$query->select($db->quoteName('a.' . $component->fields->published, 'published'));
		}

		// If component supports level, select the level also.
		if (!is_null($component->fields->level))
		{
			$query->select($db->quoteName('a.' . $component->fields->level, 'level'));
		}

		// If component supports categories, select the category also.
		if (!is_null($component->fields->catid))
		{
			$query->select($db->quoteName('a.' . $component->fields->catid, 'catid'))
				->select($db->quoteName('c.title', 'category_title'))
				->join('LEFT', $db->quoteName('#__categories', 'c') . ' ON ' . $db->qn('c.id') . ' = ' . $db->qn('a.' . $component->fields->catid));
		}

		// If component supports menu type, select the menu type also.
		if (!is_null($component->fields->menutype))
		{
			$query->select($db->quoteName('a.' . $component->fields->menutype, 'menutype'))
				->select($db->quoteName('mt.title', 'menutype_title'))
				->select($db->quoteName('mt.id', 'menutypeid'))
				->join('LEFT', $db->quoteName('#__menu_types', 'mt') . ' ON ' . $db->qn('mt.menutype') . ' = ' . $db->qn('a.' . $component->fields->menutype));
		}

		// If component supports access level, select the access level also.
		if (!is_null($component->fields->access))
		{
			$query->select($db->quoteName('a.' . $component->fields->access, 'access'))
				->select($db->quoteName('ag.title', 'access_level'))
				->join('LEFT', $db->quoteName('#__viewlevels', 'ag') . ' ON ' . $db->qn('ag.id') . ' = ' . $db->qn('a.' . $component->fields->access));

			// Implement View Level Access
			if (!$user->authorise('core.admin', $component->realcomponent))
			{
				$query->where('a.' . $component->fields->access . ' IN (' . implode(',', $user->getAuthorisedViewLevels()) . ')');
			}
		}

		// If component is menus we need to remove the root item and the administrator menu.
		if ($component->dbtable === '#__menu')
		{
			$query->where($db->quoteName('a.id') . ' > 1')
				->where($db->quoteName('a.client_id') . ' = 0');
		}
		// If component is categories we need to remove all other component categories.
		elseif ($component->dbtable === '#__categories')
		{
			$query->where($db->quoteName('a.extension') . ' = ' . $db->quote($component->extension));
		}

		// Filter on the language.
		if ($language = $this->getState('language'))
		{
			$query->where($db->quoteName('a.' . $component->fields->language) . ' = ' . $db->quote($language));
		}

		// Filter by published state.
		$published = $this->getState('filter.published');

		if (is_numeric($published))
		{
			$query->where($db->quoteName('a.' . $component->fields->published) . ' = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where($db->quoteName('a.' . $component->fields->published) . ' IN (0, 1)');
		}

		// Filter on the category.
		$baselevel = 1;

		if ($categoryId = $this->getState('filter.category_id'))
		{
			$categoryTable = JTable::getInstance('Category', 'JTable');
			$categoryTable->load($categoryId);
			$baselevel = (int) $categoryTable->level;
			$query->where($db->quoteName('c.lft') . ' >= ' . (int) $categoryTable->lft)
				->where($db->quoteName('c.rgt') . ' <= ' . (int) $categoryTable->rgt);
		}

		// Filter on the level.
		if ($level = $this->getState('filter.level'))
		{
			$tableAlias = in_array($component->component, array('com_menus', 'com_categories')) ? 'a' : 'c';
			$query->where($db->quoteName($tableAlias . '.level') . ' <= ' . ((int) $level + (int) $baselevel - 1));
		}

		// Filter by menu type.
		if ($menutype = $this->getState('filter.menutype'))
		{
			$query->where('a.' . $component->fields->menutype . ' = ' . $db->quote($menutype));
		}

		// Filter by access level.
		if ($access = $this->getState('filter.access'))
		{
			$query->where('a.' . $component->fields->access . ' = ' . (int) $access);
		}

		// Filter by search in name.
		if ($search = $this->getState('filter.search'))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
				$query->where('(' . $db->quoteName('a.' . $component->fields->title) . ' LIKE ' . $search
					. ' OR ' . $db->quoteName('a.' . $component->fields->alias) . ' LIKE ' . $search . ')');
			}
		}

		// Add the list ordering clause.
		$query->order($db->escape($this->getState('list.ordering') . ' ' . $this->getState('list.direction')));

		return $query;
	}
}
