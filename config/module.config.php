<?php
/**
 * OsmTools config
 */
return array(
// <editor-fold defaultstate="collapsed" desc="asset_manager">
    // @todo JSTree vom CDN laden statt zu bundeln
    'asset_manager' => array(
        'resolver_configs' => array(
            'paths' => array(
                __DIR__ . '/../public',
            ),
        ),
    ),
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="console">
    'console' => array(
        'router' => array(
            'routes' => array(
                'import-country' => array(
                    'options' => array(
                        'route' => 'import country <name>',
                        'defaults' => array(
                            'controller' => 'OsmTools\Controller\Osmosis',
                            'action' => 'country',
                        ),
                    ),
                ),
                'import-region' => array(
                    'options' => array(
                        'route' => 'import region <osmid>',
                        'defaults' => array(
                            'controller' => 'OsmTools\Controller\Import',
                            'action' => 'region',
                        ),
                    ),
                ),
                'import-finish' => array(
                    'options' => array(
                        'route' => 'import finish',
                        'defaults' => array(
                            'controller' => 'OsmTools\Controller\Import',
                            'action' => 'finish',
                        ),
                    ),
                ),
            ),
        ),
    ),
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="controllers">
    'controllers' => array(
        'invokables' => array(
            'OsmTools\Controller\Import' => 'OsmTools\Controller\ImportController',
            'OsmTools\Controller\Index' => 'OsmTools\Controller\IndexController',
            'OsmTools\Controller\Osmosis' => 'OsmTools\Controller\OsmosisController',
        ),
    ),
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="doctrine">
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
        'eventmanager' => array(
            'orm_default' => array(
                'subscribers' => array(
                    'Gedmo\Tree\TreeListener',
                ),
            ),
            'dql' => array(
                'numeric_functions' => array(
                    'DISTANCE' => 'OsmTools\Doctrine\DistanceFunction',
                ),
            ),
        ),
        'configuration' => array(
            'orm_default' => array(
                'numeric_functions' => array(
                    'DISTANCE' => 'OsmTools\Doctrine\DistanceFunction',
                    'DISTANCEFILTER' => 'OsmTools\Doctrine\DistanceFilter',
                ),
            ),
        ),
    ),
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="osm_tools">
    'osm_tools' => array(
        // add to config/autoload/[osm_tools.]local.php
        // 'nominatim_url' => 'http://example.com/nominatim',

        // allows to specify relations that are not imported
        'relation_blacklist' => array(
            1111111, // Deutschland, multilinestring border
        ),

        // @todo following options are probably outdated / unused
        'countries' => array(
            'germany' => array(
                'relation_id' => 51477,
                'state_level' => 4,
                'county_level' => 6,
                'city_level' => 8,
                'district_level' => 9,
            ),
        ),
        // allows to specify PBF files to use as inputFile for some relations
        // to optimize performance so only small planet files are evaluated
        // at least the countries should have an entry here
        'pbf_urls' => array(
            51477 => 'http://ftp5.gwdg.de/pub/misc/openstreetmap/download.geofabrik.de/germany-latest.osm.pbf',
            62467 => 'http://download.geofabrik.de/europe/germany/sachsen-latest.osm.pbf',
            62504 => 'http://download.geofabrik.de/europe/germany/brandenburg-latest.osm.pbf',
            62761 => 'http://download.geofabrik.de/europe/germany/nordrhein-westfalen-latest.osm.pbf',
            62771 => 'http://download.geofabrik.de/europe/germany/niedersachsen-latest.osm.pbf',
            62782 => 'http://download.geofabrik.de/europe/germany/hamburg-latest.osm.pbf',
            51529 => 'http://download.geofabrik.de/europe/germany/schleswig-holstein-latest.osm.pbf',
            62341 => 'http://download.geofabrik.de/europe/germany/rheinland-pfalz-latest.osm.pbf',
            62607 => 'http://download.geofabrik.de/europe/germany/sachsen-anhalt-latest.osm.pbf',
            62611 => 'http://download.geofabrik.de/europe/germany/baden-wuerttemberg-latest.osm.pbf',
            62366 => 'http://download.geofabrik.de/europe/germany/thueringen-latest.osm.pbf',
            62372 => 'http://download.geofabrik.de/europe/germany/saarland-latest.osm.pbf',
            62650 => 'http://download.geofabrik.de/europe/germany/hessen-latest.osm.pbf',
            62422 => 'http://download.geofabrik.de/europe/germany/berlin.html',
            2145268 => 'http://download.geofabrik.de/europe/germany/bayern-latest.osm.pbf',
            62718 => 'http://download.geofabrik.de/europe/germany/bremen-latest.osm.pbf',
            28322 => 'http://download.geofabrik.de/europe/germany/mecklenburg-vorpommern-latest.osm.pbf',
        //28711 	Luxembourg
        //2202162 	France
        //49715 	Polska
        //
        ),
    ),
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="router">
    'router' => array(
        'routes' => array(
            'osmtools' => array(
                'type' => 'Literal',
                'options' => array(
                    // Demonstration JSTree
                    'route' => '/osmtools/',
                    'defaults' => array(
                        'controller' => 'OsmTools\Controller\Index',
                        'action' => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    // Demonstration / Inspection of a stored Region
                    'region' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => 'region/[:osmtype]/[:osmid]',
                            'defaults' => array(
                                'action' => 'region',
                            ),
                        ),
                    ),
                    // AJAX handler for JSTree lazy loading
                    'jstree' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => 'json/jstree',
                            'defaults' => array(
                                'action' => 'jstree',
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="service_manager">
    'service_manager' => array(
        'factories' => array(
            'OsmTools\Service\Reader' => 'OsmTools\Service\ReaderFactory',
        ),
    ),
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="view_manager">
    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
        'strategies' => array(
            // for the AJAX Handler index/jstree to work
            'ViewJsonStrategy',
        ),
    ),
// </editor-fold>
);
