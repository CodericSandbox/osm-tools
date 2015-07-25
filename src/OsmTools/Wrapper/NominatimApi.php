<?php
/**
 * @copyright   (c) 2014, Vrok
 * @license     http://customlicense CustomLicense
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace OsmTools\Wrapper;

/**
 * Queries a Nominatim instance for relations and places and their related places.
 *
 * @link http://wiki.openstreetmap.org/wiki/Nominatim
 */
class NominatimApi
{
    const OSM_TYPE_NODE     = 'node';
    const OSM_TYPE_WAY      = 'way';
    const OSM_TYPE_RELATION = 'relation';

    const NOMINATIM_TYPE_NODE     = 'N';
    const NOMINATIM_TYPE_WAY      = 'W';
    const NOMINATIM_RELATION_TYPE = 'R';

    /**
     * @var string
     */
    protected $nominatimUrl = null;

    /**
     * Converts the OSM type to the Nominatim notation.
     *
     * @param string $type
     * @return string
     */
    public function osmToNominatim($type)
    {
        switch ($type) {
            case self::OSM_TYPE_NODE:
                return self::NOMINATIM_TYPE_NODE;
            case self::OSM_TYPE_WAY:
                return self::NOMINATIM_TYPE_WAY;
            case self::OSM_TYPE_RELATION:
                return self::NOMINATIM_RELATION_TYPE;
            default:
                // @todo throw exception
                break;
        }
    }

    /**
     * Converts the Nominatim type to the OSM notation.
     *
     * @param string $type
     * @return string
     */
    public function nominatimToOsm($type)
    {
        switch ($type) {
            case self::NOMINATIM_TYPE_NODE:
                return self::OSM_TYPE_NODE;
            case self::NOMINATIM_TYPE_WAY:
                return self::OSM_TYPE_WAY;
            case self::NOMINATIM_RELATION_TYPE:
                return self::OSM_TYPE_RELATION;
            default:
                // @todo throw exception
                break;
        }
    }

    /**
     * Gets the URL under which the nominatim instance is reachable.
     *
     * @return string
     */
    public function getNominatimUrl()
    {
        return $this->nominatimUrl;
    }

    /**
     * Sets the URL under which the nominatim instance is reachable.
     *
     * @param string $url
     * @return self
     */
    public function setNominatimUrl($url)
    {
        $this->nominatimUrl = $url;
        return $this;
    }

    /**
     * Queries the configured Nominatim service for the address and related
     * points for the given OSM object.
     *
     * @param int $osmId
     * @param string $osmType
     * @return array    or null on error
     */
    public function queryOsmObject($osmId, $osmType)
    {
        if (empty($this->nominatimUrl)) {
            throw new Exception\RuntimeException(
                'Please set an URL to the Nominatim service before querying!');
        }

        // @todo this is a custom script as the default JSON export via
        // hierarchy.php does not output the related places, e.g.
        // http://nominatim.openstreetmap.org/hierarchy.php?osmid=62422&osmtype=R&format=json
        // @todo replace fixed locale, update script to return all names
        $url = rtrim($this->nominatimUrl, '/')
            .'/jsonhierarchy.php?osmid='.(int)$osmId
            .'&osmtype='.$this->osmToNominatim($osmType)
            .'&accept-language=de,en;q=0.5';

        $client = new \Zend\Http\Client($url, array(
            'maxredirects' => 3,
            'timeout'      => 1000,
        ));
        $response = $client->send();
        if (!$response->isOk()) {
            // @todo error log
            return null;
        }

        $json = $response->getBody();

        $data = json_decode($json, true);
        if (!empty($data['error'])) {
            // @todo log the error message, return error?
            return null;
        }

        return $data;
    }

    /**
     * Searches for related addresses for the given coordinates.
     *
     * @param float $lat
     * @param float $lon
     * @return array    or null on error
     */
    public function queryAddressSearch($lat, $lon)
    {
        if (empty($this->nominatimUrl)) {
            throw new Exception\RuntimeException(
                'Please set an URL to the Nominatim service before querying!');
        }

        // @todo this is a custom script as the default JSON search via
        // reverse.php does not output all related addresses
        // @todo replace fixed locale, update script to return all names
        $url = rtrim($this->nominatimUrl, '/')
            .'/jsonsearch.php?lon='.(float)$lon.'&lat='.(float)$lat
            .'&accept-language=de,en;q=0.5';

        $client = new \Zend\Http\Client($url, array(
            'maxredirects' => 3,
            'timeout'      => 1000,
        ));

        $response = $client->send();
        if (!$response->isOk()) {
            // @todo error log
            return null;
        }

        $json = $response->getBody();

        $data = json_decode($json, true);
        if (!empty($data['error'])) {
            // @todo log the error message, return error?
            return null;
        }

        return $data;
    }

    /**
     * Tries to load the region represented by the given OSM object and her
     * children for importing into the database.
     *
     * @param int $osmId
     * @param string $osmType
     * @return array    or null on error
     */
    public function loadRegion($osmId, $osmType)
    {
        $data = $this->queryOsmObject($osmId, $osmType);

        // no address no region
        if (!$data || empty($data['address']) || empty($data['address'][0])) {
            // @todo log warning? error message?
            return null;
        }

        $address = $data['address'][0];
        if (!$this->isAcceptedRegion($address)) {
            return null;
        }

        $region = array(
            'name'       => $address['localname'],
            'type'       => $address['type'],
            'osmType'    => $this->nominatimToOsm($address['osm_type']),
            'osmId'      => (int)$address['osm_id'],
            'rank'       => (int)$address['rank_address'],
            'adminLevel' => (int)$address['admin_level'],
            'children'   => array(),
        );

        if (empty($data['relatedPlaces'])) {
            return $region;
        }

        foreach($data['relatedPlaces'] as $place) {
            if (!$this->isAcceptedRegion($place)) {
                continue;
            }

            $region['children'][] = array(
                'name'       => $place['localname'],
                'type'       => $place['type'],
                'osmType'    => $this->nominatimToOsm($place['osm_type']),
                'osmId'      => (int)$place['osm_id'],
                'rank'       => (int)$place['rank_address'],
                'adminLevel' => (int)$place['admin_level'],
            );
        }

        return $region;
    }

    /**
     * Checks if the given place is a region we accept for our database.
     *
     * @param array $data
     * @return boolean
     */
    protected function isAcceptedRegion($data)
    {
        // Officially we only need admin_levels <= 9
        // Cities sometimes have admin_level 15, e.g. Hamburg relation-2618040
        // A city rank 16 may have admin_level 15, other cities rank 16 have
        // admin_level 2...
        if ($data['admin_level'] > 15 || $data['rank_address'] > 20) {
            return false;
        }

        if ($data['class'] !== 'boundary' && $data['class'] !== 'place') {
            return false;
        }

        $acceptedPlaces = array(
            'administrative', // used across all ranks
            'country',        // rank 4
            // @todo the german states where all nodes, is that always the case?
            //'state',          // 8
            'city',           // 16
            'village',        // 16
            'municipality',   // 16
            'town',           // 16
            'borough',        // 16
            'suburb',         // 20
        );

        if (!in_array($data['type'], $acceptedPlaces)) {
            return false;
        }

        // places that are no polygons (relations or closed ways) and low
        // in the hierarchy are ignored, we don't need the deeper levels and
        // we don't need them in the database as Points don't represent a
        // region
        if ($data['rank_address'] > 16 && $data['osm_type'] == self::NOMINATIM_TYPE_NODE) {
            return false;
        }

        // villages that are nodes are not expected to have useful subregions,
        // skip them here instead of removing them afterwards as empty regions
        // because there are many and it would take a long time to check all
        // for children
        if ($data['type'] == 'village' && $data['osm_type'] == self::NOMINATIM_TYPE_NODE) {
            return false;
        }

        // skip some ways that mostly cause name duplicates (are the borders of
        // a single village etc) and are no useful region
        // @todo skip all ways?
        if (($data['admin_level'] == 2 || $data['admin_level'] == 6 || $data['admin_level'] == 15)
            && $data['osm_type'] == self::NOMINATIM_TYPE_WAY
        ) {
            return false;
        }

        return true;
    }
}
