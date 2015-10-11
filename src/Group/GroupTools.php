<?php
/**
 * @link https://github.com/bitrix-expert/tools
 * @copyright Copyright © 2015 Nik Samokhvalov
 * @license MIT
 */

namespace Bex\Tools\Group;

use Bitrix\Main\GroupTable;
use Bitrix\Main\Localization\Loc;

/**
 * Tools for working with users groups.
 *
 * @author Nik Samokhvalov <nik@samokhvalov.info>
 */
class GroupTools
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
        return static::validateStringId($fields['STRING_ID']);
    }

    public static function onBeforeGroupUpdate($id, &$fields)
    {
        return static::validateStringId($fields['STRING_ID'], $id);
    }

    /**
     * Validation string ID of the user group. If string ID not valid (empty string or string ID alredy used) 
     * will be throw Bitrix exception.
     * 
     * @param string $stringId
     * @param integer $groupId Group ID by string ID
     *
     * @return bool
     */
    protected static function validateStringId($stringId, $groupId = null)
    {
        global $APPLICATION;

        try {
            $stringId = trim($stringId);
            
            if (strlen($stringId) <= 0)
            {
                throw new \Exception('EMPTY_STRING_ID');
            }

            $rsSimilarGroup = GroupTable::getList([
                'filter' => [
                    'STRING_ID' => $stringId,
                    '!ID' => $groupId
                ],
                'select' => [
                    'ID'
                ]
            ]);

            if ($rsSimilarGroup->getSelectedRowsCount() > 0)
            {
                throw new \Exception('STRING_ID_ALREDY_USED');
            }
            
            return true;
        } catch (\Exception $e) {
            Loc::loadMessages(__FILE__);

            $APPLICATION->ThrowException(Loc::getMessage('BEX_TOOLS_GROUP_' . $e->getMessage()));
            return false;
        }
    }
}