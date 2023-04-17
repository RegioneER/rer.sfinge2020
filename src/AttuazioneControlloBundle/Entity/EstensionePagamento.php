<?php

namespace AttuazioneControlloBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity()
 * @ORM\Table(name="estensioni_pagamenti")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="tipo", type="string")
 * @ORM\DiscriminatorMap({"BANDO_7"="AttuazioneControlloBundle\Entity\Bando_7\EstensionePagamentoBando_7",
 * 						  "BANDO_8"="AttuazioneControlloBundle\Entity\Bando_8\EstensionePagamentoBando_8"
 *                        })
 *
 */
abstract class EstensionePagamento extends EntityLoggabileCancellabile {

	/**
	 * @var int
	 *
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @ORM\OneToOne(targetEntity="AttuazioneControlloBundle\Entity\Pagamento", mappedBy="estensione")
	 */
	protected $pagamento;

	/**
	 * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\DocumentoEstensionePagamento", mappedBy="estensione_pagamento")
	 */
	protected $documenti;

	/**
	 * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\MaterialeObiettivoRealizzativo", mappedBy="estensione_pagamento", cascade={"persist"})
	 */
	protected $materiali_or;

	/**
	 * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\AttivitaObiettivoRealizzativo", mappedBy="estensione_pagamento", cascade={"persist"})
	 */
	protected $attivita_or;

	public function __construct() {
		$this->materiali_or = new ArrayCollection();
		$this->attivita_or = new ArrayCollection();
	}

	public function getId() {
		return $this->id;
	}

	public function getPagamento() {
		return $this->pagamento;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function setPagamento($pagamento) {
		$this->pagamento = $pagamento;
	}

	public function getDocumenti() {
		return $this->documenti;
	}

	public function setDocumenti($documenti) {
		$this->documenti = $documenti;
	}

	public function getMaterialiOr() {
		return $this->materiali_or;
	}

	public function getAttivitaOr() {
		return $this->attivita_or;
	}

	public function setMaterialiOr($materiali_or) {
		$this->materiali_or = $materiali_or;
	}

	public function setAttivitaOr($attivita_or) {
		$this->attivita_or = $attivita_or;
	}

	public function addMaterialiOr(\AttuazioneControlloBundle\Entity\MaterialeObiettivoRealizzativo $materiali_or) {
		$this->materiali_or[] = $materiali_or;

		return $this;
	}

	public function removeMaterialiOr(\AttuazioneControlloBundle\Entity\MaterialeObiettivoRealizzativo $materiali_or) {
		$this->materiali_or->removeElement($materiali_or);
	}

	public function addAttivitaOr(\AttuazioneControlloBundle\Entity\AttivitaObiettivoRealizzativo $attivita_or) {
		$this->attivita_or[] = $attivita_or;

		return $this;
	}

	public function removeAttivitaOr(\AttuazioneControlloBundle\Entity\AttivitaObiettivoRealizzativo $attivita_or) {
		$this->attivita_or->removeElement($attivita_or);
	}

}
