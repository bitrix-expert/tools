<?php
/**
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Bex\Tools\Catalog;

use Bex\Tools\Finder;
use Bex\Tools\ValueNotFoundException;
use Bitrix\Catalog\GroupTable;
use Bitrix\Main\Application;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Loader;

/**
 * Finder of the catalog groups (price types)
 *
 * @author Mikhail Zhurov <mmjurov@gmail.com>
 */
class GroupFinder extends Finder
{
    protected static $cacheDir = 'bex_tools/catalog_groups';
    protected static $cacheTag = 'bex_catalog_groups';
    protected $id;
    protected $name;

    /**
     * @inheritdoc
     *
     * @throws ArgumentNullException Empty parameters in the filter
     * @throws \LogicException When catalog module is not found
     */
    public function __construct(array $filter, $silenceMode = false)
    {
        if (!Loader::includeModule('catalog')) {
            throw new \LogicException('Failed include module "catalog"');
        }

        parent::__construct($filter, $silenceMode);

        $filter = $this->prepareFilter($filter);

        if (isset($filter['base']) && $filter['base'] === true) {

            $this->name = $this->id = null;
            $this->base = true;
            $this->id = $this->getFromCache([
                'type' => 'base'
            ]);

        } else {

            if (isset($filter['name'])) {
                $this->name = $filter['name'];
            }

            if (!isset($this->id)) {
                if (!isset($this->name)) {
                    throw new ArgumentNullException('name');
                }

                $this->id = $this->getFromCache([
                    'type' => 'id'
                ]);
            }

        }
    }

    /**
     * @inheritdoc
     *
     * @throws ArgumentNullException
     */
    protected function prepareFilter(array $filter)
    {
        foreach ($filter as $code => &$value) {
            if ($code === 'id') {
                intval($value);

                if ($value <= 0) {
                    throw new ArgumentNullException($code);
                }
            } elseif ($code === 'base') {

                if (!is_bool($value)) {
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
     * Handler after catalog group delete event
     *
     * @param int $id Group ID
     */
    public static function onGroupDelete($id)
    {
        static::deleteCacheByTag(static::getCacheTag());
    }

    /**
     * Gets tag of cache.
     *
     * @return string
     */
    public static function getCacheTag()
    {
        return static::$cacheTag;
    }

    /**
     * Handler after catalog group add event
     *
     * @param array $fields Group fields
     */
    public static function onGroupAdd($groupId, $fields)
    {
        static::deleteCacheByTag(static::getCacheTag());
    }

    /**
     * Handler after catalog group update event
     *
     * @param int $id Group ID
     * @param array $fields Group fields
     */
    public static function onGroupUpdate($id, $fields)
    {
        static::deleteCacheByTag(static::getCacheTag());
    }

    /**
     * Gets catalog group ID.
     *
     * @return integer
     */
    public function id()
    {
        return $this->getFromCache(
            ['type' => 'id']
        );
    }

    /**
     * Gets catalog group name.
     *
     * @return string
     */
    public function name()
    {
        return $this->getFromCache(
            ['type' => 'name']
        );
    }

    /**
     * @inheritdoc
     */
    protected function getValue(array $cache, array $filter, $shard)
    {
        switch ($filter['type']) {
            case 'id':
                if (isset($this->id)) {
                    return $this->id;
                }

                $value = (int)$cache['GROUPS_ID'][$this->name];

                if ($value <= 0) {
                    throw new ValueNotFoundException('Catalog group ID', 'group name "' . $this->name . '"');
                }
                break;

            case 'name':
                $value = $cache['GROUPS_NAME'][$this->id];

                if (strlen($value) <= 0) {
                    throw new ValueNotFoundException('Catalog group name', 'ID #' . $this->id);
                }
                break;

            case 'base':
                $value = $cache['GROUPS_BASE'];

                if ($value <= 0) {
                    throw new ValueNotFoundException('Catalog group base', false);
                }
                break;

            default:
                throw new \InvalidArgumentException('Invalid type of filter');
                break;
        }

        return $value;
    }

    /**
     * @inheritdoc
     */
    protected function getItems($shard)
    {
        $items = [];

        $rsGroups = GroupTable::query()
            ->setSelect(['ID', 'NAME', 'BASE'])
            ->exec();

        while ($group = $rsGroups->fetch()) {
            if ($group['NAME']) {
                $items['GROUPS_ID'][$group['NAME']] = $group['ID'];
                $items['GROUPS_NAME'][$group['ID']] = $group['NAME'];
            }

            if ($group['BASE'] == 'Y') {
                $items['GROUPS_BASE'] = $group['ID'];
            }
        }

        if (!empty($items)) {
            Application::getInstance()->getTaggedCache()->registerTag(static::getCacheTag());
        }

        return $items;
    }
}