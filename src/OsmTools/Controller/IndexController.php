<?php

/**
 * @copyright   (c) 2014-16, Vrok
 * @license     http://customlicense CustomLicense
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace OsmTools\Controller;

use Vrok\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

/**
 * Contains the Ajax handler for the JSTree requests and demonstration actions.
 */
class IndexController extends AbstractActionController
{
    /**
     * Shows a JSTree instance for demonstration / inspecting the stored regions.
     */
    public function indexAction()
    {
    }

    /**
     * Shows the details for the region given via osm ID/type.
     *
     * @return ViewModel
     */
    public function regionAction()
    {
        $reader = $this->getServiceLocator()->get('OsmTools\Service\Reader');
        $repo   = $reader->getRegionRepository();
        $region = $repo->findOneBy([
            'osmId'   => $this->params('osmid'),
            'osmType' => $this->params('osmtype'),
        ]);

        return $this->createViewModel(['region' => $region]);
    }

    /**
     * Retrieves all subregions for the region given via GET.
     * Returns JSON formatted to be used by JSTree.
     *
     * @link http://www.jstree.com/
     *
     * @return JsonModel
     */
    public function jstreeAction()
    {
        $parent = $this->params()->fromQuery('parent', null);
        $reader = $this->getServiceLocator()->get('OsmTools\Service\Reader');
        $repo   = $reader->getRegionRepository();

        $regions = $repo->findBy([
            // force NULL as nothing would be found for the empty string
            'parent' => empty($parent) ? null : $parent,
        ]);

        $result = [];
        foreach ($regions as $region) {
            $result[] = [
                'id'    => $region->getId(),
                'text'  => $region->getName(),
                'state' => [
                    'disabled' => false,
                    'loaded'   => !$region->hasChildren(),
                    'opened'   => false,
                    'selected' => false,
                ],
            ];
        }

        return new JsonModel($result);
    }
}
