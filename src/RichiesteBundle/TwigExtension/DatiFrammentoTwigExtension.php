<?php

namespace RichiesteBundle\TwigExtension;

use BaseBundle\Service\BaseServiceTrait;
use FascicoloBundle\Entity\IstanzaFascicolo;
use RichiesteBundle\Entity\Proponente;
use RichiesteBundle\Entity\Richiesta;
use SoggettoBundle\Entity\IncaricoPersona;
use SoggettoBundle\Entity\Soggetto;
use SoggettoBundle\Entity\StatoIncarico;
use SoggettoBundle\Entity\TipoIncarico;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class DatiFrammentoTwigExtension extends AbstractExtension {
    use BaseServiceTrait;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function getName() {
        return 'base_dati_frammento';
    }

    public function datoFrammento($istanzaFascicolo, $path, $valore = false) {
        if (is_numeric($istanzaFascicolo)) {
            $istanzaFascicolo = $this->container->get("doctrine")->getRepository("FascicoloBundle:IstanzaFascicolo")->find($istanzaFascicolo);
        }
        try {
            $raw = $this->container->get("fascicolo.istanza")->getOne($istanzaFascicolo, $path, $valore);
            $risultato = $valore ? $raw : \nl2br(\htmlspecialchars($raw, ENT_COMPAT | ENT_HTML401, "UTF-8"));
        } catch (\Exception $e) {
            $risultato = "-";
        }

        return $risultato;
    }

    public function datiFrammento($istanzaFascicolo, $path, $expanded = false) {
        if (is_numeric($istanzaFascicolo)) {
            $istanzaFascicolo = $this->container->get("doctrine")->getRepository("FascicoloBundle:IstanzaFascicolo")->find($istanzaFascicolo);
        }
        try {
            $valore = $this->container->get("fascicolo.istanza")->get($istanzaFascicolo, $path, $expanded);
        } catch (\Exception $e) {
            $valore = [];
        }

        return $valore;
    }

    public function labelFrammento($istanzaFascicolo, $path) {
        try {
            $datiFrammento = $this->container->get("fascicolo")->get($istanzaFascicolo->getFascicolo(), $path);
            $label = true == array_key_exists("label", $datiFrammento) ? $datiFrammento["label"] : "-";
        } catch (\Exception $e) {
            $label = "-";
        }

        return $label;
    }

    public function labelSceltaMultipla($istanzaFascicolo, $path) {
        try {
            $datiFrammento = $this->container->get("fascicolo")->get($istanzaFascicolo->getFascicolo(), $path);
            $scelte = $datiFrammento["scelte"];
        } catch (\Exception $e) {
            $scelte = [];
        }

        return $scelte;
    }

    public function oggettoFrammento(IstanzaFascicolo $istanzaFascicolo, string $path) {
        return $this->container->get("fascicolo.istanza")->getPathValue($istanzaFascicolo, $path);
    }

    /**
     * @return TipoIncarico[]
     * @throws \LogicException
     */
    public function incarichiAttiviRichiesta(Richiesta $richiesta): array {
        $soggetti = $richiesta->getProponenti()
        ->filter(function (Proponente $p): bool {
            return $p->isMandatario();
        })
        ->map(function (Proponente $p) {
            return $p->getSoggetto();
        })->toArray();
        $incarichi = \array_reduce($soggetti, function (array $carry, Soggetto $soggetto): array {
            $incarichi = $soggetto->getIncarichiPersone()->toArray();
            return \array_merge($carry, $incarichi);
        }, []);
        $persona = $this->getUser()->getPersona();
        $incarichiUtente = \array_filter($incarichi, function (IncaricoPersona $incarico) use ($persona) {
            return $incarico->getIncaricato() == $persona;
        });
        $incarichiAttivi = \array_filter($incarichiUtente, function(IncaricoPersona $i){
            return $i->getStato()->getCodice() == StatoIncarico::ATTIVO;
        });
        $tipoIncarico = \array_map(function(IncaricoPersona $i){
            return $i->getTipoIncarico();
        }, $incarichiAttivi);

        return $tipoIncarico;
    }

    public function getFunctions() {
        return [
            new TwigFunction('dato_frammento', [$this, 'datoFrammento'], ['is_safe' => ['html']]),
            new TwigFunction('oggetto_frammento', [$this, 'oggettoFrammento'], []),
            new TwigFunction('dati_frammento', [$this, 'datiFrammento'], ['is_safe' => ['html']]),
            new TwigFunction('label_frammento', [$this, 'labelFrammento'], ['is_safe' => ['html']]),
            new TwigFunction('label_scelta_multipla', [$this, 'labelSceltaMultipla'], ['is_safe' => ['html']]),
            new TwigFunction('incarichiAttivi', [$this, 'incarichiAttiviRichiesta']),
        ];
    }

    /**
     * @param string|array $content
     * @return string|array
     * @throws \Exception
     */
    public function regexReplace($content, array $arg = []) {
        if (2 != \count($arg)) {
            throw new \Exception("E'necessario indicare due argomenti: pattern e replacement, vedi funzionamento funzione preg_replace");
        }
        $res = \preg_replace($arg[0], $arg[1], $content);

        return $res;
    }

    public function getFilters() {
        return [
            new TwigFilter('regex_replace', [$this, 'regexReplace'], ['is_variadic' => true, 'is_safe' => ['html']]),
        ];
    }
}
