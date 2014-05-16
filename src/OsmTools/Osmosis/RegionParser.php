<?php
/**
 * @copyright   (c) 2014, Vrok
 * @license     http://customlicense CustomLicense
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace OsmTools\Osmosis;

use Doctrine\Common\Persistence\ObjectManager;

/**
 * Provides functions to read the XML retrieved from osmosis and store the
 * data in the database.
 */
class RegionParser
{
    /**
     * Entity Manager instance
     *
     * @var ObjectManager
     */
    protected $entityManager = null;

    /**
     * Class constructor - saves the provided entity manager.
     *
     * @param ObjectManager $entityManager
     */
    public function __construct(ObjectManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Saves the given relation specification as Region in the database.
     *
     * @param array $relation
     */
    public function insertRegion($relation)
    {
        $name = isset($relation['names']['name:de'])
            ? $relation['names']['name:de']
            : (isset($relation['names']['name'])
                ? $relation['names']['name']
                : '--');

        $data = array(
            'name'       => $name,
            'adminLevel' => $relation['adminLevel'],
        );

        if (!empty($relation['parent'])) {
            $data['parent'] = $relation['parent'];
        }
        if (!empty($relation['pfbUrl'])) {
            $data['pfbUrl'] = $relation['pfbUrl'];
        }
        if (!empty($relation['polygonFile'])) {
            $data['polygonFile'] = $relation['polygonFile'];
        }

        $region = new \OsmTools\Entity\Region();
        $repository = $this->entityManager
                ->getRepository('OsmTools\Entity\Region');
        $repository->updateInstance($region, $data);

        return $region;
    }
}
