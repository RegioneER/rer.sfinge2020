<?php

namespace MonitoraggioBundle\DQL;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\Lexer;

class ControlloIGRUE extends FunctionNode {
    /**
     * @var string
     */
    protected $id_richiesta;

    /**
     * @var string
     */
    protected $controllo;

    public function getSql(SqlWalker $sqlWalker) {
        return 'controllo_igrue(' .
        $sqlWalker->walkStringPrimary($this->id_richiesta) .
        ',' .
        $sqlWalker->walkStringPrimary($this->controllo)
        . ')';
    }

    public function parse( Parser $parser )
    {
        $parser->Match( Lexer::T_IDENTIFIER );
        $parser->Match( Lexer::T_OPEN_PARENTHESIS );

        $this->id_richiesta = $parser->ArithmeticExpression();
        $parser->Match( Lexer::T_COMMA );

        $this->controllo = $parser->StringExpression();

        $parser->Match( Lexer::T_CLOSE_PARENTHESIS );
    }
}
