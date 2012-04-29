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
}

?>