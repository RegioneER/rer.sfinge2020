<?php
/**
 * Created by PhpStorm.
 * User: rstronati
 * Date: 07/01/16
 * Time: 18:16
 */

namespace SoggettoBundle\Form\Entity;


use SoggettoBundle\Entity\TipoIncarico;
use Symfony\Component\Validator\Constraints as Assert;


class RicercaPersonaIncaricabile
{

    /**
     * @Assert\Length(min=2, max=255)
     */
    protected $nome;
    /**
     * @Assert\NotBlank()
     * @Assert\Length(min=2, max=255)
     */
    protected $cognome;

    /**
     * @Assert\Length(min=2, max=255)
     * @Assert\Email(checkMX = false)
     */
    protected $email;
    /**
     * @Assert\NotBlank()
     * @Assert\Length(min=16, max=16)
     */
    protected $codiceFiscale;


    protected $soggetto_id;
    protected $tipo_incarico;

    /**
     * @return mixed
     */
    public function getCognome()
    {
        return $this->cognome;
    }

    /**
     * @param mixed $cognome
     */
    public function setCognome($cognome)
    {
        $this->cognome = $cognome;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getCodiceFiscale()
    {
        return $this->codiceFiscale;
    }

    /**
     * @param mixed $email
     */
    public function setCodiceFiscale($codiceFiscale)
    {
        $this->codiceFiscale = $codiceFiscale;
    }

    /**
     * @return mixed
     */
    public function getNome()
    {
        return $this->nome;
    }

    /**
     * @param mixed $nome
     */
    public function setNome($nome)
    {
        $this->nome = $nome;
    }

    /**
     * @return mixed
     */
    public function getSoggettoId()
    {
        return $this->soggetto_id;
    }

    /**
     * @param mixed $soggetto_id
     */
    public function setSoggettoId($soggetto_id)
    {
        $this->soggetto_id = $soggetto_id;
    }



    /**
     * @return TipoIncarico
     */
    public function getTipoIncarico()
    {
        return $this->tipo_incarico;
    }

    /**
     * @param mixed TipoIncarico $tipo_incarico
     */
    public function setTipoIncarico(TipoIncarico $tipo_incarico)
    {
        $this->tipo_incarico = $tipo_incarico;
    }



}