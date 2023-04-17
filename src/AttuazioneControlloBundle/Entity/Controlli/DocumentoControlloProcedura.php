<?php

namespace AttuazioneControlloBundle\Entity\Controlli;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;

/**
 * DocumentoControllo
 *
 * @ORM\Table(name="documenti_controllo_procedura")
 * @ORM\Entity()
 */
class DocumentoControlloProcedura  extends EntityLoggabileCancellabile {

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile",cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $documento_file;

    /**
     * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Controlli\ControlloProcedura", inversedBy="documenti_controllo")
     * @ORM\JoinColumn(nullable=false)
     */
    private $controllo_procedura;

	public function getId() {
		return $this->id;
	}

	public function getDocumentoFile() {
		return $this->documento_file;
	}

	public function getControlloProcedura() {
		return $this->controllo_procedura;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function setDocumentoFile($documento_file) {
		$this->documento_file = $documento_file;
	}

	public function setControlloProcedura($controllo_procedura) {
		$this->controllo_procedura = $controllo_procedura;
	}
  
}
