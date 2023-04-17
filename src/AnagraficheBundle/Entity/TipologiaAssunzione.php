<?php

namespace AnagraficheBundle\Entity;

use BaseBundle\Entity\EntityTipo;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author gdisparti
 * 
 * @ORM\Entity()
 * @ORM\Table(name="tipologie_assunzione")
 */
class TipologiaAssunzione extends EntityTipo
{
    
    const CONTESTO_RICERCATORE = 1;
    const CONTESTO_FULLTIME = 2;
    const CONTESTO_PARTTIME = 3;
    
    /**
     * In base al contesto potrebbe essere necessario filtrare le possibili opzioni da visualizzare nel menù a tendina.
     * 
     * Il filtraggio verrà fatto dentro lo specifico formtype tramite questa variabile 
     * andando a definire l'opzione query_builder e specificando nella clausola
     * where il contesto (o i contesti) a cui si è interessati
     * 
     * N.B. per ogni nuovo contesto che dovesse servire, si deve creare una nuova costante con il valore che verrà poi 
     * assegnato alle nuove istanze di tipologia assunzione a db
     * 
     * @ORM\Column(name="contesto", type="integer", nullable=false)
     */
    protected $contesto;
    
    public function __toString() {
        return $this->descrizione;
    }
}
