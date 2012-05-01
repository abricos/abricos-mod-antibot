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
	
	public static function BotIPAppend(Ab_Database $db, $fromuserid, $authorid, $ips){
		if (count($ips) == 0){ return null; }
		$awh = array();

		for ($i=0; $i<count($ips); $i++){
			$ip = $ips[$i];
			array_push($awh, "('".bkstr($ip)."', ".bkint($fromuserid).", ".bkint($authorid).", ".TIMENOW.")");
		}
		
		$sql = "
			INSERT IGNORE INTO ".$db->prefix."antibot_botip 
				(ip, fromuserid, authorid, dateline) VALUES
				".implode(",", $awh)."
		";
		$db->query_write($sql);
	}
	
	public static function BotIPCheck(Ab_Database $db, $ip){
		$sql = "
			SELECT ip
			FROM ".$db->prefix."antibot_botip
			WHERE ip='".bkstr($ip)."'
			LIMIT 1
		";
		return $db->query_first($sql);
	}
	
	public static function UserSetBotFlag(Ab_Database $db, $userid) {
		$sql = "
			UPDATE ".$db->prefix."user
			SET antibotdetect=1
			WHERE userid=".bkint($userid)."
			LIMIT 1
		";
		$db->query_write($sql);
	}
	
	public static function BotUserAppend(Ab_Database $db, $fromuserid, $authorid, $users){
		if (count($users) == 0){
			return null;
		}
		$awh = array();
		for ($i=0; $i<count($users); $i++){
			$uid = $users[$i]['id'];
			array_push($awh, "(".bkint($uid).", ".bkint($fromuserid).", ".bkint($authorid).", ".TIMENOW.")");
			AntibotQuery::UserSetBotFlag($db, $uid);
		}
	
		$sql = "
			INSERT IGNORE INTO ".$db->prefix."antibot_botuser
				(userid, fromuserid, authorid, dateline) VALUES
				".implode(",", $awh)."
		";
		$db->query_write($sql);
	}
	
	/**
	 * Проверить, нет ли среди друзей пользователя боты?
	 * 
	 * @param Ab_Database $db
	 * @param array $uids список друзей
	 */
	public static function UserFrendsIsBot(Ab_Database $db, $users){
		if (count($users) == 0){
			return null;
		}
	
		$awh = array();
		for ($i=0; $i<count($users); $i++){
			$id = $users[$i]['id'];
			array_push($awh, " userid='".bkstr($id)."'");
		}
		$sql = "
			SELECT userid as id
			FROM ".$db->prefix."user
			WHERE antibotdetect=1 AND (".implode(" OR ", $awh).")
			LIMIT 1
		";
		return $db->query_first($sql);
	}
	
}

?>