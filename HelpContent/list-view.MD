#### Contents
* [How to access](#how-to-access)
* [Description](#description)
* [Screenshots](#screenshot)
* [Column Headers](#column-headers)
* [Column Filters](#column-filters)
* [Toolbar](#toolbar)
* [List Filters](#list-filters)
* [Options](#options)
* [Quick Tips](#quick-tips)
* [Related Information](#related-information)

### How to access

- Click on Components on the Navbar 
- Select Multilingual Associations

### Description

Multilingual Associations is a tool that enables editing associated items side-by-side without the need to go back and forth, i.e. create the item in several languages and then associate them.

### Screenshots

- Filter empty
![image](https://cloud.githubusercontent.com/assets/16468676/17931824/2e5c9920-6a05-11e6-9baf-d0c4bd910432.png)

- Articles in English selected (Example)
![image](https://cloud.githubusercontent.com/assets/16468676/17931894/69b9e716-6a05-11e6-81ad-a3c6ae552694.png)

### Column Headers

Selecting an item, one would get the following columns
![image](https://cloud.githubusercontent.com/assets/16468676/17932016/f2786ef6-6a05-11e6-80be-7edfdd5a2d48.png)

- Checkbox: Check this box to select one or more items. To select all items, check the box in the column heading. After one or more boxes are checked, click a toolbar button to take an action on the selected item or items. Currently only Check-in option is available.

- Status: Status of the item, Published or Unpublished

- Title: The name of the item.

- Language: Item language.

- Associations: The associated languages for this item, Gray = already associated item, Orange = no associations for that language yet.

- Access. The viewing [Access Level](https://help.joomla.org/proxy/index.php?keyref=Help30:Users_Access_Levels) for this item.

### Column Filters

Above the column headers on the right, there are 2 drop down input fields, Sort Table by: (preset to Ordering ascending)and a number (preset to 20) to display.

![image](https://cloud.githubusercontent.com/assets/16468676/17932449/f19149de-6a07-11e6-8fe0-adbd7305826d.png)
![image](https://cloud.githubusercontent.com/assets/16468676/17932324/5b1f9c4e-6a07-11e6-9a41-98747348ccdc.png)

These column sorting input fields shows the current method of sorting applied to the table. Use the drop down field choices and click the column heading name. Alternatively, click on the column heading to sort the list by that column's value. This will also change the value in the drop field to correspond with the column header name. The list will then be sorted in order by that column and a sort icon(up or down arrow) will show next to the column name. Click a second time to reverse the sort, which will change the corresponding drop field.

- Ordering: (default). The column in which to sort displayed items in the table. The values are the same as the column heading names.
- Ascending (default). Shows ordering of selected column, ascending or descending.
- # (default is 20). Shows the number of items to display on one page, default is 20 items. If there are more items than this number, you can use the page navigation buttons located at the bottom of the table. (Start, Prev, Next, End, and page numbers) to navigate between pages.

### Toolbar

At the top you will see the toolbar, it has two states:

- Nothing selected
![image](https://cloud.githubusercontent.com/assets/16468676/17932508/453a76a0-6a08-11e6-8507-aa1bb85bc78e.png)

- An item selected
![image](https://cloud.githubusercontent.com/assets/16468676/17932522/563325ce-6a08-11e6-9766-4c98fe8de8e7.png)

- Delete All Associations. Deletes all associations on the database for all items.
- Delete Orphans. Deletes all associations for orphan items, i.e. items that are associated to deleted items.
- Check-In. Checks-in the selected item. Works with one or multiple items selected.

### List Filters

The List Filters are bellow the toolbar. These filters are a series of controls that let you limit what items show in the manager screen. More than one filter may be entered. In this case, only items that meet all of the filter conditions will show on the list.

![image](https://cloud.githubusercontent.com/assets/16468676/17932905/f7ab0006-6a09-11e6-8cb6-790b3539fd51.png)

To start filtering items, one must select an item type (Articles, Contacts, Newsfeeds, etc.) and a Language.

After that, several filters can be applied by clicking search tools:

![image](https://cloud.githubusercontent.com/assets/16468676/17933002/4f07d9f0-6a0a-11e6-8263-57ddb70b6e33.png)

![image](https://cloud.githubusercontent.com/assets/16468676/17933024/62629530-6a0a-11e6-8026-cc751c1e7ef0.png)

Use the text field to filter by partial title, enter part of the title and click Search.

Filter by Published Status. Lets you show only items with the selected published status.
- - Select Status -: Shows items that are Published and Unpublished. Does not show items that are Trashed or Archived.
  - Published: Shows only items that are Published.
  - Unpublished: Shows only items that are Unpublished.
  - Archived: Shows only items that are Archived.
  - Trashed: Shows only items that are Trashed. Important Note: To permanently delete items:
    1 - Change the status of the items to Trashed.
    2 - Change the Status filter to Trashed. At this point the trashed items will show and an icon called "Empty trash" will show in the toolbar.
    3 - Select the desired trashed items and click on "Empty Trash" in the toolbar. The items will be permanently deleted.
  - All: Shows all items regardless of published status.


Filter by Category. Lets you show only items assigned to a specific category. The list box will show the categories defined for your site.

  - - Select Category -: Show items assigned to any category.
  - <your category>: Show items assigned only to this category.


Filter by Viewing Access Level. Lets you show only items that have a specified viewing access level. The list box will show the access levels defined for your site, similar to the example below.

- - Select Access -: Show items with any viewing access level.
- <access level group>: Show items only with this viewing access level.


Filter by Max Levels (Category Level). Lets you show only items whose category is at or above the specified level in the category hierarchy.

- - Select Max Levels -: Show all items regardless of level of their assigned category.
- 1: Only show items whose category is at the top level in the category hierarchy (in other words, with categories whose parent category is "- No Parent -".)
- 2-10: Only show items whose category is in the top 2-10 levels in the category hierarchy.

### Options

Clicking on the Options icon in the toolbar opens a modal window where you can set component level options for articles. To see the help screen for Options, click [Article Manager Options](https://help.joomla.org/proxy/index.php?keyref=Help31:Components_Article_Manager_Options)

### Quick Tips

- If you want to associate a new item in a certain language, click that language on the associations column instead of going to the side-by-side view and selecting that language.