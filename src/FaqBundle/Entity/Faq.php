<?php

namespace FaqBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * Faq
 * @ORM\Entity
 * @ORM\Table(name="faq")
 */
class Faq extends EntityLoggabileCancellabile
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
     * @var string
     *
	 * @Assert\NotNull
     * @ORM\Column(name="titolo", type="string", length=1024, nullable=false)
     */
    private $titolo;

    /**
     * @var string
     *
     * @ORM\Column(name="testo", type="text", nullable=true)
     */
    private $testo;

    /**
     * @var \DateTime
     *
	 * @Assert\NotNull
     * @ORM\Column(name="data_inserimento", type="datetime",nullable=false)
     */
    private $dataInserimento;


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
     * Set titolo
     *
     * @param string $titolo
     *
     * @return Faq
     */
    public function setTitolo($titolo)
    {
        $this->titolo = $titolo;

        return $this;
    }

    /**
     * Get titolo
     *
     * @return string
     */
    public function getTitolo()
    {
        return $this->titolo;
    }

    /**
     * Set testo
     *
     * @param string $testo
     *
     * @return Faq
     */
    public function setTesto($testo)
    {
        $this->testo = $testo;

        return $this;
    }

    /**
     * Get testo
     *
     * @return string
     */
    public function getTesto()
    {
        return $this->testo;
    }

    /**
     * Set dataInserimento
     *
     * param \DateTime $dataInserimento
     *
     * @return Faq
     */
    public function setDataInserimento($dataInserimento)
    {
        $this->dataInserimento = $dataInserimento;

        return $this;
    }

    /**
     * Get dataInserimento
     *
     * @return \DateTime
     */
    public function getDataInserimento()
    {
        return $this->dataInserimento;
    }

}

