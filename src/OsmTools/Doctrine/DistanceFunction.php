<?php
/**
 * @author Konstantin.Myakshin <koc-dp@yandex.ru>
 * @link https://gist.github.com/Koc/3016704
 */

namespace OsmTools\Doctrine;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

/**
 * Calculates the distance of the given coordinate pairs in kilometers using
 * the haversine formulae.
 *
 * DISTANCE(LatitudeFrom, LongitudetFrom, LatitudeTo, LongitudeTo)
 *
 * @author Konstantin.Myakshin <koc-dp@yandex.ru>
 */
class DistanceFunction extends FunctionNode
{
    protected $fromLat = 0;
    protected $fromLng = 0;
    protected $toLat   = 0;
    protected $toLng   = 0;

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

        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker)
    {
        $earthDiameter = 6372.8;

        $sql = '('.$earthDiameter.' * 2 * ASIN(SQRT(POWER(SIN(('
                .$this->fromLat->dispatch($sqlWalker).' - ABS('
                .$this->toLat->dispatch($sqlWalker).')) * PI() / 180 / 2), 2) + '
                .'COS('.$this->fromLat->dispatch($sqlWalker).' * PI() / 180) * COS(ABS('
                .$this->toLat->dispatch($sqlWalker).') * PI() / 180) * POWER(SIN(('
                .$this->fromLng->dispatch($sqlWalker).' - '
                .$this->toLng->dispatch($sqlWalker).') * PI() / 180 / 2), 2))))';

        return $sql;
    }
}
