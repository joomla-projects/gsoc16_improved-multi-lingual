SET IDENTITY_INSERT [#__extensions] ON;

INSERT INTO [#__extensions] ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state])
SELECT 33, 'com_associations', 'component', 'com_associations', '', 1, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0;

SET IDENTITY_INSERT [#__extensions] OFF;

SET IDENTITY_INSERT [#__menu] ON;

INSERT INTO [#__menu] ([menutype], [title], [alias], [note], [path], [link], [type], [published], [parent_id], [level], [component_id], [checked_out], [checked_out_time], [browserNav], [access], [img], [template_style_id], [params], [lft], [rgt], [home], [language], [client_id])
SELECT 'menu', 'com_associations', 'Multilingual Associations', '', 'Multilingual Associations', 'index.php?option=com_associations', 'component', 1, 1, 1, 33, 0, '1900-01-01 00:00:00', 0, 1, 'class:associations', 0, '', 43, 44, 0, '*', 1;

SET IDENTITY_INSERT [#__menu] OFF;
