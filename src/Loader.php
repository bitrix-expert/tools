<?php
/**
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Bex\Tools;

use Bitrix\Main\EventManager;

/**
 * Bootstrap of the Bex\Tools.
 * 
 * @author Nik Samokhvalov <nik@samokhvalov.info>
 */
class Loader
{
    protected static $isConfigurationLoaded = false;
    /**
     * @var array Handlers of the Bex\Tools.
     */
    private static $handlers = [
        ['main', 'OnBeforeGroupAdd', ['\Bex\Tools\Group\GroupTools', 'onBeforeGroupAdd']],
        ['main', 'OnBeforeGroupUpdate', ['\Bex\Tools\Group\GroupTools', 'onBeforeGroupUpdate']],
        ['main', 'OnAfterGroupAdd', ['\Bex\Tools\Group\GroupFinder', 'onAfterGroupAdd']],
        ['main', 'OnAfterGroupUpdate', ['\Bex\Tools\Group\GroupFinder', 'onAfterGroupUpdate']],
        ['main', 'OnGroupDelete', ['\Bex\Tools\Group\GroupFinder', 'onGroupDelete']],

        ['iblock', 'OnBeforeIBlockAdd', ['\Bex\Tools\Iblock\IblockTools', 'onBeforeIBlockAdd']],
        ['iblock', 'OnBeforeIBlockUpdate', ['\Bex\Tools\Iblock\IblockTools', 'onBeforeIBlockUpdate']],
        ['iblock', 'OnAfterIBlockAdd', ['\Bex\Tools\Iblock\IblockFinder', 'onAfterIBlockAdd']],
        ['iblock', 'OnAfterIBlockUpdate', ['\Bex\Tools\Iblock\IblockFinder', 'onAfterIBlockUpdate']],
        ['iblock', 'OnAfterIBlockPropertyAdd', ['\Bex\Tools\Iblock\IblockFinder', 'onAfterIBlockPropertyAdd']],
        ['iblock', 'OnAfterIBlockPropertyUpdate', ['\Bex\Tools\Iblock\IblockFinder', 'onAfterIBlockPropertyUpdate']],
        ['iblock', 'OnIBlockDelete', ['\Bex\Tools\Iblock\IblockFinder', 'onIBlockDelete']],

        ['highloadblock', '\Bitrix\Highloadblock\HighloadBlock::OnAdd', ['\Bex\Tools\HlBlock\HlBlockFinder', 'onAfterSomething']],
        ['highloadblock', '\Bitrix\Highloadblock\HighloadBlock::OnAfterUpdate', ['\Bex\Tools\HlBlock\HlBlockFinder', 'onAfterSomething']],
        ['highloadblock', '\Bitrix\Highloadblock\HighloadBlock::OnAfterDelete', ['\Bex\Tools\HlBlock\HlBlockFinder', 'onAfterSomething']],

        ['catalog', 'OnGroupAdd', ['\Bex\Tools\CatalogGroup\GroupFinder', 'onGroupAdd']],
        ['catalog', 'OnGroupUpdate', ['\Bex\Tools\CatalogGroup\GroupFinder', 'onGroupUpdate']],
        ['catalog', 'OnGroupDelete', ['\Bex\Tools\CatalogGroup\GroupFinder', 'onGroupDelete']]
    ];
    
    /**
     * Initialize. Register handlers of the Bitrix events.
     */
    public static function initialize()
    {
        if (static::$isConfigurationLoaded === true) {
            return;
        }
        
        foreach (static::$handlers as $handler) {
            EventManager::getInstance()->addEventHandler($handler[0], $handler[1], $handler[2], $handler[3]);
        }

        static::$isConfigurationLoaded = true;
    }
}
