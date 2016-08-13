<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_associations
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');

JHtml::_('script', 'com_associations/sidebyside.js', false, true);

if (JFactory::getLanguage()->isRtl()) {
	$this->app->getDocument()->addStyleDeclaration('

		.sidebyside .outer-panel {
			float: right;
			width: 50%;
		}
		.sidebyside #right-panel .inner-panel {
			border-right: 1px solid #999999 !important;
		}
		.sidebyside #left-panel .inner-panel {
			padding-left: 10px;
		}
		.sidebyside #right-panel .inner-panel {
			padding-right: 10px;
		}
		.sidebyside .full-width {
			float: none !important;
			width: 100% !important;
		}
		.sidebyside .full-width .inner-panel {
			padding-left: 0 !important;
		}
		
		#reference-association, #target-association {
			width: 100%;
			height: 1500px;
			border: 0 !important;
		}

		.target-text {
			float: right;
			width: 30%;
		}
	');
}
else
{
	$this->app->getDocument()->addStyleDeclaration('

		.sidebyside .outer-panel {
			float: left;
			width: 50%;
		}
		.sidebyside #left-panel .inner-panel {
			border-right: 1px solid #999999 !important;
		}
		.sidebyside #left-panel .inner-panel {
			padding-right: 10px;
		}
		.sidebyside #right-panel .inner-panel {
			padding-left: 10px;
		}
		.sidebyside .full-width {
			float: none !important;
			width: 100% !important;
		}
		.sidebyside .full-width .inner-panel {
			padding-left: 0 !important;
		}
		
		#reference-association, #target-association {
			width: 100%;
			height: 1500px;
			border: 0 !important;
		}

		.target-text {
			float: left;
			width: 30%;
		}
	');
}

$input   = $this->app->input;
$options = array(
			'layout'            => $input->get('layout', '', 'string'),
			'itemtype'          => $this->itemType->key,
			'id'                => $this->referenceId,
		);
?>
<button id="toogle-left-panel" class="btn btn-small" 
		data-show-reference="<?php echo JText::_('COM_ASSOCIATIONS_EDIT_SHOW_REFERENCE'); ?>"
		data-hide-reference="<?php echo JText::_('COM_ASSOCIATIONS_EDIT_HIDE_REFERENCE'); ?>"><?php echo JText::_('COM_ASSOCIATIONS_EDIT_HIDE_REFERENCE'); ?>
</button>

<form action="<?php echo JRoute::_('index.php?option=com_associations&view=association&' . http_build_query($options)); ?>" method="post" name="adminForm" id="adminForm" data-associatedview="<?php echo $this->itemType->item; ?>">

	<div class="sidebyside">

		<div class="outer-panel" id="left-panel">
			<div class="inner-panel">
				<h3><?php echo JText::_('COM_ASSOCIATIONS_REFERENCE_ITEM'); ?></h3>
				<iframe id="reference-association" name="reference-association"
					src="<?php echo JRoute::_($this->editUri . '&task=' . $this->itemType->item . '.edit&id=' . (int) $this->referenceId); ?>"
					height="100%" width="400px" scrolling="no"
					data-action="edit"
					data-item="<?php echo $this->itemType->item; ?>"
					data-id="<?php echo $this->referenceId; ?>"
					data-language="<?php echo $this->referenceLanguage; ?>">
				</iframe>
			</div>
		</div>
		<div class="outer-panel" id="right-panel">
			<div class="inner-panel">
				<div class="language-selector">
					<h3 class="target-text"><?php echo JText::_('COM_ASSOCIATIONS_ASSOCIATED_ITEM'); ?></h3>
					<?php echo $this->form->getInput('modalassociation'); ?>
					<?php echo $this->form->getInput('itemlanguage'); ?>
				</div>
				<iframe id="target-association" name="target-association"
					src="<?php echo $this->defaultTargetSrc; ?>"
					height="100%" width="400px" scrolling="no"
					data-action="<?php echo $this->targetAction; ?>"
					data-item="<?php echo $this->itemType->item; ?>"
					data-id="<?php echo $this->targetId; ?>"
					data-language="<?php echo $this->targetLanguage; ?>"
					data-editurl="<?php echo JRoute::_($this->editUri); ?>">
				</iframe>
			</div>
		</div>

	</div>

	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="target-id" id="target-id" value=""/>
	<?php echo JHtml::_('form.token'); ?>
</form>
