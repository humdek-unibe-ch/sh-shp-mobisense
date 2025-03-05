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
-- add new filed `mobisense_ssh_user` from type JSON
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'mobisense_ssh_user', get_field_type_id('text'), '0');
-- add new filed `mobisense_ssh_key` from type JSON
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'mobisense_pull_data', get_field_type_id('password'), '0');
-- add new filed `mobisense_db_name` from type JSON
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'mobisense_db_name', get_field_type_id('text'), '0');
-- add new filed `mobisense_db_port` from type JSON
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'mobisense_db_port', get_field_type_id('number'), '0');
-- add new filed `mobisense_db_user` from type JSON
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'mobisense_db_user', get_field_type_id('text'), '0');
-- add new filed `mobisense_db_password` from type JSON
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'mobisense_db_password', get_field_type_id('password'), '0');
-- add new filed `mobisense_pull_data` from type JSON
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'mobisense_pull_data', get_field_type_id('checkbox'), '0');

INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES ((SELECT id FROM pageType WHERE `name` = 'sh_module_mobisense' LIMIT 0,1), get_field_id('mobisense_server_ip'), NULL, 'Mobisense server IP');
INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES ((SELECT id FROM pageType WHERE `name` = 'sh_module_mobisense' LIMIT 0,1), get_field_id('mobisense_ssh_port'), 2711, 'Mobisense server ssh port');
INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES ((SELECT id FROM pageType WHERE `name` = 'sh_module_mobisense' LIMIT 0,1), get_field_id('mobisense_ssh_user'), NULL, 'Mobisense server ssh user');
INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES ((SELECT id FROM pageType WHERE `name` = 'sh_module_mobisense' LIMIT 0,1), get_field_id('mobisense_ssh_key'), NULL, 'Mobisense server ssh key');
INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES ((SELECT id FROM pageType WHERE `name` = 'sh_module_mobisense' LIMIT 0,1), get_field_id('mobisense_db_name'), NULL, 'Mobisense database name');
INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES ((SELECT id FROM pageType WHERE `name` = 'sh_module_mobisense' LIMIT 0,1), get_field_id('mobisense_db_port'), 5432, 'Mobisense database port');
INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES ((SELECT id FROM pageType WHERE `name` = 'sh_module_mobisense' LIMIT 0,1), get_field_id('mobisense_db_user'), NULL, 'Mobisense database user');
INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES ((SELECT id FROM pageType WHERE `name` = 'sh_module_mobisense' LIMIT 0,1), get_field_id('mobisense_db_password'), NULL, 'Mobisense database password');
INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES ((SELECT id FROM pageType WHERE `name` = 'sh_module_mobisense' LIMIT 0,1), get_field_id('mobisense_pull_data'), NULL, 'Mobisense pull data - if enabled the job will pull the data from mobisense; if disabled the job will skip this step');
INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES ((SELECT id FROM pageType WHERE `name` = 'sh_module_mobisense' LIMIT 0,1), get_field_id('title'), NULL, 'Page title');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page_values, get_field_id('title'), '0000000001', 'Module Mobisense');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page_values, get_field_id('title'), '0000000002', 'Module Mobisense');