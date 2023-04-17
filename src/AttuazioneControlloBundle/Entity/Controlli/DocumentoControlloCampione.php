<?php

namespace AttuazioneControlloBundle\Entity\Controlli;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;

/**
 * DocumentoControllo
 *
 * @ORM\Table(name="documenti_controllo_campione")
 * @ORM\Entity()
 */
class DocumentoControlloCampione extends EntityLoggabileCancellabile {

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
     * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Controlli\ControlloCampione", inversedBy="documenti_controllo")
     * @ORM\JoinColumn(nullable=false)
     */
    private $controllo_campione;

    public function getId() {
        return $this->id;
    }

    public function getDocumentoFile() {
        return $this->documento_file;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setDocumentoFile($documento_file) {
        $this->documento_file = $documento_file;
    }
    
    function getControlloCampione() {
        return $this->controllo_campione;
    }

    function setControlloCampione($controllo_campione): void {
        $this->controllo_campione = $controllo_campione;
    }



}
