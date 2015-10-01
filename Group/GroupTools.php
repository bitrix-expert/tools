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
    public static function find($code)
    {
        return new GroupFinder([
            'code' => $code,
        ]);
    }

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