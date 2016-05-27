<?php
/**
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Bex\Tools\Iblock;

use Bitrix\Iblock\IblockTable;
use Bitrix\Main\Localization\Loc;

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
     * @param bool $silenceMode When you use silence mode instead of an exception \Bex\Tools\ValueNotFoundException
     * (if value was be not found) is returned null.
     *
     * @return IblockFinder
     */
    public static function find($type, $code, $silenceMode = false)
    {
        return new IblockFinder(
            ['type' => $type, 'code' => $code],
            $silenceMode
        );
    }

    /**
     * Gets Finder for iblock by iblock ID.
     *
     * @param int $id Iblock ID
     * @param bool $silenceMode When you use silence mode instead of an exception \Bex\Tools\ValueNotFoundException
     * (if value was be not found) is returned null.
     *
     * @return IblockFinder
     */
    public static function findById($id, $silenceMode = false)
    {
        return new IblockFinder(
            ['id' => $id],
            $silenceMode
        );
    }

    public static function onBeforeIBlockAdd(&$fields)
    {
        return static::validateCode($fields['IBLOCK_TYPE_ID'], $fields['CODE']);
    }

    public static function onBeforeIBlockUpdate(&$fields)
    {
        return static::validateCode($fields['IBLOCK_TYPE_ID'], $fields['CODE'], $fields['ID']);
    }

    /**
     * Validation code of the info block. If code not valid (empty string or code alredy used) will be throw
     * Bitrix exception.
     *
     * @param string $type
     * @param string $code
     * @param null $iblockId
     *
     * @return bool
     */
    protected static function validateCode($type, $code, $iblockId = null)
    {
        global $APPLICATION;

        if (is_null($code)) {
            // if code of info block is not updated
            return true;
        }

        try {
            $type = trim($type);
            $code = trim($code);

            if (strlen($code) <= 0) {
                throw new \Exception('EMPTY_CODE');
            }

            $rsSimilarIblock = IblockTable::query()
                ->setFilter(['IBLOCK_TYPE_ID' => $type, 'CODE' => $code, '!ID' => $iblockId])
                ->setSelect(['ID'])
                ->exec();

            if ($rsSimilarIblock->getSelectedRowsCount() > 0) {
                throw new \Exception('CODE_ALREDY_USED');
            }

            return true;
        } catch (\Exception $e) {
            Loc::loadMessages(__FILE__);

            $APPLICATION->ThrowException(Loc::getMessage('BEX_TOOLS_IBLOCK_' . $e->getMessage()));
            return false;
        }
    }
}
