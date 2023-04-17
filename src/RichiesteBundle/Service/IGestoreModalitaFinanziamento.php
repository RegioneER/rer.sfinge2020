<?php

namespace RichiesteBundle\Service;

use Doctrine\Common\Collections\ArrayCollection;
use SfingeBundle\Entity\Procedura;

interface IGestoreModalitaFinanziamento {

	public function generaModalitaFinanziamentoRichiesta($id_richiesta, $opzioni = array());

	public function validaModalitaFinanziamentoRichiesta($id_proponente, $id_richiesta, $opzioni = array());
	
}
