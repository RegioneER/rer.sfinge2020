<?php

namespace DocumentoBundle\Validator\Constraints;

use DocumentoBundle\Entity\DocumentoFile;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;

class ValidaDocumentoValidator extends ConstraintValidator {

    public $message_vuoto = 'Il documento caricato risulta essere vuoto';
    public $message_mime = 'Il tipo del documento non corrisponde a nessuno di quelli previsti (%1)';
    public $message_dimensione = 'La dimensione del documento eccede quella prevista (Max %1 Mb)';

    /**
     * @var EntityManager
     */
    protected $entityManager;
    protected $serviceContainer;

    public function __construct(Container $serviceContainer) {
        $this->serviceContainer = $serviceContainer;
        $this->entityManager = $serviceContainer->get("doctrine");
    }

    public function validate($documentoFile, Constraint $constraint) {


        if (!($documentoFile instanceof DocumentoFile)) {
            return;
        }

        if (is_null($documentoFile->getFile())) {
            return;
        }

        if (is_null($documentoFile->getTipologiaDocumento())) {
            return;
        }
        /**
         * per bypassare il controllo specificare un solo mime type ammesso, che
         * non contenga uno slash, esempi:
         * 
         * per i file excel => excel
         */
        if ($this->serviceContainer->getParameter("file.controlla.mime.attivo")) {
            $mimeUmanizzati = $this->serviceContainer->get("funzioni_utili")->getEstensioniFormattate($documentoFile->getTipologiaDocumento());
            if($documentoFile->isEstensioneNonAmmessa() == true) {
                $messaggio = str_replace("%1", $mimeUmanizzati, $this->message_mime);
                $this->context->buildViolation($messaggio)
                    ->atPath('file')
                    ->addViolation();
            }
            
            $mimeAmmessiString = $documentoFile->getTipologiaDocumento()->getMimeAmmessi();
            $mimeAmmessiArray = explode(",", $mimeAmmessiString);
            $mime_ver = $documentoFile->getFile()->getMimeType();
            if (count($mimeAmmessiArray) == 1 && strpos($mimeAmmessiArray[0], "/") == false) {
            // non effettuare il controllo
            } elseif (!in_array($mime_ver, $mimeAmmessiArray)) {
                $messaggio = str_replace("%1", $mimeUmanizzati, $this->message_mime);
                $this->context->buildViolation($messaggio)
                    ->atPath('file')
                    ->addViolation();
            }
        }

        if ($this->serviceContainer->getParameter("file.controlla.dimensione.attivo")) {
            $dimensioneAmmessa = $documentoFile->getTipologiaDocumento()->getDimensioneMassima() * 1000000;

            if ($documentoFile->getFile()->getSize() > $dimensioneAmmessa) {
                $messaggio = str_replace("%1", $documentoFile->getTipologiaDocumento()->getDimensioneMassima(), $this->message_dimensione);

                $this->context->buildViolation($messaggio)
                    ->atPath('file')
                    ->addViolation();
            }
        }

        if ($this->serviceContainer->getParameter("file.controlla.firma.attivo")) {
            if ($documentoFile->getTipologiaDocumento()->isFirmaDigitale()) {

                //controllo l'estensione
                if ($this->serviceContainer->getParameter("file.controlla.firma.estensione")) {
                    if (strlen($documentoFile->getFile()->getClientOriginalName()) <= 8) {
                        $this->context->buildViolation("Il documento caricato ha un estensione troppo corta")
                            ->atPath('file')
                            ->addViolation();
                        return;
                    }
                    $file_ext = substr($documentoFile->getFile()->getClientOriginalName(), -4);
                    $file_ext = strtolower($file_ext);
                    if ($file_ext != ".p7m") {
                        $this->context->buildViolation("Il documento caricato non ha estensione .p7m")
                            ->atPath('file')
                            ->addViolation();
                        return;
                    }
                }


                if ($this->serviceContainer->getParameter("file.controlla.firma.certificato")) {

                    $cf_firmatario_richieste = $documentoFile->getCfFirmatario();
                    //se ho attivato il controllo del cf verifico che sia stato indicato
                    if ($this->serviceContainer->getParameter("file.controlla.firma.cf")) {
                        if (empty($cf_firmatario_richieste)) {
                            $this->context->buildViolation("Codice fiscale del firmatario non indicato")
                                ->atPath('file')
                                ->addViolation();
                            return;
                        }
                    }

                    //controllo la firma
                    try {
                        $esito = $this->serviceContainer->get("documenti")->verificaDocumento($documentoFile->getFile(),
                            $this->serviceContainer->getParameter("file.controlla.firma.cf"),
                            $cf_firmatario_richieste
                        );
                    } catch (\Exception $e) {
                        $this->context->buildViolation("Documento non validabile")
                            ->atPath('file')
                            ->addViolation();
                        return;
                    }

                    if ($esito !== true) {
                        $this->context->buildViolation("Firma non valida: " . $esito["error"])
                            ->atPath('file')
                            ->addViolation();
                        return;
                    }
                }
            }
        }
    }

}
