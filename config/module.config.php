<?php

/**
 * OsmTools config.
 */
return [
// <editor-fold defaultstate="collapsed" desc="asset_manager">
    // @todo JSTree vom CDN laden statt zu bundeln
    'asset_manager' => [
        'resolver_configs' => [
            'paths' => [
                __DIR__.'/../public',
            ],
        ],
    ],
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="console">
    'console' => [
        'router' => [
            'routes' => [
                'import-country' => [
                    'options' => [
                        'route'    => 'import country <name>',
                        'defaults' => [
                            'controller' => 'OsmTools\Controller\Osmosis',
                            'action'     => 'country',
                        ],
                    ],
                ],
                'import-region' => [
                    'options' => [
                        'route'    => 'import region <osmid>',
                        'defaults' => [
                            'controller' => 'OsmTools\Controller\Import',
                            'action'     => 'region',
                        ],
                    ],
                ],
                'import-finish' => [
                    'options' => [
                        'route'    => 'import finish',
                        'defaults' => [
                            'controller' => 'OsmTools\Controller\Import',
                            'action'     => 'finish',
                        ],
                    ],
                ],
            ],
        ],
    ],
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="controllers">
    'controllers' => [
        'invokables' => [
            'OsmTools\Controller\Import'  => 'OsmTools\Controller\ImportController',
            'OsmTools\Controller\Index'   => 'OsmTools\Controller\IndexController',
            'OsmTools\Controller\Osmosis' => 'OsmTools\Controller\OsmosisController',
        ],
    ],
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="doctrine">
    'doctrine' => [
        'driver' => [
            'osm_entities' => [
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => [__DIR__.'/../src/OsmTools/Entity'],
            ],
                        'orm_default' => [
                'drivers' => [
                    'OsmTools\Entity' => 'osm_entities',
                ],
            ],
        ],
        'eventmanager' => [
            'orm_default' => [
                'subscribers' => [
                    'Gedmo\Tree\TreeListener',
                ],
            ],
            'dql' => [
                'numeric_functions' => [
                    'DISTANCE' => 'OsmTools\Doctrine\DistanceFunction',
                ],
            ],
        ],
        'configuration' => [
            'orm_default' => [
                'numeric_functions' => [
                    'DISTANCE'       => 'OsmTools\Doctrine\DistanceFunction',
                    'DISTANCEFILTER' => 'OsmTools\Doctrine\DistanceFilter',
                ],
            ],
        ],
    ],
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="osm_tools">
    'osm_tools' => [
        // add to config/autoload/[osm_tools.]local.php
        // 'nominatim_url' => 'http://example.com/nominatim',

        // allows to specify relations that are not imported
        'relation_blacklist' => [
            1111111, // Deutschland, multilinestring border
        ],

        // @todo following options are probably outdated / unused
        'countries' => [
            'germany' => [
                'relation_id'    => 51477,
                'state_level'    => 4,
                'county_level'   => 6,
                'city_level'     => 8,
                'district_level' => 9,
            ],
        ],
        // allows to specify PBF files to use as inputFile for some relations
        // to optimize performance so only small planet files are evaluated
        // at least the countries should have an entry here
        'pbf_urls' => [
            51477   => 'http://ftp5.gwdg.de/pub/misc/openstreetmap/download.geofabrik.de/germany-latest.osm.pbf',
            62467   => 'http://download.geofabrik.de/europe/germany/sachsen-latest.osm.pbf',
            62504   => 'http://download.geofabrik.de/europe/germany/brandenburg-latest.osm.pbf',
            62761   => 'http://download.geofabrik.de/europe/germany/nordrhein-westfalen-latest.osm.pbf',
            62771   => 'http://download.geofabrik.de/europe/germany/niedersachsen-latest.osm.pbf',
            62782   => 'http://download.geofabrik.de/europe/germany/hamburg-latest.osm.pbf',
            51529   => 'http://download.geofabrik.de/europe/germany/schleswig-holstein-latest.osm.pbf',
            62341   => 'http://download.geofabrik.de/europe/germany/rheinland-pfalz-latest.osm.pbf',
            62607   => 'http://download.geofabrik.de/europe/germany/sachsen-anhalt-latest.osm.pbf',
            62611   => 'http://download.geofabrik.de/europe/germany/baden-wuerttemberg-latest.osm.pbf',
            62366   => 'http://download.geofabrik.de/europe/germany/thueringen-latest.osm.pbf',
            62372   => 'http://download.geofabrik.de/europe/germany/saarland-latest.osm.pbf',
            62650   => 'http://download.geofabrik.de/europe/germany/hessen-latest.osm.pbf',
            62422   => 'http://download.geofabrik.de/europe/germany/berlin.html',
            2145268 => 'http://download.geofabrik.de/europe/germany/bayern-latest.osm.pbf',
            62718   => 'http://download.geofabrik.de/europe/germany/bremen-latest.osm.pbf',
            28322   => 'http://download.geofabrik.de/europe/germany/mecklenburg-vorpommern-latest.osm.pbf',
        //28711 	Luxembourg
        //2202162 	France
        //49715 	Polska
        //
        ],
    ],
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="router">
    'router' => [
        'routes' => [
            'osmtools' => [
                'type'    => 'Literal',
                'options' => [
                    // Demonstration JSTree
                    'route'    => '/osmtools/',
                    'defaults' => [
                        'controller' => 'OsmTools\Controller\Index',
                        'action'     => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes'  => [
                    // Demonstration / Inspection of a stored Region
                    'region' => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'    => 'region/[:osmtype]/[:osmid]',
                            'defaults' => [
                                'action' => 'region',
                            ],
                        ],
                    ],
                    // AJAX handler for JSTree lazy loading
                    'jstree' => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'    => 'json/jstree',
                            'defaults' => [
                                'action' => 'jstree',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="service_manager">
    'service_manager' => [
        'factories' => [
            'OsmTools\Service\Reader' => 'OsmTools\Service\ReaderFactory',
        ],
    ],
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="view_manager">
    'view_manager' => [
        'template_path_stack' => [
            __DIR__.'/../view',
        ],
        'strategies' => [
            // for the AJAX Handler index/jstree to work
            'ViewJsonStrategy',
        ],
    ],
// </editor-fold>
];
