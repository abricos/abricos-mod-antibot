<?php
/**
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
        switch ($d->do){
            case 'user':
                return $this->UserInfo($d->userid);
            case 'botappend':
                return $this->BotAppend($d->userid);
            case 'stopspam':
                return $this->StopSpam();
            case 'stopspamappend':
                return $this->StopSpamAppend($d->uids);
        }
        return null;
    }

    public function ToArray($rows){
        $ret = array();
        while (($row = $this->db->fetch_array($rows))){
            array_push($ret, $row);
        }
        return $ret;
    }

    private $_checker = null;
    private $_counter = 500;

    private function UserInfoLoadMethod($ret, $userid){
        // рекурсивная функция, ограничить запрос на всякий случай до 500
        $this->_counter--;
        if ($this->_counter < 0){
            return;
        }

        $ck = $this->_checker;

        if (isset($ck->users[$userid]) && $ck->users[$userid]){
            return;
        }
        $ck->users[$userid] = true;
        array_push($ret->users, $userid);

        $nextips = array();

        $rows = AntibotQuery::IPListByUser($this->db, $userid);
        while (($row = $this->db->fetch_array($rows))){
            $ip = $row['ip'];
            if (isset($ck->ips[$ip]) && $ck->ips[$ip]){
                continue;
            }
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
            $this->UserInfoLoadMethod($ret, $uid);
        }
    }

    private function UserInfoMethod($userid){
        $this->_checker = new stdClass();
        $this->_checker->ips = array();
        $this->_checker->users = array();

        $ret = new stdClass();
        $ret->user = null;
        $ret->ips = array();
        $ret->users = array();

        $this->UserInfoLoadMethod($ret, $userid);

        $users = array();
        $rows = AntibotQuery::UserList($this->db, $ret->users);
        while (($row = $this->db->fetch_array($rows))){
            if ($row['id'] == $userid){
                $ret->user = $row;
            }
            array_push($users, $row);
        }
        $ret->users = $users;

        return $ret;
    }

    public function UserInfo($userid){
        if (!$this->IsAdminRole()){
            return null;
        }
        return $this->UserInfoMethod($userid);
    }

    private function BotAppendMethod($userid){
        $info = $this->UserInfoMethod($userid);

        $author = $this->userid;
        if ($userid == $author){
            $author = 0;
        }

        AntibotQuery::BotIPAppend($this->db, $userid, $author, $info->ips);
        AntibotQuery::BotUserAppend($this->db, $userid, $author, $info->users);
    }

    public function BotAppend($userid){
        if (!$this->IsAdminRole()){
            return null;
        }
        $this->BotAppendMethod($userid);
    }

    /**
     * Проверка этого пользователя на пренадлежность к ботам.
     * Метод вызывается когда авторизованный пользователь зашел
     * с нового IP
     */
    public function UserBotCheck($userid = 0){
        $ip = AntibotModule::$instance->GetIP();
        if ($userid == 0){
            $userid = $this->userid;
        }
        $isbot = !empty($this->user->info['antibotdetect']);

        $row = AntibotQuery::BotIPCheck($this->db, $ip);

        if (!empty($row) && $isbot){
            // это бот и его IP уже в списке ботов
            return;
        }
        if (empty($row) && $isbot){
            // это бот и он засветил новый IP
            // необходимо проверить нет ли еще юзеров с этим IP, если есть,
            // то внести их в список ботов
            $this->BotAppendMethod($userid);
            return;
        }
        if (!empty($row)){
            // его IP в списке ботов, значит в бан его и всех его друзей
            $this->BotAppendMethod($userid);
            return;
        }
        /*
        // это не бот, а пока просто пользователь.
        // А нет ли среди его `друзей` ботов?

        $info = $this->UserInfoMethod($userid);
        $row = AntibotQuery::UserFrendsIsBot($this->db, $info->users);

        if (empty($row)){
            // все нормально, этот пользователь чист
            return;
        }

        // а вот и новый бот, отправляйся в бан
        $this->BotAppendMethod($userid);
        /**/
    }

    public function StopSpam(){
        if (!$this->IsAdminRole()){
            return null;
        }

        $this->StopSpamEmailsImport();

        $users = array();
        $rows = AntibotQuery::StopSpamCheck($this->db);
        while (($row = $this->db->fetch_array($rows))){
            array_push($users, $row);
        }

        $ret = new stdClass();
        $ret->users = $users;

        return $ret;
    }

    public function StopSpamEmailsImport(){
        set_time_limit(360);

        $file = CWD."/cache/stopspam/emails.txt";

        if (!file_exists($file)){
            return;
        }

        $fwrite = CWD."/cache/stopspam/temp.txt";

        $hdread = fopen($file, "r");
        $hdwrite = fopen($fwrite, "w");

        $limit = 100000;
        $i = 0;
        $emails = array();
        while ($info = fscanf($hdread, "%s\n")){
            $eml = $info[0];
            $i++;
            if ($i > $limit){
                fwrite($hdwrite, $eml."\n");
            } else {
                $emails[] = $eml;
                if (count($emails) > 500){
                    AntibotQuery::StopSpamEmailAppend($this->db, $emails);
                    $emails = array();
                }
            }
        }
        AntibotQuery::StopSpamEmailAppend($this->db, $emails);

        fclose($hdread);
        fclose($hdwrite);

        @unlink($file);
        rename($fwrite, $file);
    }

    public function StopSpamAppend($uids){
        if (!$this->IsAdminRole()){
            return null;
        }

        foreach ($uids as $userid){
            $this->BotAppend($userid);
        }
    }
}
