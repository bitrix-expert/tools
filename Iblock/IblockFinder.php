<?php
/**
 * @link https://github.com/bitrix-expert/tools
 * @copyright Copyright © 2015 Nik Samokhvalov
 * @license MIT
 */

namespace Bex\Tools\Iblock;

use Bex\Tools\Finder;
use Bitrix\Iblock\IblockTable;
use Bitrix\Iblock\PropertyTable;
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
        if (!Main\Loader::includeModule('iblock'))
        {
            throw new Main\LoaderException('Failed include module "iblock"');
        }

        $filter = $this->prepareFilter($filter);

        if (isset($filter['iblockType']))
        {
            $this->type = $filter['iblockType'];
        }

        if (isset($filter['iblockCode']))
        {
            $this->code = $filter['iblockCode'];
        }

        if (isset($filter['iblockId']))
        {
            $this->id = $filter['iblockId'];
        }

        if (!isset($this->id))
        {
            if (!isset($this->type))
            {
                throw new Main\ArgumentNullException('iblock type');
            }
            elseif (!isset($this->code))
            {
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

    public function code()
    {
        return $this->getFromCache([
            'type' => 'iblockCode'
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
        foreach ($filter as $code => &$value)
        {
            if ($code === 'iblockId' || $code === 'propId')
            {
                intval($value);

                if ($value <= 0)
                {
                    throw new Main\ArgumentNullException($code);
                }
            }
            else
            {
                trim(htmlspecialchars($value));

                if (strlen($value) <= 0)
                {
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
                $value = (int) $cache['IBLOCKS_ID'][$this->type][$this->code];

                if ($value <= 0)
                {
                    throw new \Exception();
                }

                return $value;
                break;

            case 'iblockType':
                $value = (string) $cache['IBLOCKS_TYPE'][$this->id];

                if (strlen($value) <= 0)
                {
                    throw new \Exception();
                }

                return $value;
                break;

            case 'iblockCode':
                $value = (string) $cache['IBLOCKS_CODE'][$this->id];

                if (strlen($value) <= 0)
                {
                    throw new \Exception();
                }

                return $value;
                break;

            case 'propId':
                $value = (int) $cache['PROPS_ID'][$this->id][$filter['propCode']];

                if ($value <= 0)
                {
                    throw new \Exception();
                }

                return $value;
                break;

            case 'propEnumId':
                $propId = $cache['PROPS_ID'][$this->id][$filter['propCode']];

                $value = (int) $cache['PROPS_ENUM_ID'][$propId][$filter['valueXmlId']];

                if ($value <= 0)
                {
                    throw new \Exception();
                }

                return $value;
                break;

            default:
                throw new Main\ArgumentException('', 'type');
                break;
        }
    }

    protected function getItems()
    {
        $items = [];
        $iblockIds = [];

        $rsIblocks = IblockTable::getList();

        while ($iblock = $rsIblocks->fetch())
        {
            if ($iblock['CODE'])
            {
                $items['IBLOCKS_ID'][$iblock['IBLOCK_TYPE_ID']][$iblock['CODE']] = $iblock['ID'];
                $items['IBLOCKS_CODE'][$iblock['ID']] = $iblock['CODE'];

                $iblockIds[] = $iblock['ID'];
            }

            $items['IBLOCKS_TYPE'][$iblock['ID']] = $iblock['IBLOCK_TYPE_ID'];
        }

        $rsProps = PropertyTable::getList();

        while ($prop = $rsProps->fetch())
        {
            $items['PROPS_ID'][$prop['IBLOCK_ID']][$prop['CODE']] = $prop['ID'];
        }

        // @todo Переделать на Д7
        $rsPropsEnum = \CIBlockPropertyEnum::GetList();

        while ($propEnum = $rsPropsEnum->Fetch())
        {
            if ($propEnum['PROPERTY_CODE'])
            {
                $items['PROPS_ENUM_ID'][$propEnum['PROPERTY_ID']][$propEnum['XML_ID']] = $propEnum['ID'];
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
