<?php

namespace MonitoraggioBundle\Service;

use BaseBundle\Service\BaseServiceTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use RichiesteBundle\Entity\Richiesta;
use BaseBundle\Exception\SfingeException;
use AttuazioneControlloBundle\Entity\IterProgetto;
use MonitoraggioBundle\Entity\TC46FaseProcedurale;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\HttpFoundation\RedirectResponse;
use MonitoraggioBundle\Form\IterProgettoRichiestaType;
use RichiesteBundle\Utility\EsitoValidazione;
use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintViolationInterface;
use AttuazioneControlloBundle\Entity\Pagamento;
use AttuazioneControlloBundle\Entity\ModalitaPagamento;
use CipeBundle\Entity\Classificazioni\CupNatura;
use RichiesteBundle\Service\IGestoreRichiesta;
use SfingeBundle\Entity\AssistenzaTecnica;
use SfingeBundle\Entity\IngegneriaFinanziaria;

class GestoreIterProgettoBase implements IGestoreIterProgetto {
    use BaseServiceTrait;

    const VALIDATION_GROUP_RICHIESTA = 'presentazione_richiesta';

    /**
     * @var Richiesta
     */
    protected $richiesta;

    public function __construct(ContainerInterface $container, Richiesta $richiesta) {
        $this->richiesta = $richiesta;
        $this->container = $container;
    }

    public function aggiungiFasiProcedurali(): void {
        if ($this->isNotPorFESR()) {
            return;
        }
        $tipoIter = $this->richiesta->getProcedura()->getTipoIter();
        if (\is_null($tipoIter)) {
            throw new SfingeException("Tipo iter non associato alla procedura");
        }

        $codiceNaturaCup = $tipoIter->getCodice();
        $fasiDaProcedura = $this->getEm()->getRepository('MonitoraggioBundle:TC46FaseProcedurale')->findBy([
            'codice_natura_cup' => $codiceNaturaCup,
        ]);

        $fasiInDomanda = $this->richiesta->getMonIterProgetti()->map(function (IterProgetto $iter) {
            return $iter->getFaseProcedurale();
        });

        $fasiNonPresenti = \array_filter($fasiDaProcedura, function (TC46FaseProcedurale $fase) use ($fasiInDomanda) {
            return !\in_array($fase, $fasiInDomanda->toArray());
        });

        $iterDaAggiungere = \array_map(function (TC46FaseProcedurale $fase) {
            return new IterProgetto($this->richiesta, $fase);
        }, $fasiNonPresenti);

        \array_walk($iterDaAggiungere, function (IterProgetto $iter, $index) {
            $this->richiesta->addMonIterProgetti($iter);
            $this->getEm()->persist($iter);
        });
    }

    protected function isNotPorFESR(): bool {
        return $this->richiesta->getFlagPor() == false;
    }

    public function hasSezioneRichiestaVisibile(): bool {
        if ($this->isNotPorFESR()) {
            return false;
        }
        $tipoIter = $this->richiesta->getProcedura()->getTipoIter();
        if (\is_null($tipoIter)) {
            throw new SfingeException("Tipo iter non associato alla procedura");
        }
        $codiceNaturaCup = $tipoIter->getCodice();
        $lavoriPubblici = TC46FaseProcedurale::NATURA_LAVORI_PUBBLICI == $codiceNaturaCup;

        $procedura = $this->richiesta->getProcedura();
        $assistenzaTecnica = $procedura instanceof AssistenzaTecnica;
        $ingegneriaFinanziaria = $procedura instanceof IngegneriaFinanziaria;
        $proceduraParticolare = $assistenzaTecnica || $ingegneriaFinanziaria;

        return $lavoriPubblici || $proceduraParticolare;
    }

    public function modificaIterFaseRichiesta(array $options = []): Response {
        $this->aggiungiFasiProcedurali();
        /** @var IGestoreRichiesta $gestoreRichieste */
        $gestoreRichieste = $this->container->get('gestore_richieste')->getGestore($this->richiesta->getProcedura());
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'redirect_on_success' => false,
            'form_options' => [],
        ]);
        $options = $resolver->resolve($options);

        $formOptionResolver = new OptionsResolver();
        $formOptionResolver->setDefaults([
            'indietro' => false,
            'disabled' => $gestoreRichieste->isRichiestaDisabilitata(),
        ]);
        $formOptions = $formOptionResolver->resolve($options['form_options']);

        $form = $this->createForm(IterProgettoRichiestaType::class, $this->richiesta, $formOptions);
        $form->handleRequest($this->getCurrentRequest());
        $errori = $this->validaInPresentazioneDomanda();
        if ($form->isSubmitted()) {
            try {
                $this->getEm()->flush();
                $this->addSuccess("Informazioni salvate con successo");
                if ($options['redirect_on_success'] && $errori->getEsito()) {
                    return new RedirectResponse($options['redirect_on_success']);
                }
            } catch (\Exception $e) {
                $this->container->get('logger')->error($e->getTraceAsString());
                $this->addError('Errore nel salvataggio delle informazioni');
            }
        }
        $twigData = [
            'form' => $form->createView(),
            'errori' => $errori
        ];

        return $this->render('MonitoraggioBundle:iterProgetto:iterProgettoRichiesta.html.twig', $twigData);
    }

    public function validaInPresentazioneDomanda(): EsitoValidazione {
        $this->aggiungiFasiProcedurali();
        $esito = new EsitoValidazione(true);
        if (!$this->hasSezioneRichiestaVisibile()) {
            return $esito;
        }
        /** @var ValidatorInterface $validator */
        $validator = $this->container->get('validator');

        $iter = $this->richiesta->getMonIterProgetti();
        $violazioni = $validator->validate($iter, null, [Constraint::DEFAULT_GROUP, self::VALIDATION_GROUP_RICHIESTA]);

        foreach ($violazioni as $violazione) {
            $esito->addMessaggio($violazione->getMessage());
        }
        if ($violazioni->count() > 0) {
            $esito->setEsito(false);
            $esito->addMessaggioSezione('Sezione incompleta');
        }

        return $esito;
    }

    public function validaInSaldo(): EsitoValidazione {
        $atc = $this->richiesta->getAttuazioneControllo();
        $isUltimoPagamento = \array_reduce($atc->getPagamenti()->toArray(), function (bool $carry, Pagamento $pagamento): bool {
            $modalita = $pagamento->getModalitaPagamento();
            return $carry || \in_array($modalita->getCodice(), [ModalitaPagamento::SALDO_FINALE, ModalitaPagamento::UNICA_SOLUZIONE]);
        }, false);

        if(\is_null($tipoOperazione = $this->richiesta->getMonTipoOperazione())){
            return new EsitoValidazione(false, 
                'Tipologia CUP non definita per il progetto', 
                'Tipologia CUP non definita per il progetto: contattare l\'assistenza tecnica');
        }
        $naturaCup = $tipoOperazione->getCodiceNaturaCup();
        if (!$isUltimoPagamento || !\in_array($naturaCup, [
            CupNatura::REALIZZAZIONE_LAVORI_PUBBLICI,
        ])) {
            return new EsitoValidazione(true);
        }
        $esito = new EsitoValidazione(true);

        $validator = $this->container->get('validator');/** @var ValidatorInterface $validator */

        //Verifica che tutte le fasi procedurali abbiano date effettive diverse da NULL
        /** @var ConstraintViolationListInterface $errors */
        $errors = $validator->validate($this->richiesta->getMonIterProgetti(), new Valid(['traverse' => true]), ['rendicontazione_iter_progetto_beneficiario_finale', 'Default']);
        $esito->setEsito(0 == $errors->count());
        foreach ($errors as $error) { /* @var ConstraintViolationInterface $error */
            $esito->addMessaggio($error->getMessage());
        }

        if (!$esito->getEsito()) {
            $esito->addMessaggioSezione('Sezione incompleta');
        }

        return $esito;
    }
}
