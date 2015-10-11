<?php
/**
 * @link https://github.com/bitrix-expert/tools
 * @copyright Copyright © 2015 Nik Samokhvalov
 * @license MIT
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

        try {
            if (empty($code))
            {
                throw new \Exception('EMPTY_CODE');
            }

            $rsSimilarIblock = IblockTable::getList([
                'filter' => [
                    'IBLOCK_TYPE_ID' => $type,
                    'CODE' => $code,
                    '!ID' => $iblockId
                ],
                'select' => [
                    'ID'
                ]
            ]);

            if ($rsSimilarIblock->getSelectedRowsCount() > 0)
            {
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
