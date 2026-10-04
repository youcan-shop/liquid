<?php

/*
 * This file is part of the Liquid package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Liquid
 */

namespace YouCan\Liquid\Cache;

use YouCan\Liquid\Cache;
use YouCan\Liquid\LiquidException;

/**
 * Implements cache stored in Apc.
 *
 * @codeCoverageIgnore
 */
class Apc extends Cache
{
    /**
     * Constructor.
     *
     * It checks the availability of apccache.
     *
     * @param array $options
     *
     * @throws LiquidException if APC cache extension is not loaded or is disabled.
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);

        if (!function_exists('apcu_fetch') || !apcu_enabled()) {
            throw new LiquidException(get_class($this) . ' requires the PHP apcu extension to be loaded and enabled.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function read($key, $unserialize = true)
    {
        return apcu_fetch($this->prefix . $key);
    }

    /**
     * {@inheritdoc}
     */
    public function exists($key)
    {
        return apcu_exists($this->prefix . $key);
    }

    /**
     * {@inheritdoc}
     */
    public function write($key, $value, $serialize = true)
    {
        return apcu_store($this->prefix . $key, $value, $this->expire);
    }

    /**
     * {@inheritdoc}
     */
    public function flush($expiredOnly = false)
    {
        return apcu_delete(new \APCUIterator('/^' . preg_quote($this->prefix, '/') . '/', APC_ITER_KEY)) !== false;
    }
}
