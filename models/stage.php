<?php

define("STAGE_MONSTER_ALLOC_CSV", __DIR__ . '/../data/Level Monster Allocation.csv');
define("MONSTERS_CSV", __DIR__ . '/../data/Monster Attributes.csv');
define("PHASE_PREFIX", "Phase");
define("PHASE_BOSS", "PhaseBoss");
define("STAGE_ID", "StageId");
define("MONSTER_ID", "Id");

class Stage
{
    public $stageid;
    public $monster_alloc;
    public function __construct($id)
    {
        $this->stageid = $id;


        try {
            $result = findCsvRowByColumn(STAGE_MONSTER_ALLOC_CSV, STAGE_ID, $id);
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

    public function getMonsterInfo($monsterId)
    {
        $result = findCsvRowByColumn(MONSTERS_CSV, MONSTER_ID, $monsterId);
        return $result;
    }

    public function getOnePhaseInfo($phaseId)
    {
        $phaseName = "";

        if ($phaseId == 0) {
            $phaseName = PHASE_BOSS;
        } else {
            $phaseName = PHASE_PREFIX . $phaseId;
        }

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
        $info["monster_alloc"] = $allMonster;

        $monsterDef = [];

        foreach ($allMonster as $key => $value) {
            $monsterDef[$key] = $this->getMonsterInfo($key);
        }

        $info["monster_def"] = $monsterDef;

        return $info;
    }
}
