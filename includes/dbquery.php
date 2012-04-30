<?php
/**
 * @version $Id$
 * @package Abricos
 * @subpackage Antibot
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @author  Alexander Kuzmin <roosit@abricos.org>
 */

class AntibotQuery {
	
	public static function UserIPAppend(Ab_Database $db, $userid, $ip){
		$sql = "
			INSERT IGNORE INTO ".$db->prefix."antibot_userip (userid, ip) VALUES (
				".bkint($userid).",
				'".bkstr($ip)."'
			)
		";
		$db->query_write($sql);
		return $db->affected_rows();
	}
	
	public static function IPListByUser(Ab_Database $db, $userid){
		$sql = "
			SELECT ip
			FROM ".$db->prefix."antibot_userip 
			WHERE userid=".bkint($userid)."
		";
		return $db->query_read($sql);
	}
	
	public static function UserListByIP(Ab_Database $db, $ip){
		$ips = is_array($ip) ? $ip : array($ip);
		if (count($ips) == 0){ return null; }
		
		$awh = array();
		foreach($ips as $ip){
			array_push($awh, " ip='".bkstr($ip)."'");
		}
		$sql = "
			SELECT userid
			FROM ".$db->prefix."antibot_userip 
			WHERE ".implode(" OR ", $awh)."
		";
		return $db->query_read($sql);
	}
	
	public static function UserList(Ab_Database $db, $uids){
		if (count($uids) == 0){ return null; }
		
		$awh = array();
		foreach($uids as $id){
			array_push($awh, " userid='".bkstr($id)."'");
		}
		$sql = "
			SELECT 
				userid as id,
				username as unm,
				email as eml,
				joindate as jd, 
				lastvisit as lv
			FROM ".$db->prefix."user
			WHERE ".implode(" OR ", $awh)."
		";
		return $db->query_read($sql);
	}
}

?>