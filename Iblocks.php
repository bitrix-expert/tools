<?php
/**
 * @link https://github.com/bitrix-expert/tools
 * @copyright Copyright © 2015 Nik Samokhvalov
 * @license MIT
 */

namespace Bex\Tools;

use Bitrix\Main;
use Bitrix\Main\Application;

/**
 * Helper for working with infoblocks. All requests will be cached.
 *
 * @author Nik Samokhvalov <nik@samokhvalov.info>
 */
class Iblocks
{
    /**
     * Cache time
     */
    const CACHE_TIME = '8600000';
    /**
     * Directory of the cache
     */
    const CACHE_DIR = 'bex_tools/iblocks';

    /**
     * Get ID of the infoblock by code
     *
     * @param string $iblockType Type of the infoblock
     * @param string $iblockCode Code of the infoblock
     * @param bool $withoutException Throw exception in will not found result, default false
     * @return bool|int
     * @throws \Bitrix\Main\ArgumentNullException
     */
    public static function getId($iblockType, $iblockCode, $withoutException = false)
    {
        if (!$iblockType)
        {
            throw new Main\ArgumentNullException('Type of info-block');
        }
        elseif (!$iblockCode)
        {
            throw new Main\ArgumentNullException('Code of info-block');
        }

        return static::getData(
            [
                'object' => 'iblock',
                'iblockType' => $iblockType,
                'iblockCode' => $iblockCode
            ],
            $withoutException
        );
    }

    /**
     * Get ID of the list property value by XML_ID
     *
     * @param string $iblockType Type of the infoblock
     * @param string $iblockCode Code of the infoblock
     * @param string $propCode Code of the property
     * @param string $valueXmlId XML_ID of the value
     * @return int|bool
     * @throws \Bitrix\Main\ArgumentNullException
     */
    public static function getPropEnumId($iblockType, $iblockCode, $propCode, $valueXmlId)
    {
        if (!$iblockType)
        {
            throw new Main\ArgumentNullException('Type of info-block');
        }
        elseif (!$iblockCode)
        {
            throw new Main\ArgumentNullException('Code of info-block');
        }
        elseif (!$propCode)
        {
            throw new Main\ArgumentNullException('Code of property');
        }
        elseif (!$valueXmlId)
        {
            throw new Main\ArgumentNullException('XML_ID of value');
        }

        return static::getData([
            'object' => 'propEnum',
            'iblockType' => $iblockType,
            'iblockCode' => $iblockCode,
            'propCode' => $propCode,
            'valueXmlId' => $valueXmlId
        ]);
    }

    /**
     * Get type of the infoblock
     *
     * @param int $iblockId ID of the infoblock
     * @return string|bool
     * @throws \Bitrix\Main\ArgumentNullException
     */
    public static function getIblockType($iblockId)
    {
        $iblockId = intval($iblockId);

        if ($iblockId <= 0)
        {
            throw new Main\ArgumentNullException('ID of info-block');
        }

        return static::getData([
            'object' => 'iblockType',
            'iblockId' => $iblockId
        ]);
    }

    /**
     * Get ID of the property
     *
     * @param string $iblockType Type of the infoblock
     * @param string $iblockCode Code of the infoblock
     * @param string $propCode Code of the property
     * @return string|bool
     * @throws \Bitrix\Main\ArgumentNullException
     */
    public static function getPropId($iblockType, $iblockCode, $propCode)
    {
        if (!$iblockType)
        {
            throw new Main\ArgumentNullException('Type of info-block');
        }

        if (!$iblockCode)
        {
            throw new Main\ArgumentNullException('Code of info-block');
        }

        return static::getData([
            'object' => 'propId',
            'iblockType' => $iblockType,
            'iblockCode' => $iblockCode,
            'propCode' => $propCode,
        ]);
    }

    private static function getData($filter = [], $withoutException = false)
    {
        $datas = [];
        $iblockIds = [];
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

            $rsIblocks = \CIBlock::GetList([], []);

            while ($arIblock = $rsIblocks->Fetch())
            {
                if ($arIblock['CODE'])
                {
                    $datas['IBLOCKS'][$arIblock['IBLOCK_TYPE_ID']][$arIblock['CODE']] = $arIblock['ID'];

                    $iblockIds[] = $arIblock['ID'];
                }

                $datas['IBLOCK_TYPES'][$arIblock['ID']] = $arIblock['IBLOCK_TYPE_ID'];
            }

            $rsProps = \CIBlockProperty::GetList();

            while ($arProp = $rsProps->Fetch())
            {
                $datas['PROPS_ID'][$arProp['IBLOCK_ID']][$arProp['CODE']] = $arProp['ID'];
            }

            $rsPropsEnum = \CIBlockPropertyEnum::GetList();

            while ($arPropEnum = $rsPropsEnum->Fetch())
            {
                if ($arPropEnum['PROPERTY_CODE'])
                {
                    $datas['PROPS_ENUM'][$arPropEnum['PROPERTY_ID']][$arPropEnum['XML_ID']] = $arPropEnum['ID'];
                }
            }

            if (!empty($datas))
            {
                foreach ($iblockIds as $id)
                {
                    Application::getInstance()->getTaggedCache()->registerTag('iblock_id_'.$id);
                }

                Application::getInstance()->getTaggedCache()->registerTag('iblock_id_new');
                Application::getInstance()->getTaggedCache()->endTagCache();

                $cache->endDataCache($datas);
            }
            else
            {
                $cache->abortDataCache();
            }
        }

        $iblockId = $datas['IBLOCKS'][$filter['iblockType']][$filter['iblockCode']];

        switch ($filter['object'])
        {
            case 'iblock':
                $return = (int) $iblockId;
            break;

            case 'iblockType':
                $return = (string) $datas['IBLOCK_TYPES'][$filter['iblockId']];
            break;

            case 'propId':
                $return = (int) $datas['PROPS_ID'][$iblockId][$filter['propCode']];
            break;

            case 'propEnum':
                $propId = $datas['PROPS_ID'][$iblockId][$filter['propCode']];

                $return = (int) $datas['PROPS_ENUM'][$propId][$filter['valueXmlId']];
            break;
        }

        if (!$return && !$withoutException)
        {
            throw new \Exception('Error getting ID');
        }

        return $return;
    }
}
