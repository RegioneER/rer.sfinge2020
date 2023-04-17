<?php
/**
 * Created by PhpStorm.
 * User: rstronati
 * Date: 29/01/16
 * Time: 15:01
 */

namespace RichiesteBundle\Entity;


use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping AS ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="tipi_referenza_procedura",
 *  indexes={
 *      @ORM\Index(name="idx_procedura_id", columns={"procedura_id"}),
 *		@ORM\Index(name="idx_tipi_referenza_id", columns={"tipo_referenza_id"})
 *  })
 */
class TipiReferenzaProcedura extends EntityLoggabileCancellabile
{
    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\Procedura", inversedBy="tipi_referenza")
     * @ORM\JoinColumn(name="procedura_id", referencedColumnName="id", nullable=false)
     */
    protected $procedura;

    /**
     * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\TipoReferenza")
     * @ORM\JoinColumn(name="tipo_referenza_id", referencedColumnName="id", nullable=false)
     */
    protected $tipo_referenza;

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
    public function getProcedura()
    {
        return $this->procedura;
    }

    /**
     * @param mixed $procedura
     */
    public function setProcedura($procedura)
    {
        $this->procedura = $procedura;
    }

    /**
     * @return TipoReferenza
     */
    public function getTipoReferenza()
    {
        return $this->tipo_referenza;
    }

    /**
     * @param TipoReferenza $tipo_referenza
     */
    public function setTipoReferenza(TipoReferenza $tipo_referenza)
    {
        $this->tipo_referenza = $tipo_referenza;
    }




}