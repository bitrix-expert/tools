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

    protected function prepareFilter(array $filter)
    {
        return $filter;
    }

    public static function setCacheTime($time)
    {
        static::$cacheTime = intval($time);
    }

    public static function getCacheTime()
    {
        return static::$cacheTime;
    }

    public static function getCacheDir()
    {
        return static::$cacheDir;
    }

    public static function setCacheDir($directory)
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

        $cache = Cache::createInstance();

        if ($cache->initCache($this->getCacheTime(), false, $this->getCacheDir()))
        {
            $items = $cache->getVars();
        }
        else
        {
            $cache->startDataCache();
            Application::getInstance()->getTaggedCache()->startTagCache($this->getCacheDir());
            
            $items = $this->getItems();

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

        return $this->getValue($items, $filter);
    }
}