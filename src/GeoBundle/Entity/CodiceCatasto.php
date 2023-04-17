<?php

namespace GeoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;


/**
 * GeoBundle\Entity\CodiceCatasto
 * @ORM\Table(name="codici_catasto", indexes={@ORM\Index(name="codice_catasto_idx", columns={"codice_catasto"})})
 * @ORM\Entity(repositoryClass="GeoBundle\Entity\CodiceCatastoRepository")
 */
class CodiceCatasto {

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string $codice_istat
     *
     * @ORM\Column(name="codice_istat", type="string", length=15)
     */
    protected $codice_istat;

    /**
     * @var string $codice_catasto
     *
     * @ORM\Column(name="codice_catasto", type="string", length=4)
     * 
     */
    protected $codice_catasto;
    
    /**
     * @var \DateTime $data_istituzione
     * 
     * @ORM\Column(name="data_istituzione", type="date", nullable=true) 
     */
    protected $data_istituzione;
    
    /**
     * @var \DateTime $data_soppressione
     * 
     * @ORM\Column(name="data_soppressione", type="date", nullable=true) 
     */
    protected $data_soppressione;     

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set codice_istat
     *
     * @param string $codiceIstat
     */
    public function setCodiceIstat($codiceIstat) {
        $this->codice_istat = $codiceIstat;
    }

    /**
     * Get codice_istat
     *
     * @return string 
     */
    public function getCodiceIstat() {
        return $this->codice_istat;
    }

    /**
     * Set codice_catasto
     *
     * @param string $codiceCatasto
     */
    public function setCodiceCatasto($codiceCatasto) {
        $this->codice_catasto = $codiceCatasto;
    }

    /**
     * Get codice_catasto
     *
     * @return string 
     */
    public function getCodiceCatasto() {
        return $this->codice_catasto;
    }
    
    
    public function getDataIstituzione() {
        return $this->data_istituzione;
    }

    public function setDataIstituzione($data_istituzione) {
        $this->data_istituzione = \is_string($data_istituzione) ? new \DateTime($data_istituzione) : $data_istituzione;
    }

    public function getDataSoppressione() {
        return $this->data_soppressione;
    }

    public function setDataSoppressione($data_soppressione) {
        $this->data_soppressione = \is_string($data_soppressione) ? new \DateTime($data_soppressione) : $data_soppressione;
    }

}