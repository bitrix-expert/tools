<?php
/**
 * @link https://github.com/bitrix-expert/tools
 * @copyright Copyright © 2015 Nik Samokhvalov
 * @license MIT
 */

namespace Bex\Tools\Iblock;

use Bex\Tools\Finder;
use Bitrix\Main;
use Bitrix\Main\Application;

/**
 * @author Nik Samokhvalov <nik@samokhvalov.info>
 */
class IblockFinder extends Finder
{
    protected static $cacheTime = 8600000;
    protected static $cacheDir = 'bex_tools/iblocks';
    protected $id;
    protected $type;
    protected $code;

    public function __construct(array $filter)
    {
        if (!Main\Loader::includeModule('iblock')) {
            throw new Main\LoaderException('Failed include module "iblock"');
        }

        $filter = $this->prepareFilter($filter);

        if (isset($filter['iblockType'])) {
            $this->type = $filter['iblockType'];
        }

        if (isset($filter['iblockCode'])) {
            $this->code = $filter['iblockCode'];
        }

        if (isset($filter['iblockId'])) {
            $this->id = $filter['iblockId'];
        }

        if (!isset($this->id)) {
            if (!isset($this->type)) {
                throw new Main\ArgumentNullException('iblock type');
            } elseif (!isset($this->code)) {
                throw new Main\ArgumentNullException('iblock code');
            }

            $this->id = $this->getFromCache([
                'type' => 'iblockId'
            ]);
        }
    }

    public function id()
    {
        return $this->getFromCache([
            'type' => 'iblockId'
        ]);
    }

    public function type()
    {
        return $this->getFromCache([
            'type' => 'iblockType'
        ]);
    }

    public function propId($code)
    {
        return $this->getFromCache([
            'type' => 'propId',
            'propCode' => $code
        ]);
    }

    public function propEnumId($code, $valueXmlId)
    {
        return $this->getFromCache([
            'type' => 'propEnumId',
            'propCode' => $code,
            'valueXmlId' => $valueXmlId
        ]);
    }

    protected function prepareFilter(array $filter)
    {
        foreach ($filter as $code => &$value) {
            if ($code === 'iblockId' || $code === 'propId') {
                intval($value);

                if ($value <= 0) {
                    throw new Main\ArgumentNullException($code);
                }
            } else {
                trim(htmlspecialchars($value));

                if (strlen($value) <= 0) {
                    throw new Main\ArgumentNullException($code);
                }
            }
        }

        return $filter;
    }

    protected function getValue(array $cache, array $filter)
    {
        switch ($filter['type'])
        {
            case 'iblockId':
                $value = (int) $cache['IBLOCKS'][$this->type][$this->code];

                if ($value <= 0) {
                    throw new \Exception();
                }

                return $value;
                break;

            case 'iblockType':
                $value = (string) $cache['IBLOCK_TYPES'][$this->id];

                if (strlen($value) <= 0) {
                    throw new \Exception();
                }

                return $value;
                break;

            case 'propId':
                $value = (int) $cache['PROPS_ID'][$this->id][$filter['propCode']];

                if ($value <= 0) {
                    throw new \Exception();
                }

                return $value;
                break;

            case 'propEnum':
                $propId = $cache['PROPS_ID'][$this->id][$filter['propCode']];

                $value = (int) $cache['PROPS_ENUM'][$propId][$filter['valueXmlId']];

                if ($value <= 0) {
                    throw new \Exception();
                }

                return $value;
                break;
        }
    }

    protected function getItems()
    {
        $items = [];
        $iblockIds = [];

        $rsIblocks = \CIBlock::GetList([], ['CHECK_PERMISSIONS' => 'N']);

        while ($arIblock = $rsIblocks->Fetch())
        {
            if ($arIblock['CODE'])
            {
                $items['IBLOCKS'][$arIblock['IBLOCK_TYPE_ID']][$arIblock['CODE']] = $arIblock['ID'];

                $iblockIds[] = $arIblock['ID'];
            }

            $items['IBLOCK_TYPES'][$arIblock['ID']] = $arIblock['IBLOCK_TYPE_ID'];
        }

        $rsProps = \CIBlockProperty::GetList();

        while ($arProp = $rsProps->Fetch())
        {
            $items['PROPS_ID'][$arProp['IBLOCK_ID']][$arProp['CODE']] = $arProp['ID'];
        }

        $rsPropsEnum = \CIBlockPropertyEnum::GetList();

        while ($arPropEnum = $rsPropsEnum->Fetch())
        {
            if ($arPropEnum['PROPERTY_CODE'])
            {
                $items['PROPS_ENUM'][$arPropEnum['PROPERTY_ID']][$arPropEnum['XML_ID']] = $arPropEnum['ID'];
            }
        }

        foreach ($iblockIds as $id)
        {
            Application::getInstance()->getTaggedCache()->registerTag('iblock_id_' . $id);
        }

        Application::getInstance()->getTaggedCache()->registerTag('iblock_id_new');

        return $items;
    }
}
