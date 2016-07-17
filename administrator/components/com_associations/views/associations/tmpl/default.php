<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_associations
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('jquery.framework');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$listOrder  = $this->escape($this->state->get('list.ordering'));
$listDirn   = $this->escape($this->state->get('list.direction'));
$colSpan    =  4;
$iconStates = array(
	-2 => 'icon-trash',
	0  => 'icon-unpublish',
	1  => 'icon-publish',
	2  => 'icon-archive',
);
?>
<form action="<?php echo JRoute::_('index.php?option=com_associations&view=associations'); ?>" method="post" name="adminForm" id="adminForm">

<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>

<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this, 'options' => array('filterButton' => !is_null($this->component)))); ?>
	<?php if (empty($this->items)) : ?>
		<div class="alert alert-no-items">
			<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
		</div>
	<?php else : ?>
		<?php JHtml::addIncludePath(JPATH_ADMINISTRATOR . '/components/' . $this->component->component . '/helpers/html'); ?>
		<table class="table table-striped" id="associationsList">
			<thead>
				<tr>
					<?php if (!is_null($this->component->fields->published)) : ?>
						<th width="1%" class="center nowrap">
							<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'published', $listDirn, $listOrder); $colSpan++; ?>
						</th>
					<?php endif; ?>
					<th class="nowrap">
						<?php echo JHtml::_('searchtools.sort', 'JGLOBAL_TITLE', 'title', $listDirn, $listOrder); ?>
					</th>
					<th width="15%" class="nowrap">
						<?php echo JText::_('JGRID_HEADING_LANGUAGE'); ?>
					</th>
					<th width="5%" class="nowrap">
						<?php echo JHtml::_('searchtools.sort', 'COM_ASSOCIATIONS_HEADING_ASSOCIATION', 'association', $listDirn, $listOrder); ?>
					</th>
					<?php if (!is_null($this->component->fields->menutype)) : ?>
						<th width="10%" class="nowrap">
							<?php echo JHtml::_('searchtools.sort', 'COM_ASSOCIATIONS_HEADING_MENUTYPE', 'menutype_title', $listDirn, $listOrder); $colSpan++; ?>
						</th>
					<?php endif; ?>
					<?php if (!is_null($this->component->fields->access)) : ?>
						<th width="5%" class="nowrap hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'access_level', $listDirn, $listOrder); $colSpan++; ?>
						</th>
					<?php endif; ?>
					<th width="1%" class="nowrap hidden-phone">
						<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="<?php echo $colSpan; ?>">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
			<tbody>
			<?php foreach ($this->items as $i => $item) : ?>
				<?php // @todo ACL check ?>
				<tr class="row<?php echo $i % 2; ?>">
					<?php if (!is_null($this->component->fields->published)) : ?>
						<td class="center">
							<span class="<?php echo $iconStates[$this->escape($item->published)]; ?>"></span>
						</td>
					<?php endif; ?>
					<td class="nowrap has-context">
						<?php if (!is_null($this->component->fields->level)) : ?>
							<?php echo JLayoutHelper::render('joomla.html.treeprefix', array('level' => $item->level)); ?>
						<?php endif; ?>	
						<a href="<?php echo JRoute::_($this->editLink . '&forcedlanguage=' . $item->language . '&id=' . (int) $item->id); ?>">
							<?php echo $this->escape($item->title); ?></a>
						<?php if (!is_null($this->component->fields->alias)) : ?>
							<span class="small">
								<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
							</span>
						<?php endif; ?>
						<?php if (!is_null($this->component->fields->catid)) : ?>
							<div class="small">
								<?php echo JText::_('JCATEGORY') . ": " . $this->escape($item->category_title); ?>
							</div>
						<?php endif; ?>
					</td>
					<td class="small">
						<?php echo $item->language_title ? JHtml::_('image', 'mod_languages/' . $item->language_image . '.gif', $item->language_title, array('title' => $item->language_title), true) . '&nbsp;' . $this->escape($item->language_title) : JText::_('JUNDEFINED'); ?>
					</td>
					<td>
						<?php if ($item->association) : ?>
							<?php echo JHtml::_($this->component->associationKey, $item->id, $this->component->extension); ?>
						<?php endif; ?>
					</td>
					<?php if (!is_null($this->component->fields->menutype)) : ?>
						<td class="small">
							<?php echo $this->escape($item->menutype_title); ?>
						</td>
					<?php endif; ?>
					<?php if (!is_null($this->component->fields->access)) : ?>
						<td class="small hidden-phone">
							<?php echo $this->escape($item->access_level); ?>
						</td>
					<?php endif; ?>
					<td class="hidden-phone">
						<?php echo $item->id; ?>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

	<?php endif; ?>

		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="boxchecked" value="0"/>
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
