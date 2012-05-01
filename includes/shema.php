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
	Abricos::GetModule('antibot')->permission->Install();
	
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

if (!$updateManager->isInstall() && $updateManager->isUpdate('0.1.0.1')){
	Abricos::GetModule('antibot')->permission->Install();
}

if ($updateManager->isUpdate('0.1.0.2')){
	
	$db->query_write("
		CREATE TABLE IF NOT EXISTS ".$pfx."antibot_botip (
			`ip` varchar(15) NOT NULL DEFAULT '' COMMENT 'IP адрес',
			
			`fromuserid` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Пользователь  инициатор - бот',
			`authorid` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Пользователь добавивший запись, 0 - системой на автомате',
			
			`dateline` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Дата добавления',
			
			PRIMARY KEY  (`ip`)
		)".$charset
	);

	$db->query_write("
		CREATE TABLE IF NOT EXISTS ".$pfx."antibot_botuser (
			`userid` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Идентификатор пользователя бота',
			
			`fromuserid` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Пользователь  инициатор - бот',
			`authorid` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Пользователь добавивший запись, 0 - системой на автомате',
				
			`dateline` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Дата добавления',
				
			PRIMARY KEY  (`userid`)
		)".$charset
	);
	
}

?>