<?php
/**
 * @package Abricos
 * @subpackage Antibot
 * @copyright Copyright (C) 2011 Abricos. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @author Alexander Kuzmin (roosit@abricos.org)
 */


/**
 * Модуль Финансы
 */
class AntibotModule extends Ab_Module {

    /**
     * @var AntibotModule
     */
    public static $instance = null;

    private $_manager = null;

    /**
     * Конструктор
     */
    public function __construct() {
        $this->version = "0.1.1";
        $this->name = "antibot";
        $this->takelink = "antibot"; // нужен для передачи списка блокировки другим сайтам
        $this->permission = new AntibotPermission($this);
        AntibotModule::$instance = $this;
    }

    /**
     * @return AntibotManager
     */
    public function GetManager() {
        if (is_null($this->_manager)) {
            require_once 'includes/manager.php';
            $this->_manager = new AntibotManager($this);
        }
        return $this->_manager;
    }

    public function GetIP() {
        return $_SERVER['REMOTE_ADDR'];
    }

    /**
     * Обновить информацию в базе о пользователе.
     * Вызывается всегда, когда зарегистрированный пользователь
     * обратился к серверу
     */
    public function UserDataUpdate($userid = 0) {
        if ($userid == 0) {
            $userid = Abricos::$user->id;
        }
        require_once 'includes/dbquery.php';
        $isapp = AntibotQuery::UserIPAppend(Abricos::$db, $userid, $this->GetIP());

        if ($isapp > 0) {
            // у пользовтеля новый айпишник, а может он бот?
            $this->GetManager()->UserBotCheck($userid);
        }
    }
}

class AntibotAction {
    const VIEW = 10;
    const ADMIN = 50;
}

class AntibotPermission extends Ab_UserPermission {

    public function AntibotPermission(AntibotModule $module) {
        // объявление ролей по умолчанию
        // используется при инсталяции модуля в платформе
        $defRoles = array(
            new Ab_UserRole(AntibotAction::VIEW, Ab_UserGroup::GUEST),

            new Ab_UserRole(AntibotAction::VIEW, Ab_UserGroup::ADMIN),
            new Ab_UserRole(AntibotAction::ADMIN, Ab_UserGroup::ADMIN)
        );
        parent::__construct($module, $defRoles);
    }

    public function GetRoles() {
        return array(
            AntibotAction::VIEW => $this->CheckAction(AntibotAction::VIEW),
            AntibotAction::ADMIN => $this->CheckAction(AntibotAction::ADMIN)
        );
    }
}

// создать экземляр класса модуля и зарегистрировать его в ядре 
Abricos::ModuleRegister(new AntibotModule())

?>