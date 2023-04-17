<?php
/**
 * Created by PhpStorm.
 * User: rstronati
 * Date: 05/02/16
 * Time: 16:39
 */

namespace RichiesteBundle\Service;


class GestoreResponse
{

    private $vista;
    private $dati;
    private $response;

    public function __construct($response,$vista = "",$dati = array())
    {
        $this->vista = $vista;
        $this->dati = $dati;
        $this->response = $response;
    }

    /**
     * @return string
     */
    public function getVista()
    {
        return $this->vista;
    }

    /**
     * @param string $vista
     */
    public function setVista($vista)
    {
        $this->vista = $vista;
    }

    /**
     * @return array
     */
    public function getDati()
    {
        return $this->dati;
    }

    /**
     * @param array $dati
     */
    public function setDati($dati)
    {
        $this->dati = $dati;
    }

    /**
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param mixed $response
     */
    public function setResponse($response)
    {
        $this->response = $response;
    }


}