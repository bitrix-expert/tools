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
    protected static $cacheTime;
    protected static $cacheDir;
    private $itemsCache;

    protected function prepareFilter(array $filter)
    {
        return $filter;
    }

    public function setCacheTime($time)
    {
        static::$cacheTime = intval($time);
    }

    public function getCacheTime()
    {
        return static::$cacheTime;
    }

    public function getCacheDir()
    {
        return static::$cacheDir;
    }

    public function setCacheDir($directory)
    {
        static::$cacheDir = trim(htmlspecialchars($directory));
    }

    abstract protected function getValue(array $cache, array $filter);

    abstract protected function getItems();

    /**
     * @todo Протестировать скорость чтения на больших объёмах кеша
     */
    protected function getFromCache($filter = [])
    {
        $filter = $this->prepareFilter($filter);

        if (is_array($this->itemsCache) && !empty($this->itemsCache))
        {
            return $this->getValue($this->itemsCache, $filter);
        }

        $cache = Cache::createInstance();

        if ($cache->initCache($this->getCacheTime(), false, $this->getCacheDir()))
        {
            $this->itemsCache = $cache->getVars();
        }
        else
        {
            $cache->startDataCache();
            Application::getInstance()->getTaggedCache()->startTagCache($this->getCacheDir());

            $this->itemsCache = $this->getItems();

            if (!empty($this->itemsCache))
            {
                Application::getInstance()->getTaggedCache()->endTagCache();

                $cache->endDataCache($this->itemsCache);
            }
            else
            {
                $cache->abortDataCache();
            }
        }

        return $this->getValue($this->itemsCache, $filter);
    }
}