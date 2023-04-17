<?php

namespace AttuazioneControlloBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="estensioni_giustificativi")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="tipo", type="string")
 * @ORM\DiscriminatorMap({"BANDO_7"="AttuazioneControlloBundle\Entity\Bando_7\EstensioneGiustificativoBando_7",
 *						  "BANDO_8"="AttuazioneControlloBundle\Entity\Bando_8\EstensioneGiustificativoBando_8"
 *                        })
 *
 */
abstract class EstensioneGiustificativo extends EntityLoggabileCancellabile {

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
	/**
	 * @ORM\OneToOne(targetEntity="AttuazioneControlloBundle\Entity\GiustificativoPagamento", mappedBy="estensione")
	 */
	protected $giustificativo_pagamento;   
      
	/**
	 * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\DocumentoEstensioneGiustificativo", mappedBy="estensione_giustificativo")
	 */
	protected $documenti;  


	/**
	 * @ORM\ManyToMany(targetEntity="RichiesteBundle\Entity\ObiettivoRealizzativo", inversedBy="estensione")
	 * @ORM\JoinTable(name="obiettivi_realizzativi_estenzioni")    
	 */
	protected $obiettivi_realizzativi;
	
	/**
	 * @ORM\OneToOne(targetEntity="AnagraficheBundle\Entity\Personale", inversedBy="estensioneGiustificativo", cascade={"persist"})
	 * @ORM\JoinColumn(name="ricercatore_id")
	 */
	protected $ricercatore;
	
	public function __construct() {
		$this->documenti = new \Doctrine\Common\Collections\ArrayCollection();
	}

	
    public function getId() {
        return $this->id;
    }

    public function getGiustificativoPagamento() {
        return $this->giustificativo_pagamento;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setGiustificativoPagamento($giustificativo_pagamento) {
        $this->giustificativo_pagamento = $giustificativo_pagamento;
    }

    public function getDocumenti() {
        return $this->documenti;
    }

    public function setDocumenti($documenti) {
        $this->documenti = $documenti;
    }

	public function addDocumenti(\AttuazioneControlloBundle\Entity\DocumentoEstensioneGiustificativo $documenti) {
		$this->documenti[] = $documenti;

		return $this;
	}

	public function removeDocumenti(\AttuazioneControlloBundle\Entity\DocumentoEstensioneGiustificativo  $documenti) {
		$this->documenti->removeElement($documenti);
	}
	
	public function getObiettiviRealizzativi() {
		return $this->obiettivi_realizzativi;
	}

	public function setObiettiviRealizzativi($obiettivi_realizzativi) {
		$this->obiettivi_realizzativi = $obiettivi_realizzativi;
	}
	
	public function getRicercatore() {
		return $this->ricercatore;
	}

	public function setRicercatore($ricercatore) {
		$this->ricercatore = $ricercatore;
	}
}
