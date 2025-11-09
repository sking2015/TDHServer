<?php
header("Access-Control-Allow-Origin: http://localhost:7456");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");

header("Content-Type: application/json; charset=utf-8");

define("CONST_INI", __DIR__ . '/data/const.ini');

require_once __DIR__ . "/services/userservice.php";
require_once __DIR__ . "/common/common.php";
require_once __DIR__ . "/config/constdef.php";
require_once __DIR__ . "/models/stage.php";


$reqType = $_REQUEST["reqtype"] ?? null;

if (!$reqType) {
    echo json_encode(["status" => "error", "message" => "Missing parameter: reqType"]);
}


function onLoging()
{
    $account = $_REQUEST["account"] ?? null;
    if (!$account) {
        echo json_encode(["status" => "error", "message" => "Missing parameter: account"]);
        exit;
    }

    $userService = new UserService();
    $user = $userService->getOrCreateUser($account);

    echo json_encode([
        "status"  => "ok",
        "account" => $user->account,
        "userid"  => $user->userid,
        "money"   => $user->money,

        "role"    => $user->role
    ]);
}

function onGameInfo()
{
    $dataconfig = parse_ini_file(CONST_INI);
    $dataversion = $dataconfig["version"];
    echo json_encode([
        "status"  => "ok",
        "version" => VERSION,
        "dversion" =>  $dataversion
    ]);
}

function onLoadStageInfo()
{
    $stageid = $_REQUEST["stageid"] ?? null;
    $phaseid = $_REQUEST["phaseid"] ?? null;

    if ($stageid && $phaseid) {
        $stage = new Stage($stageid);
        $info = $stage->getOnePhaseInfo($phaseid);
        echo json_encode([
            "status" => "ok",
            "info" => $info
        ]);
    } else {
        echo json_encode([
            "status"  => "未传入stageid或phaseid",
        ]);
    }
}

switch ($reqType) {
    case "login":
        onLoging();
        break;
    case "stageinfo":
        onLoadStageInfo();
        break;
    case "gameinfo":
        onGameInfo();
        break;
}
