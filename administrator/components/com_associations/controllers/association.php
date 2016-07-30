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
 * Association edit controller class.
 *
 * @since  __DEPLOY_VERSION__
 */
class AssociationsControllerAssociation extends JControllerForm
{
	/**
	 * Method for closing the template.
	 *
	 * @param   string  $key  The name of the primary key of the URL variable.
	 *
	 * @return  void.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function cancel($key = null)
	{
		$extension = $this->input->get('extension', '', 'string');

		$key = $extension !== '' ? 'com_categories.category|' . $extension : $this->input->get('acomponent', '') . '.' . $this->input->get('aview', '');
		$cp  = AssociationsHelper::getComponentProperties($key);

		// Only check in, if component allows to check out.
		if (!is_null($cp->fields->checked_out))
		{
			// Check-in reference id.
			$cp->table->checkin($this->input->get('id', null, 'int'));

			// Check-in all ithe target ids (can be several, one for each language).
			if ($targetsId = $this->input->get('target-id', '', 'string'))
			{
				$targetsId = array_unique(explode(',', $targetsId));

				foreach ($targetsId as $key => $targetId)
				{
					$cp->table->checkin((int) $targetId);
				}
			}
		}

		$this->setRedirect(JRoute::_('index.php?option=com_associations&view=associations', false));
	}
}
