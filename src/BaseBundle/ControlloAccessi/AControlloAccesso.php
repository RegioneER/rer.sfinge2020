<?php
/**
 * Created by PhpStorm.
 * User: rstronati
 * Date: 22/02/16
 * Time: 16:59
 */

namespace BaseBundle\ControlloAccessi;


abstract class AControlloAccesso implements IControlloAccesso
{

    public function verificaAccesso($utente, $oggetto, $opzioni = array())
    {
        $risultati = array();
        //valutare se mettere il $oggetto->get{contesto}() e passare questo ai metodi chiamati
        foreach($utente->getRuoli() as $ruolo){
            if(method_exists($this,"varificaAccesso".$ruolo)){
                $methodName = "varificaAccesso_".$ruolo;
                $risultati[] = $this->$methodName($oggetto, $opzioni);
            }
        }
        $puoAccedere = false;
        foreach($risultati as $risultato){
            $puoAccedere |= $risultato;
        }

        if(!$puoAccedere){

        }
    }

    /**
     * @param $oggetto
     * @param array $opzioni
     * @return boolean
     */
    public function verificaAccesso_ROLE_UTENTE($utente,$oggetto, $opzioni = array())
    {
        return true;
    }

    /**
     * @param $oggetto
     * @param array $opzioni
     * @return boolean
     */
    public function verificaAccesso_ROLE_UTENTE_PA($utente,$oggetto, $opzioni = array())
    {
        return true;
    }

    /**
     * @param $oggetto
     * @param array $opzioni
     * @return boolean
     */
    public function verificaAccesso_ROLE_MANAGER_PA($utente,$oggetto, $opzioni = array())
    {
        return true;
    }

    /**
     * @param $oggetto
     * @param array $opzioni
     * @return boolean
     */
    public function verificaAccesso_ROLE_ADMIN_PA($utente,$oggetto, $opzioni = array())
    {
        return true;
    }

    /**
     * @param $oggetto
     * @param array $opzioni
     * @return boolean
     */
    public function verificaAccesso_ROLE_SUPER_ADMIN($utente,$oggetto, $opzioni = array())
    {
        return true;
    }


}