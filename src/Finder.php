<?php
/**
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
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
 * part. Each shard of the cache is stored separately.
 *
 * The basic Finder methods:
 * * `getItems()` — returns all data from the database for the requested shard cache.
 * * `getValue()` — samples the specific value in accordance by filter.
 * * `getFromCache()` — returns the data from cache (if possible) or calls a method `getItems()`.
 *
 * Any instance Finder you can configure:
 * * `setCacheTime()` — time life of the cache.
 * * `setCacheDir()` — directory for cache.
 *
 * The logic reset of the cache by tag will be defined in a concrete implementation of Finder.
 *
 * @author Nik Samokhvalov <nik@samokhvalov.info>
 */
abstract class Finder
{
    protected static $cacheTime = 8600000;
    protected static $cacheDir;
    protected $silenceMode;

    /**
     * Constructor with filter parameters for Finder.
     *
     * Should not be called directly! Use *Tools classes (IblockTools, GroupTools, etc.)
     *
     * @param array $filter Filter paramters.
     * @param bool $silenceMode When you use silence mode instead of an exception \Bex\Tools\ValueNotFoundException
     * (if value was be not found) is returned null.
     */
    public function __construct(array $filter, $silenceMode = false)
    {
        $this->silenceMode = (bool)$silenceMode;
    }

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
     * @param int $time Seconds
     */
    public static function setCacheTime($time)
    {
        static::$cacheTime = intval($time);
    }

    /**
     * Gets cache time.
     *
     * @return int
     */
    public static function getCacheTime()
    {
        return static::$cacheTime;
    }

    /**
     * Gets cache directory.
     *
     * @return string
     */
    public static function getCacheDir()
    {
        return static::$cacheDir;
    }

    /**
     * Sets cache directory.
     *
     * @param string $directory Directory for cache. Relative to the base directory of the cache storage.
     */
    public static function setCacheDir($directory)
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
     *
     * @throws ValueNotFoundException Value was not found.
     * @throws \InvalidArgumentException Invalid type on filter.
     */
    protected function getFromCache($filter = [], $shard = null)
    {
        $filter = $this->prepareFilter($filter);

        $cache = Cache::createInstance();

        if ($cache->initCache(static::getCacheTime(), null, static::getCacheDir() . '/' . $shard)) {
            $items = $cache->getVars();
        } else {
            $cache->startDataCache();
            Application::getInstance()->getTaggedCache()->startTagCache(static::getCacheDir() . '/' . $shard);

            $items = $this->getItems($shard);

            if (!empty($items)) {
                Application::getInstance()->getTaggedCache()->endTagCache();

                $cache->endDataCache($items);
            } else {
                $cache->abortDataCache();
            }
        }

        try {
            return $this->getValue($items, $filter, $shard);
        } catch (ValueNotFoundException $e) {
            if ($this->silenceMode) {
                return null;
            }

            throw $e;
        }
    }

    /**
     * Registration of the tag cache.
     *
     * @param string $tag
     */
    protected function registerCacheTag($tag)
    {
        Application::getInstance()->getTaggedCache()->registerTag($tag);
    }

    /**
     * Deletes all cache by tag.
     *
     * @param string $tag
     */
    protected static function deleteCacheByTag($tag)
    {
        $cache = Application::getInstance()->getTaggedCache();
        $cache->clearByTag($tag);
    }
}