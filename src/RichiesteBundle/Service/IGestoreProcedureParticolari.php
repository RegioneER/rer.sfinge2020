<?php

namespace RichiesteBundle\Service;

interface IGestoreProcedureParticolari
{
    public function getProcedura();

    public function getRichiesta();

    public function getPianiDeiCosti();

    public function nuovaRichiesta($id_procedura, $opzioni = array());
	
}
