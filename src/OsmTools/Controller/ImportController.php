<?php

/**
 * @copyright   (c) 2014-16, Vrok
 * @license     http://customlicense CustomLicense
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace OsmTools\Controller;

use Vrok\Mvc\Controller\AbstractActionController;

/**
 * Controller for console routes.
 */
class ImportController extends AbstractActionController
{
    /**
     * Imports the given region and their children into the database.
     * Queries a Nominatim service. Expects the given OSM Id to belong to
     * a relation!
     */
    public function regionAction()
    {
        $osmId  = $this->params('osmid');
        $reader = $this->getServiceLocator()->get('OsmTools\Service\Reader');
        echo "\nimporting $osmId";
        $region = $reader->importRegion($osmId);
        if (!$region) {
            die("\nNo region returned for the given OSM ID!\n");
        }

        echo "\nimported ".$region->getName().' with '
            .count($region->getChildren())." children\n";
    }

    /**
     * Finishes the import of all regions in the database by iterating over all
     * that are not yet marked as "parsed" and fetching their children. Does so
     * for all siblings untill all regions are marked "parsed".
     */
    public function finishAction()
    {
        $start = microtime(true);

        $reader = $this->getServiceLocator()->get('OsmTools\Service\Reader');
        $em     = $reader->getEntityManager();
        $expr   = $em->getExpressionBuilder();
        $qb     = $reader->getRegionRepository()->createQueryBuilder('r');
        $qb->where($expr->eq('r.isParsed', '0'));

        $count = 0;
        while ($res = $reader->getRegionRepository()->findBy(
            ['isParsed' => false], null, 150)
        ) {
            foreach ($res as $region) {
                $imported = $reader->importChildren($region);
                if ($imported) {
                    echo "\nimported ".count($region->getChildren())
                        .' children for '.$region->getName();
                    ++$count;
                }
            }

            // we select only 150 regions and free them & their imported children
            // here to prevent hitting the memory limit
            $em->clear();
        }

        $end      = microtime(true);
        $duration = round($end - $start);
        echo "\nloaded $count regions in $duration seconds";

        $deleted = $reader->clearEmptyRegions();
        echo "\ndeleted $deleted empty regions\n";
    }
}
