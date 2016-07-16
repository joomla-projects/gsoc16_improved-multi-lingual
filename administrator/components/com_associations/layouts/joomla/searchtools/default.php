<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

$data = $displayData;

// Receive overridable options
$data['options'] = !empty($data['options']) ? $data['options'] : array();

if ($data['view'] instanceof AssociationsViewAssociations)
{
	JFactory::getDocument()->addStyleDeclaration("
		/* Fixed filter field in search bar */
		.js-stools .js-stools-selector {
			float: left;
			margin-right: 10px;
			min-width: 220px;
		}
		html[dir=rtl] .js-stools .js-stools-selector {
			float: right;
			margin-left: 10px
			margin-right: 0;
		}
		.js-stools .js-stools-container-bar .js-stools-selector .chzn-container {
			padding: 3px 0;
		}
	");

	// This selectors doesn't have to activate the filter bar
	unset($data['view']->activeFilters['component']);
	unset($data['view']->activeFilters['language']);
	
	//$order = $data['view']->filterForm->getField('fullordering', 'list');
	
	// Remove filters and ordering options depending on selected component.
	if (is_null($data['view']->component) || is_null($data['view']->component->fields->published))
	{
		unset($data['view']->activeFilters['published']);
		$data['view']->filterForm->removeField('published', 'filter');
	}
	if (is_null($data['view']->component) || is_null($data['view']->component->fields->catid))
	{
		unset($data['view']->activeFilters['category_id']);
		$data['view']->filterForm->removeField('category_id', 'filter');
	}
	if (is_null($data['view']->component) || is_null($data['view']->component->fields->menutype))
	{
		unset($data['view']->activeFilters['menutype']);
		$data['view']->filterForm->removeField('menutype', 'filter');
	}
	if (is_null($data['view']->component) || (is_null($data['view']->component->fields->catid) && is_null($data['view']->component->fields->menutype)))
	{
		unset($data['view']->activeFilters['level']);
		$data['view']->filterForm->removeField('level', 'filter');
	}
	if (is_null($data['view']->component) || is_null($data['view']->component->fields->access))
	{
		unset($data['view']->activeFilters['access']);
		$data['view']->filterForm->removeField('access', 'filter');
	}

	// Add extension attribute to category filter.
	if (!is_null($data['view']->component) && !is_null($data['view']->component->fields->catid))
	{
		$data['view']->filterForm->setFieldAttribute('category_id', 'extension', $data['view']->component->component, 'filter');
	}
}

// Display the main joomla layout
echo JLayoutHelper::render('joomla.searchtools.default', $data, null, array('component' => 'none'));
