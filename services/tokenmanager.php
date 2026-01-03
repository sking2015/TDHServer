<?php
require 'vendor/autoload.php';

use Predis\Client;

class TokenManager
{
    private $redis;

    public function __construct()
    {
        // 连接 Redis
        $this->redis = new Client([
            'scheme' => 'tcp',
            'host'   => '127.0.0.1',
            'port'   => 6379,
        ]);
    }

    /**
     * 为玩家生成并保存 Token
     */
    public function generateToken($playerId)
    {
        // 1. 生成独一无二的 Token
        $token = bin2hex(random_bytes(32));

        // 2. 存储 Key（建议加前缀方便管理）
        $key = "player:token:$token";

        // 3. 存储玩家 ID，设置过期时间为 24 小时 (86400秒)
        $this->redis->setex($key, 86400, $playerId);

        return $token;
    }

    /**
     * 验证 Token 并获取玩家 ID
     */
    public function verifyToken($token)
    {
        if (empty($token)) return false;

        $key = "player:token:$token";
        $playerId = $this->redis->get($key);

        return $playerId ?: false; // 如果不存在返回 false
    }
}
