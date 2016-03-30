<?php

/**
 * @copyright   (c) 2014-16, Vrok
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace OsmTools\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ReaderFactory implements FactoryInterface
{
    /**
     * Creates an instance of the translation service, injects the dependencies.
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return Reader
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $reader = new Reader($sm);

        $configuration = $serviceLocator->get('Config');
        $reader->setOptions(isset($configuration['osm_tools'])
            ? $configuration['osm_tools']
            : []
        );

        return $reader;
    }
}