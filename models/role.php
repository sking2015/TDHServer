<?php

use Data\RoleConfig;

define("ROLE_HP", 100);
define("ROLE_LV", 1);
define("ROLE_EXP", 0);
define("ROLE_SP", 0);
define("ROLE_ATK", 20);
define("ROLE_DEF", 5);
define("ROLE_CRI", 5);
define("ROLE_CRD", 120);
define("ROLE_ATK_RATE", 2);         //默认两秒钟攻击一次

class Role
{
    public $userid;             //用户id
    public $level;                 //等级
    public $hp;                 //血量
    public $exp;                //经验值
    public $sp;                 //技能点
    public $atk;                //攻击力
    public $def;                //防御力
    public $cri;                //暴击率
    public $crd;                //暴击伤害
    public $atk_rate;           //攻击频率    
    public $lvup_exp;           //升级经验

    public $skillsEquip;       //技能装备数据
    public $skills;           //技能数据

    public function __construct($id)
    {
        $this->userid = $id;
        $this->exp = 0;
        $this->skillsEquip = "";
        $this->skills = [];
    }

    public function setLevel($level)
    {
        $this->level = (int)$level;
        $roledata = RoleConfig::$data[$this->level];
        if ($roledata) {
            $this->lvup_exp = $roledata["LvUpExp"];
            $this->hp = $roledata["HP"];
            $this->sp = $roledata["SP"];
            $this->atk = $roledata["Atk"];
            $this->def = $roledata["Def"];
            $this->cri = $roledata["Cri"];
            $this->crd = $roledata["Crd"];
            $this->atk_rate = $roledata["Atk_rate"];
        }
    }

    public function InitDefaultPerproty()
    {
        $this->level = ROLE_LV;
        $this->exp = ROLE_EXP;
        $this->hp = ROLE_HP;
        $this->sp = ROLE_SP;
        $this->atk = ROLE_ATK;
        $this->def = ROLE_DEF;
        $this->cri = ROLE_CRI;
        $this->crd = ROLE_CRD;
        $this->atk_rate = ROLE_ATK_RATE;
        $this->lvup_exp = 0;
    }
}
