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
use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;

/**
 * @author Nik Samokhvalov <nik@samokhvalov.info>
 */
class IblockFinder extends Finder
{
    const CACHE_PROPS_SHARD = 'props';

    protected static $cacheTime = 8600000;
    protected static $cacheDir = 'bex_tools/iblocks';
    protected $id;
    protected $type;
    protected $code;

    public function __construct(array $filter)
    {
        if (!Loader::includeModule('iblock'))
        {
            throw new LoaderException('Failed include module "iblock"');
        }

        $filter = $this->prepareFilter($filter);

        if (isset($filter['type']))
        {
            $this->type = $filter['type'];
        }

        if (isset($filter['code']))
        {
            $this->code = $filter['code'];
        }

        if (isset($filter['id']))
        {
            $this->id = $filter['id'];
        }

        if (!isset($this->id))
        {
            if (!isset($this->type))
            {
                throw new ArgumentNullException('type');
            }
            elseif (!isset($this->code))
            {
                throw new ArgumentNullException('code');
            }

            $this->id = $this->getFromCache([
                'type' => 'id'
            ]);
        }
    }

    /**
     * Gets iblock ID.
     *
     * @return integer
     */
    public function id()
    {
        return $this->getFromCache([
            'type' => 'id'
        ]);
    }

    /**
     * Gets iblock type.
     *
     * @return string
     */
    public function type()
    {
        return $this->getFromCache([
            'type' => 'type'
        ]);
    }

    /**
     * Gets iblock code.
     *
     * @return string
     */
    public function code()
    {
        return $this->getFromCache([
            'type' => 'code'
        ]);
    }

    /**
     * Gets property ID.
     *
     * @param string $code Property code
     *
     * @return integer
     */
    public function propId($code)
    {
        return $this->getFromCache([
                'type' => 'propId',
                'propCode' => $code,
            ],
            static::CACHE_PROPS_SHARD
        );
    }

    /**
     * Gets property enum value ID.
     *
     * @param string $code Property code
     * @param integer $valueXmlId Property enum value XML ID
     *
     * @return integer
     */
    public function propEnumId($code, $valueXmlId)
    {
        return $this->getFromCache([
                'type' => 'propEnumId',
                'propCode' => $code,
                'valueXmlId' => $valueXmlId
            ],
            static::CACHE_PROPS_SHARD
        );
    }

    protected function prepareFilter(array $filter)
    {
        foreach ($filter as $code => &$value)
        {
            if ($code === 'id' || $code === 'propId')
            {
                intval($value);

                if ($value <= 0)
                {
                    throw new ArgumentNullException($code);
                }
            }
            else
            {
                trim(htmlspecialchars($value));

                if (strlen($value) <= 0)
                {
                    throw new ArgumentNullException($code);
                }
            }
        }

        return $filter;
    }

    protected function getValue(array $cache, array $filter, $shard)
    {
        switch ($filter['type'])
        {
            case 'id':
                if (isset($this->id))
                {
                    return $this->id;
                }

                $value = (int) $cache['IBLOCKS_ID'][$this->type][$this->code];

                if ($value <= 0)
                {
                    throw new ArgumentException('Iblock ID by type "' . $this->type . '" and code "'
                        . $this->code . '" not found');
                }

                return $value;
                break;

            case 'type':
                $value = (string) $cache['IBLOCKS_TYPE'][$this->id];

                if (strlen($value) <= 0)
                {
                    throw new ArgumentException('Iblock type by iblock #' . $this->id . ' not found');
                }

                return $value;
                break;

            case 'code':
                $value = (string) $cache['IBLOCKS_CODE'][$this->id];

                if (strlen($value) <= 0)
                {
                    throw new ArgumentException('Iblock code by iblock #' . $this->id . ' not found');
                }

                return $value;
                break;

            case 'propId':
                $value = (int) $cache['PROPS_ID'][$this->id][$filter['propCode']];

                if ($value <= 0)
                {
                    throw new ArgumentException('Property ID by iblock #' . $this->id . ' and property code "'
                        . $filter['propCode'] . '" not found');
                }

                return $value;
                break;

            case 'propEnumId':
                $propId = $cache['PROPS_ID'][$this->id][$filter['propCode']];

                $value = (int) $cache['PROPS_ENUM_ID'][$propId][$filter['valueXmlId']];

                if ($value <= 0)
                {
                    throw new ArgumentException('Property enum ID by iblock #' . $this->id . ', property code "'
                        . $filter['propCode'] . '" and property XML ID "' . $filter['valueXmlId'] . '" not found');
                }

                return $value;
                break;

            default:
                throw new \InvalidArgumentException('Invalid type on filter');
                break;
        }
    }

    protected function getItems($shard)
    {
        if ($shard === static::CACHE_PROPS_SHARD)
        {
            return $this->getProperties();
        }

        return $this->getIblocks();
    }

    protected function getIblocks()
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

        foreach ($iblockIds as $id)
        {
            Application::getInstance()->getTaggedCache()->registerTag('iblock_id_' . $id);
        }

        Application::getInstance()->getTaggedCache()->registerTag('iblock_id_new');

        return $items;
    }

    protected function getProperties()
    {
        $items = [];

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

        return $items;
    }
}
