<?php
/**
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
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
     * @param bool $silenceMode When you use silence mode instead of an exception \Bex\Tools\ValueNotFoundException
     * (if value was be not found) is returned null.
     *
     * @return GroupFinder
     */
    public static function find($code, $silenceMode = false)
    {
        return new GroupFinder(
            ['code' => $code],
            $silenceMode
        );
    }

    /**
     * Gets Finder for users groups by group ID.
     *
     * @param int $id Group ID
     * @param bool $silenceMode When you use silence mode instead of an exception \Bex\Tools\ValueNotFoundException
     * (if value was be not found) is returned null.
     *
     * @return GroupFinder
     */
    public static function findById($id, $silenceMode = false)
    {
        return new GroupFinder(
            ['id' => $id],
            $silenceMode
        );
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
     * @param int $groupId Group ID by string ID
     *
     * @return bool
     */
    protected static function validateStringId($stringId, $groupId = null)
    {
        global $APPLICATION;

        if (is_null($stringId)) {
            // if code of group is not updated
            return true;
        }

        try {
            $stringId = trim($stringId);

            if (strlen($stringId) <= 0) {
                throw new \Exception('EMPTY_STRING_ID');
            }

            $rsSimilarGroup = GroupTable::query()
                ->setFilter(['STRING_ID' => $stringId, '!ID' => $groupId])
                ->setSelect(['ID'])
                ->exec();

            if ($rsSimilarGroup->getSelectedRowsCount() > 0) {
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