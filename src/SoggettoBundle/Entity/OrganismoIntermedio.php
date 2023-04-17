<?php

namespace SoggettoBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;
	
/**
 * @ORM\Entity(repositoryClass="SoggettoBundle\Entity\OrganismoIntermedioRepository")
 */
class OrganismoIntermedio extends Soggetto {
	
	/**
	 * @ORM\OneToMany(targetEntity="SfingeBundle\Entity\DocumentiOOII", mappedBy="organismo_intermedio")
	 */
	protected $documenti_ooii;
	
	public function getDocumenti_ooii() {
		return $this->documenti_ooii;
	}

	public function setDocumenti_ooii($documenti_ooii) {
		$this->documenti_ooii = $documenti_ooii;
	}
		
}
