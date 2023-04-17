<?php

namespace Performer\PayERBundle\Service;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Performer\PayERBundle\Component\EnvelopeRichiestaAcquistoMarcaDaBollo;
use Performer\PayERBundle\Entity\AcquistoMarcaDaBollo;
use Performer\PayERBundle\Entity\Esito;
use Performer\PayERBundle\Entity\EsitoPagamento;
use Performer\PayERBundle\Entity\MarcaDaBollo;
use Performer\PayERBundle\Entity\RichiestaAcquistoMarcaDaBollo;
use Performer\PayERBundle\Event\EbolloNotificaEsitoEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EBollo implements EBolloInterface
{
    /**
     * @var string
     */
    protected $codiceServizio;

    /**
     * @var string
     */
    protected $urlAcquistoCarrelloMbd;

    /**
     * @var string
     */
    protected $urlInvioCarrelloMbd;

    /**
     * @var string
     */
    protected $urlEsitoCarrelloMbd;

    /**
     * @var string|null
     */
    protected $appDomain;

    /**
     * @var UrlGeneratorInterface
     */
    protected $urlGenerator;

    /**
     * @var PayERInterface
     */
    protected $payer;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @param string $codiceServizio
     * @param string $urlAcquistoCarrelloMbd
     * @param string $urlInvioCarrelloMbd
     * @param string $urlEsitoCarrelloMbd
     * @param string $appDomain
     * @param UrlGeneratorInterface $urlGenerator
     * @param PayERInterface $payer
     * @param EntityManagerInterface $em
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        string $codiceServizio,
        string $urlAcquistoCarrelloMbd,
        string $urlInvioCarrelloMbd,
        string $urlEsitoCarrelloMbd,
        ?string $appDomain,
        UrlGeneratorInterface $urlGenerator,
        PayERInterface $payer,
        EntityManagerInterface $em,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->codiceServizio = $codiceServizio;
        $this->urlAcquistoCarrelloMbd = $urlAcquistoCarrelloMbd;
        $this->urlInvioCarrelloMbd = $urlInvioCarrelloMbd;
        $this->urlEsitoCarrelloMbd = $urlEsitoCarrelloMbd;
        $this->appDomain = $appDomain;
        $this->urlGenerator = $urlGenerator;
        $this->payer = $payer;
        $this->em = $em;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @return string
     */
    public function getUrlAcquistoCarrelloMbd(): string
    {
        return $this->urlAcquistoCarrelloMbd;
    }

    /**
     * @return string
     */
    public function getUrlInvioCarrelloMbd(): string
    {
        return $this->urlInvioCarrelloMbd;
    }

    /**
     * @return string
     */
    public function getUrlEsitoCarrelloMbd(): string
    {
        return $this->urlEsitoCarrelloMbd;
    }

    /**
     * @param string $marcaDaBolloId
     * @param string $fileContentBase64
     * @param string $identificativoPagatore
     * @param string $denominazionePagatore
     * @param string $provinciaResidenzaPagatore
     * @param string|null $emailPagatore
     * @return AcquistoMarcaDaBollo
     */
    public function createAcquistoMarcaDaBollo(
        string $marcaDaBolloId,
        string $fileContentBase64,
        string $identificativoPagatore,
        string $denominazionePagatore,
        string $provinciaResidenzaPagatore,
        string $emailPagatore = null
    ): AcquistoMarcaDaBollo
    {

        $hashDocumento = hash('sha256', $fileContentBase64);

        $marcaDaBollo = $this->em->find(MarcaDaBollo::class, $marcaDaBolloId);
        $acquistoMarcaDaBollo = new AcquistoMarcaDaBollo();
        $acquistoMarcaDaBollo
            ->setMarcaDaBollo($marcaDaBollo)
            ->setHashDocumento($hashDocumento)
            ->setIdentificativoPagatore($identificativoPagatore)
            ->setDenominazionePagatore($denominazionePagatore)
            ->setProvinciaResidenzaPagatore($provinciaResidenzaPagatore)
            ->setEmailPagatore($emailPagatore)
        ;

        $this->em->persist($acquistoMarcaDaBollo);
        $this->em->flush();

        return $acquistoMarcaDaBollo;
    }

    public function handleEsitoRichiesta(array $esito)
    {
        /**
         * @var RichiestaAcquistoMarcaDaBollo $richiesta
         */
        $richiesta = $this->em->getRepository(RichiestaAcquistoMarcaDaBollo::class)->find($esito['idOperazionePortale']);
        $richiesta
            ->setDataEsito(new DateTime())
            ->setEsito($this->em->getRepository(Esito::class)->find($esito['esitoCarrello']))
            ->setDataEsito(new DateTime())
            ->setPid($esito['pid'])
        ;

        $repoEsitoPagamento = $this->em->getRepository(EsitoPagamento::class);
        $acquistoMarcaDaBollos = [];
        foreach ($esito['esitiMBD'] as $esitoMBD) {
            foreach ($richiesta->getAcquistoMarcaDaBollos() as $acquistoMarcaDaBollo) {
                if ($acquistoMarcaDaBollo->getHashDocumento() === $esitoMBD['hashDocumento']) {
                    $acquistoMarcaDaBollo
                        ->setIuv($esitoMBD['iuv'])
                        ->setIdTransazione($esitoMBD['idTransazione'])
                        ->setDataTransazione(new DateTime($esitoMBD['dataOraTransazione']))
                        ->setEsitoPagamento($repoEsitoPagamento->find($esitoMBD['codiceEsitoPagamento']))
                        ->setCodiceFiscalePsp($esitoMBD['codiceFiscalePSP'])
                        ->setDenominazionePsp($esitoMBD['denominazionePSP'])
                        ->setRt($esitoMBD['rt'])
                    ;
                    $acquistoMarcaDaBollos[] = $acquistoMarcaDaBollo;
                }
            }
        }

        $this->em->flush();

        /** Eseguo il dispatch dell'evento */
        foreach ($acquistoMarcaDaBollos as $acquistoMarcaDaBollo) {
            $event = new EbolloNotificaEsitoEvent($acquistoMarcaDaBollo);
            $this->eventDispatcher->dispatch(EbolloNotificaEsitoEvent::NAME, $event);
        }

        return $richiesta;
    }

    /**
     * @param RichiestaAcquistoMarcaDaBollo $richiestaAcquistoMarcaDaBollo
     * @return mixed
     * @throws Exception
     */
    public function send(RichiestaAcquistoMarcaDaBollo $richiestaAcquistoMarcaDaBollo)
    {
        $envelope = new EnvelopeRichiestaAcquistoMarcaDaBollo($richiestaAcquistoMarcaDaBollo);
        $envelope
            ->setCodicePortale($this->payer->getCodicePortale())
            ->setCodiceServizio($this->codiceServizio)
            ->setUrlRitorno($this->getUrlRitorno())
            ->setUrlNotifica($this->getUrlNotifica())
            ->setUrlIndietro($this->getUrlIndietro())
            ->setCommitNotifica('1')
            ->setNotificaEsitiNegativi('1')
        ;
        $dataNormalized = $envelope->normalizeForSend();
        $bufferBi = $this->payer->getBufferBi($dataNormalized);
        $result = $this->payer->sendRequest($bufferBi, $this->urlAcquistoCarrelloMbd);

        if (isset($result['codiceErrore'])) {
            $esito = $this->em->getRepository(Esito::class)->find($result['codiceErrore']);
            $richiestaAcquistoMarcaDaBollo
                ->setEsito($esito)
                ->setDataEsito(new DateTime())
            ;
            $this->em->flush();
            throw new Exception(sprintf('Errore invio richiesta a PayER (url %s): %s - %s', $this->urlAcquistoCarrelloMbd, $result['codiceErrore'], $result['descrizioneErrore']));
        }

        return $result['rid'];
    }

    /**
     * @param string $rid
     * @return RedirectResponse
     */
    public function redirectToEbollo(string $rid): RedirectResponse
    {
        return new RedirectResponse($this->urlInvioCarrelloMbd . '?rid=' . $rid );
    }

    /**
     * @param string $pid
     * @return array
     */
    public function esitoCarrello(string $pid): array
    {
        // In questa chiamata non è richiesto il buffer bi
        $data = ['pid' => $pid];
        return $this->payer->sendRequest($data, $this->urlEsitoCarrelloMbd);
    }

    /**
     * @param RichiestaAcquistoMarcaDaBollo $richiestaAcquistoMarcaDaBollo
     * @return void
     */
    public function aggiornaEsito(RichiestaAcquistoMarcaDaBollo $richiestaAcquistoMarcaDaBollo)
    {
        if (!$richiestaAcquistoMarcaDaBollo->getPid()) {

            if (!$richiestaAcquistoMarcaDaBollo->getRid()) {
                // Se non è impostato nemmeno il RID allora non è possibile aggiornare l'esito
                return;
            }

            $richiestaAcquistoMarcaDaBollo->setPid($richiestaAcquistoMarcaDaBollo->getRid());
        }

        try {
            $this->handleEsitoRichiesta($this->esitoCarrello($richiestaAcquistoMarcaDaBollo->getPid()));
        } catch (Exception $ex) {
            // Se viene restituito errore probabilmente è perchè non è ancora pronto l'esito sul server di Ebollo
            // pertanto non faccio nulla
        }
    }

    /**
     * @param RichiestaAcquistoMarcaDaBollo $richiestaAcquistoMarcaDaBollo
     * @param bool $esitoPositivo
     * @return JsonResponse
     */
    public function confermaRicezioneEsito(RichiestaAcquistoMarcaDaBollo $richiestaAcquistoMarcaDaBollo, bool $esitoPositivo = true): Response
    {
        $data = [
            'codicePortale' => $this->payer->getCodicePortale(),
            'idOperazionePortale' => $richiestaAcquistoMarcaDaBollo->getId(),
            'pid' => $richiestaAcquistoMarcaDaBollo->getPid(),
            'conferma' => $esitoPositivo ? 'OK' : 'NOK',
        ];
        return new JsonResponse($data);
    }

    /**
     * @return string
     */
    protected function getUrlRitorno(): string
    {
        if ($this->appDomain) {
            return $this->appDomain . $this->urlGenerator->generate('performer.pay_er.ebollo_ritorno');
        }

        return $this->appDomain . $this->urlGenerator->generate('performer.pay_er.ebollo_ritorno', [], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @return string
     */
    protected function getUrlNotifica(): string
    {
        if ($this->appDomain) {
            return $this->appDomain . $this->urlGenerator->generate('performer.pay_er.ebollo_notifica');
        }

        return $this->appDomain . $this->urlGenerator->generate('performer.pay_er.ebollo_notifica', [], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @return string
     */
    protected function getUrlIndietro(): string
    {
        if ($this->appDomain) {
            return $this->appDomain . $this->urlGenerator->generate('performer.pay_er.ebollo_indietro');
        }

        return $this->appDomain . $this->urlGenerator->generate('performer.pay_er.ebollo_indietro', [], UrlGeneratorInterface::ABSOLUTE_URL);
    }

}