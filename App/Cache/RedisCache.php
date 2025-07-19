<?php

namespace App\Cache;

use Redis;
use RedisException;
use RuntimeException;

class RedisCache extends AbstractCache
{
    private Redis $redis;
    private array $config;

    public function __construct($host, $port = 6379, $password = '', $database = '')
    {
        $config = [
            'host' => $host,
            'port' => $port,
            'password' => $password,
            'database' => $database,
        ];
        $this->config = $config;
        $this->connect();
    }

    private function connect(): void
    {
        try {
            $this->redis = new Redis();
            $this->redis->connect(
                $this->config['host'] ?? '127.0.0.1',
                $this->config['port'] ?? 6379,
                $this->config['timeout'] ?? 0
            );

            if (isset($this->config['password'])) {
                $this->redis->auth($this->config['password']);
            }

            if (isset($this->config['database'])) {
                $this->redis->select($this->config['database']);
            }
        } catch (RedisException $e) {
            throw new RuntimeException("Redis connection failed: " . $e->getMessage());
        }
    }

    // 字符串操作
    public function set(string $key, $value, int $ttl = 0): bool
    {
        if ($ttl > 0) {
            return $this->redis->setex($key, $ttl, $value);
        }
        return $this->redis->set($key, $value);
    }

    public function get(string $key, $default = null)
    {
        $value = $this->redis->get($key);
        return $value !== false ? $value : $default;
    }

    public function delete(string $key): bool
    {
        return $this->redis->del($key) > 0;
    }

    public function exists(string $key): bool
    {
        return $this->redis->exists($key);
    }

    // 列表操作

    public function lPush(string $key, $value): int
    {
        return $this->redis->lPush($key, $value);
    }

    public function rPush(string $key, $value): int
    {
        return $this->redis->rPush($key, $value);
    }

    public function lPop(string $key): bool
    {
        return $this->redis->lPop($key);
    }

    public function rPop(string $key): bool
    {
        return $this->redis->rPop($key);
    }

    public function lRange(string $key, int $start, int $stop): array
    {
        return $this->redis->lRange($key, $start, $stop);
    }

    // 集合操作
    public function sAdd(string $key, $value): int
    {
        return $this->redis->sAdd($key, $value);
    }

    public function sMembers(string $key): array
    {
        return $this->redis->sMembers($key);
    }

    public function sIsMember(string $key, $value): bool
    {
        return $this->redis->sIsMember($key, $value);
    }

    public function sRem(string $key, $value): int
    {
        return $this->redis->sRem($key, $value);
    }

    // 有序集合操作
    public function zAdd(string $key, float $score, $value): int
    {
        return $this->redis->zAdd($key, $score, $value);
    }

    public function zRange(string $key, int $start, int $stop, bool $withScores = false): array
    {
        return $this->redis->zRange($key, $start, $stop, $withScores);
    }

    public function zRem(string $key, $value): int
    {
        return $this->redis->zRem($key, $value);
    }
}