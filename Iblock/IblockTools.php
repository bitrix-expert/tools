<?php
/**
 * @link https://github.com/bitrix-expert/tools
 * @copyright Copyright © 2015 Nik Samokhvalov
 * @license MIT
 */

namespace Bex\Tools\Iblock;

use Bitrix\Main;

/**
 * Tools for working with infoblocks.
 *
 * @author Nik Samokhvalov <nik@samokhvalov.info>
 */
class IblockTools
{
    /**
     * Gets Finder for iblock by iblock type and code.
     *
     * @param string $type Iblock type
     * @param string $code Iblock code
     *
     * @return IblockFinder
     */
    public static function find($type, $code)
    {
        return new IblockFinder([
            'type' => $type,
            'code' => $code,
        ]);
    }

    /**
     * Gets Finder for iblock by iblock ID.
     *
     * @param integer $id Iblock ID
     *
     * @return IblockFinder
     */
    public static function findById($id)
    {
        return new IblockFinder([
            'id' => $id
        ]);
    }

    public static function onBeforeIBlockAdd(&$fields)
    {
        // @todo Запрещать создавать ИБ с одинаковыми символьными кодами
    }

    public static function onBeforeIBlockUpdate(&$fields)
    {
        // @todo Запрещать создавать ИБ с одинаковыми символьными кодами
    }
}
