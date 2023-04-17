<?php
/**
 * Created by PhpStorm.
 * User: rstronati
 * Date: 22/02/16
 * Time: 15:56
 */

namespace BaseBundle\ControlloAccessi;


interface IControlloAccesso
{

    public  function verificaAccesso($utente,$oggetto, $opzioni=array());

    /**
     * @param $oggetto
     * @param array $opzioni
     * @return boolean
     */
    public function verificaAccesso_ROLE_UTENTE($utente,$oggetto, $opzioni=array());
    /**
     * @param $oggetto
     * @param array $opzioni
     * @return boolean
     */
    public function verificaAccesso_ROLE_UTENTE_PA($utente,$oggetto, $opzioni=array());
    /**
     * @param $oggetto
     * @param array $opzioni
     * @return boolean
     */
    public function verificaAccesso_ROLE_MANAGER_PA($utente,$oggetto, $opzioni=array());
    /**
     * @param $oggetto
     * @param array $opzioni
     * @return boolean
     */
    public function verificaAccesso_ROLE_ADMIN_PA($utente,$oggetto, $opzioni=array());
    /**
     * @param $oggetto
     * @param array $opzioni
     * @return boolean
     */
    public function verificaAccesso_ROLE_SUPER_ADMIN($utente,$oggetto, $opzioni=array());

}
