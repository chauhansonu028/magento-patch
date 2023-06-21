<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\Layout;

use Magento\Framework\Api\SimpleDataObjectConverter;

class GenerateNameByClassName implements BlockNameGeneratorInterface
{
    private $generatedNames = [];

    public function generate(string $data, string $prefix = ''): string
    {
        $nameParts = [];
        $className = trim($data, '\\');
        $classNameParts = explode('\\', $className);

        if (count($classNameParts) >= 3) {
            $nameParts = array_merge($nameParts, array_slice($classNameParts, 0, 2));
            $nameParts[] = end($classNameParts);
        } else {
            $nameParts[] = uniqid();
        }

        if ($prefix !== '') {
            array_unshift($nameParts, $prefix);
        }

        return $this->generateUniqName($nameParts);
    }

    private function generateUniqName(array $nameParts): string
    {
        $nameParts = array_map([SimpleDataObjectConverter::class, 'camelCaseToSnakeCase'], $nameParts);
        $name = join('.', $nameParts);

        if (array_search($name, $this->generatedNames) !== false) {
            $counter = 0;
            $defaultName = $name;

            do {
                $name = sprintf('%s.%d', $defaultName, ++$counter);
            } while (array_search($name, $this->generatedNames) !== false);
        }

        $this->generatedNames[] = $name;

        return $name;
    }
}
