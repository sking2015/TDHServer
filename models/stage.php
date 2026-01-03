<?php

define("STAGE_MONSTER_ALLOC_CSV", __DIR__ . '/../data/Level Monster Allocation.csv');
define("MONSTERS_CSV", __DIR__ . '/../data/Monster Attributes.csv');
define("STAGE_AWARD_INFO_CSV", __DIR__ . '/../data/StageAwardInfo.csv');
define("DROP_AWARD_CSV", __DIR__ . '/../data/DropAward.csv');
define("PHASE_PREFIX", "Phase");
define("PHASE_BOSS", "PhaseBoss");
define("STAGE_ID", "StageId");
define("MONSTER_ID", "Id");
define("DROP_ID", "ID");

class Stage
{
    public $stageid;
    public $monster_alloc;
    public $award_info;
    public function __construct($id)
    {
        $this->stageid = $id;
    }

    private function loadMosterAllocInfo()
    {
        try {
            $result = findCsvRowByColumn(STAGE_MONSTER_ALLOC_CSV, STAGE_ID, $this->stageid);
            if ($result) {
                $this->monster_alloc = $result;
                // echo json_encode([
                //     'status' => "ok",
                //     'data' => $result
                // ], JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode([
                    'status' => '未找到对关卡',
                ], JSON_UNESCAPED_UNICODE);
            }
        } catch (Exception $e) {
            echo "错误：" . $e->getMessage();
        }
    }

    private function loadStageDropAward()
    {
        try {
            $result = findCsvRowByColumn(STAGE_AWARD_INFO_CSV, STAGE_ID, $this->stageid);
            if ($result) {
                $this->award_info = $result;
            } else {
                echo json_encode([
                    'status' => '未找到对关卡',
                ], JSON_UNESCAPED_UNICODE);
            }
        } catch (Exception $e) {
            echo "错误：" . $e->getMessage();
        }
    }

    //取得清关奖励，参数为通关等级1~3,如果rank为0，表示失败，需要传入击杀数，酌情给点奖励
    public function getStageReward($rank, $defeatcount) {}


    private function getDropAwardInfo($dropawrdid)
    {
        $result = findCsvRowByColumn(DROP_AWARD_CSV, DROP_ID, $dropawrdid);
        return $result;
    }

    public function getMonsterInfo($monsterId)
    {
        $result = findCsvRowByColumn(MONSTERS_CSV, MONSTER_ID, $monsterId);
        return $result;
    }

    public function getOnePhaseInfo($phaseId)
    {
        $this->loadMosterAllocInfo();
        $this->loadStageDropAward();

        $phaseName = "";

        if ($phaseId == 0) {
            $phaseName = PHASE_BOSS;
        } else {
            $phaseName = PHASE_PREFIX . $phaseId;
        }


        $dropid = $this->award_info[$phaseName];

        $dropinfo = $this->getDropAwardInfo($dropid);

        $allMonster = [];

        $strInfo = $this->monster_alloc[$phaseName];
        $result = explode(",", $strInfo);
        foreach ($result as $value) {
            $item = explode("+", $value);
            $monsterId = $item[0];
            $monsterNum = $item[1];

            $allMonster[$monsterId] = $monsterNum;
        }

        $info = [];
        $info["monsterAlloc"] = $allMonster;

        $monsterDef = [];

        foreach ($allMonster as $key => $value) {
            $monsterDef[$key] = $this->getMonsterInfo($key);
        }

        $info["monsterDef"] = $monsterDef;

        $info["dropinfo"] = $dropinfo;

        return $info;
    }
}
