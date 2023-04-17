<?php
/**
 * Created by PhpStorm.
 * User: rstronati
 * Date: 27/01/16
 * Time: 13:25
 */

namespace SfingeBundle\Entity;


use BaseBundle\Entity\EntityLoggabileCancellabile;
use FascicoloBundle\Entity\Fascicolo;
use Doctrine\ORM\Mapping AS ORM;


/**
 * @ORM\Entity(repositoryClass="SfingeBundle\Entity\FascicoloProceduraRepository")
 * @ORM\Table(name="fascicoli_procedure",
 *  indexes={
 *      @ORM\Index(name="idx_procedura_id", columns={"procedura_id"}),
 *      @ORM\Index(name="idx_fascicolo_id", columns={"fascicolo_id"})
 *  })
 */
class FascicoloProcedura extends EntityLoggabileCancellabile {

    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Procedura
     * @ORM\ManyToOne(targetEntity="Procedura", inversedBy="fascicoli_procedura")
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
	 * @var string $tipo_fascisolo
	 *
	 * @ORM\Column(name="tipo_fascicolo", type="string", length=50, nullable=true)
	 */
	protected $tipo_fascicolo;
	
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

	function getTipoFascicolo() {
		return $this->tipo_fascicolo;
	}

	function setTipoFascicolo($tipo_fascicolo) {
		$this->tipo_fascicolo = $tipo_fascicolo;
	}

}