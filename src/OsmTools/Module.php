<?php

/**
 * @copyright   (c) 2014, Vrok
 * @license     http://customlicense CustomLicense
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace OsmTools;

use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;

/**
 * Module bootstrapping.
 */
class Module implements ConfigProviderInterface, ServiceProviderInterface
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
