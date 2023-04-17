<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace SfingeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use BaseBundle\Entity\EntityLoggabileCancellabile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="programmi_procedure_operative")
 * @ORM\Entity()
 *
 * @author lfontana
 */
class ProgrammaProcedura extends EntityLoggabileCancellabile
{
    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="MonitoraggioBundle\Entity\TC4Programma", inversedBy="procedure")
     * @ORM\JoinColumn(name="programma_id", referencedColumnName="id", nullable=false)
     *
     * @var \MonitoraggioBundle\Entity\TC4Programma
     * @Assert\NotNull()
     */
    protected $tc4_programma;

    /**
     * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\Procedura", inversedBy="mon_procedure_programmi")
     * @ORM\JoinColumn(name="procedura_id", referencedColumnName="id", nullable=false)
     *
     * @var Procedura
     * @Assert\NotNull()
     */
    protected $procedura;

    /**
     * @ORM\Column(type="decimal", precision=13, scale=2, nullable=false)
     * @Assert\NotBlank()
     * @Assert\Regex( pattern="/^\d+.?\d*$/", match=true, message="Formato non valido")
     */
    protected $importo;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getTc4Programma()
    {
        return $this->tc4_programma;
    }

    /**
     * @param mixed $tc4_programma
     */
    public function setTc4Programma($tc4_programma)
    {
        $this->tc4_programma = $tc4_programma;
    }

    /**
     * @return mixed
     */
    public function getProcedura()
    {
        return $this->procedura;
    }

    /**
     * @param mixed $cod_proc_att
     */
    public function setProcedura($cod_proc_att)
    {
        $this->procedura = $cod_proc_att;
    }

    /**
     * @return mixed
     */
    public function getImporto()
    {
        return $this->importo;
    }

    /**
     * @param mixed $importo
     */
    public function setImporto($importo)
    {
        $this->importo = $importo;
    }

    public function __construct(Procedura $procedura = null, \MonitoraggioBundle\Entity\TC4Programma $tc4_programma = null)
    {
        $this->tc4_programma = $tc4_programma;
        $this->procedura = $procedura;
    }

    public function __toString()
    {
        return $this->tc4_programma->__toString();
    }
}
