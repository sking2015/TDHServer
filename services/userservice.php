<?php
require_once __DIR__ . "/../core/dbconn.php";
require_once __DIR__ . "/../models/user.php";

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
            return $user;
        }

        // 2. 新建用户
        $stmt = $this->pdo->query("SELECT MAX(userid) AS maxid FROM account");
        $maxid = $stmt->fetchColumn();
        $newUserid = $maxid ? $maxid + 1 : 900001;
        $money = 0;

        $stmt = $this->pdo->prepare("INSERT INTO account (account, userid,money) VALUES (?, ?,?)");
        $stmt->execute([$account, $newUserid, $money]);

        $user = new User($account, $newUserid);

        $user->money = $money;

        return $user;
    }
}
