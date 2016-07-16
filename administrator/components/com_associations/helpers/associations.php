<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_associations
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * Associations component helper.
 *
 * @since  __DEPLOY_VERSION__
 */
class AssociationsHelper extends JHelperContent
{
	public static $extension = 'com_associations';

	/**
	 * Get component properties based on a string.
	 *
	 * @param   string  $component  The component/extension identifier.
	 *
	 * @return  JRegistry  The component properties.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public static function getComponentProperties($component = '')
	{
		static $properties = null;

		if (empty($component))
		{
			return null;
		}

		if (is_null($properties))
		{
			// Get component info from string.
			preg_match('#(.+)\.([a-zA-Z0-9_\-]+)(|\|(.+))$#', $component, $matches);

			$properties = new Registry;
			$properties->component = $matches[1];
			$properties->item      = $matches[2];
			$properties->extension = isset($matches[4]) ? $matches[4] : null;

			// Categories component.
			// @todo This need to be checked without harcoded.
			if ($properties->component === 'com_categories' && !is_null($properties->extension))
			{
				$table = '#__categories';
			}
			// Menus component.
			elseif ($properties->component === 'com_menus')
			{
				$table = '#__menu';
			}
			// All other components.
			else
			{
				// Get component item table.
				$filePath = JPATH_ADMINISTRATOR . '/components/' . $properties->component . '/models/' . $properties->item . '.php';
				$file = file_get_contents($filePath);
				if ($position = strpos($file, 'getAssociations'))
				{
					// Searching for , '#__table' , after getAssociations(.
					$start = strpos($file, ',', $position) + 2;
					$end = strpos($file, ',', $start) - 1;

					$table = str_replace("'", "", substr($file, $start, $end - $start));

				}
			}

			// Save the table and get the table fields.
			$properties->table       = $table;
			$properties->tableFields = JFactory::getDbo()->getTableColumns($table);

			// Component fields
			// @todo This need should be checked hardcoding.
			$properties->fields            = new Registry;
			$properties->fields->title     = isset($properties->tableFields['name']) ? 'name' : null;
			$properties->fields->title     = isset($properties->tableFields['title']) ? 'title' : $properties->fields->title;
			$properties->fields->alias     = isset($properties->tableFields['alias']) ? 'alias' : null;
			$properties->fields->ordering  = isset($properties->tableFields['ordering']) ? 'ordering' : null;
			$properties->fields->ordering  = isset($properties->tableFields['lft']) ? 'lft' : $properties->fields->ordering;
			$properties->fields->menutype  = isset($properties->tableFields['menutype']) ? 'menutype' : null;
			$properties->fields->level     = isset($properties->tableFields['level']) ? 'level' : null;
			$properties->fields->catid     = isset($properties->tableFields['catid']) ? 'catid' : null;
			$properties->fields->language  = isset($properties->tableFields['language']) ? 'language' : null;
			$properties->fields->access    = isset($properties->tableFields['access']) ? 'access' : null;
			$properties->fields->published = isset($properties->tableFields['published']) ? 'published' : null;
			$properties->fields->published = isset($properties->tableFields['state']) ? 'state' : $properties->fields->published;

			// Association column key
			// @todo This need to be checked hardcoding.
			if ($properties->component == 'com_content')
			{
				$properties->associationKey = 'contentadministrator.association';
			}
			elseif ($properties->component == 'com_categories')
			{
				$properties->associationKey = 'categoriesadministrator.association';
			}
			elseif ($properties->component == 'com_menus')
			{
				$properties->associationKey = 'MenusHtml.Menus.association';
			}
			else
			{
				$properties->associationKey = $properties->item . '.association';
			}

			// Asset column key.
			// @todo This need to be confirmed.
			$properties->assetKey = $properties->component . '.' . $properties->item;
		}

		return $properties;
	}

	/**
	 * Method to load the language files for the components using associations.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function loadLanguageFiles()
	{
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		$lang = JFactory::getLanguage();
		
		$backendComponentsDirectory         = JPATH_ADMINISTRATOR . "/components";
		$frontendComponentsDirectory = JPATH_SITE . "/components";
		$backendComponents           = glob($backendComponentsDirectory . '/*', GLOB_NOSORT | GLOB_ONLYDIR);
		$frontendComponents          = glob($frontendComponentsDirectory . '/*', GLOB_NOSORT | GLOB_ONLYDIR);

		// Keeping only directory name
		for ($i = 0; $i < count($backendComponents); $i++)
		{ 
			$backendComponents[$i] = basename($backendComponents[$i]);
		}

		// Keeping only directory name
		for ($i = 0; $i < count($frontendComponents); $i++)
		{ 
			$frontendComponents[$i] = basename($frontendComponents[$i]);
		}

		foreach ($backendComponents as $key => $value)
		{
			$currentDir = $backendComponentsDirectory . "/" . $value . "/models/";

			if (JFolder::exists($currentDir))
			{
				$componentModel = scandir($currentDir);

				foreach ($componentModel as $key2 => $value2)
				{
					if (JFile::exists($currentDir . $value2))
					{
						$file = file_get_contents($currentDir . $value2);

						if (strpos($file, 'protected $associationsContext'))
						{
							$lang->load($value, JPATH_ADMINISTRATOR, null, false, true)
								|| $lang->load($value, JPATH_ADMINISTRATOR . '/components/' . $value, null, false, true);
						}
					}
				}
			}
		}

		foreach ($frontendComponents as $key => $value)
		{
			if (JFile::exists($frontendComponentsDirectory . "/" . $value . "/helpers/association.php"))
			{
				$file = file_get_contents($frontendComponentsDirectory . "/" . $value . "/helpers/association.php");

				if (strpos($file, 'getCategoryAssociations'))
				{
					$lang->load($value, JPATH_ADMINISTRATOR, null, false, true)
						|| $lang->load($value, JPATH_ADMINISTRATOR . '/components/' . $value, null, false, true);
				}
			}
		}
	}
}
