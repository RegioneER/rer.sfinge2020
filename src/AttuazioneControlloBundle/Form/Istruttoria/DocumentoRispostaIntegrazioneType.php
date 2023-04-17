<?php
namespace AttuazioneControlloBundle\Form\Istruttoria;

use AttuazioneControlloBundle\Entity\Istruttoria\DocumentoRispostaIntegrazionePagamento;
use BaseBundle\Form\CommonType;
use DocumentoBundle\Component\GestioneFirmaDigitale;
use DocumentoBundle\Service\DocumentiService;
use Gdbnet\FatturaElettronica\XmlValidator;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Gdbnet\FatturaElettronica\FatturaElettronicaXmlReader;

class DocumentoRispostaIntegrazioneType extends CommonType {

    private $docService;

    public function __construct(DocumentiService $docService) {
        $this->docService = $docService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('descrizione', self::textarea, [
            "label" => "Descrizione documento",
            'constraints' => [new NotNull()],
        ]);

        $builder->add('documento_file', self::documento, [
            "label" => false,
            "lista_tipi" => $options["lista_tipi"],
        ]);

        $builder->add("submit", "Symfony\Component\Form\Extension\Core\Type\SubmitType", ["label" => "Carica"]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => DocumentoRispostaIntegrazionePagamento::class,
            "constraints" => [
                new Callback(function (DocumentoRispostaIntegrazionePagamento $documentoPagamento, ExecutionContextInterface $context) {
                        $documento = $documentoPagamento->getDocumentoFile();
                        $file = $documento->getFile();

                        if (!$documento->getTipologiaDocumento() || !$documentoPagamento->getDescrizione()) {
                            return;
                        }

                        if (!$documento->getTipologiaDocumento()->isFatturaElettronica()) {
                            return;
                        }

                        if (!$documento->isFatturaElettronica()) {
                            $context->addViolation('Estensione file NON valida, ammesse solo .xml e .p7m');
                        }

                        $mime = $file->getClientMimeType();
                        if (!in_array($mime, ['application/pkcs7-mime', 'application/pkcs7', 'application/x-pkcs7-mime', 'application/binary', 'application/octet-stream', 'application/xml', 'text/xml', 'text/plain'])) {
                            $context->addViolation("Formato mimetype NON valido: $mime");
                        }

                        if ($context->getViolations()->count() > 0) {
                            return;
                        }

                        $mime = $file->getClientMimeType();
                        if (in_array($mime, array('application/pkcs7-mime', 'application/pkcs7', 'application/binary', 'application/octet-stream'))) {
                            /* @var $serviceFD GestioneFirmaDigitale */
                            $serviceDoc = $this->docService;
                            $serviceDoc->initGestioneFirmaDigitale();
                            $serviceFD = $serviceDoc->getGestioneFirmaDigitale();
                            $serviceFD->loadDocumentFromPath($file->getRealPath());
                            $serviceFD->estraiContenutoDocumentoInterno();
                            $extFile = $serviceFD->getContenutoDocumentoInterno();
                            $xml = FatturaElettronicaXmlReader::clearSignature($extFile);
                        } else {
                            $xml = FatturaElettronicaXmlReader::clearSignature(file_get_contents($file->getPathname()));
                        }
                        $validator = new XmlValidator();

                        if (!$validator->validate($xml)) {
                            $errori = $validator->getErrors();
                            $erroriFlat = array();
                            foreach ($errori as $e) {
                                $erroriFlat[] = $e->message;
                            }
                            $context->addViolation('Fattura NON Valida: ' . implode(', ', $erroriFlat));
                        }
                    }),
            ],
        ]);
        $resolver->setRequired("lista_tipi");
        $resolver->setRequired("url_indietro");
    }
}
