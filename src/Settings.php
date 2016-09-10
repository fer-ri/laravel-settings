<?php

namespace Ferri\LaravelSettings;

use Illuminate\Contracts\Cache\Repository as Cache;

class Settings
{
    /**
     * Cache repository.
     *
     * @var null|\Illuminate\Contracts\Cache\Repository
     */
    protected $cache;

    /**
     * Enable cache.
     *
     * @var bool
     */
    protected $cacheEnabled = false;

    /**
     * Any extra columns that should be added to the query.
     *
     * @var array
     */
    protected $extraColumns = [];

    /**
     * New instance of Settings.
     *
     * @param \Ferri\LaravelSettings\Repository $repository
     * @param string                            $table
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Set cache store.
     *
     * @param \Illuminate\Contracts\Cache\Repository $cache
     */
    public function setCache(Cache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Return cache store instance.
     *
     * @return \Illuminate\Contracts\Cache\Repository|null
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * Enable cache.
     */
    public function enableCache()
    {
        $this->cacheEnabled = true;
    }

    /**
     * Disable cache.
     */
    public function disableCache()
    {
        $this->cacheEnabled = false;
    }

    /**
     * Set extra columns to be added to the rows.
     *
     * @param array $columns
     *
     * @return $this
     */
    public function setExtraColumns(array $columns)
    {
        $this->extraColumns = $columns;

        $this->repository->setExtraColumns($this->extraColumns);

        return $this;
    }

    /**
     * Set extra columns to be added to the rows.
     *
     * @param array $columns
     *
     * @return $this
     */
    public function getExtraColumns()
    {
        return $this->extraColumns;
    }

    /**
     * Get a specific key from the settings data.
     *
     * @param string|array $key
     * @param mixed        $default Optional default value
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if ($this->isCacheEnabled()) {
            $value = $this->cache->rememberForever($this->getCacheKey($key), function () use ($key) {
                return $this->repository->get($key);
            });
        } else {
            $value = $this->repository->get($key);
        }

        $this->setExtraColumns([]);

        return ! is_null($value) ? unserialize($value) : $default;
    }

    /**
     * Determine if a key exists in the settings data.
     *
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
    {
        $has = $this->repository->has($key);

        $this->setExtraColumns([]);

        return $has;
    }

    /**
     * Set a specific key to a value in the settings data.
     *
     * @param string|array $key   Key string or associative array of key => value
     * @param mixed        $value Optional only if the first argument is an array
     */
    public function set($key, $value = null)
    {
        $setting = $this->repository->set($key, serialize($value));

        if ($this->isCacheEnabled()) {
            $this->cache->forget($this->getCacheKey($key));
        }

        $this->setExtraColumns([]);
    }

    /**
     * Unset a key in the settings data.
     *
     * @param string $key
     */
    public function forget($key)
    {
        $this->repository->forget($key);

        if ($this->isCacheEnabled()) {
            $this->cache->forget($this->getCacheKey($key));
        }

        $this->setExtraColumns([]);
    }

    /**
     * Remove all settings data and truncate table.
     */
    public function flush()
    {
        $settings = $this->repository->all();

        foreach ($settings as $setting) {
            if ($this->isCacheEnabled()) {
                $this->cache->forget($this->getCacheKey($setting->key));
            }
        }

        $this->repository->flush();
    }

    /**
     * Check if cache should be enabled or not.
     *
     * @return bool
     */
    protected function isCacheEnabled()
    {
        return $this->cacheEnabled && ! is_null($this->cache) ? true : false;
    }

    /**
     * Generate cache key for a given key.
     *
     * @param string $key
     *
     * @return string
     */
    protected function getCacheKey($key)
    {
        return md5($key.serialize($this->extraColumns));
    }
}
