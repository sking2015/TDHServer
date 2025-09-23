<?php
class User {
    public $account;
    public $userid;

    public function __construct($account, $userid) {
        $this->account = $account;
        $this->userid  = $userid;
    }
}
