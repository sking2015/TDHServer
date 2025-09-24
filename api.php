<?php
header("Access-Control-Allow-Origin: http://localhost:7456");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");

header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . "/services/userservice.php";

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
    "userid"  => $user->userid
]);
