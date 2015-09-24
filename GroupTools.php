<?php
/**
 * @link https://github.com/bitrix-expert/tools
 * @license MIT
 */

namespace Bex\Tools;

use Bitrix\Main;
use Bitrix\Main\Application;

/**
 * Helper for working with user groups. All requests will be cached.
 */
class GroupTools extends BexTools
{
    /**
     * Cache time
     */
    const CACHE_TIME = '8600000';
    /**
     * Directory of the cache
     */
    const CACHE_DIR = 'bex_tools/groups';

    /**
     * Get ID of the user group by code
     *
     * @param string $groupCode Code of the user group
     * @param bool $withoutException Throw exception in will not found result, default false
     * @return bool|int
     * @throws \Bitrix\Main\ArgumentNullException
     */
    public static function getId($groupCode, $withoutException = false)
    {
        if (!$groupCode)
        {
            throw new Main\ArgumentNullException('Code of user group');
        }

        return static::getData(
            [
                'object' => 'group',
                'groupCode' => $groupCode
            ],
            $withoutException
        );
    }

    /**
     * Get STRING_ID of the user group by ID
     *
     * @param string $groupId id of the user group
     * @param bool $withoutException Throw exception in will not found result, default false
     * @return bool|string
     * @throws \Bitrix\Main\ArgumentNullException
     */
    public static function getCode($groupId, $withoutException = false)
    {
        if (!$groupId)
        {
            throw new Main\ArgumentNullException('Id of user group');
        }

        return static::getData(
            [
                'object' => 'groupCode',
                'groupId' => $groupId
            ],
            $withoutException
        );
    }

    private static function getData($filter = [], $withoutException = false)
    {
        $datas = [];
        $return = false;

        $cache = Main\Data\Cache::createInstance();
        $cacheId = false;
        $cacheDir = static::CACHE_DIR;

        if ($cache->initCache(static::CACHE_TIME, $cacheId, $cacheDir))
        {
            $datas = $cache->getVars();
        }
        else
        {
            $cache->startDataCache();
            Application::getInstance()->getTaggedCache()->startTagCache($cacheDir);

            $rsGroups = \CGroup::GetList();

            while ($arGroup = $rsGroups->Fetch())
            {
                if ($arGroup['STRING_ID'])
                {
                    $datas['GROUPS'][$arGroup['STRING_ID']] = $arGroup['ID'];
                    $datas['CODES'][$arGroup['ID']] = $arGroup['STRING_ID'];
                }
            }

            if (!empty($datas))
            {
                Application::getInstance()->getTaggedCache()->registerTag('group_tools_cache');
                Application::getInstance()->getTaggedCache()->endTagCache();

                $cache->endDataCache($datas);
            }
            else
            {
                $cache->abortDataCache();
            }
        }

        switch ($filter['object'])
        {
            case 'group':
                $return = (int) $datas['GROUPS'][$filter['groupCode']];
                break;
            case 'groupCode':
                $return = $datas['CODES'][$filter['groupId']];
                break;
        }

        if (!$return && !$withoutException)
        {
            throw new \Exception('Error getting ID');
        }

        return $return;
    }
}