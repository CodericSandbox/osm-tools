<?php
/**
 * @copyright   (c) 2014, Vrok
 * @license     http://customlicense CustomLicense
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace OsmTools\Osmosis;

/**
 * Allows calls to the osmosis tool to extract data from planet files.
 *
 * @link http://wiki.openstreetmap.org/wiki/Osmosis
 */
class Wrapper
{
    /**
     * Directory where extracts and polygons are stored.
     *
     * @var string
     */
    protected $storageDir = '/tmp';

    /**
     * Command line to call osmosis.
     *
     * @var string
     */
    protected $cmd = '';

    /**
     * Path & filename to the planetfile to use for the calls.
     *
     * @var string
     */
    protected $inputFile = '';

    /**
     * Sets a default storage directory.
     */
    public function __construct()
    {
        $this->storageDir = sys_get_temp_dir();
    }

    /**
     * Sets a new storage dir.
     *
     * @param string $dir
     */
    public function setStorageDir($dir)
    {
        $this->storageDir = $dir;
    }

    /**
     * Sets a new osmosis command line.
     *
     * @param string $cmd
     */
    public function setCommand($cmd)
    {
        $this->cmd = $cmd;
    }

    /**
     * Sets a new planet file to use.
     *
     * @param string $filename
     */
    public function setInputFile($filename)
    {
        $this->inputFile = $filename;
    }

    /**
     * Retrieves the XML object holding the date extracted from the current
     * input file.
     * The result is cached in the storage directory.
     *
     * @param string $paramString   all osmosis parameters for data manipulation,
     *     input & output are automatically added
     * @param bool $reload  if true an existing cached result will be overwritten
     * @return SimpleXMLElement     or false on failure
     */
    public function getXml($paramString, $reload = false)
    {
        $filename = $this->storageDir . '/osmosis-' . md5($paramString) . '.xml';

        if ($reload || !file_exists($filename)) {
            $cmd = $this->cmd . ' --read-pbf file="' . $this->inputFile . '" '
                    . $paramString . ' --wx file="' . $filename . '"';

            $output = null;
            $return = null;
            exec($cmd, $output, $return);

            if ($return != 0 || !file_exists($filename)) {
                // @todo logging, exception?
                //echo 'Osmosis returned '.$return.' with outFile '.$filename;
                return false;
            }
        }

        return simplexml_load_file($filename);
    }

    /**
     * Retrieves all relations that are tagged as boundary=administrative and
     * have the given admin_level.
     * Optionally only relations within the given polygon are returned.
     *
     * @param int $adminLevel
     * @param string $inPoly    filename of the bounding polygon
     * @return array
     */
    public function getAdministrativeBoundaries($adminLevel, $inPoly = null)
    {
        $params = '--tf accept-relations admin_level=' . $adminLevel
                . ' --used-way --used-node --tf reject-nodes --tf reject-ways';
        if ($inPoly) {
            $params = "--bp file=$inPoly completeRelations=yes $params";
        }

        $xml = $this->getXml($params);

        $regions = array();
        $relations = $xml->xPath('/osm/relation');
        foreach ($relations as $relation) {
            $region = array(
                'relationId' => (int) $relation->attributes()->id,
                'adminLevel' => $adminLevel,
                'names' => array(),
            );

            $names = $relation->xPath('tag[starts-with(@k, \'name\')]');
            foreach ($names as $name) {
                $region['names'][(string) $name->attributes()->k] = (string) $name->attributes()->v;
            }
            $regions[] = $region;
        }

        return $regions;
    }

    /**
     * Retrieves the polygon file representing the outline of the given relation.
     * Searches the storage directory before querying the API.
     *
     * @param int $relationId
     * @param bool $reload  if true an existing file in the storage dir is overwritten
     * @return string
     */
    public function getRelationPoly($relationId, $reload = false)
    {
        $filename = $this->storageDir . "/relation-$relationId.poly";

        if ($reload || !file_exists($filename)) {
            $poly = $this->loadRelationPoly($relationId);
            file_put_contents($filename, $poly);
        }

        return $filename;
    }

    /**
     * Uses the OSM API to retrieve the polygon representation of the given
     * relation. This can be used to use as a bounding polygon (instead of a box).
     * Do not stress this service, use caching!
     *
     * @todo error handling, use ZF http classes?
     * @link http://wiki.openstreetmap.org/wiki/Osmosis/Polygon_Filter_File_Format
     * @param type $relationId
     * @return type
     */
    public function loadRelationPoly($relationId)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_URL,
            'http://osm102.openstreetmap.fr/~jocelyn/polygons/get_poly.py?params=0&id='
                . $relationId
        );

        $poly = curl_exec($curl);
        curl_close($curl);

        return $poly;
    }
}
