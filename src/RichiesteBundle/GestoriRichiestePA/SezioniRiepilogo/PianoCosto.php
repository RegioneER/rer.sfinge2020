<?php

namespace RichiesteBundle\GestoriRichiestePA\SezioniRiepilogo;

use RichiesteBundle\GestoriRichiestePA\ASezioneRichiesta;
use BaseBundle\Exception\SfingeException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use RichiesteBundle\Utility\EsitoValidazione;
use RichiesteBundle\Entity\Proponente;
use Symfony\Component\HttpFoundation\Response;
use RichiesteBundle\Form\PianoCostiBaseType;
use Symfony\Component\Form\FormError;
use Symfony\Component\DependencyInjection\ContainerInterface;
use RichiesteBundle\GestoriRichiestePA\IRiepilogoRichiesta;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

class PianoCosto extends ASezioneRichiesta {
    const TITOLO = 'Gestione piano costo';
    const SOTTOTITOLO = 'Inserire voci relative al piano costo';
    const NOME_SEZIONE = 'piano_costo';

    /**
     * @var Proponente
     */
    protected $proponente;

    /**
     * @var bool
     */
    protected $hasTotale = false;

    /**
     * @var string
     */
    protected $twig = 'RichiesteBundle:Richieste:pianoCosto.html.twig';

    /**
     * @var array
     */
    protected $opzioni_twig = [];

    /**
     * @var array
     */
    protected $opzioni_form = [];

    public function __construct(ContainerInterface $container, IRiepilogoRichiesta $riepilogo) {
        parent::__construct($container, $riepilogo);
        $this->proponente = $this->getMandario();
    }
	
	public function setProponente($proponente) {
		$this->proponente = $proponente;
	}
	
	public function getProponente(): ?Proponente {
		return $this->proponente;
	}

	public function getTitolo() {
        return self::TITOLO . ' ' . ($this->proponente);
    }

    public function getUrl() {
        return $this->generateUrl(self::ROTTA, [
            'id_richiesta' => $this->richiesta->getId(),
            'nome_sezione' => self::NOME_SEZIONE,
            'parametro1' => $this->proponente->getId(),
        ]);
    }

    public function valida() {

        /*if($this->richiesta->getProcedura()->getModalitaFinanziamentoAttiva() && $this->proponente->getVociModalitaFinanziamento()->count() == 0){
            $this->getGestoreModalitaFinanziamento()->generaModalitaFinanziamentoRichiesta($this->proponente);
        }*/
        $esito = $this->getGestorePianoCosto()->validaPianoDeiCostiProponente($this->proponente);
        /** @var EsitoValidazione $esito */
        $this->listaMessaggi = \array_merge($this->listaMessaggi, $esito->getTuttiMessaggi());
    }

    protected function getMandario(): Proponente {
        return $this->richiesta->getMandatario();
    }

    public function visualizzaSezione(array $parametri) {
        $this->setupPagina(self::TITOLO, self::SOTTOTITOLO);
        $em = $this->getEm();


        $procedura = $this->richiesta->getProcedura();

        try {
            if (0 == count($this->proponente->getVociPianoCosto())) {
                $esitoP = $this->getGestorePianoCosto()->generaPianoDeiCostiProponente($this->proponente->getId());

                if (!$esitoP) {
                    throw new SfingeException("Errore durante la generazione del piano costo, contattare l'assistenza tecnica");
                }
                $em->flush();
            }
            if (count($this->proponente->getVociPianoCosto()) != 0) {
                if (true == $procedura->getModalitaFinanziamentoAttiva() && $this->proponente->getVociModalitaFinanziamento()->count() == 0) {
                    $this->container->get('gestore_modalita_finanziamento')->getGestore($procedura)
                            ->generaModalitaFinanziamentoRichiesta($this->proponente->getId());
                }
            }

            $em->flush();
        } catch (SfingeException $e) {
            $this->container->get('logger')->error($e->getTraceAsString());
            $this->addFlash('error', $e->getMessage());

            return $this->addErrorRedirect('Errore generico nel salvataggio a database dei dati', 'home');
        } catch (\Exception $e) {
            $this->container->get('logger')->error($e->getTraceAsString());
            $this->addFlash('error', "Errore durante la generazione del piano costo, contattare l'assistenza tecnica");

            return $this->addErrorRedirect('Errore generico nel salvataggio a database dei dati', 'home');
        }

        return $this->aggiornaPianoDeiCostiProponente();
    }

    public function aggiornaPianoDeiCostiProponente(): Response {
        $this->ordinaVociPianoCosto();
        $gestorePianoCosto = $this->getGestorePianoCosto();

        $opzioni = [
            'url_indietro' => $this->riepilogo->getUrl(),
            'disabled' => $this->riepilogo->isRichiestaDisabilitata(),
            'modalita_finanziamento_attiva' => $this->getModalitaFinanziamentoAttiva(),
            'annualita' => \count($gestorePianoCosto->getAnnualita($this->proponente->getId())),
            'labels_anno' => $this->getLabelsAnno(),
            'totale' => $this->hasTotale,
        ];
        $form = $this->createForm(PianoCostiBaseType::class, $this->proponente, \array_merge($opzioni, $this->opzioni_form));
        $form->handleRequest($this->getCurrentRequest());

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getEm();
            try {
                $em->persist($this->proponente);
                $em->flush();
                $this->addFlash('success', 'Modifiche salvate correttamente');

                return $this->redirect($this->riepilogo->getUrl());
            } catch (\Exception $e) {
                $this->addFlash('error', 'Errore nel salvataggio delle informazioni');
            }
        } else {
            if (\count($form->getErrors()) != \count($form->getErrors(true))) {
                $error = new FormError('Sono presenti valori non corretti o non validi. È ammesso soltanto il separatore dei decimali.');
                $form->addError($error);
            }
        }

        $dati = [
            'onKeyUp' => 'calcolaTotaleSezione',
            'form' => $form->createView(),
            'annualita' => $opzioni['annualita'],
            'labels_anno' => $opzioni['labels_anno'],
            'totale' => $opzioni['totale'],
        ];

        $dati = \array_merge($dati, $this->opzioni_twig);

        return $this->render($this->twig, $dati);
    }

    protected function getModalitaFinanziamentoAttiva(): ?bool {
        return $this->richiesta->getProcedura()->getModalitaFinanziamentoAttiva();
    }

    protected function ordinaVociPianoCosto(): void {
        $voci_piano_costo = $this->ordina($this->proponente->getVociPianoCosto(), 'PianoCosto', 'Ordinamento');
        $this->proponente->setVociPianoCosto($voci_piano_costo);
    }

    protected function ordina(Collection $array, $oggettoInterno, $campo = null): Collection {
        $iterator = $array->getIterator();
        $iterator->uasort(function ($a, $b) use ($oggettoInterno, $campo) {
            $oggettoInterno = "get$oggettoInterno";
            if ($campo) {
                $campo = "get$campo";
                return $a->$oggettoInterno()->$campo() - $b->$oggettoInterno()->$campo();
            } else {
                return $a->$oggettoInterno() - $b->$oggettoInterno();
            }
        });
        return new ArrayCollection(\iterator_to_array($iterator));
    }
    
    public function showTotale(bool $v): self {
        $this->hasTotale = $v;

        return $this;
    }

    public function setTwigOptions(array $opzioni): self {
        $this->opzioni_twig = $opzioni;

        return $this;
    }

    public function setFormOptions(array $opzioni): self {
        $this->opzioni_form = $opzioni;

        return $this;
    }

    public function setTwig(string $twig): self {
        $this->twig = $twig;

        return $this;
    }

    /**
	 * @return string[]
	 */
	protected function getLabelsAnno(): array {
		$res = array();
		foreach ($this->getGestorePianoCosto()->getAnnualita($this->proponente->getId()) as $idx => $anno) {
			$res['importo_anno_' . $idx] = $anno;
		}

		return $res;
	}
}
