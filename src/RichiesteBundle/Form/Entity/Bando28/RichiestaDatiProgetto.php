<?php

namespace RichiesteBundle\Form\Entity\Bando28;

use RichiesteBundle\Entity\Richiesta;
use RichiesteBundle\Entity\Bando28\OggettoFiere;
use Symfony\Component\Validator\Constraints as Assert;




class RichiestaDatiProgetto extends Richiesta{
    
    
    /**
     * @Assert\NotNull( groups={"dati_progetto"} )
	 * @Assert\Regex(pattern="/^\d+$/", message="Il valore deve essere un numero intero", groups={"dati_progetto"})
     * @Assert\GreaterThan(value="0", groups={"dati_progetto"} )
     * @var int 
     */
    protected $numeroRelazioni;
    
    public function getNumeroRelazioni() {
        return $this->numeroRelazioni;
    }

    public function setNumeroRelazioni($numeroRelazioni) {
        $this->numeroRelazioni = $numeroRelazioni;
    }

    public function __construct( Richiesta $richiesta, OggettoFiere $oggettoRichiesta){
        parent::__construct();
        $this->setAbstract($richiesta->getAbstract());
        $this->setTitolo($richiesta->getTitolo());
        $this->numeroRelazioni = $oggettoRichiesta->getNumeroRelazioni();
        
    }


}