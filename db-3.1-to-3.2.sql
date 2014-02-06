--
-- Add this again because it was left out of the create.sql for the 3.1 release
--
ALTER TABLE fac_CabRow ADD COLUMN CabOrder ENUM( 'ASC', 'DESC' ) NOT NULL DEFAULT 'ASC';

--
-- Add the picture fields for front/rear views, and front/rear slots in device template 
--
ALTER TABLE fac_DeviceTemplate
	ADD COLUMN FrontPictureFile VARCHAR(45) NOT NULL AFTER Notes,
	ADD COLUMN RearPictureFile VARCHAR(45) NOT NULL AFTER FrontPictureFile,
	ADD COLUMN ChassisSlots SMALLINT(6) NOT NULL AFTER RearPictureFile,
	ADD COLUMN RearChassisSlots SMALLINT(6) NOT NULL AFTER ChassisSlots;

--
-- Slots table content the coodinates os slots in a picture of a chassis device template 
--
CREATE TABLE fac_Slots (
	TemplateID INT(11) NOT NULL,
	Position INT(11) NOT NULL,
	BackSide TINYINT(1) NOT NULL,
	X INT(11) NULL,
	Y INT(11) NULL,
	W INT(11) NULL,
	H INT(11) NULL,
	PRIMARY KEY (TemplateID, Position, BackSide)
) ENGINE = InnoDB;

--
-- Add field to indicate the intake/front edge of a cabinet
--

ALTER TABLE fac_Cabinet ADD COLUMN FrontEdge ENUM("Top","Right","Bottom","Left") NOT NULL DEFAULT "Top" AFTER MapX2;
--
-- Add configuration item for locking serial number
--
INSERT INTO fac_Config VALUES ('SerialLock', '', 'Enabled/Disabled', 'string', 'Disabled');

