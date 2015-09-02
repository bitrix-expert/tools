<?php
/**
 * @link https://github.com/bitrix-expert/tools
 * @license MIT
 */

namespace Bex\Tools;

/**
 * Class implements handlers on tool entities CRUD events.
 * Clears created cache
 */
class EventHandlers
{
    const CACHE_TAG_GROUP = 'group_tools_cache';

    /**
     * Handler after user group delete event
     * @param $id
     */
    public static function onGroupDeleteHandler($id)
    {
        static::deleteGroupCache();
    }

    /**
     * Handler after user group add event
     * @param $arFields
     */
    public static function onAfterGroupAddHandler($arFields)
    {
        static::deleteGroupCache();
    }

    /**
     * Handler after user group update event
     * @param $id
     * @param $arFields
     */
    public static function onAfterGroupUpdateHandler($id, $arFields)
    {
        static::deleteGroupCache();
    }

    /**
     * Clears user group cache by tag
     */
    protected static function deleteGroupCache()
    {
        global $CACHE_MANAGER;

        if(defined('BX_COMP_MANAGED_CACHE'))
            $CACHE_MANAGER->ClearByTag(static::CACHE_TAG_GROUP);
    }
}