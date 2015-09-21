<?php

/**
 * @link https://github.com/bitrix-expert/tools
 * @license MIT
 */

$manager = \Bitrix\Main\EventManager::getInstance();
$manager->addEventHandler("main", "OnGroupDelete", Array("\Bex\Tools\EventHandlers", "onGroupDeleteHandler"));
$manager->addEventHandler("main", "OnAfterGroupAddHandler", Array("\Bex\Tools\EventHandlers", "onAfterGroupAddHandler"));
$manager->addEventHandler("main", "OnAfterGroupUpdate", Array("\Bex\Tools\EventHandlers", "onAfterGroupUpdateHandler"));