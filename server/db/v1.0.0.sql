-- add plugin entry in the plugin table
INSERT IGNORE INTO plugins (name, version) 
VALUES ('mobisense', 'v1.0.0');

-- add page type sh_module_mobisense
INSERT IGNORE INTO `pageType` (`name`) VALUES ('sh_module_mobisense');

SET @id_page_modules = (SELECT id FROM pages WHERE keyword = 'sh_modules');
-- add translation page
INSERT IGNORE INTO `pages` (`id`, `keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`, `id_pageAccessTypes`) 
VALUES (NULL, 'sh_module_mobisense', '/admin/module_mobisense', 'GET|POST', (SELECT id FROM actions WHERE `name` = 'backend' LIMIT 0,1), NULL, @id_page_modules, 0, 100, NULL, (SELECT id FROM pageType WHERE `name` = 'sh_module_mobisense' LIMIT 0,1), (SELECT id FROM lookups WHERE lookup_code = 'mobile_and_web'));
SET @id_page_values = (SELECT id FROM pages WHERE keyword = 'sh_module_mobisense');
INSERT IGNORE INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) VALUES ('0000000001', @id_page_values, '1', '0', '1', '0');

-- add new filed `mobisense_server_ip` from type text
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'mobisense_server_ip', get_field_type_id('text'), '0');
-- add new filed `mobisense_ssh_port` from type number
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'mobisense_ssh_port', get_field_type_id('number'), '0');
-- add new filed `mobisense_ssh_user` from type text
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'mobisense_ssh_user', get_field_type_id('text'), '0');
-- add new filed `mobisense_db_name` from type text
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'mobisense_db_name', get_field_type_id('text'), '0');
-- add new filed `mobisense_db_port` from type number
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'mobisense_db_port', get_field_type_id('number'), '0');
-- add new filed `mobisense_db_user` from type text
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'mobisense_db_user', get_field_type_id('text'), '0');
-- add new filed `mobisense_local_host` from type text
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'mobisense_local_host', get_field_type_id('text'), '0');
-- add new filed `mobisense_db_password` from type password
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'mobisense_db_password', get_field_type_id('password'), '0');
-- add new filed `mobisense_pull_data` from type checkbox
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'mobisense_pull_data', get_field_type_id('checkbox'), '0');
-- add new filed `panel` from type panel
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'mobisense_panel', get_field_type_id('panel'), '0');

INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES ((SELECT id FROM pageType WHERE `name` = 'sh_module_mobisense' LIMIT 0,1), get_field_id('mobisense_server_ip'), NULL, 'Mobisense server IP');
INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES ((SELECT id FROM pageType WHERE `name` = 'sh_module_mobisense' LIMIT 0,1), get_field_id('mobisense_ssh_port'), 2711, 'Mobisense server ssh port');
INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES ((SELECT id FROM pageType WHERE `name` = 'sh_module_mobisense' LIMIT 0,1), get_field_id('mobisense_ssh_user'), NULL, 'Mobisense server ssh user');
INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES ((SELECT id FROM pageType WHERE `name` = 'sh_module_mobisense' LIMIT 0,1), get_field_id('mobisense_db_name'), NULL, 'Mobisense database name');
INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES ((SELECT id FROM pageType WHERE `name` = 'sh_module_mobisense' LIMIT 0,1), get_field_id('mobisense_db_port'), 5432, 'Mobisense database port');
INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES ((SELECT id FROM pageType WHERE `name` = 'sh_module_mobisense' LIMIT 0,1), get_field_id('mobisense_db_user'), NULL, 'Mobisense database user');
INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES ((SELECT id FROM pageType WHERE `name` = 'sh_module_mobisense' LIMIT 0,1), get_field_id('mobisense_db_password'), NULL, 'Mobisense database password');
INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES ((SELECT id FROM pageType WHERE `name` = 'sh_module_mobisense' LIMIT 0,1), get_field_id('mobisense_pull_data'), NULL, 'Mobisense pull data - if enabled the job will pull the data from mobisense; if disabled the job will skip this step');
INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES ((SELECT id FROM pageType WHERE `name` = 'sh_module_mobisense' LIMIT 0,1), get_field_id('mobisense_local_host'), NULL, 'Mobisense local host');
INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES ((SELECT id FROM pageType WHERE `name` = 'sh_module_mobisense' LIMIT 0,1), get_field_id('mobisense_panel'), NULL, 'Mobisense panel with extra functionality');
INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES ((SELECT id FROM pageType WHERE `name` = 'sh_module_mobisense' LIMIT 0,1), get_field_id('title'), NULL, 'Page title');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page_values, get_field_id('title'), '0000000001', 'Module Mobisense');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page_values, get_field_id('title'), '0000000002', 'Module Mobisense');

-- register hooks
-- add hook to load panels for Mobisense module page
INSERT IGNORE INTO `hooks` (`id_hookTypes`, `name`, `description`, `class`, `function`, `exec_class`, `exec_function`)
VALUES ((SELECT id FROM lookups WHERE lookup_code = 'hook_overwrite_return' LIMIT 0,1), 'field-mobisense_panel-edit', 'Output Mobisense panel', 'CmsView', 'create_field_form_item', 'MobisenseHooks', 'outputFieldPanel');

-- add hook to load panels for Mobisense module page
INSERT IGNORE INTO `hooks` (`id_hookTypes`, `name`, `description`, `class`, `function`, `exec_class`, `exec_function`)
VALUES ((SELECT id FROM lookups WHERE lookup_code = 'hook_overwrite_return' LIMIT 0,1), 'field-mobisense_panel-view', 'Output Mobisense panel', 'CmsView', 'create_field_item', 'MobisenseHooks', 'outputFieldPanel');


 -- add Mobisense page
INSERT IGNORE INTO `pages` (`id`, `keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`, `id_pageAccessTypes`) 
VALUES (NULL, 'mobisense', '/admin/mobisense/[test|pull:mode]?', 'GET|POST', '0000000002', NULL, NULL, '0', NULL, NULL, '0000000001', (SELECT id FROM lookups WHERE type_code = "pageAccessTypes" AND lookup_code = "mobile_and_web"));
SET @id_page = (SELECT id FROM pages WHERE keyword = 'mobisense');

INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page, '0000000008', '0000000001', 'Mobisense');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page, '0000000054', '0000000001', '');
INSERT IGNORE INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) VALUES ('0000000001', @id_page, '1', '0', '1', '0');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page, get_field_id('title'), '0000000001', 'Mobisense');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page, get_field_id('title'), '0000000002', 'Mobisense');