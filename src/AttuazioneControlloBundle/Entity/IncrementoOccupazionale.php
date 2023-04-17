<?php

namespace AttuazioneControlloBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping AS ORM;
use DocumentoBundle\Entity\DocumentoFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 * @ORM\Entity()
 * @ORM\Table(name="incrementi_occupazionali")
 */
class IncrementoOccupazionale extends EntityLoggabileCancellabile
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
     * @var Pagamento
     * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Pagamento", inversedBy="incremento_occupazionale")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $pagamento;

    /**
     * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\Proponente")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $proponente;
    
    /**
     * @ORM\Column(type="decimal", precision=6, scale=2, nullable=true)
     * @Assert\Regex("/^\d+(\.|,)?\d*$/")
     */
    protected $occupati_in_data_a;

    /**
     * @ORM\Column(type="decimal", precision=6, scale=2, nullable=true)
     * @Assert\Regex("/^\d+(\.|,)?\d*$/")
     */
    protected $occupati_in_data_b;

    /**
     * @var DocumentoFile
     * @ORM\OneToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    protected $allegato_dm_a;

    /**
     * @var DocumentoFile
     * @ORM\OneToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    protected $allegato_dm_b;

    /**
     * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\DocumentoIncrementoOccupazionale", mappedBy="incremento_occupazionale", cascade={"persist"})
     * @var DocumentoIncrementoOccupazionale[]
     */
    public $documenti_incremento_occupazionale;


    public function __construct()
    {
        $this->documenti_incremento_occupazionale = new ArrayCollection();
    }
    
    public function getNomeClasse()
    {
        return "IncrementoOccupazionale";
    }
    
    function getId()
    {
        return $this->id;
    }

    function getPagamento()
    {
        return $this->pagamento;
    }

    function getProponente()
    {
        return $this->proponente;
    }

    function getOccupatiInDataA()
    {
        return $this->occupati_in_data_a;
    }

    function getOccupatiInDataB()
    {
        return $this->occupati_in_data_b;
    }
    
    function getAllegatoDmA()
    {
        return $this->allegato_dm_a;
    }

    function getAllegatoDmB()
    {
        return $this->allegato_dm_b;
    }
    
    function setId($id)
    {
        $this->id = $id;
    }

    function setPagamento($pagamento)
    {
        $this->pagamento = $pagamento;
    }

    function setProponente($proponente)
    {
        $this->proponente = $proponente;
    }

    function setOccupatiInDataA($occupati_in_data_a)
    {
        $this->occupati_in_data_a = $occupati_in_data_a;
    }

    function setOccupatiInDataB($occupati_in_data_b)
    {
        $this->occupati_in_data_b = $occupati_in_data_b;
    }

    function setAllegatoDmA($allegato_dm_a)
    {
        $this->allegato_dm_a = $allegato_dm_a;
    }

    function setAllegatoDmB($allegato_dm_b)
    {
        $this->allegato_dm_b = $allegato_dm_b;
    }

    public function addDocumentiIncrementoOccupazionale(DocumentoIncrementoOccupazionale $documenti_incremento_occupazionale): self
    {
        $this->documenti_incremento_occupazionale[] = $documenti_incremento_occupazionale;
        return $this;
    }

    public function removeDocumentiIncrementoOccupazionale(DocumentoIncrementoOccupazionale $documenti_incremento_occupazionale): void
    {
        $this->documenti_incremento_occupazionale->removeElement($documenti_incremento_occupazionale);
    }

    public function getDocumentiIncrementoOccupazionale(): Collection {
        return $this->documenti_incremento_occupazionale;
    }
}
