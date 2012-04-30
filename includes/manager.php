<?php
/**
 * @version $Id$
 * @package Abricos
 * @subpackage Antibot
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @author  Alexander Kuzmin <roosit@abricos.org>
 */

require_once 'dbquery.php';

class AntibotManager extends Ab_ModuleManager {
	
	/**
	 * @var AntibotModule
	 */
	public $module = null;
	
	/**
	 * @var AntibotManager
	 */
	public static $instance = null; 
	
	public function __construct(AntibotModule $module){
		parent::__construct($module);
		
		AntibotManager::$instance = $this;
	}
	
	public function IsAdminRole(){
		return $this->IsRoleEnable(AntibotAction::ADMIN);
	}
	
	public function AJAX($d){
		switch($d->do){
			case 'user': return $this->UserInfo($d->userid);
		}
		return null;
	}
	
	private $_checker = null;
	private $_counter = 500;
	
	private function UserInfoMethod($ret, $userid){
		// рекурсивная функция, ограничить запрос на всякий случай до 500
		$this->_counter--;
		if ($this->_counte < 0){ return; }
		
		$ck = $this->_checker;
		
		if ($ck->users[$userid]){ return; }
		$ck->users[$userid] = true;
		array_push($ret->users, $userid);
		
		$nextips = array();
		
		$rows = AntibotQuery::IPListByUser($this->db, $userid);
		while (($row = $this->db->fetch_array($rows))){
			$ip = $row['ip'];
			if ($ck->ips[$ip]){ continue; }
			$ck->ips[$ip] = true;
			
			array_push($ret->ips, $ip);
			array_push($nextips, $ip);
		}
		if (count($nextips) == 0){
			return;
		}
		$rows = AntibotQuery::UserListByIP($this->db, $nextips);
		while (($row = $this->db->fetch_array($rows))){
			$uid = $row['userid'];
			$this->UserInfoMethod($ret, $uid);
		}
	}
	
	public function UserInfo($userid){
		if (!$this->IsAdminRole()){
			return null;
		}
		$this->_checker = new stdClass();
		$this->_checker->ips = array();
		$this->_checker->users = array();
		
		$ret = new stdClass();
		$ret->ips = array();
		$ret->users = array();
		
		$this->UserInfoMethod($ret, $userid);
		
		$users = array();
		$rows = AntibotQuery::UserList($this->db, $ret->users);
		while (($row = $this->db->fetch_array($rows))){
			array_push($users, $row);
		}
		$ret->users = $users;
				
		return $ret;
	}
	
	
}

?>