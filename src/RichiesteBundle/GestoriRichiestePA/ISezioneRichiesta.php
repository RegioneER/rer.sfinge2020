<?php

namespace RichiesteBundle\GestoriRichiestePA;

interface ISezioneRichiesta
{
    const NOME_SEZIONE = '';
    /** 
     * @return boolean 
     */
    public function isValido();

    /**
     * @return string
     */
    public function getTitolo();

    /**
     * @return string
     */
    public function getUrl();

    /**
     * @return array
     */
    public function getMessaggi();

    public function valida();

    public function visualizzaSezione(array $parametri);

}