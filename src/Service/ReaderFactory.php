<?php

/**
 * @copyright   (c) 2014-16, Vrok
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace OsmTools\Service;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class ReaderFactory implements FactoryInterface
{
    /**
     * Inject the dependencies into the new service instance.
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array $options
     * @return Reader
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $reader = new Reader($container);

        $configuration = $container->get('Config');
        $reader->setOptions(isset($configuration['osm_tools'])
            ? $configuration['osm_tools']
            : []
        );

        return $reader;
    }
}
