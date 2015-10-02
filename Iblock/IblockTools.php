<?php
/**
 * @link https://github.com/bitrix-expert/tools
 * @copyright Copyright © 2015 Nik Samokhvalov
 * @license MIT
 */

namespace Bex\Tools\Iblock;

use Bitrix\Main;
use Bex\Tools\BexTools;

/**
 * Helper for working with infoblocks. All requests will be cached.
 *
 * @author Nik Samokhvalov <nik@samokhvalov.info>
 *
 * @todo Перехватчики событий, запрещающие создавать ИБ с одинаковыми символьными кодами
 */
class IblockTools extends BexTools
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
}
