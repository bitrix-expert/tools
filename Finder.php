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
    abstract protected function getValue(array $cache, array $filter);

    abstract protected function getItems();

    /**
     * @todo Протестировать скорость чтения на больших объёмах кеша
     */
    protected function getFromCache($filter = [])
    {
        $cache = Cache::createInstance();
        $cacheId = false;
        $cacheDir = static::CACHE_DIR;

        if ($cache->initCache(static::CACHE_TIME, $cacheId, $cacheDir))
        {
            $items = $cache->getVars();
        }
        else
        {
            $cache->startDataCache();
            Application::getInstance()->getTaggedCache()->startTagCache($cacheDir);
            
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