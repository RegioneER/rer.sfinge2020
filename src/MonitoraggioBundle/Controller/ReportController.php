<?php

namespace MonitoraggioBundle\Controller;

use BaseBundle\Controller\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Menuitem;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use RichiesteBundle\Entity\Richiesta;
use MonitoraggioBundle\Form\Entity\RicercaProgetto;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\ProcessBuilder;
use SfingeBundle\Entity\Utente;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;
use MonitoraggioBundle\Command\EstrazioneControlliIGRUECommand;

/**
 * @Route("/report")
 */
class ReportController extends BaseController {
    const PARAMETRO_CONTROLLI = 'monitoraggio.controlliIGRUE';
    const NOME_FILE_LOG = 'controlli_igrue';    // microsecondi

    /**
     * @PaginaInfo(titolo="Validazione IGRUE", sottoTitolo="mostra i porblemi riscontrati per i progetti")
     * @Menuitem(menuAttivo="monitoraggio_menu")
     * @Route(
     *     "/validazione_igrue/{sort}/{direction}/{page}",
     *     defaults={ "sort" : "i.id", "direction" : "asc", "page" : "1"},
     *     name="monitoraggio_elenco_validazione_progetti"
     * )
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="elenco progetti")})
     */
    public function elencoProgettiAction(): Response {
        $datiRicerca = new RicercaProgetto();
        $risultato = $this->get('ricerca')->ricerca($datiRicerca);
        $risultato['risultato'] = $this->applicaFunzioniIGRUE($risultato['risultato']);
        $risultato['elenco_controlli'] = $this->getParameter(self::PARAMETRO_CONTROLLI);

        return $this->render('MonitoraggioBundle:Report:elenco_validazione_igrue.html.twig', $risultato);
    }

    protected function applicaFunzioniIGRUE(SlidingPagination $slidingPagination): SlidingPagination {
        $controlli = array_map(function (string $controllo): string {
            return "controllo_igrue(richiesta.id, '$controllo') as c$controllo";
        },
            \array_keys($this->getParameter(self::PARAMETRO_CONTROLLI))
        );
        $stringaControlli = \implode(', ', $controlli);
        $dql = "SELECT $stringaControlli FROM RichiesteBundle:Richiesta AS richiesta WHERE richiesta = :richiesta";
        $query = $this->getEm()->createQuery($dql);

        $slidingPaginationRetVal = clone $slidingPagination;
        /** @var Richiesta $item */
        foreach ($slidingPagination as $key => $item) {
            $risultato = $query->setParameter('richiesta', $item)->getOneOrNullResult();

            $slidingPaginationRetVal[$key] = [
                'richiesta' => $item,
                'controlli' => $risultato,
            ];
        }

        return $slidingPaginationRetVal;
    }

    /**
     * @Route(
     *     "/validazione_igrue_pulisci/{sort}/{direction}/{page}",
     *     defaults={ "sort" : "i.id", "direction" : "asc", "page" : "1"},
     *     name="monitoraggio_elenco_validazione_progetti_pulisci"
     * )
     */
    public function pulisciElencoProgettiAction(): Response {
        $datiRicerca = new RicercaProgetto();
        $this->get('ricerca')->pulisci($datiRicerca);

        return $this->redirectToRoute('monitoraggio_elenco_validazione_progetti');
    }

    /**
     * @Route(
     *     "/avvia_creazione_report",
     *     name="monitoraggio_avvia_creazione_report_validazione_igrue"
     * )
     * @Method({"POST"})
     */
    public function avviaCreazioneReportValidazioneAction(): Response {
        $this->avviaCreazioneReport();

        return $this->addSuccessRedirect('Avviata creazione report', 'monitoraggio_elenco_validazione_progetti');
    }

    protected function avviaCreazioneReport(): void {
        $phpBinaryFinder = new PhpExecutableFinder();

        /** @var Utente $utente */
        $utente = $this->getUser();
        $persona = $utente->getPersona();

        $appDir = $this->get('kernel')->getRootDir();
        $environment = $this->getParameter('kernel.environment');
        $logFile = "$appDir/logs/" . self::NOME_FILE_LOG . '_' . \date('Y-m-d') . '.log';
        
        $now = (new \DateTime())->format('d/m/y h:i:s');
        \shell_exec("echo Avvio processo $now >> $logFile");
        $command = implode(' ',[
            $phpBinaryFinder->find(),
            '--file',
            $appDir . '/console',
            '--',
            '--env=' . $environment,
            EstrazioneControlliIGRUECommand::COMMAND_NAME,
            $persona->getId(),
            ">>$logFile 2>>$logFile &",
        ]);
        
        \shell_exec($command);
    }

}
