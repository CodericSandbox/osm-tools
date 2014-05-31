<?php
/**
 * @copyright   (c) 2014, Vrok
 * @license     http://customlicense CustomLicense
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace OsmTools\Entity;

use \Gedmo\Tree\Entity\Repository\NestedTreeRepository;

/**
 * Holds functions to work with and manage regions.
 */
class RegionRepository extends NestedTreeRepository
{
    /**
     * Updates the given entity with the provided data.
     * Calls entityManager->persist.
     *
     * @param Entity $instance
     * @param array $formData
     * @return Entity
     */
    public function updateInstance(Entity $instance, array $formData)
    {
        $hydrator = new \DoctrineModule\Stdlib\Hydrator\DoctrineObject(
                $this->getEntityManager());
        $object = $hydrator->hydrate($formData, $instance);
        $this->getEntityManager()->persist($object);
        return $object;
    }
}
