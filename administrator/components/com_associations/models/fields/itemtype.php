<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_associations
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('JPATH_BASE') or die;

JLoader::register('AssociationsHelper', JPATH_ADMINISTRATOR . '/components/com_associations/helpers/associations.php');

JFormHelper::loadFieldClass('groupedlist');

/**
 * A drop down containing all component item types that implement associations.
 *
 * @since  __DEPLOY_VERSION__
 */
class JFormFieldItemType extends JFormFieldGroupedList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $type = 'ItemType';
	
	/**
	 * Method to get the field input markup.
	 *
	 * @return  array  The field option objects as a nested array in groups.
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  UnexpectedValueException
	 */
	protected function getGroups()
	{
		$user              = JFactory::getUser();
		$options           = array();
		$itemTypeList      = array();
		$excludeComponents = array(
			'com_admin',
			'com_ajax',
			'com_associations',
			'com_banners',
			'com_cache',
			'com_checkin',
			'com_config',
			'com_categories',
			'com_contenthistory',
			'com_cpanel',
			'com_finder',
			'com_installer',
			'com_joomlaupdate',
			'com_languages',
			'com_login',
			'com_media',
			'com_messages',
			'com_modules',
			'com_plugins',
			'com_postinstall',
			'com_redirect',
			'com_search',
			'com_tags',
			'com_templates',
			'com_users',
		);
		$excludeItemTypes = array(
			'com_contact'   => array('contacts'),
			'com_content'   => array('articles', 'featured'),
			'com_menus'     => array('items', 'menu', 'menus', 'menutypes'),
			'com_newsfeeds' => array('newsfeeds'),
		);

		// Get all admin components.
		foreach (glob(JPATH_ADMINISTRATOR . '/components/*', GLOB_NOSORT | GLOB_ONLYDIR) as $componentAdminPath)
		{
			$component           = basename($componentAdminPath);
			$componentModelsPath = $componentAdminPath . '/models';

			// Only components that exist also in the site client, aren't in the excluded components array and have models.
			if (!is_dir($componentModelsPath) || in_array($component, $excludeComponents) || !$user->authorise('core.manage', $component))
			{
				continue;
			}

			// Check if component uses associations, by checking is models.
			foreach (glob($componentModelsPath . '/*.php', GLOB_NOSORT) as $modelFile)
			{
				$itemTypeName = strtolower(basename($modelFile, '.php'));

				// Only item types that aren't in the excluded components item types array.
				if (isset($excludeItemTypes[$component]) && in_array($itemTypeName, $excludeItemTypes[$component]))
				{
					continue;
				}

				$itemType = AssociationsHelper::getItemTypeProperties($component . '.' . $itemTypeName);

				if ($itemType->componentEnabled)
				{
					// Check if component item type supports associations. Add item option to select box if so.
					if ($itemType->associations->support && !in_array($itemType->assetKey, $itemTypeList))
					{
						$options[$itemType->componentTitle][] = JHtml::_('select.option', $itemType->assetKey, $itemType->title);

						array_push($itemTypeList, $itemType->assetKey);
					}

					$itemCategoryType = AssociationsHelper::getItemTypeProperties($itemType->categoryContext . '.category');

					// Check if component item type support categories. Add category option to select box if so.
					if (isset($itemType->fields)
						&& !is_null($itemType->fields->catid)
						&& $itemCategoryType->associations->support
						&& !in_array($itemCategoryType->assetKey, $itemTypeList))
					{
						$options[$itemCategoryType->componentTitle][] = JHtml::_(
							'select.option',
							$itemCategoryType->assetKey,
							$itemCategoryType->title
						);

						array_push($itemTypeList, $itemCategoryType->assetKey);
					}
				}
			}
		}

		// Sort by alpha order.
		ksort($options, SORT_NATURAL);

		// Add options to parent array.
		return array_merge(parent::getGroups(), $options);
	}
}
