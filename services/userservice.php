<?php
require_once __DIR__ . "/../core/dbconn.php";
require_once __DIR__ . "/../models/user.php";
require_once __DIR__ . "/../models/role.php";

class UserService
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = DbConn::getConnection();
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

        return $user;
    }

    public function getOrCreateRole($userid)
    {
        // 1. 查询
        $stmt = $this->pdo->prepare("SELECT * FROM role_data WHERE user_id = ?");
        $stmt->execute([$userid]);
        $row = $stmt->fetch();

        if ($row) {
            $role = new Role($userid);
            $role->hp = $row["hp"];
            $role->sp = $row["sp"];
            $role->atk = $row["atk"];
            $role->def = $row["def"];
            $role->cri = $row["cri"];
            $role->crd = $row["crd"];
            $role->atk_rate = $row["atk_rate"];
            return $role;
        }

        // 2. 新建角色
        $role = new Role($userid);
        $role->InitDefaultPerproty();

        $stmt = $this->pdo->prepare("INSERT INTO role_data (user_id, hp, sp, atk, def, cri, crd, atk_rate) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->execute([$userid, $role->hp, $role->sp, $role->atk, $role->def, $role->cri, $role->crd, $role->atk_rate]);

        return $role;
    }
}
