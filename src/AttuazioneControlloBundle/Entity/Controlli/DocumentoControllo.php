<?php

namespace AttuazioneControlloBundle\Entity\Controlli;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;

/**
 * DocumentoControllo
 *
 * @ORM\Table(name="documenti_controllo")
 * @ORM\Entity()
 */
class DocumentoControllo  extends EntityLoggabileCancellabile {

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
     * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Controlli\ControlloProgetto", inversedBy="documenti_controllo")
     * @ORM\JoinColumn(nullable=false)
     */
    private $controllo_progetto;

    function getId() {
        return $this->id;
    }

    function getDocumentoFile() {
        return $this->documento_file;
    }

    function getControlloProgetto() {
        return $this->controllo_progetto;
    }

    function setId($id) {
        $this->id = $id;
        return $this;
    }

    function setDocumentoFile($documento_file) {
        $this->documento_file = $documento_file;
        return $this;
    }

    function setControlloProgetto($controllo_progetto) {
        $this->controllo_progetto = $controllo_progetto;
        return $this;
    }
  
}
