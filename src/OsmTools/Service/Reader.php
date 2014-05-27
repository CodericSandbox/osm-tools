<?php
/**
 * @copyright   (c) 2014, Vrok
 * @license     http://customlicense CustomLicense
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace OsmTools\Service;

use Doctrine\Common\Persistence\ObjectManager;
use OsmTools\Entity\Region as RegionEntity;
use OsmTools\Wrapper\NominatimApi;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Contains processes for creating and managing Region objects and their
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
        // for new regions the ID would be created by the hydrator by calling
        // setOsmId and setOsmType.
        // But if we do not set the ID here the hydrator does not check if an
        // record with that ID exists, tries to insert it and fails with duplicate entry
        $data['id'] = $data['osmType'].'-'.$data['osmId'];

        $region = $this->getRegionRepository()
                ->updateInstance(new RegionEntity(), $data);
        return $region;
    }

    /**
     * Queries Nominatim for the relation given by its ID and expects to
     * retrieve a region dataset (potentially including children).
     *
     * @param int $osmId
     * @return RegionEntity or null on error
     */
    public function importRegion($osmId, $osmType = NominatimApi::OSM_TYPE_RELATION)
    {
        $nominatim = $this->getNominatimApi();
        $data = $nominatim->loadRegion($osmId, $osmType);
        if (!$data) {
            return null;
        }

        // the children have no ID set, the hydrator would throw errors
        // but still not hydrate the children -> delay
        unset($data['children']);
        $region = $this->createRegion($data);

        // also flushes the entityManager
        $this->importChildren($region);

        return $region;
    }

    /**
     * Imports the children for the given Region.
     * Differs from importRegion as it does not update the parent record
     * as the address data received by NominatimApi::loadRegion has most times
     * type=>administrative instead of the correct city,suburb etc.
     *
     * @param \OsmTools\Entity\Region $region
     * @return null
     */
    public function importChildren(RegionEntity $region)
    {
        $nominatim = $this->getNominatimApi();

        $data = $nominatim->loadRegion($region->getOsmId(), $region->getOsmType());
        if ($data) {
            foreach($data['children'] as $childData) {
                $child = $this->createRegion($childData);
                $child->setParent($region);
            }
        }

        $region->setIsParsed(true);
        $this->getEntityManager()->persist($region);

        $this->getEntityManager()->flush();
        return $region->getChildren();
    }

    /**
     * Removes all Region entries from the database that are nodes (and thus
     * have no polygon) and have no children (that have a polygon).
     *
     * @return int  number of deleted regions
     */
    public function clearEmptyRegions()
    {
        $repo = $this->getRegionRepository();
        $em = $this->getEntityManager();

        $count = 0;
        $res = $repo->findBy(array('osmType' => NominatimApi::OSM_TYPE_NODE));
        while(count($res)) {
            $roundCount = 0;
            foreach($res as $region) {
                if (!count($region->getChildren())) {
                    $em->remove($region);
                    $count++;
                }
            }
            if (!$roundCount) {
                break;
            }

            $count += $roundCount;

            // update result for the next higher level
            $res = $repo->findBy(array('osmType' => NominatimApi::OSM_TYPE_NODE));
        }

        return $count;
    }

    /**
     * Searches for the smallest region (lowest in the hierarchy) that contains
     * the given coordinates.
     *
     * @param float $lat
     * @param float $lon
     * @return RegionEntity     or null on error/none found
     */
    public function searchRegion($lat, $lon)
    {
        $nominatim = $this->getNominatimApi();
        $data = $nominatim->queryAddressSearch($lat, $lon);
        if (!$data || empty($data['addresses'])) {
            return null;
        }

        $repository = $this->getRegionRepository();

        // look for regions matching the retrieved addresses in the database,
        // closest first
        foreach($data['addresses'] as $address) {
            if (empty($address['osm_id']) || empty($address['osm_type'])) {
                continue;
            }

            $region = $repository->findOneBy(array(
                'osmId'   => $address['osm_id'],
                'osmType' => $nominatim->nominatimToOsm($address['osm_type']),
            ));
            if ($region) {
                return $region;
            }
        }

        return null;
    }

    /**
     * Retrieve a configured instance of the Nominatim API.
     *
     * @return NominatimApi
     */
    public function getNominatimApi()
    {
        return $this->getServiceLocator()->get('OsmTools\Wrapper\NominatimApi');
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
     * Creates a new Wrapper instance with storageDir and osmosisCmd preset.
     *
     * @todo implement factory
     * @return \OsmTools\Osmosis\Wrapper
     */
    public function getOsmosisWrapper()
    {
        $wrapper = new \OsmTools\Osmosis\Wrapper();
        $wrapper->setStorageDir($this->getStorageDir());

        $config = $this->getServiceLocator()->get('config');
        if (isset($config['osm_tools'])
            && !empty($config['osm_tools']['osmosis_cmd'])
        )  {
            $wrapper->setCommand($config['osm_tools']['osmosis_cmd']);
        }

        return $wrapper;
    }

    /**
     * Creates a new RegionParser instance.
     *
     * @return \OsmTools\Osmosis\RegionParser
     */
    public function getRegionParser()
    {
        $parser = new \OsmTools\Osmosis\RegionParser($this->getEntityManager());
        return $parser;
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
