<?php

namespace AuditBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;
use BaseBundle\Entity\EntityLoggabileCancellabile;

/**
 * @ORM\Table(name="audit_periodi_contabili")
 * @ORM\Entity()
 */
class PeriodoContabile extends EntityLoggabileCancellabile {

	/**
	 * @var integer $id
	 *
	 * @ORM\Column(name="id", type="bigint")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @var string $codice
	 *
	 * @ORM\Column(name="codice", type="string", length=50)
	 */
	protected $codice;

	/**
	 * @var string $descrizione
	 *
	 * @ORM\Column(name="descrizione", type="string", length=1000)
	 */
	protected $descrizione;

	/**
	 * @ORM\OneToMany(targetEntity="Audit", mappedBy="periodo_contabile")
	 */
	protected $audit;

	public function getId() {
		return $this->id;
	}

	public function getCodice() {
		return $this->codice;
	}

	public function getDescrizione() {
		return $this->descrizione;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function setCodice($codice) {
		$this->codice = $codice;
	}

	public function setDescrizione($descrizione) {
		$this->descrizione = $descrizione;
	}
    
    public function getAudit() {
        return $this->audit;
    }

    public function setAudit($audit) {
        $this->audit = $audit;
    }
	
	public function __toString() {
		return $this->getDescrizione();
	}

}
