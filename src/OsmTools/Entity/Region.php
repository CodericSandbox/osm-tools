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
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Defines a geographic region in a hierarchical context.
 *
 * @ORM\Entity
 * @Gedmo\Tree(type="nested")
 * @ORM\Table(name="regions", uniqueConstraints={@ORM\UniqueConstraint(name="type_id", columns={"osmType", "osmId"})})
 * @ORM\Entity(repositoryClass="OsmTools\Entity\RegionRepository")
 */
class Region
{
    /**
     * Initialize collection for lazy loading.
     */
    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

    /**
     * Used for debugging, e.g. in Doctrines exception messages.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getName().' ('.$this->getOsmType().'-'.$this->getOsmId().')';
    }

// <editor-fold defaultstate="collapsed" desc="id">
    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=255, nullable=false, unique=true)
     */
    protected $id;

    /**
     * Returns the regions ID.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the regions ID.
     *
     * @param string $id
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="osmId">
    /**
     * @ORM\Column(type="bigint", nullable=false)
     */
    protected $osmId;

    /**
     * Returns the regions OSM ID.
     *
     * @return string
     */
    public function getOsmId()
    {
        return $this->osmId;
    }

    /**
     * Sets the regions OSM ID.
     *
     * @param string $osmId
     * @return self
     */
    public function setOsmId($osmId)
    {
        $this->osmId = $osmId;
        $this->setId($this->osmType.'-'.$this->osmId);
        return $this;
    }
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="osmType">
    /**
     * @ORM\Column(type="string", length=20, nullable=false)
     */
    protected $osmType;

    /**
     * Returns the regions OSM type.
     *
     * @return string
     */
    public function getOsmType()
    {
        return $this->osmType;
    }

    /**
     * Sets the regions OSM type.
     *
     * @param string $osmType
     * @return self
     */
    public function setOsmType($osmType)
    {
        $this->osmType = $osmType;
        $this->setId($this->osmType.'-'.$this->osmId);
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
// <editor-fold defaultstate="collapsed" desc="rank">
    /**
     * @ORM\Column(type="integer", nullable=false, unique=false)
     */
    protected $rank;

    /**
     * Returns the regions Nominatim address rank.
     *
     * @return integer
     */
    public function getRank()
    {
        return $this->rank;
    }

    /**
     * Sets the regions Nominatim address rank.
     *
     * @param integer $rank
     * @return self
     */
    public function setRank($rank)
    {
        $this->rank = $rank;
        return $this;
    }
// </editor-fold>
// // <editor-fold defaultstate="collapsed" desc="adminLevel">
    /**
     * @ORM\Column(type="integer", nullable=false)
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
// <editor-fold defaultstate="collapsed" desc="type">
    /**
     * @ORM\Column(type="string", length=20, nullable=false)
     */
    protected $type;

    /**
     * Returns the regions Nominatim defined type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Sets the regions Nominatim defined type.
     *
     * @param string $type
     * @return self
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="parent">
    /**
     * @var Region
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="Region", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE"),
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
        // this parent is already set, avoid infinite loops by calling
        // parent->addChild thus calling parent->child->setParent
        if ($this->parent === $parent) {
            return $this;
        }

        if ($this->parent) {
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
     * @ORM\OneToMany(targetEntity="Region", mappedBy="parent", fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"name" = "ASC"})
     */
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
        $child->setParent($this);
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
        if ($this->children->contains($child)) {
            $child->setParent(null);
        }
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

    /**
     * Retúrns true if the region has children, else false.
     *
     * @return bool
     */
    public function hasChildren()
    {
        return (bool)$this->getChildCount();
    }

    /**
     * Returns the number of children this region has.
     *
     * @return int
     */
    public function getChildCount()
    {
        return $this->children->count();
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
// <editor-fold defaultstate="collapsed" desc="nested set properties">
    /**
     * The root column must use the same data type as the ID column!
     *
     * @Gedmo\TreeRoot
     * @ORM\Column(name="root", type="string", length=255, nullable=true)
     */
    protected $root;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(name="lvl", type="integer")
     */
    protected $lvl;


    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(name="lft", type="integer")
     */
    protected $lft;

    /**
     * Return the nested set left limit.
     * We need this public for complex queries that include all (not only direct) children.
     *
     * @return int
     */
    public function getLft()
    {
        return $this->lft;
    }

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(name="rgt", type="integer")
     */
    protected $rgt;

    /**
     * Return the nested set right limit.
     * We need this public for complex queries that include all (not only direct) children.
     *
     * @return int
     */
    public function getRgt()
    {
        return $this->rgt;
    }

// </editor-fold>
}
