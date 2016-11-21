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
 * Contact Associations helper.
 *
 * @since  __DEPLOY_VERSION__
 */
class ContactAssociationsHelper extends JHelperContent
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
		return true;
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

		// Select the required fields from the table.
		$query->select(
			$db->quoteName(
				explode(', ', $this->getState(
					'list.select',
					'a.id, a.name, a.alias, a.checked_out, a.checked_out_time, a.catid, a.published,' .
					' a.access, a.created_by, a.ordering, a.language'
					)
				)
			)
		);

		$query->from($db->quoteName('#__contact_details', 'a'));

		// Join over the language
		$query->select($db->quoteName('l.title', 'language_title'))
			->select($db->quoteName('l.image', 'language_image'))
			->join(
				'LEFT',
				$db->quoteName('#__languages', 'l') . ' ON ' . $db->quoteName('l.lang_code') . ' = ' . $db->quoteName('a.language')
			);

		// Join over the users for the checked out user.
		$query->select($db->quoteName('uc.name', 'editor'))
			->join(
				'LEFT',
				$db->quoteName('#__users', 'uc') . ' ON ' . $db->quoteName('uc.id') . ' = ' . $db->quoteName('a.checked_out')
			);

		// Join over the asset groups.
		$query->select($db->quoteName('ag.title', 'access_level'))
			->join(
				'LEFT',
				$db->quoteName('#__viewlevels', 'ag') . ' ON ' . $db->quoteName('ag.id') . ' = ' . $db->quoteName('a.access')
			);

		// Join over the categories.
		$query->select($db->quoteName('c.title', 'category_title'))
			->join(
				'LEFT',
				$db->quoteName('#__categories', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('a.catid')
			);

		// Join over the associations.
		$assoc = JLanguageAssociations::isEnabled();

		if ($assoc)
		{
			$query->select('COUNT(' . $db->quoteName('asso2.id') . ') > 1 as ' . $db->quoteName('association'))
				->join(
					'LEFT',
					$db->quoteName('#__associations', 'asso') . ' ON ' . $db->quoteName('asso.id') . ' = ' . $db->quoteName('a.id')
					. ' AND ' . $db->quoteName('asso.context') . ' = ' . $db->quote('com_contact.item')
				)
				->join(
					'LEFT',
					$db->quoteName('#__associations', 'asso2') . ' ON ' . $db->quoteName('asso2.key') . ' = ' . $db->quoteName('asso.key')
				)
				->group(
					$db->quoteName(
						array(
							'a.id',
							'a.name',
							'a.alias',
							'a.checked_out',
							'a.checked_out_time',
							'a.catid',
							'a.user_id',
							'a.published',
							'a.access',
							'a.created',
							'a.created_by',
							'a.ordering',
							'a.featured',
							'a.language',
							'a.publish_up',
							'a.publish_down',
							'ul.name' ,
							'ul.email',
							'l.title' ,
							'l.image' ,
							'uc.name' ,
							'ag.title' ,
							'c.title',
							'c.level'
						)
					)
				);
		}

		return $query;
	}
}
