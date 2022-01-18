-- MySQL Workbench Synchronization
-- Generated: 2022-01-18 11:10
-- Model: Shack Locations
-- Version: 2.0.0
-- Project: Shack Locations
-- Author: Bill Tomczak

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;

ALTER TABLE `#__focalpoint_legends` CHANGE `created_by` `created_by` INT(11) NULL DEFAULT NULL AFTER `checked_out`;
ALTER TABLE `#__focalpoint_legends` CHANGE `ordering` `ordering` INT(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__focalpoint_legends` CHANGE `checked_out` `checked_out` INT(11) NULL DEFAULT NULL;
ALTER TABLE `#__focalpoint_legends` CHANGE `checked_out_time` `checked_out_time` DATETIME NULL DEFAULT NULL;

ALTER TABLE `#__focalpoint_locations` DROP COLUMN `geoaddress`;
ALTER TABLE `#__focalpoint_locations` CHANGE `ordering` `ordering` INT(11) NOT NULL DEFAULT 0 AFTER `id`;
ALTER TABLE `#__focalpoint_locations` CHANGE `created_by` `created_by` INT(11) NULL DEFAULT NULL AFTER `metadata`;
ALTER TABLE `#__focalpoint_locations` CHANGE `checked_out` `checked_out` INT(11) NULL DEFAULT NULL AFTER `created_by`;
ALTER TABLE `#__focalpoint_locations` CHANGE `checked_out_time` `checked_out_time` DATETIME NULL DEFAULT NULL AFTER `checked_out`;
ALTER TABLE `#__focalpoint_locations` CHANGE `maplinkid` `maplinkid` INT(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__focalpoint_locations` CHANGE `menulink` `menulink` INT(11) NOT NULL DEFAULT 0;

ALTER TABLE `#__focalpoint_locationtypes` DROP COLUMN `description`;
ALTER TABLE `#__focalpoint_locationtypes` CHANGE `customfields` `customfields` TEXT NOT NULL AFTER `legend`;
ALTER TABLE `#__focalpoint_locationtypes` CHANGE `created_by` `created_by` INT(11) NULL DEFAULT NULL AFTER `customfields`;
ALTER TABLE `#__focalpoint_locationtypes` CHANGE `ordering` `ordering` INT(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__focalpoint_locationtypes` CHANGE `checked_out` `checked_out` INT(11) NULL DEFAULT NULL;
ALTER TABLE `#__focalpoint_locationtypes` CHANGE `checked_out_time` `checked_out_time` DATETIME NULL DEFAULT NULL;

ALTER TABLE `#__focalpoint_maps` DROP COLUMN `centerpoint`;
ALTER TABLE `#__focalpoint_maps` CHANGE `created_by` `created_by` INT(11) NULL DEFAULT NULL AFTER `params`;
ALTER TABLE `#__focalpoint_maps` CHANGE `checked_out` `checked_out` INT(11) NULL DEFAULT NULL AFTER `created_by`;
ALTER TABLE `#__focalpoint_maps` CHANGE `checked_out_time` `checked_out_time` DATETIME NULL DEFAULT NULL AFTER `checked_out`;
ALTER TABLE `#__focalpoint_maps` CHANGE `ordering` `ordering` INT(11) NOT NULL DEFAULT 0;


SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
