<?php

namespace MonitoraggioBundle\Service;

use RichiesteBundle\Entity\Richiesta;
use BaseBundle\Service\BaseServiceTrait;
use MonitoraggioBundle\Entity\TC38CausaleDisimpegno;
use Symfony\Component\DependencyInjection\ContainerInterface;
use AttuazioneControlloBundle\Entity\RichiestaLivelloGerarchico;
use AttuazioneControlloBundle\Entity\RichiestaProgramma;
use AttuazioneControlloBundle\Entity\Revoche\Revoca;
use AttuazioneControlloBundle\Entity\RichiestaImpegni;
use AttuazioneControlloBundle\Entity\ImpegniAmmessi;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints\Valid;

abstract class AGestoreImpegni implements IGestoreImpegni {
    use BaseServiceTrait;
    /**
     * @var Richiesta
     */
    protected $richiesta;

    public function __construct(ContainerInterface $container, Richiesta $richiesta) {
        $this->richiesta = $richiesta;
        $this->container = $container;
    }

    protected function getEconomia(): float {
        $atc = $this->richiesta->getAttuazioneControllo();
        $rendicontatoAmmesso = $atc->getImportoRendicontatoAmmessoTotale();
        $totaleImpegni = $this->richiesta->getTotaleImportoImpegni();

        return  $totaleImpegni - $rendicontatoAmmesso;
    }

    protected function getCausaleDisimpegno(string $codice): TC38CausaleDisimpegno {
        $causale = $this->getEm()->getRepository(TC38CausaleDisimpegno::class)->findOneBy([
            'causale_disimpegno' => $codice,
        ]);
        if (\is_null($causale)) {
            throw new \Exception("Causale '$codice' non trovata");
        }

        return $causale;
    }

    protected function getLivelloGerarchico(): RichiestaLivelloGerarchico {
        /** @var RichiestaProgramma $programma */
        $programma = $this->richiesta->getMonProgrammi()->first();

        return $programma->getLivelliGerarchiciObiettivoSpecifico()->first();
    }

    abstract public function aggiornaImpegniASaldo(): void;

    abstract public function impegnoNuovoProgetto(): void;

    abstract public function mostraSezionePagamento(): bool;

    public function aggiornaRevoca(Revoca $revoca): void {
        $atto = $revoca->getAttoRevoca();
        if (\is_null($atto) || \is_null($atto->getData())) {
            return;
        }

        $impegno = $revoca->getImpegno();
        if (\is_null($impegno)) {
            $impegno = new RichiestaImpegni($this->richiesta, RichiestaImpegni::DISIMPEGNO);
            $causale = $this->getCausaleDisimpegno(TC38CausaleDisimpegno::REVOCA);
            $impegno->setTc38CausaleDisimpegno($causale);
            $impegno->setRevoca($revoca);
            $revoca->setImpegno($impegno);
            $this->richiesta->addMonImpegni($impegno);
        }

        $impegno->setImportoImpegno($revoca->getContributoRevocato());
        $impegno->setDataImpegno($atto->getData());

        if ($impegno->getMonImpegniAmmessi()->isEmpty()) {
            $ammesso = new ImpegniAmmessi($impegno);
            $impegno->addMonImpegniAmmessi($ammesso);
        } else {
            /** @var ImpegniAmmessi $ammesso */
            $ammesso = $impegno->getMonImpegniAmmessi()->first();
            $ammesso->setImportoImpAmm($impegno->getImportoImpegno());
            $ammesso->setDataImpAmm($impegno->getDataImpegno());
        }
    }

    public function rimuoviImpegniRevoca(Revoca $revoca): void {
        $em = $this->getEm();
        $impegno = $revoca->getImpegno();
        foreach ($impegno->getMonImpegniAmmessi() as $ammesso) {
            $impegno->removeMonImpegniAmmessi($ammesso);
            $em->remove($ammesso);
        }
        $this->richiesta->removeMonImpegni($impegno);
        $em->remove($impegno);
    }

    public function validaImpegniBeneficiario(): ConstraintViolationListInterface {
        $visibile = $this->mostraSezionePagamento();
        if (! $visibile) {
            return new ConstraintViolationList();
        }
        /** @var ValidatorInterface $validator */
        $validator = $this->container->get('validator');
        $violations = $validator->validate($this->richiesta, new Valid(), ['impegni_beneficiario']);

        return $violations;
    }
}
