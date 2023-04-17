<?php

namespace RichiesteBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;
use BaseBundle\Entity\EntityTipo;

/**
 * @ORM\Table(name="tipi_aiuto_proponente")
 * @ORM\Entity()
 */
class TipoAiutoProponente extends EntityTipo
{
    
	/**
     * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\Procedura", inversedBy="tipi_aiuto_proponente")
     * @ORM\JoinColumn(name="procedura_id", referencedColumnName="id", nullable=false)
     */
    protected $procedura;
	
	public function getProcedura() {
		return $this->procedura;
	}

	public function setProcedura($procedura) {
		$this->procedura = $procedura;
	}
    
    public function isDeminimis() {
        //metto la verifica in_array perchè altri bandi potrebbero utilizzare codici diversi
        //che ovviamente andranno aggiunti
        $arrayDeminimis = ['REG_1407'];
        return in_array($this->getCodice(), $arrayDeminimis);
    }
    
    public function isNoRegime() {
        //metto la verifica in_array perchè altri bandi potrebbero utilizzare codici diversi
        //che ovviamente andranno aggiunti
        $arrayNo = ['NESSUNO'];
        return in_array($this->getCodice(), $arrayNo);
    }

}