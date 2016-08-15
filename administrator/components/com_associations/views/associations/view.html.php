<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_associations
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('AssociationsHelper', JPATH_ADMINISTRATOR . '/components/com_associations/helpers/associations.php');

/**
 * View class for a list of articles.
 *
 * @since  __DEPLOY_VERSION__
 */
class AssociationsViewAssociations extends JViewLegacy
{
	/**
	 * An array of items
	 *
	 * @var  array
	 *
	 * @since __DEPLOY_VERSION__
	 */
	protected $items;

	/**
	 * The pagination object
	 *
	 * @var  JPagination
	 *
	 * @since __DEPLOY_VERSION__
	 */
	protected $pagination;

	/**
	 * The model state
	 *
	 * @var  object
	 *
	 * @since __DEPLOY_VERSION__
	 */
	protected $state;

	/**
	 * Selected item type properties.
	 *
	 * @var  Registry
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public $itemType = null;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function display($tpl = null)
	{
		$this->state         = $this->get('State');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		if (!JLanguageAssociations::isEnabled())
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_ASSOCIATIONS_ERROR_NO_ASSOC'), 'warning');
		}
		elseif ($this->state->get('itemtype') == '' || $this->state->get('language') == '')
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_ASSOCIATIONS_NOTICE_NO_SELECTORS'), 'notice');
		}
		else
		{
			$this->itemType = AssociationsHelper::getItemTypeProperties($this->state->get('itemtype'));

			// Dynamic filter form.
			// This selectors doesn't have to activate the filter bar.
			unset($this->activeFilters['itemtype']);
			unset($this->activeFilters['language']);

			// Remove filters options depending on selected itemtype.
			if (is_null($this->itemType) || is_null($this->itemType->fields->state))
			{
				unset($this->activeFilters['state']);
				$this->filterForm->removeField('state', 'filter');
			}
			if (is_null($this->itemType) || is_null($this->itemType->fields->catid))
			{
				unset($this->activeFilters['category_id']);
				$this->filterForm->removeField('category_id', 'filter');
			}
			if (is_null($this->itemType) || is_null($this->itemType->fields->menutype))
			{
				unset($this->activeFilters['menutype']);
				$this->filterForm->removeField('menutype', 'filter');
			}
			if (is_null($this->itemType)
				|| (is_null($this->itemType->fields->catid) && !in_array($this->itemType->component, array('com_categories', 'com_menus'))))
			{
				unset($this->activeFilters['level']);
				$this->filterForm->removeField('level', 'filter');
			}
			if (is_null($this->itemType) || is_null($this->itemType->fields->access))
			{
				unset($this->activeFilters['access']);
				$this->filterForm->removeField('access', 'filter');
			}

			// Add extension attribute to category filter.
			if (!is_null($this->itemType) && !is_null($this->itemType->fields->catid))
			{
				$this->filterForm->setFieldAttribute('category_id', 'extension', $this->itemType->component, 'filter');
			}

			// Only allow ordering by what the component item type allows.
			if (in_array($this->state->get('list.ordering', $this->itemType->defaultOrdering[0]), $this->itemType->excludeOrdering))
			{
				$this->state->set('list.ordering', $this->itemType->defaultOrdering[0]);
				$this->state->set('list.direction', $this->itemType->defaultOrdering[1]);
				$this->filterForm->setValue('fullordering', 'list', $this->itemType->defaultOrdering[0] . ' ' . $this->itemType->defaultOrdering[1]);
			}

			$this->items      = $this->get('Items');
			$this->pagination = $this->get('Pagination');

			$linkParameters = array(
				'layout'     => 'edit',
				'itemtype'   => $this->itemType->key,
				'task'       => 'association.edit',
			);

			$this->editUri = 'index.php?option=com_associations&view=association&' . http_build_query($linkParameters);
		}

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors), 500);

			return false;
		}

		$this->addToolbar();

		// Will add sidebar if needed $this->sidebar = JHtmlSidebar::render();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function addToolbar()
	{
		$user  = JFactory::getUser();

		JToolbarHelper::title(JText::_('COM_ASSOCIATIONS_HEADER_SELECT_REFERENCE'), 'contract');
		/*
		 * @todo Verify later if new/edit/select is really needed
		*/
		// JToolbarHelper::editList('association.edit');

		if (isset($this->itemType) && !is_null($this->itemType->fields->checked_out))
		{
			JToolbarHelper::checkin('associations.checkin', 'JTOOLBAR_CHECKIN', true);
		}

		if ($user->authorise('core.admin', 'com_associations') || $user->authorise('core.options', 'com_associations'))
		{
			if (!isset($this->itemType))
			{
				JToolbarHelper::custom('associations.purge', 'purge', 'purge', 'COM_ASSOCIATIONS_PURGE', false, false);
				JToolbarHelper::custom('associations.clean', 'refresh', 'refresh', 'COM_ASSOCIATIONS_DELETE_ORPHANS', false, false);
			}
			JToolbarHelper::preferences('com_associations');
		}

		/*
		 * @todo Help page
		*/
		JToolbarHelper::help('JGLOBAL_HELP');
	}
}
