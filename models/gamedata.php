<?php
require_once __DIR__ . "/../core/dbconn.php";

enum eGameUpdataReason: int
{
    case er_None = 0;       //纯查询，不更新数据
    case er_battleStart = 1; //战斗开始
    case er_battleClear = 2; //一个phase战斗完成
    case er_battleFail = 3; //战斗失败
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

    public function __construct($id, $pdo)
    {
        $this->userid = $id;
        $this->pdo = $pdo;

        $this->curProgress = "0-0";
        $this->unlockProgress = "0-0";
        $this->coin = 0;
        $this->diamond = 0;

        $this->GameDataInit();
    }

    private function GameDataInit()
    {
        $stmt = $this->pdo->prepare("SELECT * FROM game_data WHERE userid = ?");
        $stmt->execute([$this->userid]);
        $row = $stmt->fetch();
        if ($row) {
            $this->curProgress = $row["unlockprogress"];
            $this->unlockProgress = $row["unlockprogress"];
            $this->coin = $row["coin"];
            $this->diamond = $row["diamond"];
        } else {
            $stmt = $this->pdo->prepare("INSERT INTO game_data (userid,unlockprogress,currprogress,coin,diamond) VALUES (?, ?,?,?,?)");
            $stmt->execute([$this->userid, $this->curProgress, $this->unlockProgress, $this->coin, $this->diamond]);
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
    }

    private function processGameData()
    {
        $progress = $this->curProgress;
        [$stage, $phase] = array_map('intval', explode('-', $progress));
        [$ulstage, $ulphase] = array_map('intval', explode('-', $this->unlockProgress));
        $bReset = false;
        if ($stage == $ulstage + 1) {
            $ulstage = $stage;
            $bReset = true;
        } else if ($stage == $ulstage && $phase == $ulphase + 1) {
            $ulphase = $phase;
            $bReset = true;
        }

        if ($bReset) {
            $this->unlockProgress = $progress;
        }

        if ($this->reason == eGameUpdataReason::er_battleClear && $phase == 6) {
            $this->curProgress = "0-0";
        }
    }

    public function updateGameData($eReson, $sPara, $nPara)
    {
        $this->reason = eGameUpdataReason::tryFrom($eReson);
        if ($this->reason === null) {
            // 处理无效值的情况
            echo "错误：接收到了无效的状态码 " . $eReson;
        } else {
            switch ($this->reason) {
                case eGameUpdataReason::er_battleClear:
                case eGameUpdataReason::er_battleStart:
                case eGameUpdataReason::er_battleFail:
                    $this->curProgress = $sPara;
                    $this->processGameData();
                    break;
            }

            $this->saveGameData();
        }
    }
}
