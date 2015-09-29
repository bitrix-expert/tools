<?php
/**
 * @link https://github.com/bitrix-expert/tools
 * @copyright Copyright © 2015 Nik Samokhvalov
 * @license MIT
 */

namespace Bex\Tools\Group;

use Bex\Tools\Iblock\BexTools;
use Bitrix\Main;

/**
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
}