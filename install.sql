-- 
-- Database: `asterisk`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `gestionrenvois_features_list`
-- 

CREATE TABLE IF NOT EXISTS `gestionrenvois_features_list` (
  `id` int(11) NOT NULL auto_increment,
  `modulename` varchar(50) NOT NULL default '',
  `featurename` varchar(50) NOT NULL default '',
  `type` varchar(15) NOT NULL default '',
  `dbfamily` varchar(50) NOT NULL default '',
  `dbkey` varchar(50) NOT NULL default '',
  `value_on` varchar(50) NOT NULL default '',
  `value_off` varchar(50) NOT NULL default '',
  `display_on` varchar(50) NOT NULL default '',
  `display_off` varchar(50) NOT NULL default '',
  `extenlistdisplay_on` varchar(100) NOT NULL default '',
  `extenlistdisplay_off` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `feature` (`modulename`,`featurename`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

-- 
-- Dumping data for table `gestionrenvois_features_list`
-- 

INSERT IGNORE INTO `gestionrenvois_features_list` (`id`, `modulename`, `featurename`, `type`, `dbfamily`, `dbkey`, `value_on`, `value_off`, `display_on`, `display_off`, `extenlistdisplay_on`, `extenlistdisplay_off`) VALUES
(1, 'callforward', 'cfbon', 'EXTEN', 'CFB', '{EXTEN}', '{VALUE}', '{DELETE}', '<strong>{VALUE}</strong>', '<strong>Inactif</strong>', '<strong>CFB:</strong>{VALUE}', ''),
(2, 'callforward', 'cfon', 'EXTEN', 'CF', '{EXTEN}', '{VALUE}', '{DELETE}', '<strong>{VALUE}</strong>', '<strong>Inactif</strong>', '<strong>CF:</strong>{VALUE}', ''),
(3, 'callforward', 'cfuon', 'EXTEN', 'CFU', '{EXTEN}', '{VALUE}', '{DELETE}', '<strong>{VALUE}</strong>', '<strong>Inactif</strong>', '<strong>CFU:</strong>{VALUE}', ''),
(4, 'callwaiting', 'cwon', 'ONOFF', 'CW', '{EXTEN}', 'ENABLED', '{DELETE}', '<strong>Actif</strong>', '<strong>Inactif</strong>', '', ''),
(5, 'donotdisturb', 'dnd_on', 'ONOFF', 'DND', '{EXTEN}', 'YES', '{DELETE}', '<strong>Actif</strong>', '<strong>Inactif</strong>', '<strong><font color=#FF0000>DND</font></strong>', ''),
(6, 'paging', 'intercom-off', 'ONOFF', 'AMPUSER/{EXTEN}', 'intercom', 'disabled', 'enabled', '<strong>Interdit</strong>', '<strong>Autorise</strong>', '<strong>IC OFF</strong>', '');


-- 
-- Table structure for table `gestionrenvois_module`
-- 

CREATE TABLE IF NOT EXISTS `gestionrenvois_module` (
  `id` varchar(50) NOT NULL default '',
  `value` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `gestionrenvois_module`
-- 

INSERT IGNORE INTO `gestionrenvois_module` VALUES ('modulerawname', 'gestionrenvois');
INSERT IGNORE INTO `gestionrenvois_module` VALUES ('moduledisplayname', 'Gestion Renvois');
INSERT IGNORE INTO `gestionrenvois_module` VALUES ('moduleversion', '0.0.1');

UPDATE IGNORE `gestionrenvois_module` SET `value` = '15.0.1' where `id` = 'moduleversion';

