<?php

require_once __DIR__ . "/role.php";
require_once __DIR__ . "/gamedata.php";

class User
{
    public $account;
    public $userid;

    public $money;

    public $role;
    public $token;

    public $unlockProgress;
    public $currProgress;
    public $coin;
    public $diamond;

    public function __construct($account, $userid)
    {
        $this->account = $account;
        $this->userid  = $userid;
    }

    //读取游戏数据
    public function readGamedata($gamedata)
    {
        $this->unlockProgress = $gamedata->unlockProgress;
        $this->currProgress = $gamedata->curProgress;
        $this->coin = $gamedata->coin;
        $this->diamond = $gamedata->diamond;
    }
}
