<?php

require_once __DIR__ . "/role.php";

class User
{
    public $account;
    public $userid;

    public $money;

    public $role;

    public function __construct($account, $userid)
    {
        $this->account = $account;
        $this->userid  = $userid;
    }
}
