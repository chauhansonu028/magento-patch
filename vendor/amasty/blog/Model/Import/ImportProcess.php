<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\Import;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class ImportProcess
 */
class ImportProcess
{
    /**
     * @var array
     */
    private $imports;

    public function __construct($imports = [])
    {
        $this->imports = $imports;
    }
    
    public function processImport()
    {
        /** @var \Amasty\Blog\Model\Import\AbstractImport $import */
        foreach ($this->imports as $import) {
            $import->processImport();
        }
    }
}
