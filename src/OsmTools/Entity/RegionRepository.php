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
 */
class RegionRepository extends EntityRepository
{
    use \Vrok\Doctrine\Traits\GetById;
}
