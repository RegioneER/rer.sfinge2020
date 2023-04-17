<?php

namespace AttuazioneControlloBundle\TwigExtension;

use AttuazioneControlloBundle\Entity\VariazioneRichiesta;
use AttuazioneControlloBundle\Service\GestoreVariazioniService;
use AttuazioneControlloBundle\Service\Variazioni\IGestoreVariazioniConcreta;
use AttuazioneControlloBundle\Service\Variazioni\IGestoreVariazioniDatiBancari;
use AttuazioneControlloBundle\Service\Variazioni\IGestoreVariazioniPianoCosti;
use AttuazioneControlloBundle\Service\Variazioni\IGestoreVariazioniReferenti;
use AttuazioneControlloBundle\Service\Variazioni\IGestoreVariazioniSedeOperativa;
use RichiesteBundle\Utility\EsitoValidazione;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class MostraValidazioneVariazioneTwigExtension extends AbstractExtension {
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var GestoreVariazioniService
     */
    protected $factoryVariazioni;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
        $this->factoryVariazioni = $container->get("gestore_variazioni");
    }

    public function mostraValidazioneVariazione($sezione, $path, $label, $variazione = null, $annualita = null, $proponente = null, $variazioneDatiBancari = null) {
        $esito = $this->trovaEsito($sezione, $variazione, $annualita, $proponente, $variazioneDatiBancari);

        return $this->container->get("templating")->render("AttuazioneControlloBundle:Pagamenti:mostraValidazioneAttuazione.html.twig", ["esito" => $esito, "path" => $path, "label" => $label]);
    }

    public function mostraValidazioneInLineVariazione($sezione, $path, $label, $variazione = null, $annualita = null, $proponente = null) {
        $esito = $this->trovaEsito($sezione, $variazione, $annualita, $proponente);

        return $this->container->get("templating")->render("AttuazioneControlloBundle:Pagamenti:mostraValidazioneInLineAttuazione.html.twig", ["esito" => $esito, "path" => $path, "label" => $label]);
    }

    private function trovaEsito(string $sezione, VariazioneRichiesta $variazione, $annualita = null, $proponente = null, $variazioneDatiBancari = null) {
        /** @var IGestoreVariazioniConcreta|IGestoreVariazioniPianoCosti|IGestoreVariazioniDatiBancari|IGestoreVariazioniSedeOperativa|IGestoreVariazioniReferenti */
        $gestoreVariazioni = $this->factoryVariazioni->getGestoreVariazione($variazione);
        $esito = new EsitoValidazione(true);
        switch ($sezione) {
            case 'dati_generali_variazione':
                $esito = $gestoreVariazioni->validaDatiGenerali();
                break;
            case 'piano_costi_variazione':
                $esito = $gestoreVariazioni->validaPianoDeiCosti($annualita, $proponente);
                break;
            case 'documenti_variazione':
                $esito = $gestoreVariazioni->validaDocumenti($annualita);
                break;
            case 'dati_bancari':
                $esito = $gestoreVariazioni->validaDatiBancari();
                break;
            case 'dati_bancari_proponente':
                $esito = $gestoreVariazioni->validaDatiBancariProponente($variazioneDatiBancari);
                break;
            case 'sede_operativa':
                $esito = $gestoreVariazioni->validaSedeOperativa();
                break;
            case 'referenti':
                $esito = $gestoreVariazioni->validaReferenti();
            default:

            break;
        }

        return $esito;
    }

    public function getFunctions() {
        return [
            new TwigFunction('mostra_validazione_variazione', [$this, 'mostraValidazioneVariazione'], ['is_safe' => ['html']]),
            new TwigFunction('mostra_validazione_in_line_variazione', [$this, 'mostraValidazioneInLineVariazione'], ['is_safe' => ['html']]),
        ];
    }
}
