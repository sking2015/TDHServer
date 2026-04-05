<?php
require_once __DIR__ . "/../core/dbconn.php";
require_once __DIR__ . "/role.php";

enum eGameUniMessage: int
{
    case eum_None = 0;       //纯查询，不更新数据
    case eum_battleStart = 1; //战斗开始
    case eum_battleClear = 2; //一个phase战斗完成
    case eum_battleFail = 3; //战斗失败
    case eum_getRoleData = 4; //请求角色数据
    case eum_saveSkillsEquip = 5; //保存技能装备
    case eum_skillUpgrade = 6; //技能升级
}


//游戏数据控制器
class GameDataController
{
    public $userid;
    private $pdo;

    private $reason;

    public $curProgress;
    public $unlockProgress;
    public $coin;
    public $diamond;
    public $exp;
    public $curLevel;               //若保存了当前进度，要一并保存当前等级（以后可能还有其它数据，先就只等级吧）

    public $skillsEquip;           //技能装备数据，以一个字符串保存，格式为 "skillId1,skillId2,skillId3,skillId4" 代表4个技能槽位的装备情况

    public $skills = [];                 //技能数据，格式为 ["skillId" => level, ...] 代表技能id和等级的对应关系


    public function __construct($id, $pdo)
    {
        $this->userid = (int)$id;
        $this->pdo = $pdo;

        $this->curProgress = "0-0";
        $this->unlockProgress = "0-0";
        $this->coin = 0;
        $this->diamond = 0;
        $this->exp = 0;

        $this->GameDataInit();
    }

    private function GameDataInit()
    {
        $stmt = $this->pdo->prepare("SELECT * FROM game_data WHERE userid = ?");
        $stmt->execute([$this->userid]);
        $row = $stmt->fetch();
        if ($row) {
            $this->curProgress = $row["currprogress"];
            $this->unlockProgress = $row["unlockprogress"];
            $this->coin = $row["coin"];
            $this->diamond = $row["diamond"];
        } else {
            $stmt = $this->pdo->prepare("INSERT INTO game_data (userid,unlockprogress,currprogress,coin,diamond) VALUES (?, ?,?,?,?)");
            $stmt->execute([$this->userid, $this->unlockProgress, $this->curProgress, $this->coin, $this->diamond]);
        }
    }

    private function saveGameData()
    {
        // 编写 SQL 更新语句
        // 注意：SET 后面跟着的是“列名 = 占位符”，WHERE 用于锁定特定用户
        $sql = "UPDATE game_data 
        SET unlockprogress = ?, 
            currprogress = ?, 
            coin = ?, 
            diamond = ?            
        WHERE userid = ?";

        // 准备预处理语句
        $stmt = $this->pdo->prepare($sql);

        // 执行更新
        // 数组中的参数顺序必须与 SQL 语句中问号 (?) 的顺序完全一致
        $stmt->execute([
            $this->unlockProgress,
            $this->curProgress,
            $this->coin,
            $this->diamond,
            $this->userid
        ]);


        //只有胜利会保存角色等级
        if ($this->reason == eGameUniMessage::eum_battleClear) {
            $this->saveRoleLevel();
        }
    }

    private function loadskills()
    {
        // 从数据库加载技能数据
        $db = MongoConn::getDB();
        $collection = $db->selectCollection('roles');

        $roledata = $collection->findOne(['user_id' => $this->userid]);

        if ($roledata && isset($roledata["skills"])) {
            $this->skills = $roledata["skills"];
        } else {
            $this->skills = [];
        }
    }

    private function skillUpgrade($skillid)
    {
        // 先加载技能数据，确保是最新的
        $this->loadskills();

        //技能升级，由于现在还没有技能学习的设计，所以先默认只要升级就加1级，如果没有这个技能，就直接变成2级（相当于默认有1级了）
        if (isset($this->skills[$skillid])) {
            $this->skills[$skillid]++;
        } else {
            $this->skills[$skillid] = 2;
        }

        $this->saveSkills();
    }

    private function saveSkills()
    {
        // 获取数据库对象
        $db = MongoConn::getDB();
        $collection = $db->selectCollection('roles');

        $collection->updateOne(
            ['user_id' => (int)$this->userid],        // 查询条件
            ['$set' => ['skills' => $this->skills]],    // 更新指令 (务必带上 $set)
            ['upsert' => true]             // 关键选项：开启 upsert
        );
    }

    private function saveSkillsEquip()
    {
        // 获取数据库对象
        $db = MongoConn::getDB();
        $collection = $db->selectCollection('roles');

        $collection->updateOne(
            ['user_id' => (int)$this->userid],        // 查询条件
            ['$set' => ['skills_equip' => $this->skillsEquip]],    // 更新指令 (务必带上 $set)
            ['upsert' => true]             // 关键选项：开启 upsert
        );
    }

    private function processGameData()
    {
        $progress = $this->curProgress;
        [$stage, $phase] = array_map('intval', explode('-', $progress));
        [$ulstage, $ulphase] = array_map('intval', explode('-', $this->unlockProgress));
        $bReset = false;

        $newProgress = $progress;

        if ($this->reason == eGameUniMessage::eum_battleClear) {
            //如果是完成关卡，那么重置当前进度，当前关卡重置为0-0            
            if ($phase >= 6) {
                $this->curProgress = "0-0";
            } else {
                //如果小于6，则当前进度要前进1
                ++$phase;
                $newProgress = $stage . "-" . $phase;
                $this->curProgress = $newProgress;
            }
        }



        if ($stage == $ulstage + 1) {
            $ulstage = $stage;
            $bReset = true;
        } else if ($stage == $ulstage && $phase == $ulphase + 1) {
            $ulphase = $phase;
            $bReset = true;
        }

        if ($bReset) {
            $this->unlockProgress = $newProgress;
        }
    }

    private function getRoleData($level)
    {
        $role = new Role($this->userid);
        $role->setLevel($level);

        return $role;
    }

    private function saveRoleLevel()
    {
        // 获取数据库对象
        $db = MongoConn::getDB();
        $collection = $db->selectCollection('roles');

        $collection->updateOne(
            ['user_id' => (int)$this->userid],
            ['$set' => [
                'level' => (int)$this->curLevel,
                'exp' => (int)$this->exp
            ]],
            ['upsert' => true]
        );
    }

    public function onGameUniMessage($eReson, $sPara, $nPara)
    {
        $ret = null;
        $this->reason = eGameUniMessage::tryFrom($eReson);
        if ($this->reason === null) {
            // 处理无效值的情况
            echo "错误：接收到了无效的状态码 " . $eReson;
        } else {
            switch ($this->reason) {
                case eGameUniMessage::eum_getRoleData:
                    $ret = $this->getRoleData($nPara);
                    break;
                case eGameUniMessage::eum_battleClear:
                case eGameUniMessage::eum_battleStart:
                case eGameUniMessage::eum_battleFail:
                    $result = json_decode($sPara, true);
                    $this->curProgress = $result['progress'];
                    $this->exp = $result['exp'];
                    $this->coin = $result['coin'];
                    $this->diamond = $result['diamond'];
                    $this->curLevel = (int)$nPara;
                    $this->processGameData();
                    $this->saveGameData();
                    break;
                case eGameUniMessage::eum_saveSkillsEquip:
                    $this->skillsEquip = $sPara;
                    $this->saveSkillsEquip();
                    break;
                case eGameUniMessage::eum_skillUpgrade:
                    $skillid = $sPara;
                    $this->skillUpgrade($skillid);
                    break;
            }
        }

        return ["role" => $ret];
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

            if (isset($roledata["exp"])) {
                $role->exp = $roledata["exp"];
            } else {
                $role->exp = 0;
            }

            if (isset($roledata["skills_equip"])) {
                $role->skillsEquip = $roledata["skills_equip"];
            } else {
                $role->skillsEquip = "";
            }

            if (isset($roledata["skills"])) {
                $role->skills = $roledata["skills"];
            } else {
                $role->skills = [];
            }

            return $role;
        }

        // // 2. 新建角色
        $role = new Role($userid);
        $role->InitDefaultPerproty();

        // 准备数据
        $newData = [
            'user_id' => $userid,
            'level'     => 1,
            'exp'       => 0,
            'skills_equip' => "", // 初始技能装备数据为空，可以根据需要调整
            'skills' => [] // 初始技能数据为空，可以根据需要调整
        ];

        $collection->insertOne($newData);

        // $stmt = $this->pdo->prepare("INSERT INTO role_data (user_id, lv,exp,hp, sp, atk, def, cri, crd, atk_rate) VALUES (?,?,?,?,?,?,?,?,?,?)");
        // $stmt->execute([$userid, $role->level, $role->exp, $role->hp, $role->sp, $role->atk, $role->def, $role->cri, $role->crd, $role->atk_rate]);

        return $role;
    }
}
