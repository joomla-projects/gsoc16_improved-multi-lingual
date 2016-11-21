#### Introduction

Besides core components, third party extensions can also have associations and take advantage of the com_associations list view, as well as the side-by-side for better work-flow. Meeting certain requirements, third party extensions will also be listed on the list view's filters, and on the side-by-side as these images exemplify:

List view:
![image](https://cloud.githubusercontent.com/assets/16468676/17851759/9e7bed24-685b-11e6-9379-c11d6b6a0aaf.png)

Side-by-side:
![image](https://cloud.githubusercontent.com/assets/16468676/17851854/08a37212-685c-11e6-8ec5-29c3378296bc.png)

#### Associations Component (com_associations) Requirements

1. Component should have a `ROOT/components/<com_componentname>/helpers/association.php` Example [here](https://github.com/joomla/joomla-cms/blob/staging/components/com_content/helpers/association.php)

2. `ROOT/component/<com_componentname>/helpers/association.php` needs to load `route.php`:
`JLoader::register(<ComponentName>'HelperRoute', JPATH_SITE . '/components/<com_componentname>/helpers/route.php');` Example [here](https://github.com/joomla/joomla-cms/blob/staging/components/com_content/helpers/association.php#L13)

3. Component using Categories with associations should have the method `getCategoryAssociations` in `ROOT/component/<com_componentname>/helpers/association.php`. Example [here](https://github.com/joomla/joomla-cms/blob/staging/components/com_content/helpers/association.php#L33)

4. Component with associated items should have `$associationsContext` and `$typeAlias` defined in the model concerned. Example [here](https://github.com/joomla/joomla-cms/blob/staging/administrator/components/com_content/models/article.php#L35) and [here](https://github.com/joomla/joomla-cms/blob/staging/administrator/components/com_content/models/article.php#L43)

5. Component should have in backend specific `modal_associations.php` views for the item, parallel to the edit view. Example [here](https://github.com/joomla/joomla-cms/tree/staging/administrator/components/com_content/views/article/tmpl) Another way is to have chosen fields, any other implementation will not work.

6. Edit modals have to use `&tmpl=component` by implementing an EDIT functionnality in `ROOT/administrator/components/<com_componentname>/models/fields/modal/<item>`, not only a Select. Example: [here](https://github.com/joomla/joomla-cms/tree/staging/administrator/components/com_content/models/fields/modal)

7. Associated item must have the following fields: `id, title, alias, language` in order to work, com_associations will also use the following fields if they exist: `ordering, menutype, level, catid, access, published, created_by, checked_out, checked_out_time` the fields mentioned can have other names in case the model contains the variable `$_columnAlias`, example:

```php
    protected $_columnAlias = array(
        'component_id' => 'id',
        'title'        => 'name',
        'created'      => 'created_by',
    );
```

8. Components that support multiple categories context MUST have each item type categories context (aka extension) in their item types models. Exmaple:

```php
    protected $categoriesContext = 'com_mycomponent.myitem1';
```

#### Frequent Issues

to do
