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

JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Framework.
 *
 * @since  __DEPLOY_VERSION__
 */
class JFormFieldItemLanguage extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var        string
	 * @since   __DEPLOY_VERSION__
	 */
	protected $type = 'ItemLanguage';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getOptions()
	{
		$input       = JFactory::getApplication()->input;
		$itemType    = AssociationsHelper::getItemTypeProperties($input->get('itemtype', '', 'string'));
		$referenceId = $input->get('id', 0, 'int');

		// Get reference language.
		$table = clone $itemType->table;
		$table->load($referenceId);
		$referenceLang = $table->{$itemType->fields->language};

		// Get item associations given ID and item type
		$associations = AssociationsHelper::getAssociationList($itemType, $referenceId);

		// Check if user can create items in this component item type.
		$canCreate = AssociationsHelper::allowAdd($itemType, null);

		// Gets existing languages.
		$existingLanguages = AssociationsHelper::getContentLanguages();

		$options = array();

		// Each option has the format "<lang>|<id>", example: "en-GB|1"
		foreach ($existingLanguages as $langCode => $language)
		{
			// If language code is equal to reference language we don't need it.
			if ($language->lang_code == $referenceLang)
			{
				continue;
			}

			$options[$langCode]       = new stdClass;
			$options[$langCode]->text = $language->title;

			// If association exists in this language.
			if (isset($associations[$language->lang_code]))
			{
				$itemId                    = (int) $associations[$language->lang_code]->id;
				$options[$langCode]->value = $language->lang_code . ':' . $itemId . ':edit';

				// Load the item.
				$table->load($itemId);

				 // Check if user does have permission to edit the associated item.
				$canEdit = AssociationsHelper::allowEdit($itemType, $table);

				// Do an additional check to check if user can edit a checked out item (if component item type supports it).
				$canCheckout = AssociationsHelper::allowCheckActions($itemType, $table);

				// Disable language if user is not allowed to edit the item associated to it.
				$options[$langCode]->disable = !($canEdit && $canCheckout);
			}
			else
			{
				// New item, id = 0 and disabled if user is not allowed to create new items.
				$options[$langCode]->value   = $language->lang_code . ':0:add';

				// Disable language if user is not allowed to create items.
				$options[$langCode]->disable = !$canCreate;
			}
		}

		return array_merge(parent::getOptions(), $options);
	}
}
