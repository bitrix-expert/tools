<?php
/**
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Bex\Tools\HlBlock;

use Bex\Tools\Finder;
use Bex\Tools\ValueNotFoundException;
use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Entity\Event;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;

/**
 * Finder of the highloadblocks.
 *
 * @author Mikhail Zhurov <mmjurov@gmail.com>
 */
class HlBlockFinder extends Finder
{

    protected static $cacheDir = 'bex_tools/hlblocks';
    protected static $cacheTag = 'bex_tools_hlblocks';
    protected $id;
    protected $name;

    /**
     * @inheritdoc
     *
     * @throws ArgumentNullException Empty parameters in the filter
     * @throws LoaderException Module "iblock" not installed
     */
    public function __construct(array $filter, $silenceMode = false)
    {
        if (!Loader::includeModule('highloadblock')) {
            throw new LoaderException('Failed include module "highloadblock"');
        }

        parent::__construct($filter, $silenceMode);

        $filter = $this->prepareFilter($filter);

        if (isset($filter['name'])) {
            $this->name = $filter['name'];
        }

        if (isset($filter['id'])) {
            $this->id = $filter['id'];
        } else {
            $this->id = $this->getFromCache(
                ['type' => 'id']
            );
        }
    }

    /**
     * Gets hlblock ID.
     *
     * @return integer
     */
    public function id()
    {
        return $this->id;
    }

    /**
     * Gets hlblock name.
     *
     * @return string
     */
    public function name()
    {
        return $this->getFromCache(
            ['type' => 'name'],
            $this->id
        );
    }

    /**
     * @inheritdoc
     *
     * @throws ArgumentNullException Empty parameters in the filter
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
     *
     * @throws ArgumentNullException
     * @throws ValueNotFoundException
     */
    protected function getValue(array $cache, array $filter, $shard)
    {
        switch ($filter['type']) {
            case 'id':
                if (isset($this->id)) {
                    return $this->id;
                }

                $value = (int)$cache[$this->name];

                if ($value <= 0) {
                    throw new ValueNotFoundException('HlBlock ID', 'hlblock name "' . $this->name . '"');
                }

                return $value;
                break;

            case 'name':
                $value = (string)$this->name;

                if (strlen($value) <= 0) {
                    throw new ValueNotFoundException('HlBlock name', 'hlblock #' . $this->id);
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
        $items = [];

        $dbHlBlocks = HighloadBlockTable::query()
            ->setSelect(['NAME', 'ID'])
            ->exec();

        while ($hlBlock = $dbHlBlocks->fetch()) {
            $items[$hlBlock['NAME']] = $hlBlock['ID'];
        }

        if (empty($items)) {
            throw new ValueNotFoundException('HlBlock', 'ID #' . $this->id);
        }

        $this->registerCacheTag(static::$cacheTag);

        return $items;
    }

    /**
     * Event handler for clearing cache dependencies
     * @param Event $event
     */
    public static function onAfterSomething(Event $event)
    {
        static::deleteCacheByTag(static::$cacheTag);
    }
}
