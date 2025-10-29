<?php

define("ROLE_HP", 100);
define("ROLE_LV", 1);
define("ROLE_EXP", 0);
define("ROLE_SP", 100);
define("ROLE_ATK", 20);
define("ROLE_DEF", 5);
define("ROLE_CRI", 5);
define("ROLE_CRD", 120);
define("ROLE_ATK_RATE", 2);         //默认两秒钟攻击一次

class Role
{
    public $userid;             //用户id
    public $hp;                 //血量
    public $lv;                 //等级
    public $exp;                //经验值
    public $sp;                 //技能点
    public $atk;                //攻击力
    public $def;                //防御力
    public $cri;                //暴击率
    public $crd;                //暴击伤害
    public $atk_rate;           //攻击频率    

    public function __construct($id)
    {
        $this->userid = $id;
    }

    public function InitDefaultPerproty()
    {
        $this->lv = ROLE_LV;
        $this->exp = ROLE_EXP;
        $this->hp = ROLE_HP;
        $this->sp = ROLE_SP;
        $this->atk = ROLE_ATK;
        $this->def = ROLE_DEF;
        $this->cri = ROLE_CRI;
        $this->crd = ROLE_CRD;
        $this->atk_rate = ROLE_ATK_RATE;
    }
}
