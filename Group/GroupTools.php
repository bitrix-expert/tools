<?php
/**
 * @link https://github.com/bitrix-expert/tools
 * @copyright Copyright © 2015 Nik Samokhvalov
 * @license MIT
 */

namespace Bex\Tools\Group;

use Bitrix\Main;
use Bex\Tools\BexTools;

/**
 * Tools for working with users groups.
 *
 * @author Nik Samokhvalov <nik@samokhvalov.info>
 */
class GroupTools extends BexTools
{
    /**
     * Gets Finder for users groups by group code.
     *
     * @param string $code Group code
     *
     * @return GroupFinder
     */
    public static function find($code)
    {
        return new GroupFinder([
            'code' => $code,
        ]);
    }

    /**
     * Gets Finder for users groups by group ID.
     *
     * @param integer $id Group ID
     *
     * @return GroupFinder
     */
    public static function findById($id)
    {
        return new GroupFinder([
            'id' => $id
        ]);
    }

    public static function onBeforeGroupAdd(&$fields)
    {
        // @todo Запрещать создавать группы с одинаковыми символьными кодами
    }

    public static function onBeforeGroupUpdate($id, &$fields)
    {
        // @todo Запрещать обновлять группы с одинаковыми символьными кодами
    }
}