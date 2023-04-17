<?php

namespace AttuazioneControlloBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="documenti_estensioni_giustificativi")
 *  })
 */
class DocumentoEstensioneGiustificativo extends EntityLoggabileCancellabile {

	/**
	 * @ORM\Id
	 * @ORM\Column(type="bigint", name="id")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @ORM\OneToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile",cascade={"persist"})
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $documento_file;

	/**
	 * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\EstensioneGiustificativo", inversedBy="documenti")
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $estensione_giustificativo;   

	function getId() {
		return $this->id;
	}

	function getDocumentoFile() {
		return $this->documento_file;
	}

	function setId($id) {
		$this->id = $id;
	}

	function setDocumentoFile($documento_file) {
		$this->documento_file = $documento_file;
	}

    public function getEstensioneGiustificativo() {
        return $this->estensione_giustificativo;
    }

    public function setEstensioneGiustificativo($estensione_giustificativo) {
        $this->estensione_giustificativo = $estensione_giustificativo;
    }

}
