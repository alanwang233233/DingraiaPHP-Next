<?php /** @noinspection PhpUnused */

namespace App\Cache;

use Exception;

abstract class AbstractCache
{
    // 字符串操作
    abstract public function set(string $key, $value, int $ttl = 0): bool;

    abstract public function get(string $key);

    abstract public function delete(string $key): bool;

    abstract public function exists(string $key): bool;

    // 列表操作
    abstract public function lPush(string $key, $value): int;

    abstract public function lPop(string $key): bool;

    abstract public function rPop(string $key): bool;

    public function lGet(string $key): array
    {
        return $this->lRange($key, 0, -1);
    }

    abstract public function lRange(string $key, int $start, int $stop): array;

    /**
     * 新列表
     * @throws Exception
     */
    public function newList($key, array $data): void
    {
        foreach ($data as $item) {
            if (is_array($item)) {
                // Test
                // $this->redis->rPush($key, json_encode($item));
                throw new Exception('Redis List error');
            } else {
                $this->rPush($key, $item);
            }
        }
    }

    abstract public function rPush(string $key, $value): int;

    // 集合操作

    abstract public function sAdd(string $key, $value): int;

    abstract public function sMembers(string $key): array;

    abstract public function sIsMember(string $key, $value): bool;

    abstract public function sRem(string $key, $value): int;

    // 有序集合操作
    abstract public function zAdd(string $key, float $score, $value): int;

    abstract public function zRange(string $key, int $start, int $stop, bool $withScores = false): array;

    abstract public function zRem(string $key, $value): int;
}