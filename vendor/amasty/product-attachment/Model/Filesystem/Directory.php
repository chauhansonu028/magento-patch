<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Model\Filesystem;

class Directory
{
    public const AMFILE_DIRECTORY = 'amasty' . DIRECTORY_SEPARATOR . 'amfile' . DIRECTORY_SEPARATOR;

    public const ATTACHMENT = 'attachment';

    public const ICON = 'icon';

    public const CATEGORY_ICON = 'category';

    public const TMP_DIRECTORY = 'tmp';

    public const IMPORT = 'import';

    public const IMPORT_FTP = 'ftp';

    public const DIRECTORY_CODES = [
        self::ATTACHMENT => self::AMFILE_DIRECTORY . 'attach',
        self::ICON => self::AMFILE_DIRECTORY . 'icon',
        self::CATEGORY_ICON => self::AMFILE_DIRECTORY . 'category',
        self::TMP_DIRECTORY => self::AMFILE_DIRECTORY . 'tmp',
        self::IMPORT => self::AMFILE_DIRECTORY . 'import',
        self::IMPORT_FTP => self::AMFILE_DIRECTORY . 'import' . DIRECTORY_SEPARATOR . 'ftp'
    ];
}
