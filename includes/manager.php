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
	
}

?>