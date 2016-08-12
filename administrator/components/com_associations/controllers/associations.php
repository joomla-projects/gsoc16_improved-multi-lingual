<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined("_JEXEC") or die("Restricted access");

/**
 * Associations controller class.
 *
 * @since  __DEPLOY_VERSION__
 */
class AssociationsControllerAssociations extends JControllerAdmin
{
	/**
	 * The URL view list variable.
	 *
	 * @var    string
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $view_list = 'associations';

	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  The array of possible config values. Optional.
	 *
	 * @return  JModel
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getModel($name = 'Associations', $prefix='AssociationsModel', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}

	/**
	 * Check in of one or more records.
	 *
	 * @return  boolean  True on success
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function checkin()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$key    = JFactory::getApplication()->getUserState('com_associations.associations.component');
		$cp     = AssociationsHelper::getComponentProperties($key);
		$return = false;

		// Only check in, if component allows to check out.
		if (!is_null($cp->fields->checked_out))
		{
			$ids    = JFactory::getApplication()->input->post->get('cid', array(), 'array');
			$return = $cp->model->checkin($ids);

			// Load component language files.
			$lang = JFactory::getLanguage();
			$lang->load($cp->component . '.sys', JPATH_ADMINISTRATOR) || $lang->load($cp->component . '.sys', $cp->adminPath);
			$lang->load($cp->component, JPATH_ADMINISTRATOR) || $lang->load($cp->component, $cp->adminPath);

			// Checkin failed.
			if ($return === false)
			{
				$message     = JText::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $cp->model->getError());
				$messageType = 'error';
			}
			// Checkin succeeded.
			else
			{
				$message     = JText::plural(strtoupper($cp->component) . '_N_ITEMS_CHECKED_IN', count($ids));
				$messageType = 'message';
			}

			$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false), $message, $messageType);
		}

		return (boolean) $return;
	}

	/**
	 * Method to purge the associations table.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function purge()
	{
		$model = $this->getModel('associations');
		$model->purge();
		$this->setRedirect(JRoute::_('index.php?option=com_associations&view=associations', false));
	}
}
