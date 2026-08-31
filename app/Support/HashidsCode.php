<?php

namespace App\Support;

final class HashidsCode
{
    private const ALPHABET = 'abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    private const MIN_LENGTH = 8;

    public static function encode(int $id, string $namespace = 'default'): string
    {
        if ($id < 1) {
            throw new \InvalidArgumentException('Hashids code can only encode positive integer IDs.');
        }

        $alphabet = self::alphabet($namespace);
        $base = strlen($alphabet);
        $encoded = '';
        $value = $id;

        while ($value > 0) {
            $encoded = $alphabet[$value % $base].$encoded;
            $value = intdiv($value, $base);
        }

        $bodyLength = max(strlen($encoded), self::MIN_LENGTH - 2);
        $body = str_pad($encoded, $bodyLength, $alphabet[0], STR_PAD_LEFT);

        return $body.self::checksum($id, $alphabet, $namespace);
    }

    public static function decode(string $code, string $namespace = 'default'): ?int
    {
        if (strlen($code) < 3) {
            return null;
        }

        $alphabet = self::alphabet($namespace);
        $checksum = substr($code, -2);
        $body = substr($code, 0, -2);
        $indexes = array_flip(str_split($alphabet));
        $base = strlen($alphabet);
        $id = 0;

        foreach (str_split($body) as $character) {
            if (! isset($indexes[$character])) {
                return null;
            }

            $id = ($id * $base) + $indexes[$character];
        }

        if ($id < 1) {
            return null;
        }

        if (! hash_equals(self::checksum($id, $alphabet, $namespace), $checksum)) {
            return null;
        }

        return $id;
    }

    private static function alphabet(string $namespace): string
    {
        $characters = str_split(self::ALPHABET);
        $salt = self::salt().'|'.$namespace;
        $hash = hash('sha256', $salt, true);
        $position = 0;

        for ($i = count($characters) - 1; $i > 0; $i--) {
            if ($position >= strlen($hash)) {
                $hash = hash('sha256', $hash.$salt, true);
                $position = 0;
            }

            $j = ord($hash[$position]) % ($i + 1);
            [$characters[$i], $characters[$j]] = [$characters[$j], $characters[$i]];
            $position++;
        }

        return implode('', $characters);
    }

    private static function checksum(int $id, string $alphabet, string $namespace): string
    {
        $base = strlen($alphabet);
        $hash = hexdec(substr(hash('sha256', self::salt().'|'.$namespace.'|'.$id), 0, 8));

        return $alphabet[$hash % $base].$alphabet[intdiv($hash, $base) % $base];
    }

    private static function salt(): string
    {
        return (string) (config('app.key') ?: config('app.name', 'Laravel'));
    }
}
