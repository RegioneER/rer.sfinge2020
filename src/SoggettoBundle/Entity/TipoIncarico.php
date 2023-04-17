<?php
/**
 * Created by PhpStorm.
 * User: rstronati
 * Date: 04/01/16
 * Time: 15:50
 */

namespace SoggettoBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;
use BaseBundle\Entity\EntityTipo;

/**
 *
 * @ORM\Entity()
 * @ORM\Table(name="tipo_incarico")
 */
class TipoIncarico extends EntityTipo
{

    const OPERATORE = "OPERATORE";
    const LR = "LR";
    const DELEGATO = "DELEGATO";
    const CONSULENTE = "CONSULENTE";
    const UTENTE_PRINCIPALE = "UTENTE_PRINCIPALE";
    const AFFILIATO = "AFFILIATO";
	const OPERATORE_RICHIESTA = "OPERATORE_RICHIESTA";


    //metodo che indica se l'incarico deve avere un ruolo nell'applicazione
    public function hasRuoloApplicativo(){
        if($this->getCodice() == self::OPERATORE){
            return true;
        }
		 if($this->getCodice() == self::OPERATORE_RICHIESTA){
            return true;
        }
        if($this->getCodice() == self::CONSULENTE){
            return true;
        }
        if($this->getCodice() == self::UTENTE_PRINCIPALE){
            return true;
        }
        return false;
    }
}