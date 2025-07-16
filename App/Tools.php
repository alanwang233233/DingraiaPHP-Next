<?php

namespace App;
use InvalidArgumentException;
use Random\RandomException;

trait Tools
{
    /**
     * 生成指定位数的随机数字字符串
     * @param int $length 数字长度，默认6位
     * @param bool $strict 是否严格模式，严格模式下第一位不会生成0
     * @return int 随机数字字符串
     * @throws RandomException
     */
    public function generateRandomInt(int $length = 5, bool $strict = true): int
    {
        if ($length <= 0) {
            throw new InvalidArgumentException('长度必须是正整数');
        }

        if ($strict && $length === 1) {
            throw new InvalidArgumentException('严格模式下长度不能为1');
        }

        if ($strict) {
            // 严格模式：第一位1-9，后续0-9
            $firstDigit = random_int(1, 9);
            $remainingLength = $length - 1;

            if ($remainingLength > 0) {
                $remainingDigits = random_int(0, pow(10, $remainingLength) - 1);
                return $firstDigit . str_pad((string)$remainingDigits, $remainingLength, '0', STR_PAD_LEFT);
            }

            return (string)$firstDigit;
        } else {
            // 普通模式：直接生成指定位数的随机数
            $min = $length === 1 ? 0 : pow(10, $length - 1);
            $max = pow(10, $length) - 1;
            return random_int($min, $max);
        }
    }

    public function parseHeaders(): array
    {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (str_starts_with($name, 'HTTP_')) {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}    