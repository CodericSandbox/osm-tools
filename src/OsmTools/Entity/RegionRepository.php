<?php

/**
 * @copyright   (c) 2014, Vrok
 * @license     http://customlicense CustomLicense
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace OsmTools\Entity;

use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

/**
 * Holds functions to work with and manage regions.
 */
class RegionRepository extends NestedTreeRepository
{
    /**
     * Updates the given entity with the provided data.
     * Calls entityManager->persist.
     *
     * @param Region $instance
     * @param array  $formData
     *
     * @return Region
     */
    public function updateInstance(Region $instance, array $formData)
    {
        $hydrator = new \DoctrineModule\Stdlib\Hydrator\DoctrineObject(
                $this->getEntityManager());
        $object = $hydrator->hydrate($formData, $instance);
        $this->getEntityManager()->persist($object);

        return $object;
    }
}
