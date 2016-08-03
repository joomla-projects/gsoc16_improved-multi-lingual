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
class AssociationsViewAssociation extends JViewLegacy
{
	/**
	 * An array of items
	 *
	 * @var  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected $items;

	/**
	 * The pagination object
	 *
	 * @var  JPagination
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected $pagination;

	/**
	 * The model state
	 *
	 * @var  object
	 *
	 * @since   __DEPLOY_VERSION__
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
		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors), 500);

			return false;
		}

		/*
		* @todo Review later
		*/

		// We don't need toolbar in the modal window.
		if ($this->getLayout() !== 'modal')
		{
			$this->addToolbar();
			$this->sidebar = JHtmlSidebar::render();
		}
		else
		{
			// In article associations modal we need to remove language filter if forcing a language.
			// We also need to change the category filter to show show categories with All or the forced language.
			if ($forcedLanguage = JFactory::getApplication()->input->get('forcedLanguage', '', 'CMD'))
			{
				// If the language is forced we can't allow to select the language, so transform the language selector filter into an hidden field.
				$languageXml = new SimpleXMLElement('<field name="language" type="hidden" default="' . $forcedLanguage . '" />');
				$this->filterForm->setField($languageXml, 'filter', true);

				// Also, unset the active language filter so the search tools is not open by default with this filter.
				unset($this->activeFilters['language']);

				// One last changes needed is to change the category filter to just show categories with All language or with the forced language.
				$this->filterForm->setFieldAttribute('category_id', 'language', '*,' . $forcedLanguage, 'filter');
			}
		}

		$this->app   = JFactory::getApplication();

		$this->form  = $this->get('Form');
		$input       = $this->app->input;

		$associatedComponent  = $input->get('acomponent', '', 'string');
		$this->associatedView = $input->get('aview', '', 'string');
		$extension            = $input->get('extension', '', 'string');
		$this->referenceId    = $input->get('id', 0, 'int');

		$key = $extension !== '' ? 'com_categories.category|' . $extension : $associatedComponent . '.' . $this->associatedView;
		$this->component  = AssociationsHelper::getComponentProperties($key);

		// Get reference language.
		$table = clone $this->component->table;
		$table->load($this->referenceId);

		$this->referenceLanguage = $table->{$this->component->fields->language};

		$options = array(
			'option'    => $associatedComponent,
			'view'      => $this->associatedView,
			'extension' => '',
			'task'      => $this->associatedView . '.edit',
			'layout'    => 'edit',
			'tmpl'      => 'component',
			'id'        => $this->referenceId,
		);

		// Special cases for categories.
		if ($associatedComponent === 'com_categories')
		{
			$options['view']      = '';
			$options['task']      = 'category.edit';
			$options['extension'] = $extension;
		}

		// Reference item edit link.
		$this->link = 'index.php?' . http_build_query($options);
		$options['id'] = '';
		$this->targetLink = 'index.php?' . http_build_query($options);

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
		$input = JFactory::getApplication()->input;
		$input->set('hidemainmenu', 1);

		JToolbarHelper::title(JText::_('COM_ASSOCIATIONS_HEADER_EDIT'), 'contract');

		$bar = JToolbar::getInstance('toolbar');
		$bar->appendButton(
			'Custom', '<button onclick="Joomla.submitbutton(\'reference\')"'
			. 'class="btn btn-small btn-success"><span class="icon-apply icon-white"></span>'
			. JText::_('COM_ASSOCIATIONS_SAVE_REFERENCE') . '</button>', 'reference'
		);
		$bar->appendButton(
			'Custom', '<button onclick="Joomla.submitbutton(\'target\')"'
			. 'class="btn btn-small btn-success"><span class="icon-apply icon-white"></span>' 
			. JText::_('COM_ASSOCIATIONS_SAVE_TARGET') . '</button>', 'target'
		);

		JToolBarHelper::custom('copy', 'copy.png', '', 'COM_ASSOCIATIONS_COPY_REFERENCE', false);

		$bar->appendButton(
			'Custom', '<button onclick="Joomla.submitbutton(\'undo-association\')"'
			. 'class="btn btn-small"><span class="icon-cancel icon-white"></span>' 
			. JText::_('COM_ASSOCIATIONS_UNDO_ASSOCIATION') . '</button>', 'undo-association'
		);

		JToolbarHelper::cancel('association.cancel', 'JTOOLBAR_CLOSE');
		JToolbarHelper::help('JGLOBAL_HELP');

		JHtmlSidebar::setAction('index.php?option=com_associations');
	}
}
