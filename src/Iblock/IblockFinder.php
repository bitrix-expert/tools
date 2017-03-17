<?php
/**
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Bex\Tools\Iblock;

use Bex\Tools\Finder;
use Bex\Tools\ValueNotFoundException;
use Bitrix\Iblock\IblockTable;
use Bitrix\Iblock\PropertyEnumerationTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\EventManager;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;

/**
 * Finder of the info blocks and properties of the info blocks.
 *
 * @author Nik Samokhvalov <nik@samokhvalov.info>
 */
class IblockFinder extends Finder
{
    /**
     * Code of the shard cache for iblocks IDs.
     */
    const CACHE_SHARD_LITE = 'lite';
    /**
     * @deprecated
     */
    const CACHE_PROPS_SHARD = 'props';

    protected static $cacheDir = 'bex_tools/iblocks';
    protected static $delayedIblocks = [];
    protected $id;
    protected $type;
    protected $code;

    /**
     * @inheritdoc
     *
     * @throws ArgumentNullException Empty parameters in the filter
     * @throws LoaderException Module "iblock" not installed
     */
    public function __construct(array $filter, $silenceMode = false)
    {
        if (!Loader::includeModule('iblock')) {
            throw new LoaderException('Failed include module "iblock"');
        }

        parent::__construct($filter, $silenceMode);

        $filter = $this->prepareFilter($filter);

        if (isset($filter['type'])) {
            $this->type = $filter['type'];
        }

        if (isset($filter['code'])) {
            $this->code = $filter['code'];
        }

        if (isset($filter['id'])) {
            $this->id = $filter['id'];
        } else {
            $this->id = $this->getFromCache(
                ['type' => 'id'],
                static::CACHE_SHARD_LITE
            );
        }
    }

    /**
     * Gets iblock ID.
     *
     * @return integer
     */
    public function id()
    {
        return $this->id;
    }

    /**
     * Gets iblock type.
     *
     * @return string
     */
    public function type()
    {
        return $this->getFromCache(
            ['type' => 'type'],
            $this->id
        );
    }

    /**
     * Gets iblock code.
     *
     * @return string
     */
    public function code()
    {
        return $this->getFromCache(
            ['type' => 'code'],
            $this->id
        );
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
        return $this->getFromCache(
            ['type' => 'propId', 'propCode' => $code],
            $this->id
        );
    }

    /**
     * Gets property enum value ID.
     *
     * @param string $code Property code
     * @param int $valueXmlId Property enum value XML ID
     *
     * @return integer
     */
    public function propEnumId($code, $valueXmlId)
    {
        return $this->getFromCache(
            ['type' => 'propEnumId', 'propCode' => $code, 'valueXmlId' => $valueXmlId],
            $this->id
        );
    }

    /**
     * Preliminary collection of cache.
     *
     * @param string $iblockType Type of info block.
     * @param string $iblockCode Code of info block.
     */
    public static function runCacheCollector($iblockType = null, $iblockCode = null)
    {
        if (!$iblockType || !$iblockCode) {
            $iblock = IblockTable::query()
                ->setFilter(['!CODE' => false])
                ->setLimit(1)
                ->setSelect(['IBLOCK_TYPE_ID', 'CODE'])
                ->exec()
                ->fetch();

            $iblockType = $iblock['IBLOCK_TYPE_ID'];
            $iblockCode = $iblock['CODE'];
        }

        $finder = new static(['type' => $iblockType, 'code' => $iblockCode]);
        $finder->code();
    }

    /**
     * Preliminary collection of cache by id
     *
     * @param string|int|null $iblockId Identifier of info block
     */
    protected static function runCacheCollectorById($iblockId = null)
    {
        if (!$iblockId) {
            return static::runCacheCollector();
        }

        $finder = new static(['id' => $iblockId]);
        $finder->code();
    }

    /**
     * @inheritdoc
     *
     * @throws ArgumentNullException Empty parameters in the filter
     */
    protected function prepareFilter(array $filter)
    {
        foreach ($filter as $code => &$value) {
            if ($code === 'id' || $code === 'propId') {
                intval($value);

                if ($value <= 0) {
                    throw new ArgumentNullException($code);
                }
            } else {
                trim(htmlspecialchars($value));

                if (strlen($value) <= 0) {
                    throw new ArgumentNullException($code);
                }
            }
        }

        return $filter;
    }

    /**
     * @inheritdoc
     *
     * @throws ArgumentNullException
     * @throws ValueNotFoundException
     */
    protected function getValue(array $cache, array $filter, $shard)
    {
        if ($shard === static::CACHE_SHARD_LITE) {
            return $this->getValueLiteShard($cache, $filter);
        } else {
            return $this->getValueIblockShard($cache, $filter);
        }
    }

    /**
     * @see IblockFinder::getValue()
     *
     * @param array $cache
     * @param array $filter
     *
     * @return int
     *
     * @throws ArgumentNullException
     * @throws ValueNotFoundException
     */
    protected function getValueLiteShard(array $cache, array $filter)
    {
        if (!isset($this->type)) {
            throw new ArgumentNullException('type');
        } elseif (!isset($this->code)) {
            throw new ArgumentNullException('code');
        }

        if ($filter['type'] === 'id') {
            $value = (int)$cache[$this->type][$this->code];

            if ($value <= 0) {
                throw new ValueNotFoundException('Iblock ID', 'type "' . $this->type . '" and code "'
                    . $this->code . '"');
            }

            return $value;
        } else {
            throw new \InvalidArgumentException('Invalid type on filter');
        }
    }

    /**
     * @see IblockFinder::getValue()
     *
     * @param array $cache
     * @param array $filter
     *
     * @return int|string
     *
     * @throws ValueNotFoundException
     */
    protected function getValueIblockShard(array $cache, array $filter)
    {
        switch ($filter['type']) {
            case 'type':
                $value = (string)$cache['TYPE'];

                if (strlen($value) <= 0) {
                    throw new ValueNotFoundException('Iblock type', 'iblock #' . $this->id);
                }

                return $value;
                break;

            case 'code':
                $value = (string)$cache['CODE'];

                if (strlen($value) <= 0) {
                    throw new ValueNotFoundException('Iblock code', 'iblock #' . $this->id);
                }

                return $value;
                break;

            case 'propId':
                $value = (int)$cache['PROPS_ID'][$filter['propCode']];

                if ($value <= 0) {
                    throw new ValueNotFoundException('Property ID', 'iblock #' . $this->id . ' and property code "'
                        . $filter['propCode'] . '"');
                }

                return $value;
                break;

            case 'propEnumId':
                $value = (int)$cache['PROPS_ENUM_ID'][$filter['propCode']][$filter['valueXmlId']];

                if ($value <= 0) {
                    throw new ValueNotFoundException('Property enum ID', 'iblock #' . $this->id . ', property code "'
                        . $filter['propCode'] . '" and property XML ID "' . $filter['valueXmlId'] . '"');
                }

                return $value;
                break;

            default:
                throw new \InvalidArgumentException('Invalid type on filter');
                break;
        }
    }

    /**
     * @inheritdoc
     */
    protected function getItems($shard)
    {
        if ($shard === static::CACHE_SHARD_LITE) {
            return $this->getItemsLiteShard();
        } else {
            return $this->getItemsIblockShard();
        }
    }

    /**
     * @see IblockFinder::getItems()
     *
     * @return array
     *
     * @throws ArgumentException
     */
    protected function getItemsLiteShard()
    {
        $items = [];

        $rsIblocks = IblockTable::query()
            ->setSelect(['IBLOCK_TYPE_ID', 'ID', 'CODE'])
            ->exec();

        while ($iblock = $rsIblocks->fetch()) {
            if ($iblock['CODE']) {
                $items[$iblock['IBLOCK_TYPE_ID']][$iblock['CODE']] = $iblock['ID'];
            }
        }

        $this->registerCacheTag('bex_iblock_new');

        return $items;
    }

    /**
     * @see IblockFinder::getItems()
     *
     * @return array
     *
     * @throws ValueNotFoundException
     * @throws ArgumentException
     */
    protected function getItemsIblockShard()
    {
        $items = [];

        $rsIblocks = IblockTable::query()
            ->setFilter(['ID' => $this->id])
            ->setSelect(['IBLOCK_TYPE_ID', 'CODE'])
            ->exec();

        if ($iblock = $rsIblocks->fetch()) {
            if ($iblock['CODE']) {
                $items['CODE'] = $iblock['CODE'];
            }

            $items['TYPE'] = $iblock['IBLOCK_TYPE_ID'];
        }

        if (empty($items)) {
            throw new ValueNotFoundException('Iblock', 'ID #' . $this->id);
        }

        $propIds = [];

        $rsProps = PropertyTable::query()
            ->setFilter(['IBLOCK_ID' => $this->id])
            ->setSelect(['ID', 'CODE', 'IBLOCK_ID'])
            ->exec();

        while ($prop = $rsProps->fetch()) {
            $propIds[] = $prop['ID'];
            $items['PROPS_ID'][$prop['CODE']] = $prop['ID'];
        }

        if (!empty($propIds)) {
            $rsPropsEnum = PropertyEnumerationTable::query()
                ->setFilter(['PROPERTY_ID' => $propIds])
                ->setSelect(['ID', 'XML_ID', 'PROPERTY_ID', 'PROPERTY_CODE' => 'PROPERTY.CODE'])
                ->exec();

            while ($propEnum = $rsPropsEnum->fetch()) {
                if ($propEnum['PROPERTY_CODE']) {
                    $items['PROPS_ENUM_ID'][$propEnum['PROPERTY_CODE']][$propEnum['XML_ID']] = $propEnum['ID'];
                }
            }
        }

        $this->registerCacheTag('bex_iblock_' . $this->id);

        return $items;
    }

    /**
     * @param $iblockId
     */
    protected static function delayCacheCollector($iblockId)
    {
        if (empty(static::$delayedIblocks)) {
            EventManager::getInstance()->addEventHandler(
                'main', 'OnEpilog', [get_called_class(), 'onEpilog']
            );
        }

        static::$delayedIblocks[] = $iblockId;
    }

    public static function onAfterIBlockAdd(&$fields)
    {
        if ($fields['ID'] > 0) {
            static::deleteCacheByTag('bex_iblock_new');
            new static(['id' => $fields['ID']]);
            static::delayCacheCollector($fields['ID']);
        }
    }

    public static function onAfterIBlockUpdate(&$fields)
    {
        if ($fields['RESULT'] && $fields['ID'] > 0) {
            static::deleteCacheByTag('bex_iblock_' . $fields['ID']);
            static::deleteCacheByTag('bex_iblock_new');
            new static(['id' => $fields['ID']]);
            static::delayCacheCollector($fields['ID']);
        }
    }

    public static function onIBlockDelete($id)
    {
        static::deleteCacheByTag('bex_iblock_' . $id);
        static::deleteCacheByTag('bex_iblock_new');

        static::runCacheCollectorById($id);
    }

    public static function onAfterIBlockPropertyAdd(&$fields)
    {
        if ($fields['RESULT']) {
            static::deleteCacheByTag('bex_iblock_' . $fields['IBLOCK_ID']);
            static::runCacheCollectorById($fields['IBLOCK_ID']);
        }
    }

    public static function onAfterIBlockPropertyUpdate(&$fields)
    {
        static::onAfterIBlockPropertyAdd($fields);
    }

    /**
     * TODO when bitrix will support this event
     */
    public static function OnAfterIBlockPropertyDelete()
    {

    }

    public static function onEpilog()
    {
        $iblockIds = static::$delayedIblocks;
        
        if (!empty($iblockIds)) {
            foreach ($iblockIds as $iblockId) {
                static::runCacheCollectorById($iblockId);
            }
        }
    }
}
