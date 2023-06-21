<?php

declare(strict_types=1);

namespace Amasty\EmailUnsubscribe\Model;

use Amasty\EmailUnsubscribe\Model\ResourceModel\Salt;

class UrlHash
{
    public const MIN_SALT_LENGTH = 10;
    public const MAX_SALT_LENGTH = 13;
    public const SALT = 'xhjn';

    /**
     * @var Salt
     */
    private $salt;

    public function __construct(Salt $salt)
    {
        $this->salt = $salt;
    }

    public function getHash(string $type, string $email): string
    {
        return hash('sha256', $type . $email . self::SALT . $this->getSalt());
    }

    public function validate(string $type, string $email, string $hash): bool
    {
        return $hash === $this->getHash($type, $email);
    }

    private function getSalt(): string
    {
        $salt = $this->salt->getSalt();
        if (!$salt) {
            $this->salt->insert($this->generateRandomString());
            $salt = $this->salt->getSalt();
        }

        return $salt;
    }

    private function generateRandomString(): string
    {
        $length = rand(self::MIN_SALT_LENGTH, self::MAX_SALT_LENGTH);
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }
}
