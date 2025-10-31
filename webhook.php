<?php
// === 配置区 ===
$secret = 'sking2025fortdhserver#$'; // 与 GitHub 设置的 secret 一致
$project_dir = '/home/TDHGame/TDHServer';
$log_file = '/home/TDHGame/deploy.log'; // 可选：记录执行日志

// === 校验签名 ===
$hubSignature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
$payload = file_get_contents('php://input');
$hash = 'sha256=' . hash_hmac('sha256', $payload, $secret);
if (!hash_equals($hubSignature, $hash)) {
    http_response_code(403);
    echo "Invalid signature";
    exit;
}

// === 切换目录并执行 git pull ===
chdir($project_dir);
exec('git pull 2>&1', $output, $return_var);

// === 写入日志 ===
$log = date('Y-m-d H:i:s') . " git pull\n" . implode("\n", $output) . "\n\n";
file_put_contents($log_file, $log, FILE_APPEND);

// === 返回执行结果 ===
http_response_code($return_var === 0 ? 200 : 500);
echo implode("\n", $output);
