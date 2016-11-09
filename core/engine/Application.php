<?php
/*
  +------------------------------------------------------------------------+
  | PhalconEye CMS                                                         |
  +------------------------------------------------------------------------+
  | Copyright (c) 2013-2016 PhalconEye Team (http://phalconeye.com/)       |
  +------------------------------------------------------------------------+
  | This source file is subject to the New BSD License that is bundled     |
  | with this package in the file LICENSE.txt.                             |
  |                                                                        |
  | If you did not receive a copy of the license and are unable to         |
  | obtain it through the world-wide-web, please send an email             |
  | to license@phalconeye.com so we can send you a copy immediately.       |
  +------------------------------------------------------------------------+
  | Author: Ivan Vorontsov <lantian.ivan@gmail.com>                        |
  +------------------------------------------------------------------------+
*/

namespace Engine;

use Engine\Behavior\ApplicationBehavior;
use Engine\Behavior\DIBehavior;
use Phalcon\DI;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Mvc\Application as PhalconApplication;
use Phalcon\Registry;

/**
 * Application class.
 *
 * @category  PhalconEye
 * @package   Engine
 * @author    Ivan Vorontsov <lantian.ivan@gmail.com>
 * @copyright 2013-2016 PhalconEye Team
 * @license   New BSD License
 * @link      http://phalconeye.com/
 */
class Application extends PhalconApplication
{
    const
        /**
         * Default module.
         */
        CMS_MODULE_CORE = 'core',

        /**
         * User module.
         */
        CMS_MODULE_USER = 'user',

        /**
         * Installation module.
         */
        CMS_MODULE_INSTALL = 'install',

        /**
         * Normal run mode
         */
        MODE_NORMAL = 'normal',

        /**
         * Console run mode
         */
        MODE_CONSOLE = 'console',

        /**
         * Session run mode
         */
        MODE_SESSION = 'session',

        /**
         * System modules.
         */
        SYSTEM_MODULES = [self::CMS_MODULE_CORE, self::CMS_MODULE_USER];

    use ApplicationBehavior;

    /**
     * Application configuration.
     *
     * @var Config
     */
    protected $_config;

    /**
     * Loaders for different modes.
     *
     * @var array
     */
    private $_loaders =
        [
            self::MODE_NORMAL => [
                'logger',
                'loader',
                'environment',
                'cache',
                'modules',
                'loader',
                'plugins',
                'annotations',
                'database',
                'router',
                'session',
                'flash',
                'view',
                'widgets',
                'themes',
                'engine'
            ],
            self::MODE_CONSOLE => [
                'logger',
                'loader',
                'environment',
                'cache',
                'modules',
                'plugins',
                'database',
                'widgets',
                'themes',
                'engine'
            ],
            self::MODE_SESSION => [
                'logger',
                'loader',
                'cache',
                'modules',
                'plugins',
                'database',
                'session'
            ],
        ];

    /**
     * Constructor.
     */
    public function __construct()
    {
        /**
         * Create default DI.
         */
        $di = new DI\FactoryDefault();

        /**
         * Get config.
         */
        $this->_config = Config::factory();

        /**
         * Setup Registry.
         */
        $registry = new Registry();
        $registry->offsetSet(
            'directories',
            (object)[
                'engine' => ROOT_PATH . '/core/engine/',
                'cms' => ROOT_PATH . '/core/cms/',
                'modules' => ROOT_PATH . '/app/modules/',
                'plugins' => ROOT_PATH . '/app/plugins/',
                'widgets' => ROOT_PATH . '/app/widgets/',
                'themes' => ROOT_PATH . '/app/themes/',
            ]
        );
        $di->set('registry', $registry);

        // Store config in the DI container.
        $di->setShared('config', $this->_config);
        parent::__construct($di);
    }

    /**
     * Runs the application, performing all initializations.
     *
     * @param string $mode Mode name.
     *
     * @return void
     */
    public function run($mode = self::MODE_NORMAL)
    {
        if (!isset($this->_loaders[$mode])) {
            $mode = self::MODE_NORMAL;
        }

        // Set application main objects.
        /** @var DIBehavior $di */
        $di = $this->_dependencyInjector;
        $di->setShared('app', $this);
        $config = $this->_config;
        $eventsManager = new EventsManager();
        $this->setEventsManager($eventsManager);

        // Init services and engine system.
        foreach ($this->_loaders[$mode] as $service) {
            $serviceName = ucfirst($service);
            $eventsManager->fire('init:before' . $serviceName, null);
            $result = $this->{'_init' . $serviceName}($di, $config, $eventsManager);
            $eventsManager->fire('init:after' . $serviceName, $result);
        }

        $di->setShared('eventsManager', $eventsManager);
    }

    /**
     * Init modules and register them.
     *
     * @param array   $modules Modules bootstrap classes.
     * @param boolean $merge   Merge with existing.
     *
     * @return $this
     */
    public function registerModules(array $modules, $merge = false)
    {
        $bootstraps = [];
        $di = $this->getDI();
        foreach ($modules as $moduleName => $moduleClass) {
            if (isset($this->_modules[$moduleName])) {
                continue;
            }

            $bootstrap = new $moduleClass($di, $this->getEventsManager());
            $bootstraps[$moduleName] = function () use ($bootstrap, $di) {
                $bootstrap->initialize();

                return $bootstrap;
            };
        }

        return parent::registerModules($bootstraps, $merge);
    }

    /**
     * Get application output.
     *
     * @return string
     */
    public function getOutput()
    {
        return $this->handle()->getContent();
    }

    /**
     * Clear application cache.
     *
     * @return void
     */
    public function clearCache()
    {
        $cacheOutput = $this->_dependencyInjector->get('cacheOutput');
        $cacheData = $this->_dependencyInjector->get('cacheData');
        $config = $this->_dependencyInjector->get('config');

        $cacheOutput->flush();
        $cacheData->flush();

        // Files deleter helper.
        $deleteFiles = function ($files) {
            foreach ($files as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
        };

        // Clear files cache.
        if (isset($config->application->cache->cacheDir)) {
            $deleteFiles(glob($config->application->cache->cacheDir . '*'));
        }

        // Clear view cache.
        $deleteFiles(glob($config->application->view->compiledPath . '*'));

        // Clear metadata cache.
        if ($config->application->metadata && $config->application->metadata->metaDataDir) {
            $deleteFiles(glob($config->application->metadata->metaDataDir . '*'));
        }

        // Clear annotations cache.
        if ($config->application->annotations && $config->application->annotations->annotationsDir) {
            $deleteFiles(glob($config->application->annotations->annotationsDir . '*'));
        }

        // Clear assets.
        $this->_dependencyInjector->getShared('assets')->clear(true);
    }

    /**
     * Check if application is used from console.
     *
     * @return bool
     */
    public function isConsole()
    {
        return (php_sapi_name() == 'cli');
    }
}
