<?php

namespace RichiesteBundle\Service;

use BaseBundle\Service\BaseServiceTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use RichiesteBundle\Entity\Richiesta;
use RichiesteBundle\Utility\EsitoValidazione;
use Symfony\Component\HttpFoundation\Response;
use RichiesteBundle\Entity\ObiettivoRealizzativo;
use RichiesteBundle\Form\ObiettivoRealizzativoType;
use BaseBundle\Exception\SfingeException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Validator\ConstraintViolationInterface;

class GestoreObiettiviRealizzativiBase implements IGestoreObiettiviRealizzativi {
    use BaseServiceTrait;

    /**
     * @var Richiesta
     */
    protected $richiesta;

    public function __construct(ContainerInterface $container, Richiesta $richiesta) {
        $this->container = $container;
        $this->richiesta = $richiesta;
    }

    public function valida(): EsitoValidazione {
        $esito = new EsitoValidazione(true);

        /* @var ConstraintViolationInterface $violazione */
        foreach ($this->richiesta->getObiettiviRealizzativi() as $obiettivo) {
            $esitoObiettivo = $this->validaObiettivo($obiettivo);
            $esito = $esito->merge($esitoObiettivo);
        }

        if ($this->richiesta->getObiettiviRealizzativi()->isEmpty()) {
            $esito->setEsito(false);
            $esito->addMessaggio("E' necessario compilare almento un obiettivo realizzativo");
        }

        /*if ($this->checkObiettiviDistinti()) {
            $esito->setEsito(false);
            $esito->addMessaggio("Sono presenti Obiettivi realizzativi con codice duplicato");
        }*/

        if (false == $esito->getEsito()) {
            $esito->addMessaggioSezione('Le informazioni inserire non sono corrette o complete');
        }
        return $esito;
    }

    public function checkObiettiviDistinti(): bool {
        $presenti = [];

        foreach ($this->richiesta->getObiettiviRealizzativi() as $obiettivo) {
            $codice = $obiettivo->getCodiceOr();
            if (\in_array($codice, $presenti)) {
                return false;
            }
            $presenti[] = $codice;
        }

        return true;
    }

    public function validaObiettivo(ObiettivoRealizzativo $obiettivo): EsitoValidazione {
        $gruppiValidazione = $this->getValidationGroups();

        /** @var ValidatorInterface $validator */
        $validator = $this->container->get('validator');
        $validationList = $validator->validate($obiettivo, new Valid(), $gruppiValidazione);

        $esito = new EsitoValidazione(true);
        
        $lunghezza_testo = 
                $this->calcolaLunghezzaStringa($obiettivo->getAttivitaPreviste()) +
                $this->calcolaLunghezzaStringa($obiettivo->getObiettiviPrevisti()) +
                $this->calcolaLunghezzaStringa($obiettivo->getRisultatiAttesi());
        if ($lunghezza_testo > 5000) {
            $esito->setEsito(false);
            $esito->addMessaggio("OR" . $obiettivo->getCodiceOr() . ": la aree di testo 'obiettivi', 'attività previste' e 'risultati attesi' in totale non devono contenere più di 5.000 caratteri");
        }

        if ($validationList->count() > 0) {
            $esito->setEsito(false);
            $esito->addMessaggio("OR" . $obiettivo->getCodiceOr() . ": Informazioni non complete o errate");
        }

        return $esito;
    }

    protected function getValidationGroups(): array {
        $gruppoSpecificoBando = 'presentazione_' . $this->richiesta->getProcedura()->getId();

        return ['Default', 'presentazione', $gruppoSpecificoBando];
    }

    public function elencoObiettivi(): Response {
        return $this->render(self::STD_TWIG_ELENCO_OBIETTIVI, [
            'richiesta' => $this->richiesta,
            'disabled' => $this->isRichiestaDisabilitata(),
            'esito' => $this->valida(),
        ]);
    }

    protected function isRichiestaDisabilitata(): bool {
        /** @var GestoreRichiestaService $gestoreRichiesteFactory */
        $gestoreRichiesteFactory = $this->container->get('gestore_richieste');
        $gestore = $gestoreRichiesteFactory->getGestore($this->richiesta->getProcedura());
        $disabilitata = (bool) $gestore->isRichiestaDisabilitata($this->richiesta->getId());

        return $disabilitata;
    }

    public function nuovoObiettivo(): Response {
        //$numero = $this->getNuovoNumeroObiettivo();
        $obiettivo = new ObiettivoRealizzativo($this->richiesta);
        $this->richiesta->addObiettiviRealizzativi($obiettivo);

        return $this->modificaObiettivo($obiettivo);
    }

    protected function getElencoObiettiviUrl(): string {
        return $this->generateUrl(self::ROUTE_ELENCO_OBIETTIVI, [
            'id_richiesta' => $this->richiesta->getId(),
        ]);
    }

    public function getNuovoNumeroObiettivo(): int {
        $numeri = $this->richiesta->getObiettiviRealizzativi()
        ->map(function (ObiettivoRealizzativo $obiettivo) {
            return $obiettivo->getCodiceOr();
        })->filter(function ($codice) {
            return !\is_null($codice);
        })->toArray();

        for ($i = 1; $i < 7; ++$i) {
            if (\in_array($i, $numeri)) {
                continue;
            }

            return $i;
        }
        throw( new SfingeException('Impossibile creare ulteriori obiettivi') );
    }

    public function modificaObiettivo(ObiettivoRealizzativo $obiettivo): Response {
        $form = $this->createForm(ObiettivoRealizzativoType::class, $obiettivo, [
            'indietro' => $this->getElencoObiettiviUrl(),
            'disabled' => $this->isRichiestaDisabilitata(),
            'validation_groups' => $this->getValidationGroups(),
        ]);
        $request = $this->getCurrentRequest();
        $form->handleRequest($request);
        /*if ($this->checkObiettiviDistinti()) {
            $esito->setEsito(false);
            $esito->addMessaggio("Sono presenti Obiettivi realizzativi con codice duplicato");
        }*/
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $em = $this->getEm();
                $em->persist($obiettivo);
                $em->flush();

                $this->addSuccess('Informazioni salvate con successo');
                return $this->redirect($this->getElencoObiettiviUrl());
            } catch (\Exception $e) {
                $this->container->get('logger')->error($e->getTraceAsString());
                $this->addError('Errore durante il salvataggio delle informazioni');
            }
        }

        return $this->render(self::STD_TWIG_FORM_OBIETTIVO, [
            'form' => $form->createView(),
        ]);
    }

    public function eliminaObiettivo(ObiettivoRealizzativo $obiettivo): Response {
        if ($this->isRichiestaDisabilitata()) {
            throw new SfingeException('Impossibile cancellare un obiettivo di una richiesta disabilitata');
        }
        try {
            $em = $this->getEm();
            $this->richiesta->removeObiettiviRealizzativi($obiettivo);
            $em->remove($obiettivo);
            $em->flush();
            $this->addSuccess('Operazione effettuata con successo');
        } catch (\Exception $e) {
            $this->container->get('logger')->error($e->getTraceAsString());
            $this->addError('Errore durante il salvataggio delle informazioni');
        }

        return $this->redirect($this->getElencoObiettiviUrl());
    }
    
    public function calcolaLunghezzaStringa($stringa){
		$chars   = array('\r');
		$stringa = str_replace($chars, '', $stringa);
		if(function_exists("mb_strlen")){
			$lunghezza = mb_strlen($stringa, "utf-8");
		} else {
			$lunghezza = strlen($stringa);
		}
		return $lunghezza;
	}
}
