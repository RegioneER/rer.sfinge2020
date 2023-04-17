<?php


namespace SfingeBundle\Entity;


use BaseBundle\Entity\EntityLoggabileCancellabile;
use FascicoloBundle\Services\Fascicolo;
use Doctrine\ORM\Mapping AS ORM;


/**
 * @ORM\Entity(repositoryClass="SfingeBundle\Entity\FascicoloProceduraRendicontoRepository")
 * @ORM\Table(name="fascicoli_procedure_rendiconti",
 *  indexes={
 *      @ORM\Index(name="idx_procedura_rend_id", columns={"procedura_id"}),
 *      @ORM\Index(name="idx_fascicolo_rend_id", columns={"fascicolo_id"})
 *  })
 */
class FascicoloProceduraRendiconto extends EntityLoggabileCancellabile {

    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Procedura
     * @ORM\ManyToOne(targetEntity="Procedura", inversedBy="fascicoli_procedura_rendiconto")
     * @ORM\JoinColumn(name="procedura_id", referencedColumnName="id", nullable=false)
     */
    private $procedura;

    /**
     * @var Fascicolo
     * @ORM\ManyToOne(targetEntity="FascicoloBundle\Entity\Fascicolo")
     * @ORM\JoinColumn(name="fascicolo_id", referencedColumnName="id", nullable=false)
     */
    private $fascicolo;

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
     * @return Procedura
     */
    public function getProcedura()
    {
        return $this->procedura;
    }

    /**
     * @param Procedura $procedura
     */
    public function setProcedura(Procedura $procedura)
    {
        $this->procedura = $procedura;
    }

    /**
     * @return Fascicolo
     */
    public function getFascicolo()
    {
        return $this->fascicolo;
    }

    /**
     * @param Fascicolo $fascicolo
     */
    public function setFascicolo(Fascicolo $fascicolo)
    {
        $this->fascicolo = $fascicolo;
    }



}