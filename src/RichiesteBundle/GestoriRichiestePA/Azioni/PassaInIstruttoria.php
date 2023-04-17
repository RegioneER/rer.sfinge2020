<?php

namespace RichiesteBundle\GestoriRichiestePA\Azioni;

use RichiesteBundle\GestoriRichiestePA\Azione;
use RichiesteBundle\GestoriRichiestePA\IRiepilogoRichiesta;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use IstruttorieBundle\Entity\IstruttoriaRichiesta;
use BaseBundle\Entity\StatoRichiesta;
use IstruttorieBundle\Entity\IstruttoriaVocePianoCosto;
use RichiesteBundle\Entity\VocePianoCosto;
use RichiesteBundle\Entity\Proponente;
use SoggettoBundle\Service\SoggettoVersioning;
use RichiesteBundle\Entity\TipoVoceSpesa;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface;
use RichiesteBundle\Entity\Richiesta;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;

class PassaInIstruttoria extends Azione {
    const NOME_AZIONE = 'passa_in_istruttoria';
    const TOKEN_CSRF_NAME = 'token';
    const TOKEN_ID = '_csrf/token';

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Richiesta
     */
    protected $richiesta;

    public function __construct(ContainerInterface $container, IRiepilogoRichiesta $riepilogo, $nome_azione = self::NOME_AZIONE) {
        parent::__construct($container->get('router'), $riepilogo, $nome_azione);
        $this->titolo = 'Completa';
        $this->container = $container;
        $this->richiesta = $this->getRichiesta();

        $this->url = $this->url = $this->generateUrl(self::ROTTA, [
            'id_richiesta' => $this->getRichiesta()->getid(),
            'nome_azione' => $nome_azione,
            self::TOKEN_CSRF_NAME => $this->generaToken(self::TOKEN_ID),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function isVisibile() {
        return $this->riepilogo->isValido() && !$this->riepilogo->isRichiestaDisabilitata();
    }

    /**
     * @return RedirectResponse
     *
     * @throws AccessDeniedHttpException
     */
    public function getRisultatoEsecuzione() {
        $this->checkSicurezza();
        $this->passaInIstruttoria();

        return $this->redirect($this->riepilogo->getUrl());
    }

    protected function generaToken(string $tokenId): string{
        /** @var CsrfTokenManagerInterface $manager */
        $manager = $this->container->get('security.csrf.token_manager');

        return $manager->getToken($tokenId);
    }

    /**
     * @throws AccessDeniedHttpException
     */
    protected function checkSicurezza() {
        $request = $this->getCurrentRequest();
        $tokenValue = $request->query->get(self::TOKEN_CSRF_NAME);
        $token = new CsrfToken(self::TOKEN_ID, $tokenValue);
        /** @var CsrfTokenManagerInterface $manager */
        $manager = $this->container->get('security.csrf.token_manager');
        if (!$manager->isTokenValid($token)) {
            throw new AccessDeniedHttpException('CSRF token is invalid.');
        }

        if (!$this->riepilogo->isValido()) {
            throw new AccessDeniedHttpException('Operazione non consentita');
        }
    }

    /**
     * @return Request
     */
    protected function getCurrentRequest() {
        return $this->container->get('request_stack')->getCurrentRequest();
    }

    protected function passaInIstruttoria() {
        $this->checkSicurezza();
        $em = $this->getEm();
        $connection = $em->getConnection();
        try {
            $connection->beginTransaction();

            $this->creaOggettiVersions();
            $this->richiesta->setDataInvio(new \DateTime());

            $mandatario = $this->creaPianoCostoIstruttoria();
            foreach ($mandatario->getVociPianoCosto() as $voce) {
                $em->persist($voce->getIstruttoria());
                $em->persist($voce);
            }
            $em->persist($mandatario);

            $istruttoria = $this->inizializzaIstruttoria();
            $this->effettuaOperazioniSuplementari();
            $em->persist($this->richiesta);
            $em->persist($istruttoria);
            $em->flush();

            $this->container->get('sfinge.stati')->avanzaStato($this->richiesta, StatoRichiesta::PRE_PROTOCOLLATA);
            $this->getGestoreIstruttoria()->aggiornaIstruttoriaRichiesta($this->richiesta->getId());

            $connection->commit();

            $this->addFlash('success', 'Richiesta validata con successo');
        } catch (\Exception $e) {
            if ($connection->isTransactionActive()) {
                $connection->rollBack();
            }
            $this->container->get('logger')->error('Errore passaggio in istruttoria', [
                'exception' => $e,
                'richiesta' => $this->richiesta,
            ]);
            $this->addFlash('error', "Errore durante la validazione, contattare l'assistenza tecnica");
        }
    }

    protected function effettuaOperazioniSuplementari(): void {
    }

    /**
     * @return IstruttoriaRichiesta
     * @throws \Exception
     */
    protected function inizializzaIstruttoria() {
        /** @var \RichiesteBundle\Entity\Bando60\OggettoLegge14 $oggettoRichiesta */
        //$oggettoRichiesta = $this->richiesta->getPrimoOggetto();
        if(is_null($this->richiesta->getIstruttoria())) {
            $istruttoria = new IstruttoriaRichiesta();
            $istruttoria->setRichiesta($this->richiesta);
            $this->richiesta->setIstruttoria($istruttoria);
        } else {
            $istruttoria = $this->richiesta->getIstruttoria();
        }
        $istruttoria->setSospesa(false);
        $istruttoria->setCostoAmmesso($this->calcolaCostoAmmesso());
        
        return $istruttoria;
    }

    /**
     * @return float
     */
    protected function calcolaCostoAmmesso() {
        $tot = \array_reduce($this->richiesta->getVociPianoCosto()->toArray(), function ($carry, VocePianoCosto $voce) {
            if (TipoVoceSpesa::TOTALE == $voce->getPianoCosto()->getTipoVoceSpesa()->getCodice()) {
                return $carry;
            }
            $res = $carry;
            for ($i = 1; $i < 8; ++$i) {
                $res += $voce->{'getImportoAnno' . $i}();
            }
            return $res;
        }, 0);
        return $tot;
    }

    /**
     * @return \IstruttorieBundle\Service\IGestoreIstruttoria
     */
    protected function getGestoreIstruttoria() {
        return $this->container->get('gestore_istruttoria')->getGestore($this->richiesta->getProcedura());
    }

    protected function creaOggettiVersions() {
        $versioning = new SoggettoVersioning();
        foreach ($this->richiesta->getProponenti() as $proponente) {
            if (\is_null($proponente->getSoggettoVersion())) {
                $soggetto_version = $versioning->creaSoggettoVersion($proponente->getSoggetto());
                $proponente->setSoggettoVersion($soggetto_version);
            }

            foreach ($proponente->getSedi() as $sedeOperativa) {
                if (is_null($sedeOperativa->getSedeVersion())) {
                    $sede_version = $versioning->creaSedeVersion($sedeOperativa->getSede());
                    $sedeOperativa->setSedeVersion($sede_version);
                }
            }
        }
    }

    /**
     * @throws \Exception
     *
     * @return Proponente
     */
    public function creaPianoCostoIstruttoria() {
        $proponente = $this->getRichiesta()->getMandatario();
        $voci_piano_costo = $proponente->getVociPianoCosto();
        $vociConIstruttoria = $voci_piano_costo
            ->map(function (VocePianoCosto $voce) {
                return \RichiesteBundle\GestoriRichiestePA\Azioni\PassaInIstruttoria::inizializzaPianoCostoIstruttoria($voce);
            });
        $proponente->setVociPianoCosto($vociConIstruttoria);

        return $proponente;
    }

    /**
     * @param VocePianoCosto
     * @return VocePianoCosto
     */
    public static function inizializzaPianoCostoIstruttoria(VocePianoCosto $vocePianoCosto) {
        $voceIstruttoria = $vocePianoCosto->getIstruttoria();
        if (\is_null($voceIstruttoria)) {
            $voceIstruttoria = new IstruttoriaVocePianoCosto();
            $voceIstruttoria->setTaglioAnno1(0.00);
            for ($anno = 1; $anno < 8; ++$anno) {
                $voceIstruttoria->{'setImportoAmmissibileAnno' . $anno}(
                    $vocePianoCosto->{'getImportoAnno' . $anno}()
                );
            }
            $voceIstruttoria->setVocePianoCosto($vocePianoCosto);
            $vocePianoCosto->setIstruttoria($voceIstruttoria);
        }

        return $vocePianoCosto;
    }

    /**
     * Adds a flash message to the current session for type.
     *
     * @param string $type    The type
     * @param string $message The message
     *
     * @throws \LogicException
     */
    protected function addFlash($type, $message) {
        if (!$this->container->has('session')) {
            throw new \LogicException('You can not use the addFlash method if sessions are disabled.');
        }

        $this->container->get('session')->getFlashBag()->add($type, $message);
    }

    /**
     * @return EntityManagerInterface
     */
    protected function getEm() {
        return $this->container->get('doctrine.orm.entity_manager');
    }
}
