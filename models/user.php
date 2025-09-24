<?php
class User
{
    public $account;
    public $userid;

    public $money;

    public function __construct($account, $userid)
    {
        $this->account = $account;
        $this->userid  = $userid;
    }
}
