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
    /**
     * @var EventManager
     */
    protected $eventManager;
    /**
     * @var array Handlers of the Bex\Tools.
     */
    private $handlers = [
        ['main', 'OnBeforeGroupAdd', ['\Bex\Tools\Group\GroupTools', 'onBeforeGroupAdd']],
        ['main', 'OnBeforeGroupUpdate', ['\Bex\Tools\Group\GroupTools', 'onBeforeGroupUpdate']],
        ['main', 'OnAfterGroupAdd', ['\Bex\Tools\Group\GroupFinder', 'onAfterGroupAdd']],
        ['main', 'OnAfterGroupUpdate', ['\Bex\Tools\Group\GroupFinder', 'onAfterGroupUpdate']],
        ['main', 'OnGroupDelete', ['\Bex\Tools\Group\GroupFinder', 'onGroupDelete']],
        ['iblock', 'OnBeforeIBlockAdd', ['\Bex\Tools\Iblock\IblockTools', 'onBeforeIBlockAdd']],
        ['iblock', 'OnBeforeIBlockUpdate', ['\Bex\Tools\Iblock\IblockTools', 'onBeforeIBlockUpdate']],
        ['iblock', 'OnAfterIBlockAdd', ['\Bex\Tools\Iblock\IblockFinder', 'onAfterIBlockAdd']],
        ['iblock', 'OnAfterIBlockUpdate', ['\Bex\Tools\Iblock\IblockFinder', 'onAfterIBlockUpdate']],
        ['iblock', 'OnIBlockDelete', ['\Bex\Tools\Iblock\IblockFinder', 'onIBlockDelete']],
        ['highloadblock', '\Bitrix\Highloadblock\HighloadBlock::OnAdd', ['\Bex\Tools\HlBlock\HlBlockFinder', 'onAfterSomething']],
        ['highloadblock', '\Bitrix\Highloadblock\HighloadBlock::OnAfterUpdate', ['\Bex\Tools\HlBlock\HlBlockFinder', 'onAfterSomething']],
        ['highloadblock', '\Bitrix\Highloadblock\HighloadBlock::OnAfterDelete', ['\Bex\Tools\HlBlock\HlBlockFinder', 'onAfterSomething']]
    ];
    
    public function __construct()
    {
        $this->eventManager = EventManager::getInstance();
    }

    /**
     * Runs loader.
     */
    public function run()
    {
        $this->registerHandlers();
    }
    
    /**
     * Register handlers of the Bitrix events.
     */
    protected function registerHandlers()
    {
        foreach ($this->getHandlers() as $handler) {
            $this->eventManager->addEventHandler($handler[0], $handler[1], $handler[2], $handler[3]);
        }
    }

    /**
     * Gets event handlers of the Bex\Tools.
     * 
     * @return array
     */
    public function getHandlers()
    {
        return (array) $this->handlers;
    }
}