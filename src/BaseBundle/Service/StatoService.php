<?php

/**
 * Created by PhpStorm.
 * User: rstronati
 * Date: 09/02/16
 * Time: 14:42
 */

namespace BaseBundle\Service;

use AttuazioneControlloBundle\Entity\Pagamento;
use BaseBundle\Entity\StatoLog;
use RichiesteBundle\Entity\Richiesta;
use Symfony\Component\DependencyInjection\ContainerInterface;

class StatoService {

    private $reader;
    private $container;

    /**
     * StatoService constructor.
     */
    public function __construct(ContainerInterface $container) {
        $this->reader = $container->get("annotation_reader");
        $this->container = $container;
    }

    public function avanzaStato($entity, $statoDestinazione, $flusha = false) {
        // Fix: il controllo !is_null serve per vedere se l'oggetto è presente nel DB
        if (method_exists($entity, 'getProcedura') && !is_null($entity->getId())) {
            if ($entity instanceof Richiesta) {
                $isRichiestaFirmaDigitale = $entity->getProcedura()->isRichiestaFirmaDigitale();
            } elseif ($entity instanceof Pagamento) {
                if ($entity->getProcedura()->getRendicontazioneProceduraConfig()) {
                    $isRichiestaFirmaDigitale = $entity->getProcedura()->getRendicontazioneProceduraConfig()->isRichiestaFirmaDigitale();
                } else {
                    // Per altre tipologia di Procedure come per esempio
                    // le acquisizioni imposto forzatamente la richiesta della firma digitale
                    $isRichiestaFirmaDigitale = true;
                }
            } else {
                $isRichiestaFirmaDigitale = $entity->getProcedura()->isRichiestaFirmaDigitaleStepSuccessivi();
            }

            // Cerco e sostituisco il testo troncato perché per le richieste di contributo e gli step successivi
            // gli stati sono VALIDATA e FIRMATA mentre per il pagamento è VALIDATO e FIRMATO.
            if (!$isRichiestaFirmaDigitale && strstr($statoDestinazione, '_VALIDAT')) {
                $statoDestinazione = str_replace('_VALIDAT', '_FIRMAT', $statoDestinazione);
            }
        }

        $object = new \ReflectionObject($entity);
        foreach ($object->getProperties() as $proprieta) {
            $annotazione_stato = $this->reader->getPropertyAnnotation($proprieta, "BaseBundle\Annotation\CampoStato");
            if ($annotazione_stato != null) {

                //trovo il nome del campo da leggere nell'oggetto stato
                $annotazioneReflection = new \ReflectionObject($annotazione_stato);
                $proprietaOggettoStato = $annotazioneReflection->getProperty("proprieta");
                $nomeProprietaOggettoStato = $proprietaOggettoStato->getValue($annotazione_stato); //rappresenta la proprieta da leggere nell'oggetto stato, tipicamente il codice
                //controllo se lo stato di destinazione e' un oggetto o una stringa
                if (!is_object($statoDestinazione)) {
                    //provo a tirare su l'oggetto stato
                    if (is_string($statoDestinazione)) {
                        $statoDestinazione = $this->container->get("doctrine")->getRepository("BaseBundle:Stato")->findOneBy(array($nomeProprietaOggettoStato => $statoDestinazione));
                    } else if (is_numeric($statoDestinazione)) {
                        $statoDestinazione = $this->container->get("doctrine")->getRepository("BaseBundle:Stato")->find($statoDestinazione);
                    }
                    if (is_null($statoDestinazione)) {
                        throw new \Exception("Oggetto stato non trovato per il codice indicato: " . $statoDestinazione);
                    }
                }

                $logStato = new StatoLog();

                //trovo l'utente connesso o se è commad metto default
                if (is_null($this->container->get('security.token_storage')->getToken())) {
                     $logStato->setUsername('<<command>>');
                } else {
                    $utente = $this->container->get('security.token_storage')->getToken()->getUser();
                    $logStato->setUsername($utente->getUsername());
                }

                //setto l'oggetto da loggare
                $logStato->setOggetto($object->getName());
                $logStato->setIdOggetto($entity->getId());

                //trovo lo stato attuale
                if (is_null($entity->getId())) {
                    $entitySalvata = $entity;
                } else {
                    $entitySalvata = $this->container->get("doctrine")->getRepository($object->getName())->find($entity->getId());
                }
                $entitySalvataReflection = new \ReflectionClass($entitySalvata);
                $proprietaStato = $entitySalvataReflection->getProperty($proprieta->getName());
                $proprietaStato->setAccessible(true);
                $statoAttuale = $proprietaStato->getValue($entitySalvata);

                //trovo il codice dello stato attuale
                $codiceStatoAttuale = "-";
                if ($statoAttuale != null) {
                    $statoAttualeReflection = new \ReflectionClass($statoAttuale);
                    $proprietaCodiceStato = $statoAttualeReflection->getProperty($nomeProprietaOggettoStato);
                    $proprietaCodiceStato->setAccessible(true);
                    $codiceStatoAttuale = $proprietaCodiceStato->getValue($statoAttuale);
                }
                $logStato->setStatoPrecedente($codiceStatoAttuale);

                //trovo il codice dello lo stato di destinazione
                $statoDestinazioneReflection = new \ReflectionClass($statoDestinazione);
                $proprietaStato = $statoDestinazioneReflection->getProperty($nomeProprietaOggettoStato);
                $proprietaStato->setAccessible(true);
                $codiceStatoDestinazione = $proprietaStato->getValue($statoDestinazione);
                $logStato->setStatoDestinazione($codiceStatoDestinazione);

                //setto lo stato di destinazione sull'oggetto
                $proprietaStato = $object->getProperty($proprieta->getName());
                $proprietaStato->setAccessible(true);
                $proprietaStato->setValue($entity, $statoDestinazione);

                $this->container->get("doctrine")->getManager()->persist($entity);
                $this->container->get("doctrine")->getManager()->persist($logStato);
                if ($flusha) {
                    $this->container->get("doctrine")->getManager()->flush();
                }
                return;
            }
        }

        throw new \Exception("Annotazione Campo Stato non definita");
    }

}
