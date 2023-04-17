<?php
/**
 * Created by PhpStorm.
 * User: rstronati
 * Date: 13/01/16
 * Time: 17:52
 */

namespace SoggettoBundle\Form\Entity;


use DocumentoBundle\Entity\DocumentoFile;
use Symfony\Component\Validator\Constraints as Assert;


class DocumentiIncarico
{

	/**
	 * @Assert\Valid
	 */
    protected $file_nomina;

	/**
	 * @Assert\Valid
	 */
    protected $file_carta_identita;

    /**
     * @Assert\Valid
     */
    protected $file_carta_identita_lr;

    /**
     * @return DocumentoFile
     */
    public function getFileCartaIdentita()
    {
        return $this->file_carta_identita;
    }

    /**
     * @param DocumentoFile $file_carta_identita
     */
    public function setFileCartaIdentita(DocumentoFile $file_carta_identita)
    {
        $this->file_carta_identita = $file_carta_identita;
    }

    /**
     * @return DocumentoFile
     */
    public function getFileNomina()
    {
        return $this->file_nomina;
    }

    /**
     * @param DocumentoFile $file_nomina
     */
    public function setFileNomina(DocumentoFile $file_nomina)
    {
        $this->file_nomina = $file_nomina;
    }

    /**
     * @return DocumentoFile
     */
    public function getFileCartaIdentitaLr()
    {
        return $this->file_carta_identita_lr;
    }

    /**
     * @param DocumentoFile $file_carta_identita_lr
     */
    public function setFileCartaIdentitaLr(DocumentoFile $file_carta_identita_lr)
    {
        $this->file_carta_identita_lr = $file_carta_identita_lr;
    }
}