<?php

/**
 * @copyright   (c) 2014-16, Vrok
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace OsmTools;

use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ControllerProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;

/**
 * Module bootstrapping.
 */
class Module implements
    ConfigProviderInterface,
    ControllerProviderInterface,
    ServiceProviderInterface
{
    /**
     * Returns the modules default configuration.
     *
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__.'/../../config/module.config.php';
    }

    /**
     * Return additional serviceManager config with closures that should not be
     * in the config files to allow caching of the complete configuration.
     *
     * @return array
     * @todo alle controller auf ihre dependencies prüfen und ggf direct injecten
     */
    public function getControllerConfig()
    {
        return [
            'factories' => [
                'OsmTools\Controller\Import' => function ($sm) {
                    return new Controller\ImportController($sm);
                },
                'OsmTools\Controller\Index' => function ($sm) {
                    return new Controller\IndexController($sm);
                },
                'OsmTools\Controller\Osmosis' => function ($sm) {
                    return new Controller\OsmosisController($sm);
                },
            ],
        ];
    }

    /**
     * Return additional serviceManager config with closures that should not be in the
     * config files to allow caching of the complete configuration.
     *
     * @return array
     */
    public function getServiceConfig()
    {
        return [
            'factories' => [
                'OsmTools\Wrapper\NominatimApi' => function ($sm) {
                    $config = $sm->get('Config');
                    $nominatim = new \OsmTools\Wrapper\NominatimApi();
                    if (!empty($config['osm_tools']['nominatim_url'])) {
                        $url = $config['osm_tools']['nominatim_url'];
                        $nominatim->setNominatimUrl($url);
                    }

                    return $nominatim;
                },
            ],
        ];
    }
}
