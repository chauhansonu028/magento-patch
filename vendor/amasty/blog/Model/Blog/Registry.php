<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\Blog;

class Registry
{
    const CURRENT_POST = 'current_post';

    const CURRENT_AUTHOR = 'current_author';

    const CURRENT_CATEGORY = 'current_category';

    const CURRENT_TAG = 'current_tag';

    const INDEX_PAGE = 'index_page';

    const SEARCH_PAGE = 'search_page';

    /**
     * @var array
     */
    private $registry = [];

    /**
     * @param string $key
     * @return mixed|null
     */
    public function registry(string $key)
    {
        if (isset($this->registry[$key])) {
            return $this->registry[$key];
        }
        return null;
    }

    /**
     * @param string $key
     * @param $value
     * @param bool $graceful
     */
    public function register(string $key, $value, bool $graceful = false): void
    {
        if (isset($this->registry[$key])) {
            if ($graceful) {
                $this->unregister($key);
            } else {
                throw new \RuntimeException('Registry key "' . $key . '" already exists');
            }
        }

        $this->registry[$key] = $value;
    }

    public function unregister(string $key): void
    {
        if (isset($this->registry[$key])) {
            if (is_object($this->registry[$key])
                && method_exists($this->registry[$key], '__destruct')
                && is_callable([$this->registry[$key], '__destruct'])
            ) {
                $this->registry[$key]->__destruct();
            }
            unset($this->registry[$key]);
        }
    }

    /**
     * Destruct registry items
     */
    public function __destruct()
    {
        $keys = array_keys($this->registry);
        array_walk($keys, [$this, 'unregister']);
    }
}
