<?php
/**
 * @copyright   (c) 2014, Vrok
 * @license     http://customlicense CustomLicense
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace OsmTools\Entity;

use Vrok\Doctrine\EntityRepository;

/**
 * Holds functions to work with and manage regions.
 * @todo necessary? only used function is updateInstance in Service\Reader,
 * replace with the direct call to the DoctrineModule\Hydrator
 */
class RegionRepository extends EntityRepository
{
}
