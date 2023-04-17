<?php
/**
 * Created by PhpStorm.
 * User: rstronati
 * Date: 09/02/16
 * Time: 13:12
 */

namespace BaseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * BaseBundle\Entity\StatoLog
 *
 * @ORM\Entity()
 * @ORM\Table(name="stati_log",
 *  indexes={
 *      @ORM\Index(name="idx_id_oggetto", columns={"id_oggetto"}),
 *  })
 */
class StatoLog
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string $username
     *
     * @ORM\Column(name="username", type="string", length=255, nullable=true)
     */
    protected $username;


    /**
     * @var string $stato_precedente
     *
     * @ORM\Column(name="stato_precedente", type="string", length=255, nullable=true)
     */
    protected $stato_precedente;


    /**
     * @var string $stato_destinazione
     *
     * @ORM\Column(name="stato_destinazione", type="string", length=255, nullable=true)
     */
    protected $stato_destinazione;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $data;

    /**
     * @var string $oggetto
     *
     * @ORM\Column(name="oggetto", type="string", length=255, nullable=true)
     */
    protected $oggetto;


    /**
     * @var string $id_oggetto
     *
     * @ORM\Column(name="id_oggetto", type="integer", nullable=true)
     */
    protected $id_oggetto;

    /**
     * StatoLog constructor.
     */
    public function __construct()
    {
        $this->data = new \DateTime();
    }


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getStatoPrecedente()
    {
        return $this->stato_precedente;
    }

    /**
     * @param string $stato_precedente
     */
    public function setStatoPrecedente($stato_precedente)
    {
        $this->stato_precedente = $stato_precedente;
    }

    /**
     * @return string
     */
    public function getStatoDestinazione()
    {
        return $this->stato_destinazione;
    }

    /**
     * @param string $stato_destinazione
     */
    public function setStatoDestinazione($stato_destinazione)
    {
        $this->stato_destinazione = $stato_destinazione;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getOggetto()
    {
        return $this->oggetto;
    }

    /**
     * @param string $oggetto
     */
    public function setOggetto($oggetto)
    {
        $this->oggetto = $oggetto;
    }

    /**
     * @return string
     */
    public function getIdOggetto()
    {
        return $this->id_oggetto;
    }

    /**
     * @param string $id_oggetto
     */
    public function setIdOggetto($id_oggetto)
    {
        $this->id_oggetto = $id_oggetto;
    }

}