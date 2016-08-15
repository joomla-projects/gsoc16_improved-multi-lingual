<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_associations
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
JHtml::_('behavior.tabstate');

if (!JFactory::getUser()->authorise('core.manage', 'com_associations'))
{
	throw new JControllerExceptionNotallowed(JText::_('JERROR_ALERTNOAUTHOR'), 403);
}

JLoader::register('AssociationsHelper', __DIR__ . '/helpers/associations.php');

// Check if user has permission to access the component item type.
if ($itemKey = JFactory::getApplication()->input->get('itemtype', '', 'string'))
{
	$itemType = AssociationsHelper::getItemTypeProperties($itemKey);

	if (!$itemType->componentEnabled)
	{
		throw new Exception(JText::_('JLIB_APPLICATION_ERROR_COMPONENT_NOT_FOUND') . ' ' . $itemType->realcomponent, 404);
	}

	if (!$itemType->associations->support)
	{
		throw new Exception(JText::_('COM_ASSOCIATIONS_COMPONENT_NOT_SUPPORTED') . ' ' . $itemType->realcomponent, 404);
	}

	if (!JFactory::getUser()->authorise('core.manage', $itemType->realcomponent))
	{
		throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
	}
}

$controller = JControllerLegacy::getInstance('Associations');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
