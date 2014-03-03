-- 
UPDATE fac_Config set Value='3.1-phuse' WHERE Parameter='Version';
--
-- Add configuration item for locking serial number 
--
INSERT INTO fac_Config VALUES ('SerialLock', '', 'Enabled/Disabled', 'string', 'Disabled'); 
ALTER TABLE `fac_Device` ADD `AssetLifeCycle` ENUM('Installing','In Production','Maintenance','End Of Life','Decomissioned') NULL DEFAULT NULL; 
ALTER TABLE `fac_Device` ADD `DecomDate` DATE NOT NULL AFTER `AssetLifeCycle`; 
CREATE TABLE IF NOT EXISTS `fac_EISservice` (
  `EISServiceID` int(11) NOT NULL AUTO_INCREMENT,
  `ServiceName` varchar(255) NOT NULL,
  `SOM` varchar(80) NOT NULL,
  `ServiceColor` varchar(7) NOT NULL DEFAULT '#FFFFFF',
  PRIMARY KEY (`EISServiceID`),
  UNIQUE KEY `ServiceName` (`ServiceName`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;
ALTER TABLE `fac_Device` ADD `EISService` INT (11) NOT NULL ;
