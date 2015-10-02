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
 * @author Nik Samokhvalov <nik@samokhvalov.info>
 */
class GroupTools extends BexTools
{
    protected static $cacheTag = 'group_tools_cache';

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
}