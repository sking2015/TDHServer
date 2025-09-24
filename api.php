<?php
header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . "/services/UserService.php";

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


