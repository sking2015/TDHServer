<?php
require_once __DIR__ . "/../core/dbconn.php";
require_once __DIR__ . "/../models/gamedata.php";
require_once __DIR__ . "/../models/user.php";
require_once __DIR__ . "/../models/role.php";
require_once __DIR__ . "/../services/tokenmanager.php";


class UserService
{
    private $pdo;
    private $auth;

    public function __construct()
    {
        $this->pdo = DbConn::getConnection();
        $this->auth = new TokenManager();
    }

    public function verifyToken($userid, $token)
    {
        $checkid = $this->auth->verifyToken($token);
        if ($checkid && $checkid == $userid) {
            return true;
        }
    }

    public function onGameUniMessage($userid, $eReason, $sPara, $nPara)
    {
        $gamedata = new GameDataController($userid, $this->pdo);
        return $gamedata->onGameUniMessage($eReason, $sPara, $nPara);
    }

    public function getOrCreateUser($account)
    {
        // 1. 查询
        $stmt = $this->pdo->prepare("SELECT * FROM account WHERE account = ?");
        $stmt->execute([$account]);
        $row = $stmt->fetch();

        if ($row) {
            $user = new User($row["account"], $row["userid"]);
            $user->money = $row["money"];
        } else {
            // 2. 新建用户
            $stmt = $this->pdo->query("SELECT MAX(userid) AS maxid FROM account");
            $maxid = $stmt->fetchColumn();
            $newUserid = $maxid ? $maxid + 1 : 900001;
            $money = 0;

            $stmt = $this->pdo->prepare("INSERT INTO account (account, userid,money) VALUES (?, ?,?)");
            $stmt->execute([$account, $newUserid, $money]);

            $user = new User($account, $newUserid);

            $user->money = $money;
        }


        $user->role = $this->getOrCreateRole($user->userid);
        $user->token = $this->auth->generateToken($user->userid);

        $gamedata = new GameDataController($user->userid, $this->pdo);
        $user->readGamedata($gamedata);

        return $user;
    }

    public function getOrCreateRole($userid)
    {
        // 1. 查询,角色数据不再使用mysql
        // $stmt = $this->pdo->prepare("SELECT * FROM role_data WHERE user_id = ?");
        // $stmt->execute([$userid]);
        // $row = $stmt->fetch();

        $db = MongoConn::getDB();
        $collection = $db->selectCollection('roles');

        $roledata = $collection->findOne(['user_id' => $userid]);

        if ($roledata) {
            $role = new Role($userid);
            $role->setLevel($roledata["level"]);

            return $role;
        }

        // // 2. 新建角色
        $role = new Role($userid);
        $role->InitDefaultPerproty();

        // 准备数据
        $newData = [
            'user_id' => $userid,
            'level'     => 1,
        ];

        $collection->insertOne($newData);

        // $stmt = $this->pdo->prepare("INSERT INTO role_data (user_id, lv,exp,hp, sp, atk, def, cri, crd, atk_rate) VALUES (?,?,?,?,?,?,?,?,?,?)");
        // $stmt->execute([$userid, $role->level, $role->exp, $role->hp, $role->sp, $role->atk, $role->def, $role->cri, $role->crd, $role->atk_rate]);

        return $role;
    }
}
