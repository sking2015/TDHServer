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

    public function delSalt($saltId)
    {
        $this->redis->del("auth_salt:" . $saltId);
    }

    public function getSalt($saltId)
    {
        return $this->redis->get("auth_salt:" . $saltId);
    }

    /**
     * 生成唯一盐值
     */
    public function genSalt()
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        // 简单的 IP 限流：每个 IP 每分钟只能取 20 次盐
        $limitKey = "limit:salt:" . $ip;
        $count = $this->redis->incr($limitKey);
        if ($count == 1) $this->redis->expire($limitKey, 60);
        if ($count > 20) {
            return false;
        }

        // 生成随机盐值和 ID
        $saltId = bin2hex(random_bytes(8)); // 随机标识符
        $salt = bin2hex(random_bytes(16));  // 真正的盐值

        // 存储到 Redis，设置 300 秒有效期
        $this->redis->setex("auth_salt:" . $saltId, 300, $salt);

        return [
            "saltId" => $saltId,
            "salt" => $salt
        ];
    }
}
