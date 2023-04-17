<?php

namespace RichiesteBundle\Service;

use Doctrine\Common\Collections\ArrayCollection;
use SfingeBundle\Entity\Procedura;

interface IGestoreFaseProcedurale {

	public function aggiornaFaseProceduraleRichiesta($id_richiesta, $opzioni = array());

	public function generaFaseProceduraleRichiesta($id_richiesta, $opzioni = array());

	public function validaFaseProceduraleRichiesta($id_richiesta, $opzioni = array());
	
	public function ottieniFasiDaRichiestaProcedura($id_richiesta, $opzioni = array());
}
