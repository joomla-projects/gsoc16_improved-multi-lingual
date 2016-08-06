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
	 * Selected component
	 *
	 * @var  Registry
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public $component = null;

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
		elseif ($this->state->get('component') == '' || $this->state->get('language') == '')
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_ASSOCIATIONS_NOTICE_NO_SELECTORS'), 'notice');
		}
		else
		{
			$this->component  = AssociationsHelper::getComponentProperties($this->state->get('component'));

			// Dynamic filter form.
			// This selectors doesn't have to activate the filter bar.
			unset($this->activeFilters['component']);
			unset($this->activeFilters['language']);
			
			// Remove filters options depending on selected component.
			if (is_null($this->component) || is_null($this->component->fields->published))
			{
				unset($this->activeFilters['published']);
				$this->filterForm->removeField('published', 'filter');
			}
			if (is_null($this->component) || is_null($this->component->fields->catid))
			{
				unset($this->activeFilters['category_id']);
				$this->filterForm->removeField('category_id', 'filter');
			}
			if (is_null($this->component) || is_null($this->component->fields->menutype))
			{
				unset($this->activeFilters['menutype']);
				$this->filterForm->removeField('menutype', 'filter');
			}
			if (is_null($this->component)
				|| (is_null($this->component->fields->catid) && !in_array($this->component->component, array('com_categories', 'com_menus'))))
			{
				unset($this->activeFilters['level']);
				$this->filterForm->removeField('level', 'filter');
			}
			if (is_null($this->component) || is_null($this->component->fields->access))
			{
				unset($this->activeFilters['access']);
				$this->filterForm->removeField('access', 'filter');
			}

			// Add extension attribute to category filter.
			if (!is_null($this->component) && !is_null($this->component->fields->catid))
			{
				$this->filterForm->setFieldAttribute('category_id', 'extension', $this->component->component, 'filter');
			}
	
			// Only allow ordering by what the component allows.
			if (in_array($this->state->get('list.ordering', $this->component->defaultOrdering[0]), $this->component->excludeOrdering))
			{
				$this->state->set('list.ordering', $this->component->defaultOrdering[0]);
				$this->state->set('list.direction', $this->component->defaultOrdering[1]);
				$this->filterForm->setValue('fullordering', 'list', $this->component->defaultOrdering[0] . ' ' . $this->component->defaultOrdering[1]);
			}

			$this->items      = $this->get('Items');
			$this->pagination = $this->get('Pagination');

			$linkParameters = array(
				'layout'     => 'edit',
				'component'  => $this->state->get('component'),
				'task'       => 'association.edit',
			);

			$this->editUri = 'index.php?option=com_associations&view=association&' . http_build_query($linkParameters);

			// Load the current component html helper class.
			JLoader::register($this->component->associations->htmlhelper->class, $this->component->associations->htmlhelper->file);
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

		if (isset($this->component) && !is_null($this->component->fields->checked_out))
		{
			JToolbarHelper::checkin('associations.checkin', 'JTOOLBAR_CHECKIN', true);
		}

		if ($user->authorise('core.admin', 'com_associations') || $user->authorise('core.options', 'com_associations'))
		{
			JToolbarHelper::preferences('com_associations');
		}

		/*
		 * @todo Help page
		*/
		JToolbarHelper::help('JGLOBAL_HELP');
	}
}
