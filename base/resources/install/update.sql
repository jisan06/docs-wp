# 1.3.2

ALTER TABLE `#__easydoc_categories` CHANGE `locked_on` `locked_on` datetime DEFAULT NULL;
ALTER TABLE `#__easydoc_categories` CHANGE `created_on` `created_on` datetime DEFAULT NULL;
ALTER TABLE `#__easydoc_categories` CHANGE `modified_on` `modified_on` datetime DEFAULT NULL;

UPDATE `#__easydoc_categories` SET `locked_on` = NULL WHERE `locked_on` = 0000-00-00;
UPDATE `#__easydoc_categories` SET `created_on` = NULL WHERE `created_on` = 0000-00-00;
UPDATE `#__easydoc_categories` SET `modified_on` = NULL WHERE `modified_on` = 0000-00-00;

ALTER TABLE `#__easydoc_documents` CHANGE `publish_on` `publish_on` datetime DEFAULT NULL;
ALTER TABLE `#__easydoc_documents` CHANGE `unpublish_on` `unpublish_on` datetime DEFAULT NULL;
ALTER TABLE `#__easydoc_documents` CHANGE `locked_on` `locked_on` datetime DEFAULT NULL;
ALTER TABLE `#__easydoc_documents` CHANGE `created_on` `created_on` datetime DEFAULT NULL;
ALTER TABLE `#__easydoc_documents` CHANGE `modified_on` `modified_on` datetime DEFAULT NULL;

UPDATE `#__easydoc_documents` SET `publish_on` = NULL WHERE `publish_on` = 0000-00-00;
UPDATE `#__easydoc_documents` SET `unpublish_on` = NULL WHERE `unpublish_on` = 0000-00-00;
UPDATE `#__easydoc_documents` SET `locked_on` = NULL WHERE `locked_on` = 0000-00-00;
UPDATE `#__easydoc_documents` SET `created_on` = NULL WHERE `created_on` = 0000-00-00;
UPDATE `#__easydoc_documents` SET `modified_on` = NULL WHERE `modified_on` = 0000-00-00;

ALTER TABLE `#__easydoc_files` CHANGE `created_on` `created_on` datetime DEFAULT NULL;
ALTER TABLE `#__easydoc_files` CHANGE `modified_on` `modified_on` datetime DEFAULT NULL;

UPDATE `#__easydoc_files` SET `created_on` = NULL WHERE `created_on` = 0000-00-00;
UPDATE `#__easydoc_files` SET `modified_on` = NULL WHERE `modified_on` = 0000-00-00;

ALTER TABLE `#__easydoc_folders` CHANGE `created_on` `created_on` datetime DEFAULT NULL;
ALTER TABLE `#__easydoc_folders` CHANGE `modified_on` `modified_on` datetime DEFAULT NULL;

UPDATE `#__easydoc_folders` SET `created_on` = NULL WHERE `created_on` = 0000-00-00;
UPDATE `#__easydoc_folders` SET `modified_on` = NULL WHERE `modified_on` = 0000-00-00;

ALTER TABLE `#__easydoc_scans` CHANGE `created_on` `created_on` datetime DEFAULT NULL;
ALTER TABLE `#__easydoc_scans` CHANGE `modified_on` `modified_on` datetime DEFAULT NULL;
ALTER TABLE `#__easydoc_scans` CHANGE `sent_on` `sent_on` datetime DEFAULT NULL;

UPDATE `#__easydoc_scans` SET `created_on` = NULL WHERE `created_on` = 0000-00-00;
UPDATE `#__easydoc_scans` SET `modified_on` = NULL WHERE `modified_on` = 0000-00-00;
UPDATE `#__easydoc_scans` SET `sent_on` = NULL WHERE `sent_on` = 0000-00-00;

ALTER TABLE `#__easydoc_usergroups` CHANGE `created_on` `created_on` datetime DEFAULT NULL;
ALTER TABLE `#__easydoc_usergroups` CHANGE `modified_on` `modified_on` datetime DEFAULT NULL;

UPDATE `#__easydoc_usergroups` SET `created_on` = NULL WHERE `created_on` = 0000-00-00;
UPDATE `#__easydoc_usergroups` SET `modified_on` = NULL WHERE `modified_on` = 0000-00-00;

ALTER TABLE `#__easydoc_tags` CHANGE `created_on` `created_on` datetime DEFAULT NULL;
ALTER TABLE `#__easydoc_tags` CHANGE `modified_on` `modified_on` datetime DEFAULT NULL;
ALTER TABLE `#__easydoc_tags` CHANGE `locked_on` `locked_on` datetime DEFAULT NULL;


UPDATE `#__easydoc_tags` SET `created_on` = NULL WHERE `created_on` = 0000-00-00;
UPDATE `#__easydoc_tags` SET `modified_on` = NULL WHERE `modified_on` = 0000-00-00;
UPDATE `#__easydoc_tags` SET `locked_on` = NULL WHERE `locked_on` = 0000-00-00;

