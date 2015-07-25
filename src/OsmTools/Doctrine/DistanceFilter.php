<?php

/**
 * @copyright   (c) 2014, Vrok
 * @license     http://customlicense CustomLicense
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace OsmTools\Doctrine;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

/**
 * Filters the records by their distance (in km) to the given coordinates:
 * DISTANCEFILTER($fromLat, $fromLng, $toLat, $toLng, $maxDist).
 *
 * Uses a bounding rectangle to make use of indices on the lon/lat columns and avoid a
 * full table scan with the complex calculations.
 *
 * @link http://www.arubin.org/files/geo_search.pdf
 */
class DistanceFilter extends FunctionNode
{
    protected $fromLat = 0;
    protected $fromLng = 0;
    protected $toLat   = 0;
    protected $toLng   = 0;
    protected $dist    = 0;

    /**
     * {@inheritdoc}
     */
    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);

        $this->fromLat = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_COMMA);

        $this->fromLng = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_COMMA);

        $this->toLat = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_COMMA);

        $this->toLng = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_COMMA);

        $this->dist = $parser->ArithmeticPrimary();

        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    /**
     * {@inheritdoc}
     */
    public function getSql(SqlWalker $sqlWalker)
    {
        $earthDiameter = 6372.8;

        // order is important, this adds the parameters to the unnamed parameter list!
        $tolon1   = $this->toLng->dispatch($sqlWalker);
        $fromLon1 = $this->fromLng->dispatch($sqlWalker);
        $dist1    = $this->dist->dispatch($sqlWalker);
        $fromLat1 = $this->fromLat->dispatch($sqlWalker);
        $fromLon2 = $this->fromLng->dispatch($sqlWalker);
        $dist2    = $this->dist->dispatch($sqlWalker);
        $fromLat2 = $this->fromLat->dispatch($sqlWalker);
        $toLat1   = $this->toLat->dispatch($sqlWalker);
        $fromLat3 = $this->fromLat->dispatch($sqlWalker);
        $dist3    = $this->dist->dispatch($sqlWalker);
        $fromLat4 = $this->fromLat->dispatch($sqlWalker);
        $dist4    = $this->dist->dispatch($sqlWalker);
        $fromLat5 = $this->fromLat->dispatch($sqlWalker);
        $toLat2   = $this->toLat->dispatch($sqlWalker);
        $fromLat6 = $this->fromLat->dispatch($sqlWalker);
        $toLat3   = $this->toLat->dispatch($sqlWalker);
        $fromLon3 = $this->fromLng->dispatch($sqlWalker);
        $tolon2   = $this->toLng->dispatch($sqlWalker);
        $dist5    = $this->dist->dispatch($sqlWalker);

        $sql = "($tolon1 BETWEEN $fromLon1 - $dist1 / ABS(COS(RADIANS($fromLat1)) * 111.045)"
            ." AND $fromLon2 + $dist2 / ABS(COS(RADIANS($fromLat2)) * 111.045) AND "
            ."$toLat1 BETWEEN $fromLat3 - ($dist3 / 111.045) AND $fromLat4 + ($dist4 / 111.045)"
            ."AND ($earthDiameter * 2 * ASIN(SQRT(POWER(SIN(($fromLat5 - ABS($toLat2))"
            ." * PI() / 180 / 2), 2) + COS($fromLat6 * PI() / 180) * COS(ABS($toLat3)"
            ." * PI() / 180) * POWER(SIN(($fromLon3 - $tolon2) * PI() / 180 / 2), 2)))) < $dist5)";

        return $sql;
    }
}
