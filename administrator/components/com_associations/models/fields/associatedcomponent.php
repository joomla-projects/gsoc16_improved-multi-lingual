<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_associations
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('JPATH_BASE') or die;

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
		$lang              = JFactory::getLanguage();
		$options           = array();
		$excludeComponents = array('com_categories', 'com_menus', 'com_config', 'com_admin', 'com_users');
		$typeAliasList     = array();

		// Get all admin components.
		foreach (glob(JPATH_ADMINISTRATOR . '/components/*', GLOB_NOSORT | GLOB_ONLYDIR) as $componentAdminPath)
		{
			$component           = basename($componentAdminPath);
			$componentName       = ucfirst(substr($component, 4));
			$componentModelsPath = $componentAdminPath . '/models';

			// Only components that exist also in the site client, aren't in the excluded components array and have models.
			if (!is_dir(JPATH_SITE . '/components/' . $component) || !is_dir($componentModelsPath) || in_array($component, $excludeComponents))
			{
				continue;
			}

			// Check if component uses associations, by checking is models.
			foreach (glob($componentModelsPath . '/*.php', GLOB_NOSORT) as $modelFile)
			{
				$itemName = ucfirst(basename($modelFile, '.php'));

				JLoader::register($componentName . 'Model' . $itemName, $modelFile);
				$model = JModelLegacy::getInstance($itemName, $componentName . 'Model', array('ignore_request' => true));

				// Check if this model uses associations.
				if ($model->get('associationsContext') && $model->get('typeAlias') && !in_array($model->get('typeAlias'), $typeAliasList))
				{
					// Load component language file.
					$lang->load($component, JPATH_ADMINISTRATOR) || $lang->load($component, $componentAdminPath);

					// Add component option select box.
					$options[JText::_($component)][] = JHtml::_('select.option', $model->get('typeAlias'), JText::_($component));

					array_push($typeAliasList, $model->get('typeAlias'));
				}
			}

			// Check if component uses categories with associations. Add category option to select box if so.
			if (file_exists(JPATH_SITE . '/components/' . $component . '/helpers/association.php'))
			{
				JLoader::register($componentName . 'HelperAssociation', JPATH_SITE . '/components/' . $component . '/helpers/association.php');

				if (method_exists($componentName . 'HelperAssociation', 'getCategoryAssociations'))
				{
					// Load component language file.
					$lang->load($component, JPATH_ADMINISTRATOR) || $lang->load($component, $componentAdminPath);

					$options[JText::_($component)][] = JHtml::_('select.option', 'com_categories.category|' . $component, JText::_("JCATEGORIES"));
				}
			}
		}

		// Load menus component language file.
		$lang->load('com_menus', JPATH_ADMINISTRATOR) || $lang->load('com_menus', JPATH_ADMINISTRATOR . '/components/com_menus');

		// Add also the menus component to the list.
		$options[JText::_("COM_MENUS_SUBMENU_MENUS")][] = JHtml::_('select.option', 'com_menus.item', JText::_("COM_MENUS_SUBMENU_ITEMS"));

		// Sort by alpha order.
		ksort($options, SORT_NATURAL);

		// Add options to parent array.
		return array_merge(parent::getGroups(), $options);
	}
}
