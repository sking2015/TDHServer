<?php
// 1. 定义允许的来源列表
$allowed_origins = [
    'http://127.0.0.1',           // 本地浏览器测试
    'http://localhost',           // Android Capacitor 默认
    'capacitor://localhost',      // iOS Capacitor 默认
    'http://localhost:7456',       // 你之前的调试地址
    'http://165.154.203.112'
];

// 2. 获取当前请求的 Origin
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

// 3. 检查当前 Origin 是否在允许列表中
if (in_array($origin, $allowed_origins)) {
    header('Access-Control-Allow-Origin: ' . $origin);
}


header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");

header("Content-Type: application/json; charset=utf-8");

require 'vendor/autoload.php';

define("CONST_INI", __DIR__ . '/data/const.ini');

require_once __DIR__ . "/services/userservice.php";
require_once __DIR__ . "/common/common.php";
require_once __DIR__ . "/config/constdef.php";
require_once __DIR__ . "/models/stage.php";
require_once __DIR__ . "/models/gamedata.php";




// 获取原始输入流
$json = file_get_contents('php://input');
// 解码为 PHP 关联数组
$post_data = json_decode($json, true);
// 现在可以通过 $post_data['key'] 访问数据了

$reqType = $post_data["reqtype"] ?? null;



if (!$reqType) {
    echo json_encode(["status" => "error", "message" => "Missing parameter: reqType"]);
}

function thowUser($account)
{
    $userService = new UserService();
    $user = $userService->getOrCreateUser($account);

    echo json_encode([
        "status"  => "ok",
        "account" => $user->account,
        "userid"  => $user->userid,
        "money"   => $user->money,

        "role"    => $user->role,
        "token"   => $user->token,
        "unlock_progress" => $user->unlockProgress,
        "curr_progress"    => $user->currProgress,
        "coin"      =>  $user->coin,
        "diamond"   =>  $user->diamond,
    ]);
}


function onLoging($post_data)
{
    $account = $post_data["account"] ?? null;
    if (!$account) {
        echo json_encode(["status" => "error", "message" => "Missing parameter: account"]);
        exit;
    }

    thowUser($account);
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

function onLoadStageInfo($post_data)
{
    if (isset($post_data["stageid"]) && isset($post_data["phaseid"])) {

        $stageid = (int)$post_data["stageid"];
        $phaseid = (int)$post_data["phaseid"];
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

function onGameUniMessage($post_data)
{
    if (!isset($post_data["userid"]) || !isset($post_data["token"])) {
        echo json_encode([
            "status"  => "协议错误，未检测到userid或token",
        ]);
    }

    $userService = new UserService();
    $userid = $post_data["userid"];
    if ($userService->verifyToken($userid, $post_data["token"])) {
        $data = $userService->onGameUniMessage($userid, $post_data["reason"], $post_data["sPara"], $post_data["nPara"]);
        echo json_encode([
            "status"  => "ok",
            "data" => $data
        ]);
    } else {
        echo json_encode([
            "status"  => "登录信息已经过期，请重新登录",
        ]);
    }
}

function onReqSalt()
{
    $userService = new UserService();
    $salt = $userService->getSalt();
    if ($salt) {
        echo json_encode([
            "status"  => "ok",
            "data" => $salt
        ]);
    } else {
        echo json_encode([
            "status"  => "取盐失败，同ip段请求是否过于频繁",
        ]);
    }
}

function onTokenLogin($post_data)
{
    $token = $post_data["token"] ?? null;
    if ($token) {
        $userService = new UserService();
        $account = $userService->getAccountByAuthToken($token);
        if ($account) {
            thowUser($account);
        } else {
            echo json_encode([
                "status"  => "登录信息已经过期，请清空缓存后重新登录",
            ]);
        }
    }
}

function onRegister($post_data)
{
    $userService = new UserService();

    $token = $post_data["token"];
    $sign = $post_data["sign"];
    $saltId = $post_data["saltId"];
    $timestamp = $post_data["timestamp"];

    //验证token注册账号
    $account = $userService->RegisterUser($token, $sign, $saltId, $timestamp);

    echo json_encode([
        "status"  => "ok",
        "account" => $account
    ]);
}

switch ($reqType) {
    case "salt":
        onReqSalt();
        break;
    case "register":
        onRegister($post_data);
        break;
    case "token_login":
        onTokenLogin($post_data);
        break;
    case "login":
        onLoging($post_data);
        break;
    case "stageinfo":
        onLoadStageInfo($post_data);
        break;
    case "gameinfo":
        onGameInfo();
        break;
    case "uniMessage":
        onGameUniMessage($post_data);
        break;
}
