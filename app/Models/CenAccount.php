<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class CenAccount extends Authenticatable
{
    protected $table = 'cen.accounts';
    protected $primaryKey = 'acc_id';
    public $timestamps = false;
    protected $rememberTokenName = null;

    protected $hidden = ['acc_pass'];

    private static function normalizeStoredPassword(string $stored): string
    {
        $stored = trim($stored);
        $stored = str_replace(["\r", "\n", ' '], '', $stored);

        return $stored;
    }

    private static function sbox(): array
    {
        static $sbox = null;
        if ($sbox !== null) {
            return $sbox;
        }

        $sbox = [
            0x63, 0x7c, 0x77, 0x7b, 0xf2, 0x6b, 0x6f, 0xc5, 0x30, 0x01, 0x67, 0x2b, 0xfe, 0xd7, 0xab, 0x76,
            0xca, 0x82, 0xc9, 0x7d, 0xfa, 0x59, 0x47, 0xf0, 0xad, 0xd4, 0xa2, 0xaf, 0x9c, 0xa4, 0x72, 0xc0,
            0xb7, 0xfd, 0x93, 0x26, 0x36, 0x3f, 0xf7, 0xcc, 0x34, 0xa5, 0xe5, 0xf1, 0x71, 0xd8, 0x31, 0x15,
            0x04, 0xc7, 0x23, 0xc3, 0x18, 0x96, 0x05, 0x9a, 0x07, 0x12, 0x80, 0xe2, 0xeb, 0x27, 0xb2, 0x75,
            0x09, 0x83, 0x2c, 0x1a, 0x1b, 0x6e, 0x5a, 0xa0, 0x52, 0x3b, 0xd6, 0xb3, 0x29, 0xe3, 0x2f, 0x84,
            0x53, 0xd1, 0x00, 0xed, 0x20, 0xfc, 0xb1, 0x5b, 0x6a, 0xcb, 0xbe, 0x39, 0x4a, 0x4c, 0x58, 0xcf,
            0xd0, 0xef, 0xaa, 0xfb, 0x43, 0x4d, 0x33, 0x85, 0x45, 0xf9, 0x02, 0x7f, 0x50, 0x3c, 0x9f, 0xa8,
            0x51, 0xa3, 0x40, 0x8f, 0x92, 0x9d, 0x38, 0xf5, 0xbc, 0xb6, 0xda, 0x21, 0x10, 0xff, 0xf3, 0xd2,
            0xcd, 0x0c, 0x13, 0xec, 0x5f, 0x97, 0x44, 0x17, 0xc4, 0xa7, 0x7e, 0x3d, 0x64, 0x5d, 0x19, 0x73,
            0x60, 0x81, 0x4f, 0xdc, 0x22, 0x2a, 0x90, 0x88, 0x46, 0xee, 0xb8, 0x14, 0xde, 0x5e, 0x0b, 0xdb,
            0xe0, 0x32, 0x3a, 0x0a, 0x49, 0x06, 0x24, 0x5c, 0xc2, 0xd3, 0xac, 0x62, 0x91, 0x95, 0xe4, 0x79,
            0xe7, 0xc8, 0x37, 0x6d, 0x8d, 0xd5, 0x4e, 0xa9, 0x6c, 0x56, 0xf4, 0xea, 0x65, 0x7a, 0xae, 0x08,
            0xba, 0x78, 0x25, 0x2e, 0x1c, 0xa6, 0xb4, 0xc6, 0xe8, 0xdd, 0x74, 0x1f, 0x4b, 0xbd, 0x8b, 0x8a,
            0x70, 0x3e, 0xb5, 0x66, 0x48, 0x03, 0xf6, 0x0e, 0x61, 0x35, 0x57, 0xb9, 0x86, 0xc1, 0x1d, 0x9e,
            0xe1, 0xf8, 0x98, 0x11, 0x69, 0xd9, 0x8e, 0x94, 0x9b, 0x1e, 0x87, 0xe9, 0xce, 0x55, 0x28, 0xdf,
            0x8c, 0xa1, 0x89, 0x0d, 0xbf, 0xe6, 0x42, 0x68, 0x41, 0x99, 0x2d, 0x0f, 0xb0, 0x54, 0xbb, 0x16,
        ];

        return $sbox;
    }

    private static function sboxInv(): array
    {
        static $inv = null;
        if ($inv !== null) {
            return $inv;
        }

        $inv = array_fill(0, 256, 0);
        $sbox = self::sbox();
        for ($i = 0; $i < 256; $i++) {
            $inv[$sbox[$i]] = $i;
        }

        return $inv;
    }

    private static function rcon(): array
    {
        static $rcon = null;
        if ($rcon !== null) {
            return $rcon;
        }

        $rcon = [0];
        $c = 1;
        for ($i = 1; $i < 255; $i++) {
            $rcon[$i] = $c & 0xff;
            $c = self::gfMul($c, 2);
        }

        return $rcon;
    }

    private static function gfMul(int $a, int $b): int
    {
        $p = 0;
        $a &= 0xff;
        $b &= 0xff;
        for ($i = 0; $i < 8; $i++) {
            if ($b & 1) {
                $p ^= $a;
            }
            $hi = $a & 0x80;
            $a = ($a << 1) & 0xff;
            if ($hi) {
                $a ^= 0x1b;
            }
            $b >>= 1;
        }

        return $p & 0xff;
    }

    private static function keyScheduleCore(array $row, int $a): array
    {
        $sbox = self::sbox();
        $rcon = self::rcon();

        $result = [
            $sbox[$row[1] & 0xff],
            $sbox[$row[2] & 0xff],
            $sbox[$row[3] & 0xff],
            $sbox[$row[0] & 0xff],
        ];
        $result[0] = ($result[0] ^ ($rcon[$a] & 0xff)) & 0xff;

        return $result;
    }

    private static function expandKey(string $password): array
    {
        $key = array_fill(0, 32, 0);
        $len = strlen($password);
        for ($i = 0; $i < $len && $i < 32; $i++) {
            $key[$i] = ord($password[$i]) & 0xff;
        }
        for ($i = $len; $i < 32; $i++) {
            $key[$i] = 0;
        }

        $sbox = self::sbox();

        $result = array_fill(0, 240, 0);
        for ($i = 0; $i < 32; $i++) {
            $result[$i] = $key[$i];
        }

        $rConIter = 1;
        for ($i = 32; $i < 240; $i += 4) {
            $temp = [$result[$i - 4], $result[$i - 3], $result[$i - 2], $result[$i - 1]];

            if ($i % 32 === 0) {
                $temp = self::keyScheduleCore($temp, $rConIter);
                $rConIter++;
            }

            if ($i % 32 === 16) {
                $temp[0] = $sbox[$temp[0] & 0xff];
                $temp[1] = $sbox[$temp[1] & 0xff];
                $temp[2] = $sbox[$temp[2] & 0xff];
                $temp[3] = $sbox[$temp[3] & 0xff];
            }

            $result[$i] = ($result[$i - 32] ^ $temp[0]) & 0xff;
            $result[$i + 1] = ($result[$i - 31] ^ $temp[1]) & 0xff;
            $result[$i + 2] = ($result[$i - 30] ^ $temp[2]) & 0xff;
            $result[$i + 3] = ($result[$i - 29] ^ $temp[3]) & 0xff;
        }

        return $result;
    }

    private static function aesRound(string $message, bool $isEncode, string $password): string
    {
        $sbox = self::sbox();
        $sboxinv = self::sboxInv();

        $nonce = array_fill(0, 16, 0);
        $expandedKey = self::expandKey($password);

        $out = '';
        $j = 0;
        $len = strlen($message);

        while (true) {
            $offset = $j * 16;
            $chunk = substr($message, $offset, 16);
            if ($chunk === false) {
                $chunk = '';
            }
            if (strlen($chunk) < 16) {
                $chunk .= str_repeat(chr(0), 16 - strlen($chunk));
            }

            $block = array_fill(0, 16, 0);
            for ($i = 0; $i < 16; $i++) {
                $idx = (($i % 4) * 4) + intdiv($i, 4);
                $block[$i] = ord($chunk[$idx]) & 0xff;
            }

            $isDone = ($offset + 16) >= $len;
            $j++;

            if ($isEncode) {
                for ($i = 0; $i < 16; $i++) {
                    $k = (($i % 4) * 4) + intdiv($i, 4);
                    $block[$i] = ($block[$i] ^ $nonce[$i] ^ ($expandedKey[$k] ?? 0)) & 0xff;
                }

                for ($x = 1; $x <= 13; $x++) {
                    $block[0] = $sbox[$block[0]];
                    $block[1] = $sbox[$block[1]];
                    $block[2] = $sbox[$block[2]];
                    $block[3] = $sbox[$block[3]];

                    $intTemp = $sbox[$block[4]];
                    $block[4] = $sbox[$block[5]];
                    $block[5] = $sbox[$block[6]];
                    $block[6] = $sbox[$block[7]];
                    $block[7] = $intTemp;

                    $intTemp = $sbox[$block[8]];
                    $block[8] = $sbox[$block[10]];
                    $block[10] = $intTemp;
                    $intTemp = $sbox[$block[9]];
                    $block[9] = $sbox[$block[11]];
                    $block[11] = $intTemp;

                    $intTemp = $sbox[$block[12]];
                    $block[12] = $sbox[$block[15]];
                    $block[15] = $sbox[$block[14]];
                    $block[14] = $sbox[$block[13]];
                    $block[13] = $intTemp;

                    $R = $x * 16;
                    for ($i = 0; $i <= 3; $i++) {
                        $t0 = $block[$i];
                        $t1 = $block[$i + 4];
                        $t2 = $block[$i + 8];
                        $t3 = $block[$i + 12];

                        $block[$i] = (self::gfMul($t0, 2) ^ $t3 ^ $t2 ^ self::gfMul($t1, 3) ^ ($expandedKey[$R + $i * 4] ?? 0)) & 0xff;
                        $block[$i + 4] = (self::gfMul($t1, 2) ^ $t0 ^ $t3 ^ self::gfMul($t2, 3) ^ ($expandedKey[$R + $i * 4 + 1] ?? 0)) & 0xff;
                        $block[$i + 8] = (self::gfMul($t2, 2) ^ $t1 ^ $t0 ^ self::gfMul($t3, 3) ^ ($expandedKey[$R + $i * 4 + 2] ?? 0)) & 0xff;
                        $block[$i + 12] = (self::gfMul($t3, 2) ^ $t2 ^ $t1 ^ self::gfMul($t0, 3) ^ ($expandedKey[$R + $i * 4 + 3] ?? 0)) & 0xff;
                    }
                }

                $block[0] = ($sbox[$block[0]] ^ ($expandedKey[224] ?? 0)) & 0xff;
                $block[1] = ($sbox[$block[1]] ^ ($expandedKey[228] ?? 0)) & 0xff;
                $block[2] = ($sbox[$block[2]] ^ ($expandedKey[232] ?? 0)) & 0xff;
                $block[3] = ($sbox[$block[3]] ^ ($expandedKey[236] ?? 0)) & 0xff;

                $intTemp = ($sbox[$block[4]] ^ ($expandedKey[237] ?? 0)) & 0xff;
                $block[4] = ($sbox[$block[5]] ^ ($expandedKey[225] ?? 0)) & 0xff;
                $block[5] = ($sbox[$block[6]] ^ ($expandedKey[229] ?? 0)) & 0xff;
                $block[6] = ($sbox[$block[7]] ^ ($expandedKey[233] ?? 0)) & 0xff;
                $block[7] = $intTemp;

                $intTemp = ($sbox[$block[8]] ^ ($expandedKey[234] ?? 0)) & 0xff;
                $block[8] = ($sbox[$block[10]] ^ ($expandedKey[226] ?? 0)) & 0xff;
                $block[10] = $intTemp;
                $intTemp = ($sbox[$block[9]] ^ ($expandedKey[238] ?? 0)) & 0xff;
                $block[9] = ($sbox[$block[11]] ^ ($expandedKey[230] ?? 0)) & 0xff;
                $block[11] = $intTemp;

                $intTemp = ($sbox[$block[12]] ^ ($expandedKey[231] ?? 0)) & 0xff;
                $block[12] = ($sbox[$block[15]] ^ ($expandedKey[227] ?? 0)) & 0xff;
                $block[15] = ($sbox[$block[14]] ^ ($expandedKey[239] ?? 0)) & 0xff;
                $block[14] = ($sbox[$block[13]] ^ ($expandedKey[235] ?? 0)) & 0xff;
                $block[13] = $intTemp;

                for ($i = 0; $i < 16; $i++) {
                    $nonce[$i] = $block[$i];
                }
            } else {
                $priorCipher = $block;

                $block[0] = $sboxinv[($block[0] ^ ($expandedKey[224] ?? 0)) & 0xff];
                $block[1] = $sboxinv[($block[1] ^ ($expandedKey[228] ?? 0)) & 0xff];
                $block[2] = $sboxinv[($block[2] ^ ($expandedKey[232] ?? 0)) & 0xff];
                $block[3] = $sboxinv[($block[3] ^ ($expandedKey[236] ?? 0)) & 0xff];

                $intTemp = $sboxinv[($block[4] ^ ($expandedKey[225] ?? 0)) & 0xff];
                $block[4] = $sboxinv[($block[7] ^ ($expandedKey[237] ?? 0)) & 0xff];
                $block[7] = $sboxinv[($block[6] ^ ($expandedKey[233] ?? 0)) & 0xff];
                $block[6] = $sboxinv[($block[5] ^ ($expandedKey[229] ?? 0)) & 0xff];
                $block[5] = $intTemp;

                $intTemp = $sboxinv[($block[8] ^ ($expandedKey[226] ?? 0)) & 0xff];
                $block[8] = $sboxinv[($block[10] ^ ($expandedKey[234] ?? 0)) & 0xff];
                $block[10] = $intTemp;
                $intTemp = $sboxinv[($block[9] ^ ($expandedKey[230] ?? 0)) & 0xff];
                $block[9] = $sboxinv[($block[11] ^ ($expandedKey[238] ?? 0)) & 0xff];
                $block[11] = $intTemp;

                $intTemp = $sboxinv[($block[12] ^ ($expandedKey[227] ?? 0)) & 0xff];
                $block[12] = $sboxinv[($block[13] ^ ($expandedKey[231] ?? 0)) & 0xff];
                $block[13] = $sboxinv[($block[14] ^ ($expandedKey[235] ?? 0)) & 0xff];
                $block[14] = $sboxinv[($block[15] ^ ($expandedKey[239] ?? 0)) & 0xff];
                $block[15] = $intTemp;

                for ($x = 13; $x >= 1; $x--) {
                    $R = $x * 16;

                    for ($i = 0; $i <= 3; $i++) {
                        $t0 = ($block[$i] ^ ($expandedKey[$R + $i * 4] ?? 0)) & 0xff;
                        $t1 = ($block[$i + 4] ^ ($expandedKey[$R + $i * 4 + 1] ?? 0)) & 0xff;
                        $t2 = ($block[$i + 8] ^ ($expandedKey[$R + $i * 4 + 2] ?? 0)) & 0xff;
                        $t3 = ($block[$i + 12] ^ ($expandedKey[$R + $i * 4 + 3] ?? 0)) & 0xff;

                        $block[$i] = (self::gfMul($t0, 14) ^ self::gfMul($t3, 9) ^ self::gfMul($t2, 13) ^ self::gfMul($t1, 11)) & 0xff;
                        $block[$i + 4] = (self::gfMul($t1, 14) ^ self::gfMul($t0, 9) ^ self::gfMul($t3, 13) ^ self::gfMul($t2, 11)) & 0xff;
                        $block[$i + 8] = (self::gfMul($t2, 14) ^ self::gfMul($t1, 9) ^ self::gfMul($t0, 13) ^ self::gfMul($t3, 11)) & 0xff;
                        $block[$i + 12] = (self::gfMul($t3, 14) ^ self::gfMul($t2, 9) ^ self::gfMul($t1, 13) ^ self::gfMul($t0, 11)) & 0xff;
                    }

                    $block[0] = $sboxinv[$block[0]];
                    $block[1] = $sboxinv[$block[1]];
                    $block[2] = $sboxinv[$block[2]];
                    $block[3] = $sboxinv[$block[3]];

                    $intTemp = $sboxinv[$block[4]];
                    $block[4] = $sboxinv[$block[7]];
                    $block[7] = $sboxinv[$block[6]];
                    $block[6] = $sboxinv[$block[5]];
                    $block[5] = $intTemp;

                    $intTemp = $sboxinv[$block[8]];
                    $block[8] = $sboxinv[$block[10]];
                    $block[10] = $intTemp;
                    $intTemp = $sboxinv[$block[9]];
                    $block[9] = $sboxinv[$block[11]];
                    $block[11] = $intTemp;

                    $intTemp = $sboxinv[$block[12]];
                    $block[12] = $sboxinv[$block[13]];
                    $block[13] = $sboxinv[$block[14]];
                    $block[14] = $sboxinv[$block[15]];
                    $block[15] = $intTemp;
                }

                for ($i = 0; $i < 16; $i++) {
                    $k = (($i % 4) * 4) + intdiv($i, 4);
                    $block[$i] = ($block[$i] ^ ($expandedKey[$k] ?? 0) ^ $nonce[$i]) & 0xff;
                    $nonce[$i] = $priorCipher[$i];
                }
            }

            for ($i = 0; $i < 16; $i++) {
                $idx = (($i % 4) * 4) + intdiv($i, 4);
                $out .= chr($block[$idx] & 0xff);
            }

            if ($isDone) {
                break;
            }
        }

        return $out;
    }

    private static function encryptAES(string $message, string $password, int $rounds): string
    {
        $data = $message;
        for ($i = 0; $i < $rounds; $i++) {
            $data = self::aesRound($data, true, $password);
            if ($data === '') {
                return '';
            }
        }

        return $data;
    }

    private static function decryptAES(string $cipher, string $password, int $rounds): string
    {
        $data = $cipher;
        for ($i = 0; $i < $rounds; $i++) {
            $data = self::aesRound($data, false, $password);
            if ($data === '') {
                return '';
            }
        }

        return rtrim($data, chr(0));
    }

    private static function storeEncryptAES(string $message, string $password, int $rounds): string
    {
        $plain = sha1($message).':'.$message;
        $cipher = self::encryptAES($plain, $password, $rounds);
        if ($cipher === '') {
            return '';
        }

        return base64_encode($cipher);
    }

    private static function retrieveDecryptAES(string $stored, string $password, int $rounds): ?string
    {
        $stored = self::normalizeStoredPassword((string) $stored);
        if ($stored === '') {
            return null;
        }

        $raw = base64_decode($stored, true);
        if ($raw === false || $raw === '') {
            return null;
        }

        $dMessage = self::decryptAES($raw, $password, $rounds);
        $pos = strpos($dMessage, ':');
        if ($pos === false || $pos < 1) {
            return null;
        }

        $hash = substr($dMessage, 0, $pos);
        $msg = substr($dMessage, $pos + 1);
        if ($msg === '') {
            return null;
        }

        if (! hash_equals(strtolower($hash), strtolower(sha1($msg)))) {
            return null;
        }

        return $msg;
    }

    private static function verifyLegacySaltSha(string $plain, string $stored): bool
    {
        $raw = base64_decode($stored, true);
        if ($raw === false || strlen($raw) !== 48) {
            return false;
        }

        $salt = substr($raw, 0, 16);
        $hash = substr($raw, 16, 32);

        $calc1 = hash('sha256', $salt.$plain, true);
        if (hash_equals($hash, $calc1)) {
            return true;
        }

        $calc2 = hash('sha256', $plain.$salt, true);
        if (hash_equals($hash, $calc2)) {
            return true;
        }

        $calc3 = hash_hmac('sha256', $plain, $salt, true);
        if (hash_equals($hash, $calc3)) {
            return true;
        }

        return false;
    }

    public static function hashPassword(string $username, string $plain, int $rounds = 5): string
    {
        return self::storeEncryptAES($username, $plain, $rounds);
    }

    public static function verifyPassword(string $username, string $plain, string $stored, int $rounds = 5): bool
    {
        $stored = self::normalizeStoredPassword((string) $stored);
        if ($stored === '') {
            return false;
        }

        if (str_starts_with($stored, '$2y$') || str_starts_with($stored, '$argon2')) {
            return password_verify($plain, $stored);
        }

        $roundCandidates = array_unique([$rounds, 5]);
        $nameCandidates = [
            (string) $username,
            strtolower((string) $username),
            strtoupper((string) $username),
            trim((string) $username),
            strtolower(trim((string) $username)),
        ];

        foreach ($roundCandidates as $r) {
            $decrypted = self::retrieveDecryptAES($stored, $plain, (int) $r);
            if (! is_string($decrypted)) {
                continue;
            }
            foreach ($nameCandidates as $cand) {
                if ($cand !== '' && hash_equals($cand, $decrypted)) {
                    return true;
                }
            }
        }

        return self::verifyLegacySaltSha($plain, $stored);
    }

    private function normalizedArea(): string
    {
        return strtolower(trim((string) ($this->acc_untarea ?? '')));
    }

    private function normalizedAuth(): string
    {
        return strtolower(trim((string) ($this->acc_auth ?? '')));
    }

    public function getAuthPassword()
    {
        return $this->acc_pass;
    }

    public function getAuthPasswordName()
    {
        return 'acc_pass';
    }

    public function getAuthIdentifierName()
    {
        return 'acc_id';
    }

    public function isSORD()
    {
        $area = $this->normalizedArea();

        return in_array($area, ['rdwprj', 'prjrdw', 'rdw'], true);
    }

    public function isDivision()
    {
        return $this->normalizedArea() === 'prj';
    }

    public function isApprover(): bool
    {
        return in_array($this->normalizedAuth(), ['approver', 'editor'], true);
    }

    public function isViewer(): bool
    {
        return $this->normalizedAuth() === 'viewer';
    }

    public function canAccessArea(string $area): bool
    {
        $userArea = $this->normalizedArea();
        $areas = [$userArea];

        if (in_array($userArea, ['rdwprj', 'prjrdw'], true)) {
            $areas = ['rdw', 'prj', 'rdwprj', 'prjrdw'];
        }

        return in_array(strtolower(trim($area)), $areas, true);
    }

    public function canSeeRecord(int $unitId): bool
    {
        if ($this->acc_lowers == 0) {
            return $unitId >= $this->acc_lowerm && $unitId <= $this->acc_upperm;
        }

        return $unitId >= $this->acc_lowers && $unitId <= $this->acc_uppers;
    }

    public function unitRange(): array
    {
        return ['lower' => $this->acc_lowers, 'upper' => $this->acc_uppers];
    }

    public function moduleRange(): array
    {
        return ['lower' => $this->acc_lowerm, 'upper' => $this->acc_upperm];
    }
}
