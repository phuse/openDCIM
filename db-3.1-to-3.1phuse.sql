-- 
UPDATE fac_Config set Value='3.1-phuse' WHERE Parameter='Version';
--
-- Add configuration item for locking serial number 
--
INSERT INTO fac_Config VALUES ('SerialLock', '', 'Enabled/Disabled', 'string', 'Disabled'); 
