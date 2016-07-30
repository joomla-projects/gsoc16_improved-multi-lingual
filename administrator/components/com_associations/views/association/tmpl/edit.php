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

$this->app->getDocument()->addScriptDeclaration("
// TO use later?
//function copyRefToTarget() {
//	var ref = document.getElementById('reference-association').contentWindow.document.getElementsByName('checkbox');
//	jQuery('#reference-association').contents().find('input').each(function () {
//		//console.log(jQuery('#target-association').contents().find(this.id).text());
//		id = '#'+this.id;
//		jQuery('#target-association').contents().find(id).val((jQuery(this).val()));
//	});
//	return false;
//}

jQuery(document).ready(function($) {

	// Save button actions, replacing the default Joomla.submitbutton() with custom function.
	Joomla.submitbutton = function(task)
	{
		// Using close button, normal joomla submit.
		if (task == 'association.cancel')
		{
			Joomla.submitform(task);
		}
		// Saving target or reference, send the save action to the target/reference iframe.
		else
		{
			// We need to re-enable the language field to save.

			$('#' + task + '-association').contents().find('#jform_language').attr('disabled', false);
			window.frames[task + '-association'].Joomla.submitbutton(document.getElementById('adminForm').getAttribute('data-associatedview') + '.apply');
		}

		return false;
	}

	// Preload Joomla loading layer.
	Joomla.loadingLayer('load');

	// Attach behaviour to toggle button.
	$('body').on('click', '#toogle-left-panel', function() {
		$('#left-panel').toggle();
		$('#right-panel').toggleClass('full-width');
	});

	// Attach behaviour to language selector change event.
	$('body').on('change', '#jform_itemlanguage', function() {
		var target   = document.getElementById('target-association');
		var selected = $(this).val();

		// Populate the data attributes and load the the edit page in target frame.
		if (selected != '' && typeof selected !== 'undefined')
		{
			target.setAttribute('data-id', selected.split('|')[1]);
			target.setAttribute('data-language', selected.split('|')[0]);

			// Iframe load start, show Joomla loading layer.
			Joomla.loadingLayer('show');

			// Load the target frame.
			target.src = target.getAttribute('data-editurl') + target.getAttribute('data-id');
		}
		// Reset the data attributes and no item to load.
		else
		{
			target.setAttribute('data-id', '0');
			target.setAttribute('data-language', '');
			target.src = '';
		}
	});

	// Attach behaviour to reference frame load event.
	$('#reference-association').on('load', function() {

		// Disable language field.
		$(this).contents().find('#jform_language_chzn').remove();
		$(this).contents().find('#jform_language').attr('disabled', true).chosen();

		// Later usage copy function?
		//$('#toolbar-copy').children().first().attr('onclick', 'return copyRefToTarget()');
		//referenceContents.find('#associations .controls').css('pointer-events', 'auto');

		// Iframe load finished, hide Joomla loading layer.
		Joomla.loadingLayer('hide');
	});

	// Attach behaviour to target frame load event.
	$('#target-association').on('load', function() {

		// We need to check if we are not loading a blank iframe.
		if (this.src != '')
		{
			var targetLanguage       = this.getAttribute('data-language');
			var targetId             = this.getAttribute('data-id');
			var targetLoadedId       = $(this).contents().find('#jform_id').val() || '0';

			// Hide associations tab.
			$(this).contents().find('a[href=\"#associations\"]').parent().hide();

			// Update language field with the selected language and them disable it.
			$(this).contents().find('#jform_language_chzn').remove();
			$(this).contents().find('#jform_language').val(targetLanguage).change().attr('disabled', true).chosen();

			// If we are creating a new association (before save) we need to add the new association.
			if (targetLoadedId == '0')
			{
				// Update the target item associations tab.
				var reference     = document.getElementById('reference-association');
				var referenceId   = reference.getAttribute('data-id');
				var languageCode  = reference.getAttribute('data-language').replace(/-/, '_');

				// - For modal association selectors.
				$(this).contents().find('#jform_associations_' + languageCode + '_id').val(referenceId);
				$(this).contents().find('#jform_associations_' + languageCode + '_name').val($(reference).contents().find('#jform_title').val());

				target = $(this).contents();

				$('#jform_itemlanguage option').each(function()
				{
					parse = $(this).val().split('|');

					if (typeof parse[1] !== 'undefined' && parse[1] !== '0')
					{
	 					langAssociation = parse[0].replace(/-/,'_');
	 					target.find('#jform_associations_' + langAssociation + '_id').val(parse[1]);
	 				}
				});

				// - For chosen association selectors (menus).
				$(this).contents().find('div[id^=\"jform_associations_' + languageCode + '_chzn\"]').remove();
				$(this).contents().find('select[id^=\"jform_associations_' + languageCode + '\"]').val(referenceId).change().chosen();
			}
			// If we are editing a association.
			else 
			{
				// Add the id to list of items to check in on close.
				var currentIdList = document.getElementById('target-id').value;
				var updatedList   = currentIdList == '' ? targetLoadedId : currentIdList + ',' + targetLoadedId;
				document.getElementById('target-id').value = updatedList;

				// If we created a new association (after save).
				if (targetLoadedId != targetId)
				{
					// Refresh the language selector with the new id (used after save).
					$('#jform_itemlanguage option[value^=\"' + targetLanguage + '|' + targetId + '\"]').val(targetLanguage + '|' + targetLoadedId);

					// Update main frame data-id attribute (used after save).
					this.setAttribute('data-id', targetLoadedId);

					// Update the reference item associations tab.
					var reference     = document.getElementById('reference-association');
					var languageCode  = targetLanguage.replace(/-/, '_');

					// - For modal association selectors.
					$(reference).contents().find('#jform_associations_' + languageCode + '_id').val(targetLoadedId);
					$(reference).contents().find('#jform_associations_' + languageCode + '_name').val($(this).contents().find('#jform_title').val());

					// - For chosen association selectors (menus).
					$(reference).contents().find('div[id^=\"jform_associations_' + languageCode + '_chzn\"]').remove();
					$(reference).contents().find('select[id^=\"jform_associations_' + languageCode + '\"]').val(targetLoadedId).change().chosen();
				}
			}

			// Iframe load finished, hide Joomla loading layer.
			Joomla.loadingLayer('hide');
		}
	});
});
");

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
	.sidebyside iframe {
		width: 100%;
		height: 1500px;
		border: 0 !important;
	}

	.language-selector h3 {
		float: left;
		width: 50%;
	}
');

$input      = $this->app->input;
$layout     = $input->get('layout', '', 'string');
$aComponent = $input->get('acomponent', '', 'string');
$aView      = $input->get('aview', '', 'string');
$extension  = $input->get('extension', '', 'string');
$rLanguage  = $input->get('referencelanguage', '', 'string') != null ? $input->get('referencelanguage', '', 'string') : '';
?>
<button id="toogle-left-panel" class="btn btn-small">Show/Hide Reference (PoC)</button>

<form action="<?php echo JRoute::_(
			'index.php?option=com_associations&view=association&layout=' . $layout . '&acomponent='
			. $aComponent . '&aview=' . $aView . '&extension=' . $extension . '&referencelanguage=' . $rLanguage . '&id='
			. $this->referenceId
		); ?>" method="post" name="adminForm" id="adminForm" class="form-validate" data-associatedview="<?php echo $this->associatedView; ?>">

<div class="sidebyside">
	<div class="outer-panel" id="left-panel">
		<div class="inner-panel">
			<h3><?php echo JText::_('COM_ASSOCIATIONS_REFERENCE_ITEM'); ?></h3>
			<iframe id="reference-association" name="reference-association"
				src="<?php echo JRoute::_($this->link); ?>"
				height="100%" width="400px" scrolling="no"
				data-id="<?php echo $this->referenceId; ?>"
				data-language="<?php echo $this->referenceLanguage; ?>">
			</iframe>
		</div>
	</div>
	<div class="outer-panel" id="right-panel">
		<div class="inner-panel">
			<div class="language-selector">
				<h3><?php echo JText::_('COM_ASSOCIATIONS_ASSOCIATED_ITEM'); ?></h3>
				<?php echo $this->form->getInput('itemlanguage'); ?>
			</div>
			<iframe id="target-association" name="target-association"
				src=""
				height="100%" width="400px" scrolling="no"
				data-id="0"
				data-language=""
				data-editurl="<?php echo JRoute::_($this->targetLink); ?>">
			</iframe>
		</div>
	</div>
</div>
<input type="hidden" name="task" value=""/>
<input type="hidden" name="target-id" id="target-id" value=""/>
</form>
