<?php
/**
 * @link https://github.com/bitrix-expert/tools
 * @copyright Copyright © 2015 Nik Samokhvalov
 * @license MIT
 */

namespace Bex\Tools\Group;

use Bex\Tools\Finder;
use Bitrix\Main;
use Bitrix\Main\Application;

/**
 * @author Nik Samokhvalov <nik@samokhvalov.info>
 */
class GroupFinder extends Finder
{
    protected static $cacheTime = 8600000;
    protected static $cacheDir = 'bex_tools/groups';
    protected $id;
    protected $code;

    public function __construct(array $filter)
    {
        $filter = $this->prepareFilter($filter);

        if (isset($filter['id'])) {
            $this->id = $filter['id'];
        }

        if (isset($filter['code'])) {
            $this->code = $filter['code'];
        }

        if (!isset($this->id)) {
            if (!isset($this->code)) {
                throw new Main\ArgumentNullException('code');
            }

            $this->id = $this->getFromCache([
                'type' => 'groupId'
            ]);
        }
    }

    public function id()
    {
        return $this->getFromCache([
            'object' => 'groupId'
        ]);
    }

    public function code()
    {
        return $this->getFromCache([
            'object' => 'groupCode'
        ]);
    }

    protected function prepareFilter(array $filter)
    {
        foreach ($filter as $code => &$value) {
            if ($code === 'id') {
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
        switch ($filter['object'])
        {
            case 'groupId':
                $value = (int) $cache['GROUPS'][$filter['groupCode']];

                if ($value <= 0) {
                    throw new \Exception();
                }
            break;
            case 'groupCode':
                $value = $cache['CODES'][$this->id];

                if (strlen($value) <= 0) {
                    throw new \Exception();
                }
            break;
        }

        return $value;
    }

    protected function getItems()
    {
        $items = [];

        $rsGroups = \CGroup::GetList();

        while ($group = $rsGroups->Fetch())
        {
            if ($group['STRING_ID'])
            {
                $items['GROUPS'][$group['STRING_ID']] = $group['ID'];
                $items['CODES'][$group['ID']] = $group['STRING_ID'];
            }
        }

        if (!empty($items))
        {
            Application::getInstance()->getTaggedCache()->registerTag('group_tools_cache');
        }

        return $items;
    }
}