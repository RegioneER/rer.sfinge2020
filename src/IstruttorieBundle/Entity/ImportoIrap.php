<?php

namespace IstruttorieBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="IstruttorieBundle\Entity\ImportoIrapRepository")
 * @ORM\Table(name="importi_irap")
 */
class ImportoIrap extends EntityLoggabileCancellabile
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(name = "codice_fiscale", type="string", length=16, unique=true, nullable=false)
     * @Assert\NotBlank()
     * @Assert\Length(min=2, max=16)
     */
    private $codice_fiscale;

    /**
     * @ORM\Column(name="importo_irap", type="decimal", precision=13, scale=2, nullable=true)
     */
    protected $importo_irap;


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
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getCodiceFiscale()
    {
        return $this->codice_fiscale;
    }

    /**
     * @param mixed $codice_fiscale
     */
    public function setCodiceFiscale($codice_fiscale): void
    {
        $this->codice_fiscale = $codice_fiscale;
    }

    /**
     * @return mixed
     */
    public function getImportoIrap()
    {
        return $this->importo_irap;
    }

    /**
     * @param mixed $importo_irap
     */
    public function setImportoIrap($importo_irap): void
    {
        $this->importo_irap = $importo_irap;
    }
}
