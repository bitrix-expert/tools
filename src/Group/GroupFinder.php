<?php
/**
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Bex\Tools\Group;

use Bex\Tools\Finder;
use Bex\Tools\ValueNotFoundException;
use Bitrix\Main\Application;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\GroupTable;

/**
 * Finder of the users groups.
 *
 * @author Nik Samokhvalov <nik@samokhvalov.info>
 */
class GroupFinder extends Finder
{
    protected static $cacheDir = 'bex_tools/groups';
    protected static $cacheTag = 'bex_user_groups';
    protected $id;
    protected $code;

    /**
     * @inheritdoc
     *
     * @throws ArgumentNullException Empty parameters in the filter
     */
    public function __construct(array $filter, $silenceMode = false)
    {
        parent::__construct($filter, $silenceMode);

        $filter = $this->prepareFilter($filter);

        if (isset($filter['id'])) {
            $this->id = $filter['id'];
        }

        if (isset($filter['code'])) {
            $this->code = $filter['code'];
        }

        if (!isset($this->id)) {
            if (!isset($this->code)) {
                throw new ArgumentNullException('code');
            }

            $this->id = $this->getFromCache([
                'type' => 'id'
            ]);
        }
    }

    /**
     * Gets group ID.
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
     * Gets group code.
     *
     * @return string
     */
    public function code()
    {
        return $this->getFromCache(
            ['type' => 'code']
        );
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
     */
    protected function getValue(array $cache, array $filter, $shard)
    {
        switch ($filter['type']) {
            case 'id':
                if (isset($this->id)) {
                    return $this->id;
                }

                $value = (int)$cache['GROUPS_ID'][$this->code];

                if ($value <= 0) {
                    throw new ValueNotFoundException('Group ID', 'group code "' . $this->code . '"');
                }
                break;

            case 'code':
                $value = $cache['GROUPS_CODE'][$this->id];

                if (strlen($value) <= 0) {
                    throw new ValueNotFoundException('Group code', 'ID #' . $this->id);
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
            ->setSelect(['ID', 'STRING_ID'])
            ->exec();

        while ($group = $rsGroups->fetch()) {
            if ($group['STRING_ID']) {
                $items['GROUPS_ID'][$group['STRING_ID']] = $group['ID'];
                $items['GROUPS_CODE'][$group['ID']] = $group['STRING_ID'];
            }
        }

        if (!empty($items)) {
            Application::getInstance()->getTaggedCache()->registerTag(static::getCacheTag());
        }

        return $items;
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
     * Sets tag for cache.
     *
     * @param string $tag
     */
    public static function setCacheTag($tag)
    {
        static::$cacheTag = htmlspecialchars(trim($tag));
    }

    /**
     * Handler after user group delete event
     *
     * @param int $id Group ID
     */
    public static function onGroupDelete($id)
    {
        static::deleteCacheByTag(static::getCacheTag());
    }

    /**
     * Handler after user group add event
     *
     * @param array $fields Group fields
     */
    public static function onAfterGroupAdd(&$fields)
    {
        static::deleteCacheByTag(static::getCacheTag());
    }

    /**
     * Handler after user group update event
     *
     * @param int $id Group ID
     * @param array $fields Group fields
     */
    public static function onAfterGroupUpdate($id, &$fields)
    {
        static::deleteCacheByTag(static::getCacheTag());
    }

    /**
     * @deprecated
     * @see Finder::deleteCacheByTag()
     */
    protected static function deleteGroupCache()
    {
        global $CACHE_MANAGER;

        if (defined('BX_COMP_MANAGED_CACHE')) {
            $CACHE_MANAGER->ClearByTag(static::getCacheTag());
        }
    }
}