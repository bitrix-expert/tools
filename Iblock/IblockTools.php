<?php
/**
 * @link https://github.com/bitrix-expert/tools
 * @copyright Copyright © 2015 Nik Samokhvalov
 * @license MIT
 */

namespace Bex\Tools\Iblock;

use Bitrix\Main;

/**
 * Helper for working with infoblocks. All requests will be cached.
 *
 * @author Nik Samokhvalov <nik@samokhvalov.info>
 */
class IblockTools extends BexTools
{
    public static function find($type, $code)
    {
        return new IblockFinder([
            'iblockType' => $type,
            'iblockCode' => $code,
        ]);
    }

    public static function findById($id)
    {
        return new IblockFinder([
            'iblockId' => $id
        ]);
    }
}
