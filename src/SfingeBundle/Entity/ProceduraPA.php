<?php

namespace SfingeBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;


/**
 * @ORM\Entity(repositoryClass="SfingeBundle\Repository\ProceduraPARepository")
 */
class ProceduraPA extends Bando{

    const TIPO = 'PROCEDURA_PA';

    public function getTipo(){
        return self::TIPO;
    }

    public function isProceduraParticolare(){
        return false;
    }
}