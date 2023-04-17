<?php

namespace IstruttorieBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RichiesteBundle\Entity\Richiesta;

/**
 * AssegnamentoIstruttoriaRichiesta
 *
 * @ORM\Table(name="assegnamenti_istruttorie_richieste")
 * @ORM\Entity()
 */
class AssegnamentoIstruttoriaRichiesta
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\Richiesta", inversedBy="assegnamenti_istruttoria")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $richiesta;

    /**
     * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\Utente", inversedBy="assegnamenti_istruttorie_richieste")
     * @ORM\JoinColumn(nullable=false)
    */
    protected $istruttore;  
    
    /**
     * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\Utente")
     * @ORM\JoinColumn(nullable=false)
    */
    protected $assegnatore;      

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="data_assegnamento", type="datetime")
     */
    protected $dataAssegnamento;

    /**
     * @var bool
     *
     * @ORM\Column(name="attivo", type="boolean")
     */
    protected $attivo;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return Richiesta|null
     */
    public function getRichiesta(): ?Richiesta
    {
        return $this->richiesta;
    }

    /**
     * @param Richiesta $richiesta
     * @return AssegnamentoIstruttoriaRichiesta
     */
    public function setRichiesta(Richiesta $richiesta)
    {
        $this->richiesta = $richiesta;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIstruttore()
    {
        return $this->istruttore;
    }

    /**
     * @param mixed $istruttore
     */
    public function setIstruttore($istruttore): void
    {
        $this->istruttore = $istruttore;
    }

    /**
     * @return mixed
     */
    public function getAssegnatore()
    {
        return $this->assegnatore;
    }

    /**
     * @param mixed $assegnatore
     */
    public function setAssegnatore($assegnatore): void
    {
        $this->assegnatore = $assegnatore;
    }

    /**
     * @return \DateTime
     */
    public function getDataAssegnamento(): \DateTime
    {
        return $this->dataAssegnamento;
    }

    /**
     * @param \DateTime $dataAssegnamento
     */
    public function setDataAssegnamento(\DateTime $dataAssegnamento): void
    {
        $this->dataAssegnamento = $dataAssegnamento;
    }

    /**
     * @return bool
     */
    public function isAttivo(): bool
    {
        return $this->attivo;
    }

    /**
     * Set attivo
     *
     * @param boolean $attivo
     * @return AssegnamentoIstruttoriaRichiesta
     */
    public function setAttivo($attivo)
    {
        $this->attivo = $attivo;

        return $this;
    }
}
