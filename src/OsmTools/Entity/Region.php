<?php
/**
 * @copyright   (c) 2014, Vrok
 * @license     http://customlicense CustomLicense
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace OsmTools\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Vrok\Doctrine\Entity;

/**
 * Defines a geographic region in a hierarchical context.
 *
 * @ORM\Entity
 * @ORM\Table(name="regions")
 * @ORM\Entity(repositoryClass="OsmTools\Entity\RegionRepository")
 */
class Region extends Entity
{
    /**
     * Initialize collection for lazy loading.
     */
    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

    /**
     * Retrieve the child level to query for when retrieving the child regions.
     *
     * @todo save in an extra field as JSON, inherit from the parent
     * @return int|null
     */
    public function getChildLevel()
    {
         switch($this->adminLevel) {
             case 2:  return 4;
             case 4:  return 6;
             case 6:  return 8;
             case 8:  return 9;
             default: return null;
         }
    }

// <editor-fold defaultstate="collapsed" desc="relationId">
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", nullable=false, unique=true)
     */
    protected $relationId;

    /**
     * Returns the regions OSM relation ID.
     *
     * @return integer
     */
    public function getRelationId()
    {
        return $this->relationId;
    }

    /**
     * Sets the regions OSM relation ID.
     *
     * @param integer $relationId
     * @return self
     */
    public function setRelationId($relationId)
    {
        $this->relationId = $relationId;
        return $this;
    }
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="name">
    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=false, unique=false)
     */
    protected $name;

    /**
     * Returns the modules name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the language name.
     *
     * @param string $name
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="parent">
    /**
     * @ORM\ManyToOne(targetEntity="Region", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $parent;

    /**
     * Retrieve the region this current region is part of.
     *
     * @return Region
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Sets the parent region.
     *
     * @param Region $parent
     * @return self
     */
    public function setParent(Region $parent = null)
    {
        if ($this->parent && $this->parent !== $parent) {
            $this->parent->removeChild($this);
        }

        $this->parent = $parent;
        if ($parent) {
            $this->parent->addChild($this);
        }

        return $this;
    }
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="children">
    /**
     * @ORM\OneToMany(targetEntity="Region", mappedBy="parent")
     * */
    protected $children;

    /**
     * Retrieve the regions that are part of this current region.
     *
     * @return Collection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Adds the given Region to the collection.
     * Called by $region->setParent to keep the collection consistent.
     *
     * @param Region $child
     * @return boolean  false if the Region was already in the collection,
     *  else true
     */
    public function addChild(Region $child)
    {
        if ($this->children->contains($child)) {
            return false;
        }
        return $this->children->add($child);
    }

    /**
     * Removes the given Region from the collection.
     * Called by $region->setParent to keep the collection consistent.
     *
     * @param Region $child
     * @return boolean     true if the Region was in the collection and was
     *     removed, else false
     */
    public function removeChild(Region $child)
    {
        return $this->children->removeElement($child);
    }

    /**
     * Proxies to addChild for multiple elements.
     *
     * @param Collection $children
     */
    public function addChildren($children)
    {
        foreach($children as $child) {
            $this->addChild($child);
        }
    }

    /**
     * Proxies to removeChild for multiple elements.
     *
     * @param Collection $children
     */
    public function removeChildren($children)
    {
        foreach($children as $child) {
            $this->removeChild($child);
        }
    }
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="adminLevel">
    /**
     * @ORM\Column(type="integer", nullable=false, unique=false)
     */
    protected $adminLevel;

    /**
     * Returns the regions OSM admin_level.
     *
     * @return integer
     */
    public function getAdminLevel()
    {
        return $this->adminLevel;
    }

    /**
     * Sets the regions OSM admin_level.
     *
     * @param integer $adminLevel
     * @return self
     */
    public function setAdminLevel($adminLevel)
    {
        $this->adminLevel = $adminLevel;
        return $this;
    }
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="isParsed">
    /**
     * @var bool
     * @ORM\Column(type="boolean", nullable=false)
     */
    protected $isParsed = false;

    /**
     * Returns wether the region is completely parsed.
     *
     * @return bool
     */
    public function getIsParsed()
    {
        return $this->isParsed;
    }

    /**
     * Sets wether the regions is completely parsed.
     *
     * @param bool $isParsed
     * @return self
     */
    public function setIsParsed($isParsed)
    {
        $this->isParsed = (bool) $isParsed;
        return $this;
    }
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="pbfUrl">
    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $pbfUrl;

    /**
     * Returns the pbf URL.
     *
     * @return string
     */
    public function getPbfUrl()
    {
        return $this->pbfUrl;
    }

    /**
     * Sets the pbf URL.
     *
     * @param string $pbfUrl
     * @return self
     */
    public function setPbfUrl($pbfUrl)
    {
        $this->pbfUrl = $pbfUrl;
        return $this;
    }
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="polygonFile">
    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $polygonFile;

    /**
     * Returns the polygon file name.
     *
     * @return string
     */
    public function getPolygonFile()
    {
        return $this->polygonFile;
    }

    /**
     * Sets the polygon file name.
     *
     * @param string $polygonFile
     * @return self
     */
    public function setPolygonFile($polygonFile)
    {
        $this->polygonFile = $polygonFile;
        return $this;
    }
// </editor-fold>
}
