<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\Preview;

use Magento\Framework\Encryption\Encryptor as MagentoEncryptor;
use Magento\Framework\Serialize\Serializer\Json;

class Encryptor
{
    public const POST_ID = 'post_id';
    public const DATE_KEY = 'date';

    /**
     * @var MagentoEncryptor
     */
    private $encryptor;

    /**
     * @var Json
     */
    private $json;

    public function __construct(
        MagentoEncryptor $encryptor,
        Json $json
    ) {
        $this->encryptor = $encryptor;
        $this->json = $json;
    }

    public function encryptParams(int $postId, int $date): string
    {
        $params = [
            self::POST_ID => $postId,
            self::DATE_KEY => $date,
        ];

        return $this->encryptor->encrypt($this->json->serialize($params));
    }

    public function decryptParams(string $encryptedString): array
    {
        return $this->json->unserialize($this->encryptor->decrypt($encryptedString));
    }
}
