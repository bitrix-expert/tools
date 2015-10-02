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

abstract class Finder
{
    protected static $cacheTime = 8600000;
    protected static $cacheDir;

    /**
     * Prepare filter.
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

    abstract protected function getValue(array $cache, array $filter, $shard);

    abstract protected function getItems($shard);

    /**
     * @todo Протестировать скорость чтения на больших объёмах кеша
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