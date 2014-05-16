<?php
/**
 * @copyright   (c) 2014, Vrok
 * @license     http://customlicense CustomLicense
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace OsmTools\Service;

use Doctrine\Common\Persistence\ObjectManager;
use OsmTools\Entity\Region as RegionEntity;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Contains processes for creating and managing Translation objects and their
 * associated actions.
 */
class Reader implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /**
     * Directory where the xml and polygon files are stored.
     *
     * @var string
     */
    protected $storageDir = 'data/osmtools';

    /**
     * Creates a new Region from the given data.
     *
     * @param array $data
     * @return RegionEntity
     */
    public function createRegion(array $data)
    {
        $region = new RegionEntity();
        $this->getRegionRepository()->updateInstance($region, $data);
        $this->getEntityManager()->flush();
        return $region;
    }

    /**
     * Retrieve the repository for all regions.
     *
     * @return \OsmTools\Entity\RegionRepository
     */
    public function getRegionRepository()
    {
        $em = $this->getEntityManager();
        return $em->getRepository('OsmTools\Entity\Region');
    }

    /**
     * Retrieve the entity manager.
     *
     * @return ObjectManager
     */
    public function getEntityManager()
    {
        return $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    }

    /**
     * Allows to set multiple options at once.
     *
     * @todo support ArrayObject etc
     * @param array $options
     */
    public function setOptions(array $options)
    {
        if (isset($options['storage_dir'])) {
            $this->setStorageDir($options['storage_dir']);
        }
    }

    /**
     * Retrieve the current storage directory.
     *
     * @return string
     */
    public function getStorageDir()
    {
        return $this->storageDir;
    }

    /**
     * Sets a new storage directory.
     *
     * @param string $dir
     */
    public function setStorageDir($dir)
    {
        $this->storageDir = $dir;
    }
}
