<?php
/**
 * @link https://github.com/bitrix-expert/tools
 * @copyright Copyright © 2015 Nik Samokhvalov
 * @license MIT
 */

namespace Bex\Tools;

use Bitrix\Main;
use Bitrix\Main\Application;
use Bitrix\Main\Data\Cache;

/**
 * Finder performs a fast search for data in standard structures of the Bitrix CMS with subsequent caching to speed 
 * up repeated queries.
 *
 * Finder uses the term "shard of the cache". The shards are used to separate the large volume cache for the logical 
 * part. Each shard of the cache is stored in a separate file. By default used shard "common".
 *
 * The basic Finder methods:
 * * getItems() — returns all data from the database for the requested shard cache.
 * * getValue() — samples the specific value in accordance by filter.
 * * getFromCache() — returns the data from cache (if possible) or calls a method getItems().
 *
 * Any instance Finder you can configure:
 * * setCacheTime() — time life of the cache.
 * * setCacheDir() — directory for cache.
 * 
 * The logic reset of the cache by tag will be defined in a concrete implementation of Finder.
 * 
 * @author Nik Samokhvalov <nik@samokhvalov.info>
 */
abstract class Finder
{
    protected static $cacheTime = 8600000;
    protected static $cacheDir;

    /**
     * Constructor with filter parameters for Finder.
     * 
     * Should not be called directly! Use *Tools classes (IblockTools, GroupTools, etc.)
     * 
     * @param array $filter
     */
    abstract public function __construct(array $filter);

    /**
     * Prepare parameters of the filter from API request.
     *
     * @param array $filter The array filter, which is passed through the Finder constructor
     *
     * @return array
     */
    protected function prepareFilter(array $filter)
    {
        return $filter;
    }

    /**
     * Sets cache time for Finder.
     *
     * @param integer $time Seconds
     */
    public function setCacheTime($time)
    {
        static::$cacheTime = intval($time);
    }

    /**
     * Gets cache time.
     *
     * @return int
     */
    public function getCacheTime()
    {
        return static::$cacheTime;
    }

    /**
     * Gets cache directory.
     *
     * @return string
     */
    public function getCacheDir()
    {
        return static::$cacheDir;
    }

    /**
     * Sets cache directory.
     *
     * @param string $directory
     */
    public function setCacheDir($directory)
    {
        static::$cacheDir = trim(htmlspecialchars($directory));
    }

    /**
     * Returns value by filter from cache.
     * 
     * @param array $cache All items for shard of the cache.
     * @param array $filter Parameters for filter.
     * @param string $shard Shard of the cache.
     *
     * @return mixed
     */
    abstract protected function getValue(array $cache, array $filter, $shard);

    /**
     * Returns all items for shard of the cache.
     * 
     * @param string $shard Shard of the cache.
     *
     * @return mixed
     */
    abstract protected function getItems($shard);

    /**
     * Returns items from cache. If cache expired will be executed request to DB (method $this->getItems()).
     * 
     * @param array $filter Parameters for filter.
     * @param string $shard Shard of the cache.
     *
     * @return mixed
     */
    protected function getFromCache($filter = [], $shard = 'common')
    {
        $filter = $this->prepareFilter($filter);

        $cache = Cache::createInstance();

        if ($cache->initCache($this->getCacheTime(), $shard, $this->getCacheDir()))
        {
            $items = $cache->getVars();
        }
        else
        {
            $cache->startDataCache();
            Application::getInstance()->getTaggedCache()->startTagCache($this->getCacheDir());

            $items = $this->getItems($shard);

            if (!empty($items))
            {
                Application::getInstance()->getTaggedCache()->endTagCache();

                $cache->endDataCache($items);
            }
            else
            {
                $cache->abortDataCache();
            }
        }

        return $this->getValue($items, $filter, $shard);
    }
}