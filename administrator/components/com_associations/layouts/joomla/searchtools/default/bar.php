<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

$data = $displayData;

if ($data['view'] instanceof AssociationsViewAssociations)
{
	// We will get the component and language filters & remove it from the form filters
	$componentTypeField = $data['view']->filterForm->getField('component');
	$languageField      = $data['view']->filterForm->getField('language');
?>
	<div class="js-stools-field-filter js-stools-selector">
		<?php echo $componentTypeField->input; ?>
	</div>
	<div class="js-stools-field-filter js-stools-selector">
		<?php echo $languageField->input; ?>
	</div>
<?php
}

// Display the main joomla layout
echo JLayoutHelper::render('joomla.searchtools.default.bar', $data, null, array('component' => 'none'));
