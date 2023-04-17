<?php

namespace AttuazioneControlloBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AttuazioneControlloBundle\Entity\Pagamento;
use AttuazioneControlloBundle\Entity\GiustificativoPagamento;
use AttuazioneControlloBundle\Entity\QuietanzaGiustificativo;
use AttuazioneControlloBundle\Entity\Contratto;
use AttuazioneControlloBundle\Entity\VocePianoCostoGiustificativo;
use AttuazioneControlloBundle\Entity\ProceduraAggiudicazione;
use AttuazioneControlloBundle\Entity\RichiestaImpegni;
use Swaggest\JsonSchema\Schema;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use AttuazioneControlloBundle\Entity\DocumentoGiustificativo;
use AttuazioneControlloBundle\Entity\DocumentoContratto;
use AttuazioneControlloBundle\Entity\DocumentoPagamento;
use AttuazioneControlloBundle\Entity\ImpegniAmmessi;
use AttuazioneControlloBundle\Entity\RichiestaLivelloGerarchico;
use AttuazioneControlloBundle\Entity\RichiestaProgramma;
use Doctrine\ORM\EntityManagerInterface;
use RichiesteBundle\Entity\Richiesta;

/**
 * @author vdamico
 */
class aggiornaRinviatiCommand extends ContainerAwareCommand {

    private $em;
    protected $container;

    public function __construct($name = null) {
        parent::__construct($name);
    }

    protected function configure() {
        $this->setName('pagamenti:aggiornaRinviati')->setDescription('');
        $this->addArgument('id_pagamento', InputArgument::REQUIRED, 'tipo di risorsa da trasmettere');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $id_pagamento = $input->getArgument('id_pagamento');
        $this->em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $pagamento = $this->em->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        $gestore = $this->getContainer()->get("gestore_pagamenti")->getGestore($pagamento->getProcedura());
        $gestore->aggiornaGiustificativiConImportiDaRipresentare($pagamento);

        $gestore->calcolaImportoRichiestoIniziale($pagamento);
        try {
            $this->em->beginTransaction();
            $this->em->persist($pagamento);
            // errore perchè il pagamento non è flushato, forse meglio fare una transazione
            $this->em->flush();
            //aggiorno le spese generali dopo ripresentati
            $gestore->gestioneGiustificativoSpeseGenerali($pagamento);
            $this->em->flush();
            $importoRendicontato = $pagamento->calcolaImportoRendicontato();
            $importoRendicontatoAmmesso = $pagamento->calcolaImportoRendicontatoAmmesso();
            $pagamento->setImportoRendicontato($importoRendicontato);
            $pagamento->setImportoRendicontatoAmmesso($importoRendicontatoAmmesso);
            $this->em->flush();
            $this->em->commit();
            $output->writeln("<comment>********** Operazione completa **********</comment>");
        } catch (\Exception $e) {
            $this->em->rollback();
            $output->writeln("<error>Impossibile effettuare l'operazione errore: {$e}</error>");
        }
    }

}
