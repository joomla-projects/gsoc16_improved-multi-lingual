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
	function triggerSave(frame) {
		if (frame == 'reference') {
			window.frames['reference-association'].Joomla.submitbutton('" . $this->associatedView . ".apply');
		}
		if (frame == 'target') {
			//The field needs to be re-enabled or the language field won't be saved
			jQuery('#target-association').contents().find('#jform_language').attr('disabled', false);

			target = jQuery('#target-association').contents();
			selectedLang = jQuery('#jform_itemlanguage').val();
			split = selectedLang.split('|');
			langAssociation = split[0].replace('-','_');

			langID = " . $this->referenceId . ";
			referenceLanguage = '" . str_replace("-", "_", $this->referenceLanguage) . "';
			target.find('#jform_associations_' + referenceLanguage + '_chzn').remove();
			target.find('#jform_associations_' + referenceLanguage).val(langID).chosen().change();

			//Storing existing associations on target
			jQuery('#jform_itemlanguage option').each(function()
			{
				split = jQuery(this).val().split('|');
				if (typeof split[1] !== 'undefined' && split[1] !== '0') {
					langAssociation = split[0].replace('-','_');
					langID = split[1];

					target.find('#jform_associations_' + langAssociation + '_chzn').remove();
					target.find('#jform_associations_' + langAssociation).val(langID).chosen().change();
				}
			});

			window.frames['target-association'].Joomla.submitbutton('" . $this->associatedView . ".apply');

			//Will only execute this AFTER save to get the ID in case it's a new item
			jQuery('#target-association').load(function () {
				reference = jQuery('#reference-association').contents();
				selectedLang = jQuery('#jform_itemlanguage').val();
				split = selectedLang.split('|');
				langAssociation = split[0].replace('-','_');
				langID = jQuery(this).contents().find('#jform_id').val();
				reference.find('#jform_associations_' + langAssociation + '_id').val(langID);
				reference.find('#jform_associations_' + langAssociation + '_chzn').remove();
				title = jQuery(this).contents().find('#jform_title').val();
				referenceAssoc = reference.find('#jform_associations_' + langAssociation);
				referenceAssoc.append('<option value=\"'+ langID + '\">' + title + '</option>');
				referenceAssoc.val(langID).chosen().change();

				//Updating language selector when a new item is saved
				if (split[1] == '0') {
					jQuery('#jform_itemlanguage option').each(function() {
						if(jQuery(this).val() == selectedLang) {
							jQuery(this).val(split[0] + '|' + langID);
						}
					});
				}

				if (langID != '0') {
					if (!jQuery('#target-id').val()) {
						jQuery('#target-id').val(langID);
					}
					else {
						jQuery('#target-id').val(jQuery('#target-id').val()+','+langID);
					}
				}
			});
		}
		return false;
	}
");

$this->app->getDocument()->addScriptDeclaration("
	jQuery(document).ready(function($) {
		Joomla.loadingLayer('load');
		$('#toogle-left-panel').on('click', function() {
			$('#left-panel').toggle();
			$('#right-panel').toggleClass('full-width');
		});

		selectedLang = $('#jform_itemlanguage').val();

		if (selectedLang == '')
		{
			document.getElementById('target-association').src = '';
		}

		//Save button action, replacing the fault Joomla.submitbutton() with custom function
		$('.btn-success').attr('onclick', function(){
			var frame = $(this).attr('onclick');

			frame = frame.substring(frame.lastIndexOf('(')+1,frame.lastIndexOf(')'));
			return 'return triggerSave(' + frame + ')';
		});

		$('#reference-association').load(function ()
		{
			var referenceContents = $(this).contents();

			// Disable reference fields.
			referenceContents.find('#jform_language_chzn').remove();
			referenceContents.find('#jform_language').attr('disabled', true).chosen();

			$('#toolbar-copy').children().first().attr('onclick', 'return copyRefToTarget()');
			referenceContents.find('#associations .controls').css('pointer-events', 'auto');

			// Iframe load finished, hide Joomla loading layer.
			Joomla.loadingLayer('hide');
		});

		$('#target-association').load(function () {
			target = $(this).contents();
			selectedLang = $('#jform_itemlanguage').val();

			//Hide associations tab
			target.find('a[href=#associations]').parent().hide();

			split = selectedLang.split('|');

			if (typeof split[1] !== 'undefined') {
				selectedLang = split[0];

				//Adding checkin IDs to an hidden input
				if (split[1] != '0') {
					if (!$('#target-id').val()) {
						$('#target-id').val(split[1]);
					}
					else {
						$('#target-id').val($('#target-id').val()+','+split[1]);
					}
				}
				//Auto-picking the selected language on the switcher
				target.find('#jform_language option[value='+selectedLang+']').attr('selected','selected');
				target.find('#jform_language').trigger('liszt:updated');
			}

			// Disable target fields.
			target.find('#jform_language_chzn').remove();
			target.find('#jform_language').attr('disabled', true).chosen();

			// Iframe load finished, hide Joomla loading layer.
			Joomla.loadingLayer('hide');
		});

	});

	function loadFrame(id) {
		split = id.split('|');
		id = split[1];

		newSrc = '" . $this->targetLink . "' + id;

		selectedLang = jQuery('#jform_itemlanguage').val();

		if (selectedLang == '')
		{
			document.getElementById('target-association').src = '';
		}
		else
		{
			// Iframe load start, show Joomla loading layer.
			Joomla.loadingLayer('show');

			document.getElementById('target-association').src = newSrc;
		}
	}

	function copyRefToTarget() {
		var ref = document.getElementById('reference-association').contentWindow.document.getElementsByName('checkbox');
		jQuery('#reference-association').contents().find('input').each(function () {
			//console.log(jQuery('#target-association').contents().find(this.id).text());
			id = '#'+this.id;
			jQuery('#target-association').contents().find(id).val((jQuery(this).val()));
		});
		return false;
	}
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

	.reference-side h3 {
		float: left;
		width: 50%;
	}
');

$input      = $this->app->input;
$layout     = $input->get('layout', '', 'string');
$aComponent = $input->get('acomponent', '', 'string');
$aView      = $input->get('aview', '', 'string');
$rLanguage  = $input->get('referencelanguage', '', 'string') != null ? $input->get('referencelanguage', '', 'string') : '';
?>
<button id="toogle-left-panel" class="btn btn-small">Show/Hide Reference (PoC)</button>

<form action="<?php echo JRoute::_(
			'index.php?option=com_associations&view=association&layout=' . $layout . '&acomponent='
			. $aComponent . '&aview=' . $aView . '&referencelanguage=' . $rLanguage . '&id='
			. $this->referenceId
		); ?>"
 method="post" name="adminForm" id="adminForm" class="form-validate">

<div class="sidebyside">
	<div class="outer-panel" id="left-panel">
		<div class="inner-panel">
			<h3><?php echo JText::_('COM_ASSOCIATIONS_REFERENCE_ITEM'); ?></h3>
			<iframe id="reference-association" name="reference-association"
				src="<?php echo JRoute::_($this->link); ?>"
				height="100%" width="400px" scrolling="no">
			</iframe>
		</div>
	</div>
	<div class="outer-panel" id="right-panel">
		<div class="inner-panel">
			<div class="reference-side">
				<h3><?php echo JText::_('COM_ASSOCIATIONS_ASSOCIATED_ITEM'); ?></h3>
				<?php echo $this->form->getInput('itemlanguage'); ?>
			</div>
			<iframe id="target-association" name="target-association"
				src="<?php echo JRoute::_($this->targetLink); ?>"
				height="100%" width="400px" scrolling="no">
			</iframe>
		</div>
	</div>
</div>
<input type="hidden" name="task" value=""/>
<input id="target-id" type="hidden" name="target-id" value=""/>
</form>
