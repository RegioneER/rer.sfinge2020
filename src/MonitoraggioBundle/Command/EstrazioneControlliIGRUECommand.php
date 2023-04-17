<?php

namespace MonitoraggioBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use BaseBundle\Service\BaseServiceTrait;
use AnagraficheBundle\Entity\Persona;
use Symfony\Component\Console\Input\InputArgument;

class EstrazioneControlliIGRUECommand extends ContainerAwareCommand {
    use BaseServiceTrait;

    const PERSONA_ID_ARGUMENT = 'persona_id';
    const SPREADSHEET_FORMAT = 'xlsx';
    const COMMAND_NAME = 'sfinge:monitoraggio:estrazione_igrue';

    /**
     * @var Persona
     */
    protected $persona;
    /**
     * @var \Swift_Message
     */
    protected $email;

    /**
     * @var string
     */
    protected $emailFrom = "no-reply@servizifederati.regione.emilia-romagna.it";
    /**
     * @var string
     */
    protected $soggettoFrom = "no-reply@servizifederati.regione.emilia-romagna.it";

    protected function initialize(InputInterface $input, OutputInterface $output) {
        $this->container = $this->getContainer();
        $this->container->get('translator')->setLocale('it_IT');

        $this->email = new \Swift_Message();
    }

    protected function configure() {
        $this
            ->setName(self::COMMAND_NAME)
            ->setDescription('Crea un file excel con i controlli IGRUE ed invia il risultato via email')
            ->addArgument(self::PERSONA_ID_ARGUMENT, InputArgument::REQUIRED, 'ID della persona a cui inviare il risultato');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $persona_id = $input->getArgument(self::PERSONA_ID_ARGUMENT);
        $this->persona = $this->getEm()->getRepository(Persona::class)->find($persona_id);
        if (\is_null($this->persona)) {
            throw new \Exception("Persona con ID '$persona_id' non trovata a sistema");
        }
        $this->preparaMessaggio();
        $excel = $this->getContainer()->get('monitoraggio.excel_controlli_igrue')->execute();
        $fileExcel = $this->getContainer()->get('phpoffice.spreadsheet')->getFile($excel, self::SPREADSHEET_FORMAT);
        $this->allega($fileExcel);
        $this->invia();
    }

    private function preparaMessaggio(): void {
        $this->email->setTo(
            $this->persona->getEmailPrincipale() ?: $this->persona->getEmailSecondario(),
            $this->persona->getNome() . ' ' . $this->persona->getCognome()
        );
        $this->email->setFrom($this->emailFrom, $this->soggettoFrom);

        $this->email->setSubject("Estrazione controlli igrue del " . (new \DateTime())->format('d/m/Y H:i:s'));
    }

    private function allega(string $file): void {
        $attachment = \Swift_Attachment::fromPath($file);
        $attachment->setFilename('estrazione.' . self::SPREADSHEET_FORMAT);

        $this->email->attach($attachment);
    }

    private function invia() {
        $this->container->get('mailer')->send($this->email);
    }
}
