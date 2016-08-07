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
 * A drop down containing all components that implement associations
 *
 * @since  __DEPLOY_VERSION__
 */
class JFormFieldAssociatedComponent extends JFormFieldGroupedList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $type = 'AssociatedComponent';
	
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
		$typeAliasList     = array();
		$excludeComponents = array(
			'com_admin',
			'com_ajax',
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
				$cp = AssociationsHelper::getComponentProperties($component . '.' . strtolower(basename($modelFile, '.php')));

				// Check if component supports associations.
				if ($cp->enabled && $cp->associations->support && $cp->associations->supportItem && !in_array($cp->typeAlias, $typeAliasList))
				{
					// Add component option select box.
					$options[$cp->title][] = JHtml::_('select.option', $cp->typeAlias, $cp->title);

					array_push($typeAliasList, $cp->typeAlias);
				}
			}

			// Check if component uses categories with associations. Add category option to select box if so.
			$cp = AssociationsHelper::getComponentProperties($component);

			// Check if component uses categories with associations. Add category option to select box if so.
			if ($cp->enabled && $cp->associations->supportCategories)
			{
				$languageKey           = JText::_(strtoupper($cp->realcomponent) . '_CATEGORIES');
				$options[$cp->title][] = JHtml::_('select.option', 'com_categories.category:' . $cp->realcomponent, $languageKey);
			}
		}

		// Sort by alpha order.
		ksort($options, SORT_NATURAL);

		// Add options to parent array.
		return array_merge(parent::getGroups(), $options);
	}
}
