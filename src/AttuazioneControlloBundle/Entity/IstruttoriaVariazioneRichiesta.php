<?php

namespace AttuazioneControlloBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping AS ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * IstruttoriaVariazioneRichiesta
 *
 * @ORM\Table(name="istruttorie_variazioni")
 * @ORM\Entity()
 */

class IstruttoriaVariazioneRichiesta extends EntityLoggabileCancellabile {

	/**
	 * @ORM\Id
	 * @ORM\Column(type="bigint", name="id")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @ORM\Column(type="boolean", nullable=false)
	 */
	protected $esito_positivo;

	/**
	 * @ORM\OneToOne(targetEntity="AttuazioneControlloBundle\Entity\VariazioneRichiesta", inversedBy="istruttoria_variazione")
	 * @ORM\JoinColumn()
	 */
	protected $variazione_richiesta;
	
	public function getId() {
		return $this->id;
	}

	public function getEsitoPositivo() {
		return $this->esito_positivo;
	}

	public function getVariazioneRichiesta() {
		return $this->variazione_richiesta;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function setEsitoPositivo($esito_positivo) {
		$this->esito_positivo = $esito_positivo;
	}

	public function setVariazioneRichiesta($variazione_richiesta) {
		$this->variazione_richiesta = $variazione_richiesta;
	}



}
