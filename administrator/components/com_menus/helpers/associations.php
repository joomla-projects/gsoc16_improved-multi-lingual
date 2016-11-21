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
 * Menus Associations helper.
 *
 * @since  __DEPLOY_VERSION__
 */
class MenusAssociationsHelper extends JHelperContent
{
	/**
	 * Check if item supports associations
	 *
	 * @return  boolean
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public static function hasAssociationsSupport()
	{
		return true;
	}

	/**
	 * Check if item supports category associations
	 *
	 * @return  boolean
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public static function hasAssociationsCategories()
	{
		return false;
	}

	/**
	 * Mapping of database columns alias.
	 *
	 * @return  array
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public static function getListQuery($itemType = '')
	{
		// Create a new query object.
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$user = JFactory::getUser();
		$app = JFactory::getApplication();

		// Select all fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.id, a.menutype, a.title, a.alias, a.level, a.published, a.checked_out' .
				'a.checked_out_time, a.access, a.lft, a.language'
			)
		);

		$query->from($db->quoteName('#__menu') . ' AS a');

		// Join over the language
		$query->select('l.title AS language_title, l.image AS language_image')
			->join('LEFT', $db->quoteName('#__languages') . ' AS l ON l.lang_code = a.language');

		// Join over the users.
		$query->select('u.name AS editor')
			->join('LEFT', $db->quoteName('#__users') . ' AS u ON u.id = a.checked_out');

		// Join over the asset groups.
		$query->select('ag.title AS access_level')
			->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');

		// Join over the menu types.
		$query->select($db->quoteName('mt.title', 'menutype_title'))
			->join('LEFT', $db->quoteName('#__menu_types', 'mt') . ' ON ' . $db->qn('mt.menutype') . ' = ' . $db->qn('a.menutype'));

		// Join over the associations.
		$assoc = JLanguageAssociations::isEnabled();

		if ($assoc)
		{
			$query->select('COUNT(asso2.id)>1 as association')
				->join('LEFT', '#__associations AS asso ON asso.id = a.id AND asso.context=' . $db->quote('com_menus.item'))
				->join('LEFT', '#__associations AS asso2 ON asso2.key = asso.key')
				->group('a.id, e.enabled, l.title, l.image, u.name, c.element, ag.title, e.name, mt.title');
		}

		// Join over the extensions
		$query->select('e.name AS name')
			->join('LEFT', '#__extensions AS e ON e.extension_id = a.component_id');

		// Exclude the root category.
		$query->where('a.id > 1')
			->where('a.client_id = 0');

		return $query;
	}
}
