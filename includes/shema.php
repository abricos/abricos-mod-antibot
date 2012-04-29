<?php
/**
 * @version $Id$
 * @package Abricos
 * @subpackage Antibot
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @author  Alexander Kuzmin <roosit@abricos.org>
 */

$charset = "CHARACTER SET 'utf8' COLLATE 'utf8_general_ci'";
$updateManager = Ab_UpdateManager::$current; 
$db = Abricos::$db;
$pfx = $db->prefix;

if ($updateManager->isInstall()){
	
	$db->query_write("
		ALTER TABLE ".$pfx."user
			ADD `antibotdetect` TINYINT(1) unsigned NOT NULL DEFAULT 0 COMMENT '1 - модуль антибот определил в этом пользователе бота',
			ADD `antibotnotuse` TINYINT(1) unsigned NOT NULL DEFAULT 0 COMMENT '1 - админ сказал что это не бот'
		");

	$db->query_write("
		CREATE TABLE IF NOT EXISTS ".$pfx."antibot_userip (
			`userid` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Идентификатор пользователя',
			`ip` varchar(15) NOT NULL DEFAULT '' COMMENT 'IP адрес',
			UNIQUE KEY `userip` (`userid`,`ip`)
		)".$charset
	);
	
}


?>