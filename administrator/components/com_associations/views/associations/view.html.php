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
			$this->items      = $this->get('Items');
			$this->pagination = $this->get('Pagination');

			$linkParameters = array(
				'layout'     => 'edit',
				'acomponent' => $this->component->component,
				'aview'      => $this->component->item,
			);

			if (!is_null($this->component->extension))
			{
				$linkParameters['extension'] = $this->component->extension;
			}

			$this->editLink = 'index.php?option=com_associations&view=association&' . http_build_query($linkParameters);
		}

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

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
