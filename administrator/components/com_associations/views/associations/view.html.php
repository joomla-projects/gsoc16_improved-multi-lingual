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
		$this->menuType      = false;

		if (!JLanguageAssociations::isEnabled())
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_ASSOCIATIONS_ERROR_NO_ASSOC'), 'warning');
		}
		elseif ($this->state->get('associationcomponent') == '' || $this->state->get('associationlanguage') == '')
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_ASSOCIATIONS_NOTICE_NO_SELECTORS'), 'notice');
		}
		else
		{
			$this->items      = $this->get('Items');
			$this->pagination = $this->get('Pagination');
			$componentFilter  = $this->state->get('associationcomponent');
			$parts            = explode('.', $componentFilter);
			$comp             = $parts[0];
			$assocItem        = $parts[1];
			$this->compLevel  = false;

			JHtml::addIncludePath(JPATH_ADMINISTRATOR . '/components/' . $comp . '/helpers/html');

			// Get the value in the Association column
			if ($comp == "com_content")
			{
				$this->assocValue = "contentadministrator.association";
			}
			elseif ($comp == "com_categories")
			{
				$this->assocValue = "categoriesadministrator.association";
				$this->compLevel  = true;
			}
			elseif ($comp == "com_menus")
			{
				$this->assocValue = "MenusHtml.Menus.association";
				$this->compLevel  = true;
				$this->menuType   = true;
			}
			else
			{
				$this->assocValue = $assocItem . '.association';
			}

			// If it's not a category
			if ($componentFilter != '' && !strpos($componentFilter, '|'))
			{
				$componentSplit = explode('.', $componentFilter);
				$aComponent = $componentSplit[0];
				$aView = $componentSplit[1];
			}
			elseif ($componentFilter != '')
			{
				$componentSplit = explode('|', $componentFilter);
				$aComponent = 'com_categories';
				$aView = $componentSplit[1];
			}

			if (isset($aComponent) && isset($aView))
			{
				$this->link = 'index.php?option=com_associations&view=association&layout=edit&forcedlanguage='
				. $this->state->get('associationlanguage') . '&acomponent='	. $aComponent . '&aview=' . $aView . '&id=';
			}

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
