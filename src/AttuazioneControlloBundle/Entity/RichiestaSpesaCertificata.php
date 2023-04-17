<?php

namespace AttuazioneControlloBundle\Entity;


use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Description of RichiestaSpesaCertificata.
 *
 * @author lfontana
 * @ORM\Entity()
 * @ORM\Table(name="richieste_spese_certificate")
 */
class RichiestaSpesaCertificata extends EntityLoggabileCancellabile
{
    public static $TIPOLOGIE_IMPORTO = array(
        'C' => 'Certificato',
        'D' => 'Decertificato'
    );
    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var int|null
     */
     protected $id;
     
    /**
     * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\Richiesta", inversedBy="mon_spese_certificate")
     * @ORM\JoinColumn(name="richiesta_id", referencedColumnName="id", nullable=false)
     * @var \RichiesteBundle\Entity\Richiesta|null
     */
     protected $richiesta;
     
    /**
     * @ORM\ManyToOne(targetEntity="MonitoraggioBundle\Entity\TC41DomandaPagamento")
     * @ORM\JoinColumn(name="domanda_pagamento_id", referencedColumnName="id", nullable=false)
     * @var \MonitoraggioBundle\Entity\TC41DomandaPagamento|null
     */
     protected $domanda_pagamento;
     
    /**
    * @ORM\Column(type="date", nullable=false)
    * @var \DateTime|null
    */
    protected $data_pagamento;

    /**
    * @ORM\Column(type="string", length=1, nullable=false, options={"default" : ""})
    * @var string|null
    */    
    protected $tipologia_importo;

    /**
     * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\RichiestaLivelloGerarchico")
     * @ORM\JoinColumn(name="livello_gerarchico_id", nullable=false)
     * @Assert\NotNull()
     * @var \MonitoraggioBundle\Entity\TC36LivelloGerarchico
     */
     protected $livello_gerarchico;

     /**
     * @ORM\Column(type="decimal", precision=14, scale=2, nullable=false, options={"default" : 0})
     * @var float 
	 */
    protected $importo_spesa_pubblica = 0;

    /**
     * @ORM\Column(type="decimal", precision=14, scale=2, nullable=false, options={"default" : 0})
     * @var float
	 */
    protected $importo_spesa_totale = 0;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set data_pagamento
     *
     * @param \DateTime $dataPagamento
     * @return RichiestaSpesaCertificata
     */
    public function setDataPagamento($dataPagamento)
    {
        $this->data_pagamento = $dataPagamento;

        return $this;
    }

    /**
     * Get data_pagamento
     *
     * @return \DateTime 
     */
    public function getDataPagamento()
    {
        return $this->data_pagamento;
    }

    /**
     * Set tipologia_importo
     *
     * @param string $tipologiaImporto
     * @return RichiestaSpesaCertificata
     */
    public function setTipologiaImporto($tipologiaImporto)
    {
        $this->tipologia_importo = $tipologiaImporto;

        return $this;
    }

    /**
     * Get tipologia_importo
     *
     * @return string 
     */
    public function getTipologiaImporto()
    {
        return $this->tipologia_importo;
    }

    /**
     * Set importo_spesa_pubblica
     *
     * @param string $importoSpesaPubblica
     * @return RichiestaSpesaCertificata
     */
    public function setImportoSpesaPubblica($importoSpesaPubblica)
    {
        $this->importo_spesa_pubblica = $importoSpesaPubblica;

        return $this;
    }

    /**
     * Get importo_spesa_pubblica
     *
     * @return string 
     */
    public function getImportoSpesaPubblica()
    {
        return $this->importo_spesa_pubblica;
    }

    /**
     * Set importo_spesa_totale
     *
     * @param string $importoSpesaTotale
     * @return RichiestaSpesaCertificata
     */
    public function setImportoSpesaTotale($importoSpesaTotale)
    {
        $this->importo_spesa_totale = $importoSpesaTotale;

        return $this;
    }

    /**
     * Get importo_spesa_totale
     *
     * @return string 
     */
    public function getImportoSpesaTotale()
    {
        return $this->importo_spesa_totale;
    }

    /**
     * Set richiesta
     *
     * @param \RichiesteBundle\Entity\Richiesta $richiesta
     * @return RichiestaSpesaCertificata
     */
    public function setRichiesta(\RichiesteBundle\Entity\Richiesta $richiesta = NULL)
    {
        $this->richiesta = $richiesta;

        return $this;
    }

    /**
     * Get richiesta
     *
     * @return \RichiesteBundle\Entity\Richiesta 
     */
    public function getRichiesta()
    {
        return $this->richiesta;
    }

    /**
     * Set domanda_pagamento
     *
     * @param \MonitoraggioBundle\Entity\TC41DomandaPagamento $domandaPagamento
     * @return RichiestaSpesaCertificata
     */
    public function setDomandaPagamento(\MonitoraggioBundle\Entity\TC41DomandaPagamento $domandaPagamento = NULL)
    {
        $this->domanda_pagamento = $domandaPagamento;

        return $this;
    }

    /**
     * Get domanda_pagamento
     *
     * @return \MonitoraggioBundle\Entity\TC41DomandaPagamento 
     */
    public function getDomandaPagamento()
    {
        return $this->domanda_pagamento;
    }

    /**
     * Set livello_gerarchico
     *
     * @param \AttuazioneControlloBundle\Entity\RichiestaLivelloGerarchico $livelloGerarchico
     * @return RichiestaSpesaCertificata
     */
    public function setLivelloGerarchico(\AttuazioneControlloBundle\Entity\RichiestaLivelloGerarchico $livelloGerarchico = NULL)
    {
        $this->livello_gerarchico = $livelloGerarchico;

        return $this;
    }

    /**
     * Get livello_gerarchico
     *
     * @return \AttuazioneControlloBundle\Entity\RichiestaLivelloGerarchico 
     */
    public function getLivelloGerarchico()
    {
        return $this->livello_gerarchico;
    }

    public function getDescrizioneTipologiaImporto(){
        return is_null($this->tipologia_importo) ? NULL : 
            self::$TIPOLOGIE_IMPORTO[ $this->tipologia_importo ];
    }
}
