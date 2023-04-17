<?php

namespace ProtocollazioneBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * IstanzaProcesso
 *
 * @ORM\Entity(repositoryClass="ProtocollazioneBundle\Repository\IstanzaProcessoRepository")
 * @ORM\Table(name="istanze_processi",
 *  indexes={
 *      @ORM\Index(name="idx_processo_id", columns={"processo_id"}),
 *  })
 */
class IstanzaProcesso extends EntityLoggabileCancellabile
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Processo", inversedBy="istanze_processi")
     * @ORM\JoinColumn(name="processo_id", referencedColumnName="id")
     */
    protected $processo;
    
    /**
     * @var \DateTime $dat\a_avvio
     *
     * @ORM\Column(name="data_avvio", type="datetime", nullable=true)
     */
    private $data_avvio;

    /**
     * @var \DateTime $data_fine
     *
     * @ORM\Column(name="data_fine", type="datetime", nullable=true)
     */
    private $data_fine;

    /**
     * @var integer,
     *
     * @ORM\Column(name="elementi_elaborati", type="integer", nullable=true)
     */
    private $elementi_elaborati;

    /**
     * @var string $stato
     *
     * @ORM\Column(name="stato", type="string", length=50, nullable=true)
     */
    private $stato;

    /**
     * @var int $esito
     *
     * @ORM\Column(name="esito", type="integer", nullable=true)
     */
    private $esito;


    /**
     * @ORM\OneToMany(targetEntity="ProtocollazioneBundle\Entity\RichiestaProtocollo", mappedBy="istanza_processo")
     */
    protected $richieste_protocollo;

    
    public function __construct()
    {
        $this->richieste_protocollo = new ArrayCollection();
    }
    
    
    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set dataAvvio
     *
     * @param \DateTime $dataAvvio
     *
     * @return IstanzaProcesso
     */
    public function setDataAvvio($dataAvvio)
    {
        $this->data_avvio = $dataAvvio;

        return $this;
    }

    /**
     * Get dataAvvio
     *
     * @return \DateTime
     */
    public function getDataAvvio()
    {
        return $this->data_avvio;
    }

    /**
     * Set dataFine
     *
     * @param \DateTime $dataFine
     *
     * @return IstanzaProcesso
     */
    public function setDataFine($dataFine)
    {
        $this->data_fine = $dataFine;

        return $this;
    }

    /**
     * Get dataFine
     *
     * @return \DateTime
     */
    public function getDataFine()
    {
        return $this->data_fine;
    }

    /**
     * Set elementiElaborati
     *
     * @param integer $elementiElaborati
     *
     * @return IstanzaProcesso
     */
    public function setElementiElaborati($elementiElaborati)
    {
        $this->elementi_elaborati = $elementiElaborati;

        return $this;
    }

    /**
     * Get elementiElaborati
     *
     * @return integer
     */
    public function getElementiElaborati()
    {
        return $this->elementi_elaborati;
    }

    /**
     * Set stato
     *
     * @param string $stato
     *
     * @return IstanzaProcesso
     */
    public function setStato($stato)
    {
        $this->stato = $stato;

        return $this;
    }

    /**
     * Get stato
     *
     * @return string
     */
    public function getStato()
    {
        return $this->stato;
    }

    /**
     * Set esito
     *
     * @param integer $esito
     *
     * @return IstanzaProcesso
     */
    public function setEsito($esito)
    {
        $this->esito = $esito;

        return $this;
    }

    /**
     * Get esito
     *
     * @return int
     */
    public function getEsito()
    {
        return $this->esito;
    }

    /**
     * Set processo
     *
     * @param \ProtocollazioneBundle\Entity\Processo $processo
     *
     * @return IstanzaProcesso
     */
    public function setProcesso($processo = null)
    {
        $this->processo = $processo;

        return $this;
    }

    /**
     * Get processo
     *
     * @return \ProtocollazioneBundle\Entity\Processo
     */
    public function getProcesso()
    {
        return $this->processo;
    }

	/**
	 * 
	 * @return \Doctrine\Common\Collections\Collection
	 */
	function getRichiesteProtocollo() {
		return $this->richieste_protocollo;
	}

	function setRichiesteProtocollo($richieste_protocollo) {
		$this->richieste_protocollo = $richieste_protocollo;
	}

	function addRichiestaProtocollo($richiesta_protocollo) {
		$richieste_protocollo = $this->getRichiesteProtocollo();
		$richieste_protocollo->add($richiesta_protocollo);
		$this->setRichiesteProtocollo($richieste_protocollo);
		
	}
		
	
    /**
     * Add richiestaProtocolloId
     *
     * @param \ProtocollazioneBundle\Entity\RichiestaProtocollo $richiestaProtocolloId
     *
     * @return IstanzaProcesso
     */
//    public function addRichiestaProtocolloId($richiestaProtocolloId)
//    {
//
//        $this->richiesta_protocollo_id[] = $richiestaProtocolloId;
//
//        return $this;
//    }

    /**
     * Remove richiestaProtocolloId
     *
     * @param \ProtocollazioneBundle\Entity\RichiestaProtocollo $richiestaProtocolloId
     */
//    public function removeRichiestaProtocolloId($richiestaProtocolloId)
//    {
//        $this->richiesta_protocollo_id->removeElement($richiestaProtocolloId);
//    }

    /**
     * Get richiestaProtocolloId
     *
     * @return \Doctrine\Common\Collections\Collection
     */
//    public function getRichiestaProtocolloId()
//    {
//        return $this->richiesta_protocollo_id;
//    }
}
