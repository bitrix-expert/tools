<?php
/**
 * @link https://github.com/bitrix-expert/tools
 * @copyright Copyright © 2015 Nik Samokhvalov
 * @license MIT
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) return false;

$manager = \Bitrix\Main\EventManager::getInstance();

$manager->addEventHandler('main', 'OnBeforeGroupAdd', ['\Bex\Tools\Group\GroupTools', 'onBeforeGroupAdd']);
$manager->addEventHandler('main', 'OnBeforeGroupUpdate', ['\Bex\Tools\Group\GroupTools', 'onBeforeGroupUpdate']);

$manager->addEventHandler('main', 'OnAfterGroupAdd', ['\Bex\Tools\Group\GroupFinder', 'onAfterGroupAdd']);
$manager->addEventHandler('main', 'OnAfterGroupUpdate', ['\Bex\Tools\Group\GroupFinder', 'onAfterGroupUpdate']);
$manager->addEventHandler('main', 'OnGroupDelete', ['\Bex\Tools\Group\GroupFinder', 'onGroupDelete']);

$manager->addEventHandler('iblock', 'OnBeforeIBlockAdd', ['\Bex\Tools\Iblock\IblockTools', 'onBeforeIBlockAdd']);
$manager->addEventHandler('iblock', 'OnBeforeIBlockUpdate', ['\Bex\Tools\Iblock\IblockTools', 'onBeforeIBlockUpdate']);

$manager->addEventHandler('iblock', 'OnAfterIBlockAdd', ['\Bex\Tools\Iblock\IblockFinder', 'onAfterIBlockAdd']);
$manager->addEventHandler('iblock', 'OnAfterIBlockUpdate', ['\Bex\Tools\Iblock\IblockFinder', 'onAfterIBlockUpdate']);
$manager->addEventHandler('iblock', 'OnIBlockDelete', ['\Bex\Tools\Iblock\IblockFinder', 'onIBlockDelete']);
