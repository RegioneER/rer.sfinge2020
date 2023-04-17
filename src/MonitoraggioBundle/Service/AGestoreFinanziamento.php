<?php

namespace MonitoraggioBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use RichiesteBundle\Entity\Richiesta;
use Doctrine\ORM\EntityManagerInterface;
use MonitoraggioBundle\Entity\TC33FonteFinanziaria;
use MonitoraggioBundle\Entity\TC34DeliberaCIPE;
use MonitoraggioBundle\Entity\TC35Norma;
use AttuazioneControlloBundle\Entity\Finanziamento;
use MonitoraggioBundle\Repository\TC33FonteFinanziariaRepository;
use MonitoraggioBundle\Repository\TC34DeliberaCIPERepository;
use MonitoraggioBundle\Repository\TC35NormaRepository;
use AttuazioneControlloBundle\Entity\Pagamento;
use AttuazioneControlloBundle\Entity\RichiestaProgramma;
use AttuazioneControlloBundle\Entity\RichiestaLivelloGerarchico;
use BaseBundle\Exception\SfingeException;
use MonitoraggioBundle\Entity\TC36LivelloGerarchico;
use MonitoraggioBundle\Entity\TC16LocalizzazioneGeografica;

abstract class AGestoreFinanziamento implements IGestoreFinanziamento {
    /**
     * @var Richiesta
     */
    protected $richiesta;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var TC33FonteFinanziariaRepository
     */
    protected $tc33Repository;

    /**
     * @var TC34DeliberaCIPERepository
     */
    protected $tc34Repository;

    /**
     * @var TC35NormaRepository
     */
    protected $tc35Repository;

    public function __construct(ContainerInterface $container, Richiesta $richiesta) {
        $this->container = $container;
        $this->richiesta = $richiesta;
        $this->em = $container->get('doctrine.orm.entity_manager');
        $this->tc33Repository = $this->em->getRepository(TC33FonteFinanziaria::class);
        $this->tc34Repository = $this->em->getRepository(TC34DeliberaCIPE::class);
        $this->tc35Repository = $this->em->getRepository(TC35Norma::class);
    }

    protected function getFinanziamento(TC33FonteFinanziaria $fondo, TC34DeliberaCIPE $delibera, TC35Norma $norma): Finanziamento {
        /** @var Finanziamento|bool $finanziamento */
        $finanziamento = $this->richiesta->getMonFinanziamenti()->filter(function (Finanziamento $f) use ($fondo, $delibera, $norma): bool {
            return $f->getTc35Norma() == $norma && $f->getTc33FonteFinanziaria() == $fondo && $f->getTc34DeliberaCipe() == $delibera;
        })->first();

        if (!$finanziamento) {
            $finanziamento = new Finanziamento($this->richiesta);
            $finanziamento->setTc33FonteFinanziaria($fondo)
                ->setTc34DeliberaCipe($delibera)
                ->setTc35Norma($norma);
            $this->richiesta->addMonFinanziamenti($finanziamento);

            if (TC33FonteFinanziaria::REGIONE == $fondo->getCodFondo()) {
                $regione = $this->getRegioneEmilia();
                $finanziamento->setTc16LocalizzazioneGeografica($regione);
            }
        }

        return $finanziamento;
    }

    protected function setFinanziamento(string $codiceFonteFinanziaria, string $codiceDelibera, string $codiceNorma, float $importo): Finanziamento {
        $fondo = $this->getFondoFinanziamento($codiceFonteFinanziaria);
        $delibera = $this->getDeliberaCIPE($codiceDelibera);
        $norma = $this->getNorma($codiceNorma);

        $finanziamento = $this->getFinanziamento($fondo, $delibera, $norma);
        $finanziamento->setImporto($importo);

        return $finanziamento;
    }

    protected function getFondoFinanziamento(string $codiceFondo): TC33FonteFinanziaria {
        return $this->tc33Repository->findOneBy(["cod_fondo" => $codiceFondo]);
    }

    protected function getDeliberaCIPE(string $codiceCIPE): TC34DeliberaCIPE {
        return $this->tc34Repository->findOneBy(["cod_del_cipe" => $codiceCIPE]);
    }

    protected function getNorma(string $codiceNorma): TC35Norma {
        return $this->tc35Repository->findOneBy(["cod_norma" => $codiceNorma]);
    }

    public function persistFinanziamenti(): void {
        foreach ($this->richiesta->getMonFinanziamenti() as $finanziamento) {
            $this->em->persist($finanziamento);
        }
    }

    public function aggiornaFinanziamento(bool $force = false): void {
        if(!$this->isFesr()){
            return;
        }

        if ($force || $this->isNecessarioRicalcoloFinanziamento()) {
            $this->calcolaFinanziamento();
            $this->aggiornaImportoAmmesso();
        }
    }

    protected function isFesr(): bool {
        return $this->richiesta->getFlagPor();
    }

    abstract protected function calcolaFinanziamento();

    protected function isNecessarioRicalcoloFinanziamento(): bool {
        if (!$this->hasUltimoPagamento()) {
            return false;
        }

        $atc = $this->richiesta->getAttuazioneControllo();
        $contributoConcesso = $atc->getContributoConcesso();
        $rendicontatoAmmesso = $atc->getImportoRendicontatoAmmessoTotale();
        $finanziamento = $this->richiesta->getTotaleFinanziamento();
        $contributoErogato = $this->getContributoErogato();
        /** @var \AttuazioneControlloBundle\Entity\VariazioneRichiesta $variazione */
        $variazione = $atc->getVariazioni(\AttuazioneControlloBundle\Entity\VariazionePianoCosti::class)->last();
        if ($variazione) {
            $contributoConcesso = $variazione->getContributoAmmesso();
        }

        $condizione = $contributoConcesso - $contributoErogato > $finanziamento - $rendicontatoAmmesso;

        return $condizione;
    }

    protected function hasUltimoPagamento(): bool {
        return \array_reduce($this->richiesta->getAttuazioneControllo()->getPagamenti()->toArray(),
            function (bool $carry, Pagamento $pagamento): bool {
                return $carry || $pagamento->isUltimoPagamento();
            }, false);
    }

    protected function getContributoErogato(): float {
        $atc = $this->richiesta->getAttuazioneControllo();
        if (\is_null($atc)) {
            return $this->getContributoConcesso();
        }
        if ($this->hasUltimoPagamento()) {
            return $atc->getContributoErogato() ?: 0.0;
        }

        return $this->getContributoConcesso();
    }

    protected function getCostoAmmesso() {
        return $this->richiesta->getIstruttoria()->getCostoAmmesso();
    }

    protected function getContributoConcesso() {
        return $this->richiesta->getIstruttoria()->getContributoAmmesso()?: 0.0;
    }

    protected function getRegioneEmilia(): TC16LocalizzazioneGeografica {
        return $this->em->getRepository('MonitoraggioBundle:TC16LocalizzazioneGeografica')->findOneBy([
            'codice_regione' => '08',
            'codice_provincia' => 'P08',
            'codice_comune' => '000',
        ]);
    }

    protected function aggiornaImportoAmmesso(): void {
        if ($this->richiesta->getMonProgrammi()->isEmpty()) {
            $this->container->get('logger')->warning('Nessun programma associato alla richiesta', [
                'ID richiesta' => $this->richiesta->getId(),
            ]);
        }

        $richiestaProgramma = $this->richiesta->getMonProgrammi()->first();

        $livelli = $richiestaProgramma->getLivelliGerarchiciObiettivoSpecifico();

        /** @var RichiestaLivelloGerarchico $livello */
        $livello = $livelli->first();
        $livello = $livello ?: $this->creaLivelloGerarchico($richiestaProgramma);
        $importo = $this->calcolaImportoAmmesso();

        $livello->setImportoCostoAmmesso($importo);
    }

    protected function calcolaImportoAmmesso(): float {
        $finanziamentiAmmessi = $this->richiesta->getMonFinanziamenti()->filter(function (Finanziamento $finanziamento): bool {
            return TC33FonteFinanziaria::PRIVATO != $finanziamento->getTc33FonteFinanziaria()->getCodFondo();
        });

        return \array_reduce($finanziamentiAmmessi->toArray(),
            function (float $carry, Finanziamento $finanziamento): float {
                return $carry + $finanziamento->getImporto();
            }, 0.0);
    }

    /**
     * @throws SfingeException
     */
    protected function creaLivelloGerarchico(RichiestaProgramma $programma): RichiestaLivelloGerarchico {
        $richiesta = $programma->getRichiesta();
        $procedura = $richiesta->getProcedura();
        /** @var TC36LivelloGerarchico $livello */
        $livello = $procedura->getLivelliGerarchici()->first();
        if (false === $livello) {
            throw new SfingeException('Livello gerarchico non associato all\'obiettivo specifico');
        }

        $livelloRichiesta = new RichiestaLivelloGerarchico($programma, $livello);
        $programma->addMonLivelliGerarchici($livelloRichiesta);

        return $livelloRichiesta;
    }
}
