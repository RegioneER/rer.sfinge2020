<?php

namespace AttuazioneControlloBundle\Form\Entity;

use BaseBundle\Service\AttributiRicerca;
use AttuazioneControlloBundle\Entity\ProrogaRendicontazione;
use AttuazioneControlloBundle\Form\RicercaProrogaRendicontazioneType;
use SfingeBundle\Entity\Procedura;

class RicercaProrogaRendicontazione extends AttributiRicerca
{
    /**
     * @var string|null
     */
    public $protocollo;

    /**
     * @var Procedura
     */
    public $procedura;

    /**
     * @var string|null
     */
    public $id_operazione;


    public function getNomeMetodoRepository(){
        return 'getElencoProroghe';
    }

    public function getNomeRepository(){
        return ProrogaRendicontazione::class;
    }

    public function getType(){
        return RicercaProrogaRendicontazioneType::class;
    }

    public function getNomeParametroPagina()
	{
		return "page";
    }
    
    public function getNumeroElementiPerPagina()
    {
        return null;
    }
}