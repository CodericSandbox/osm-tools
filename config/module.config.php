<?php
/**
 * OsmTools config
 */
return array(
    'doctrine' => array(
        'driver' => array(
            'osm_entities' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => array(__DIR__ . '/../src/OsmTools/Entity')
            ),

            'orm_default' => array(
                'drivers' => array(
                    'OsmTools\Entity' => 'osm_entities'
                ),
            ),
        ),
    ),

    'service_manager' => array(
        'invokables' => array(
        ),
        'factories' => array(
            'OsmTools\Service\Reader' => 'OsmTools\Service\ReaderFactory',
        ),
    ),

    // Placeholder for console routes
    'console' => array(
        'router' => array(
            'routes' => array(
            ),
        ),
    ),
);
