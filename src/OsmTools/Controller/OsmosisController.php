<?php
/**
 * @copyright   (c) 2014, Vrok
 * @license     http://customlicense CustomLicense
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace OsmTools\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class OsmosisController extends AbstractActionController
{
    public function countryAction()
    {
        $start = microtime(true);

        $config = $this->getServiceLocator()->get('Config');
        if (!isset($config['osm_tools'])) {
            die('Config key "osm_tools" not found');
        }
        if (!isset($config['osm_tools']['countries'])) {
            die('Config key "osm_tools[countries]" not found');
        }

        $country = $this->params('name');
        if (!isset($config['osm_tools']['countries'][$country])) {
            die('Config key "osm_tools[countries]['.$country.']" not found');
        }

        if (!isset($config['osm_tools']['countries'][$country]['relation_id'])) {
            die('Config key "osm_tools[countries]['.$country.'][relation_id]" not found');
        }
        $countryId = $config['osm_tools']['countries'][$country]['relation_id'];

        if (!isset($config['osm_tools']['pbf_urls'])) {
            die('Config key "osm_tools[pbf_urls]" not found');
        }
        $pbfUrls = $config['osm_tools']['pbf_urls'];

        if (!isset($pbfUrls[$countryId])) {
            die("No PBF-URL for $country ($countryId) found in osm_tools[pbf_urls]!");
        }
        $pbfUrl = $pbfUrls[$countryId];

        $reader = $this->getServiceLocator()->get('OsmTools\Service\Reader');
        $wrapper = $reader->getOsmosisWrapper();
        $pbfFile = $wrapper->getPbfFile($pbfUrl);
        $wrapper->setInputFile($pbfFile);

        $polyFile = $wrapper->getRelationPoly($countryId);

        if (!isset($config['osm_tools']['countries'][$country]['state_level'])) {
            die('Config key "osm_tools[countries]['.$country.'][state_level]" not found');
        }
        $stateLevel = $config['osm_tools']['countries'][$country]['state_level'];

        $regions = $wrapper->getAdministrativeBoundaries($stateLevel, $polyFile);

        $blacklist = array();
        if (isset($config['osm_tools']['relation_blacklist'])) {
            $blacklist = $config['osm_tools']['relation_blacklist'];
        }


        $parser = $reader->getRegionParser();
        foreach($regions as $region) {
            if (in_array($region['relationId'], $blacklist)) {
                continue;
            }
            if (isset($pbfUrls[$region['relationId']])) {
                $region['pbfUrl'] = $pbfUrls[$region['relationId']];
            }

            $region['parent'] = $countryId;

            $regionEntity = $parser->insertRegion($region);
            echo "\nState found: ".$regionEntity->getName();
        }

        $reader->getEntityManager()->flush();
        $end = microtime(true);
        $duration = round($end-$start);
        echo "\n took $duration seconds";
    }
}
