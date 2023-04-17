<?php

namespace AttuazioneControlloBundle\Controller;

use AttuazioneControlloBundle\Entity\AzioneComunicazioneStampa;
use BaseBundle\Controller\BaseController;
use DateTime;
use Fpdf\Fpdf;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use SfingeBundle\Entity\Utente;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Length;

/**
 * Class AzioniComunicazioneController
 *
 * @Route("/azioni_comunicazione")
 */
class AzioniComunicazioneController extends BaseController
{
    const DPI = 96;
    const MM_IN_INCH = 25.4;
    const A4_HEIGHT = 297;
    const A4_WIDTH = 210;

    const MAX_WIDTH = 12;
    const MAX_HEIGHT = 12;

    /**
     * @Route("/{idRichiesta}/poster", name="azioni_comunicazione_poster")
     *
     * @param Request $request
     * @param string  $idRichiesta
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Exception
     */
    public function posterAction(Request $request, $idRichiesta)
    {
        $richiesta = $this->getEm()->getRepository("RichiesteBundle\Entity\Richiesta")->find($idRichiesta);

        $formBuilder = $this->createFormBuilder()
            ->add('titolo', TextareaType::class, [
                'label' => 'Titolo',
                'required' => true,
                'constraints' => [
                    new Length([
                        'min' => 1,
                        'max' => 140,
                    ])
                ]
            ])
            ->add('descrizione', TextareaType::class, [
                'label' => 'Descrizione del progetto',
                'required' => true,
                'constraints' => [
                    new Length([
                        'min' => 1,
                        'max' => 450,
                    ])
                ]
            ])
            ->add('obiettivi', TextareaType::class, [
                'label' => 'Obiettivi',
                'required' => true,
                'constraints' => [
                    new Length([
                        'min' => 1,
                        'max' => 450,
                    ])
                ]
            ])
            ->add('risultati', TextareaType::class, [
                'label' => 'Risultati',
                'required' => true,
                'constraints' => [
                    new Length([
                        'min' => 1,
                        'max' => 450,
                    ])
                ]
            ]);

        $form = $formBuilder->getForm();

        if($form->handleRequest($request)->isValid()) {

            /** @var Utente $utente */
            $utente = $this->getUser();
            $tipoStampa = "poster";

            /** @var AzioneComunicazioneStampa $stampa */
            $stampa = $this->getDoctrine()->getRepository("AttuazioneControlloBundle:AzioneComunicazioneStampa")->findOneBy(['richiestaId' => $richiesta->getId(), 'tipoStampa' => $tipoStampa]);

            $data = $form->getData();

            $pathLogo = $this->get('kernel')->getRootDir() . '/../web/assets/img/regione/Por_Fesr_ER_loghi_ITA_CMYK.png';
            $pathSfondo = $this->get('kernel')->getRootDir() . '/../web/assets/img/regione/sfondo1_comunicazioni.jpg';

            $pdf = new Fpdf('P', 'mm', 'A3');

            $pdf->AddPage();

            $pdf->SetAutoPageBreak(false);

            $pdf->SetTextColor(0, 55, 130);

            $pdf->Image($pathSfondo, 0, 0, 297,420, 'jpg');

            $pdf->SetMargins(15,15,15);

            $pdf->SetFont('Arial', 'B', 20);
            $pdf->SetY(15);
            $pdf->MultiCell(267,14,iconv('UTF-8', 'windows-1252//IGNORE', $data['titolo']));

            $pdf->SetY(91);
            $pdf->SetFont('Arial', 'B', 16);
            $pdf->Cell(267, 14, 'Descrizione del progetto');
            $pdf->SetY(105);
            $pdf->SetFont('Arial', null, 14);
            $pdf->MultiCell(267,10,iconv('UTF-8', 'windows-1252//IGNORE', $data['descrizione']));

            $pdf->SetY(178);
            $pdf->SetFont('Arial', 'B', 16);
            $pdf->Cell(267, 14, 'Obiettivi');
            $pdf->SetY(192);
            $pdf->SetFont('Arial', null, 14);
            $pdf->MultiCell(267,10,iconv('UTF-8', 'windows-1252//IGNORE', $data['obiettivi']));

            $pdf->SetY(267);
            $pdf->SetFont('Arial', 'B', 16);
            $pdf->Cell(267, 14, 'Risultati');
            $pdf->SetY(281);
            $pdf->SetFont('Arial', null, 14);
            $pdf->MultiCell(267,10,iconv('UTF-8', 'windows-1252//IGNORE', $data['risultati']));

            $pdf->SetY(-70);
            $pdf->SetFont('Arial', 'B', 16);
            $pdf->Cell(267, 14, 'Progetto cofinanziato dal Fondo europeo di sviluppo regionale');

            $pdf->SetY(-45);

            $pdf->Image($pathLogo, null, null, 267,null, 'png');

            if ($stampa instanceof AzioneComunicazioneStampa) {
                $stampa->setUltimaStampaUtenteId($utente);
                $stampa->setUltimaStampaData(new DateTime());
            } else {
                $stampa = new AzioneComunicazioneStampa();
                $stampa->setRichiestaId($richiesta);
                $stampa->setUltimaStampaUtenteId($utente);
                $stampa->setTipoStampa($tipoStampa);
                $stampa->setUltimaStampaData(new DateTime());
            }

            $this->getEm()->persist($stampa);
            $this->getEm()->flush();

            $pdf->Output('D', 'poster.pdf');
        }

        return $this->render('AttuazioneControlloBundle:AzioniComunicazione:azioniComunicazionePosterForm.html.twig', [
            'form' => $form->createView(),
            'richiesta' => $richiesta,
            'titolo' => 'Inserimento dati per poster'
        ]);
    }


    /**
     * @Route("/{idRichiesta}/cartellone", name="azioni_comunicazione_cartellone")
     *
     * @param Request $request
     * @param string  $idRichiesta
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Exception
     */
    public function cartelloneTemporaneoAction(Request $request, $idRichiesta)
    {
        ini_set('memory_limit', '-1');

        $richiesta = $this->getEm()->getRepository("RichiesteBundle\Entity\Richiesta")->find($idRichiesta);

        $formBuilder = $this->createFormBuilder()
            ->add('nomeProgetto', TextareaType::class, [
                'label' => 'Nome del progetto',
                'required' => true,
                'constraints' => [
                    new Length([
                        'min' => 1,
                        'max' => 140,
                    ])
                ]
            ])
            ->add('obiettivoPrincipale', TextareaType::class, [
                'label' => 'Obiettivo principale dell\'attività sostenuta dall\'operatore',
                'required' => true,
                'constraints' => [
                    new Length([
                        'min' => 1,
                        'max' => 1000,
                    ])
                ]
            ])
            ->add('formato', ChoiceType::class, [
                'choices' => ['100cm x 150cm' => '1000x1500', '200cm x 300cm' => '2000x3000', '150cm x 100cm' => '1500x1000', '300cm x 200cm' => '3000x2000',],
                'choices_as_values' => true,
                'label' => 'Formato cartellone',
                'empty_data' => '1000x1500',
            ])
            ->add('logoAggiuntivo1', FileType::class, [
                'label' => 'Logo aggiuntivo 1',
                'required' => false,
            ])
            ->add('logoAggiuntivo2', FileType::class, [
                'label' => 'Logo aggiuntivo 2',
                'required' => false,
            ])
            ->add('logoAggiuntivo3', FileType::class, [
                'label' => 'Logo aggiuntivo 3',
                'required' => false,
            ])
            ->add('logoAggiuntivo4', FileType::class, [
                'label' => 'Logo aggiuntivo 4',
                'required' => false,
            ])
        ;

        $form = $formBuilder->getForm();

        if($form->handleRequest($request)->isValid()) {
            $data = $form->getData();

            $pathLogo = $this->get('kernel')->getRootDir() . '/../web/assets/img/regione/Loghi_POR_FESR.jpg';
            $pathSfondo = $this->get('kernel')->getRootDir() . '/../web/assets/img/regione/sfondo2_comunicazioni.jpg';
            $pathEu = $this->get('kernel')->getRootDir() . '/../web/assets/img/regione/Logo_UE.jpg';
            $nrUploads = 0;

            list($altezza, $larghezza) = explode('x', $data['formato']);

            if($altezza > $larghezza) {
                $orientamento = 'P';
            } else {
                $orientamento = 'L';
            }

            if(in_array($altezza, [2000,3000])) {
                $moltiplicatore = 2;
            } else {
                $moltiplicatore = 1;
            }

            $utente = $this->getUser();
            $tipoStampa = "cartellone" . " - " . $altezza / 10 . "x" . $larghezza / 10 . "cm";

            /** @var AzioneComunicazioneStampa $stampa */
            $stampa = $this->getDoctrine()->getRepository("AttuazioneControlloBundle:AzioneComunicazioneStampa")->findOneBy(['richiestaId' => $richiesta->getId(), 'tipoStampa' => $tipoStampa]);

            if(isset($data['logoAggiuntivo1'])) {
                $nrUploads++;
            }

            if(isset($data['logoAggiuntivo2'])) {
                $nrUploads++;
            }

            if(isset($data['logoAggiuntivo3'])) {
                $nrUploads++;
            }

            if(isset($data['logoAggiuntivo4'])) {
                $nrUploads++;
            }

            switch (true) {
                case $orientamento == 'L' && $nrUploads <= 2:
                    $mw = 150;
                    break;
                case $orientamento == 'P' && $nrUploads <= 2:
                    $mw = 120;
                    break;
                case $orientamento == 'L' && $nrUploads == 3:
                    $mw = 100;
                    break;
                case $orientamento == 'P' && $nrUploads == 3:
                    $mw = 100;
                    break;
                default:
                    $mw = 80;
                    break;
            }

            $mh = 80;

            if(isset($data['logoAggiuntivo1'])) {
                /** @var UploadedFile $logoAggiuntivo1 */
                $logoAggiuntivo1 = $data['logoAggiuntivo1'];
                $logoAggiuntivo1Dati = file_get_contents($logoAggiuntivo1->getPathname());
                $logoAggiuntivo1Exte = $logoAggiuntivo1->getClientOriginalExtension();
                $logoAggiuntivo1Add = 'data://text/plain;base64,' . base64_encode($logoAggiuntivo1Dati);

                $logoAggiuntivo1Scalato = $this->resizeToFit($logoAggiuntivo1Add, ($mw * $moltiplicatore), ($mh * $moltiplicatore));
            }

            if(isset($data['logoAggiuntivo2'])) {
                /** @var UploadedFile $logoAggiuntivo2 */
                $logoAggiuntivo2 = $data['logoAggiuntivo2'];
                $logoAggiuntivo2Dati = file_get_contents($logoAggiuntivo2->getPathname());
                $logoAggiuntivo2Exte = $logoAggiuntivo2->getClientOriginalExtension();
                $logoAggiuntivo2Add = 'data://text/plain;base64,' . base64_encode($logoAggiuntivo2Dati);

                $logoAggiuntivo2Scalato = $this->resizeToFit($logoAggiuntivo2Add, ($mw * $moltiplicatore), ($mh * $moltiplicatore));
            }

            if(isset($data['logoAggiuntivo3'])) {
                /** @var UploadedFile $logoAggiuntivo3 */
                $logoAggiuntivo3 = $data['logoAggiuntivo3'];
                $logoAggiuntivo3Dati = file_get_contents($logoAggiuntivo3->getPathname());
                $logoAggiuntivo3Exte = $logoAggiuntivo3->getClientOriginalExtension();
                $logoAggiuntivo3Add = 'data://text/plain;base64,' . base64_encode($logoAggiuntivo3Dati);

                $logoAggiuntivo3Scalato = $this->resizeToFit($logoAggiuntivo3Add, ($mw * $moltiplicatore), ($mh * $moltiplicatore));
            }

            if(isset($data['logoAggiuntivo4'])) {
                /** @var UploadedFile $logoAggiuntivo4 */
                $logoAggiuntivo4 = $data['logoAggiuntivo4'];
                $logoAggiuntivo4Dati = file_get_contents($logoAggiuntivo4->getPathname());
                $logoAggiuntivo4Exte = $logoAggiuntivo4->getClientOriginalExtension();
                $logoAggiuntivo4Add = 'data://text/plain;base64,' . base64_encode($logoAggiuntivo4Dati);

                $logoAggiuntivo4Scalato = $this->resizeToFit($logoAggiuntivo4Add, ($mw * $moltiplicatore), ($mh * $moltiplicatore));
            }

            $pdf = new Fpdf($orientamento, 'mm', [$altezza, $larghezza]);

            $pdf->AddPage();

            $pdf->SetAutoPageBreak(false);

            $pdf->Image($pathSfondo, 0, 230 * $moltiplicatore, $larghezza,$altezza - (375 * $moltiplicatore), 'jpg');

            $pdf->SetMargins((50 * $moltiplicatore),(50 * $moltiplicatore),(50 * $moltiplicatore));

            $pdf->Image($pathEu, (50 * $moltiplicatore), (50 * $moltiplicatore), 0,(130 * $moltiplicatore), 'jpg');

            $pdf->SetFont('Arial', 'B', (100 * $moltiplicatore));
            $pdf->SetY((260 * $moltiplicatore));
            $pdf->MultiCell($larghezza - (100 * $moltiplicatore),(40 * $moltiplicatore),iconv('UTF-8', 'windows-1252//IGNORE', $data['nomeProgetto']));

            $H = $pdf->GetY();

            $pdf->SetY($H + (25 * $moltiplicatore));
            $pdf->SetFont('Arial', null, (60 * $moltiplicatore));
            $pdf->MultiCell($larghezza - (100 * $moltiplicatore),(30 * $moltiplicatore),iconv('UTF-8', 'windows-1252//IGNORE', $data['obiettivoPrincipale']));

            if($orientamento == 'L') {
                $posY = $altezza - (bcmul($altezza, .11));
                $posYY = $altezza - (bcmul($altezza, .12));
                $posX = $larghezza - (bcmul($larghezza, .37));
            } else {
                $posY = $altezza - (bcmul($altezza, .065));
                $posYY = $altezza - (bcmul($altezza, .08));
                $posX = $larghezza - (bcmul($larghezza, .37));
            }

            $pdf->SetY($posY);

            $pdf->Image($pathLogo, null, null, $larghezza - (bcmul($larghezza, .46)),null, 'jpg');

            $pdf->Line($larghezza - (bcmul($larghezza, .37)),$altezza - (100 * $moltiplicatore), $larghezza - (bcmul($larghezza, .37)),$altezza - (50 * $moltiplicatore));

            if($nrUploads > 0) {
                $a = bcdiv(bcmul($larghezza, .37) - bcmul($mw, $nrUploads), ($nrUploads + 1));

                // loghi aggiuntivi
                if(isset($data['logoAggiuntivo1'])) {
                    $posX += $a;
                    $pdf->Image($logoAggiuntivo1Add, $posX + bcdiv(($mw - $logoAggiuntivo1Scalato[0]),2),$posYY + (((90 * $moltiplicatore) - $logoAggiuntivo1Scalato[1]) / 2), $logoAggiuntivo1Scalato[0], $logoAggiuntivo1Scalato[1], $logoAggiuntivo1Exte);
                    $posX += $mw;
                }
                if(isset($data['logoAggiuntivo2'])) {
                    $posX += $a;
                    $pdf->Image($logoAggiuntivo2Add, $posX + bcdiv(($mw - $logoAggiuntivo2Scalato[0]),2),$posYY + (((90 * $moltiplicatore) - $logoAggiuntivo2Scalato[1]) / 2), $logoAggiuntivo2Scalato[0], $logoAggiuntivo2Scalato[1], $logoAggiuntivo2Exte);
                    $posX += $mw;
                }
                if(isset($data['logoAggiuntivo3'])) {
                    $posX += $a;
                    $pdf->Image($logoAggiuntivo3Add, $posX + bcdiv(($mw - $logoAggiuntivo3Scalato[0]),2),$posYY + (((90 * $moltiplicatore) - $logoAggiuntivo3Scalato[1]) / 2), $logoAggiuntivo3Scalato[0], $logoAggiuntivo3Scalato[1], $logoAggiuntivo3Exte);
                    $posX += $mw;
                }
                if(isset($data['logoAggiuntivo4'])) {
                    $posX += $a;
                    $pdf->Image($logoAggiuntivo4Add, $posX + bcdiv(($mw - $logoAggiuntivo4Scalato[0]),2),$posYY + (((90 * $moltiplicatore) - $logoAggiuntivo4Scalato[1]) / 2), $logoAggiuntivo4Scalato[0], $logoAggiuntivo4Scalato[1], $logoAggiuntivo4Exte);
                }
            }

            if ($stampa instanceof AzioneComunicazioneStampa) {
                $stampa->setUltimaStampaUtenteId($utente);
                $stampa->setUltimaStampaData(new DateTime());
            } else {
                $stampa = new AzioneComunicazioneStampa();
                $stampa->setRichiestaId($richiesta);
                $stampa->setUltimaStampaUtenteId($utente);
                $stampa->setTipoStampa($tipoStampa);
                $stampa->setUltimaStampaData(new DateTime());
            }

            $this->getEm()->persist($stampa);
            $this->getEm()->flush();

            $pdf->Output('D', 'cartellone.pdf');

        }

        return $this->render('AttuazioneControlloBundle:AzioniComunicazione:azioniComunicazioneCartelloneForm.html.twig', [
            'form' => $form->createView(),
            'richiesta' => $richiesta,
            'titolo' => $request->get('_route') == 'azioni_comunicazione_cartellone' ? 'Inserimento dati per cartellone temporaneo' : 'Inserimento dati per targa permanente',
            'azione' => $request->get('_route') == 'azioni_comunicazione_cartellone' ? 'Stampa cartellone temporaneo' : 'Stampa targa permanente',
        ]);
    }

    /**
     * @Route("/{idRichiesta}/targa", name="azioni_comunicazione_targa")
     *
     * @param Request $request
     * @param string  $idRichiesta
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Exception
     */
    public function targaAction(Request $request, $idRichiesta)
    {
        ini_set('memory_limit', '-1');

        $richiesta = $this->getEm()->getRepository("RichiesteBundle\Entity\Richiesta")->find($idRichiesta);

        $formBuilder = $this->createFormBuilder()
            ->add('nomeProgetto', TextareaType::class, [
                'label' => 'Nome del progetto',
                'required' => true,
                'constraints' => [
                    new Length([
                        'min' => 1,
                        'max' => 140,
                    ])
                ]
            ])
            ->add('obiettivoPrincipale', TextareaType::class, [
                'label' => 'Obiettivo principale dell\'attività sostenuta dall\'operatore',
                'required' => true,
                'constraints' => [
                    new Length([
                        'min' => 1,
                        'max' => 1000,
                    ])
                ]
            ])
            ->add('orientamento', ChoiceType::class, [
                'choices' => ['Orizzontale' => true, 'Verticale' => false],
                'choices_as_values' => true,
                'label' => 'Orientamento stampa',
            ])
            ->add('logoAggiuntivo1', FileType::class, [
                'label' => 'Logo aggiuntivo 1',
                'required' => false,
            ])
            ->add('logoAggiuntivo2', FileType::class, [
                'label' => 'Logo aggiuntivo 2',
                'required' => false,
            ])
            ->add('logoAggiuntivo3', FileType::class, [
                'label' => 'Logo aggiuntivo 3',
                'required' => false,
            ])
            ->add('logoAggiuntivo4', FileType::class, [
                'label' => 'Logo aggiuntivo 4',
                'required' => false,
            ])
        ;

        $form = $formBuilder->getForm();

        if($form->handleRequest($request)->isValid()) {
            $data = $form->getData();

            $pathLogo = $this->get('kernel')->getRootDir() . '/../web/assets/img/regione/Por_Fesr_ER_loghi_ITA_CMYK.png';
            $pathSfondo = $this->get('kernel')->getRootDir() . '/../web/assets/img/regione/sfondo2_comunicazioni.jpg';
            $pathEu = $this->get('kernel')->getRootDir() . '/../web/assets/img/regione/logo_eu_per_cartelloni_comunicazioni.png';
            $nrUploads = 0;

            if($data['orientamento'] === true) {
                $orientamento = 'L';
            } else {
                $orientamento = 'P';
            }

            $utente = $this->getUser();
            $tipoStampa = "targa";

            /** @var AzioneComunicazioneStampa $stampa */
            $stampa = $this->getDoctrine()->getRepository("AttuazioneControlloBundle:AzioneComunicazioneStampa")->findOneBy(['richiestaId' => $richiesta->getId(), 'tipoStampa' => $tipoStampa]);

            if(isset($data['logoAggiuntivo1'])) {
                $nrUploads++;
            }

            if(isset($data['logoAggiuntivo2'])) {
                $nrUploads++;
            }

            if(isset($data['logoAggiuntivo3'])) {
                $nrUploads++;
            }

            if(isset($data['logoAggiuntivo4'])) {
                $nrUploads++;
            }

            switch (true) {
                case $orientamento === 'L' && $nrUploads <= 2:
                    $mw = 37;
                    break;
                case $orientamento === 'P' && $nrUploads <= 2:
                    $mw = 30;
                    break;
                case $orientamento === 'P' && $nrUploads === 3:
                case $orientamento === 'L' && $nrUploads === 3:
                    $mw = 25;
                    break;
                default:
                    $mw = 20;
                    break;
            }

            $mh = 12;
            $moltiplicatore = 1;

            if(isset($data['logoAggiuntivo1'])) {
                /** @var UploadedFile $logoAggiuntivo1 */
                $logoAggiuntivo1 = $data['logoAggiuntivo1'];
                $logoAggiuntivo1Dati = file_get_contents($logoAggiuntivo1->getPathname());
                $logoAggiuntivo1Exte = $logoAggiuntivo1->getClientOriginalExtension();
                $logoAggiuntivo1Add = 'data://text/plain;base64,' . base64_encode($logoAggiuntivo1Dati);

                $logoAggiuntivo1Scalato = $this->resizeToFit($logoAggiuntivo1Add, ($mw * $moltiplicatore), ($mh * $moltiplicatore));
            }

            if(isset($data['logoAggiuntivo2'])) {
                /** @var UploadedFile $logoAggiuntivo2 */
                $logoAggiuntivo2 = $data['logoAggiuntivo2'];
                $logoAggiuntivo2Dati = file_get_contents($logoAggiuntivo2->getPathname());
                $logoAggiuntivo2Exte = $logoAggiuntivo2->getClientOriginalExtension();
                $logoAggiuntivo2Add = 'data://text/plain;base64,' . base64_encode($logoAggiuntivo2Dati);

                $logoAggiuntivo2Scalato = $this->resizeToFit($logoAggiuntivo2Add, ($mw * $moltiplicatore), ($mh * $moltiplicatore));
            }

            if(isset($data['logoAggiuntivo3'])) {
                /** @var UploadedFile $logoAggiuntivo3 */
                $logoAggiuntivo3 = $data['logoAggiuntivo3'];
                $logoAggiuntivo3Dati = file_get_contents($logoAggiuntivo3->getPathname());
                $logoAggiuntivo3Exte = $logoAggiuntivo3->getClientOriginalExtension();
                $logoAggiuntivo3Add = 'data://text/plain;base64,' . base64_encode($logoAggiuntivo3Dati);

                $logoAggiuntivo3Scalato = $this->resizeToFit($logoAggiuntivo3Add, ($mw * $moltiplicatore), ($mh * $moltiplicatore));
            }

            if(isset($data['logoAggiuntivo4'])) {
                /** @var UploadedFile $logoAggiuntivo4 */
                $logoAggiuntivo4 = $data['logoAggiuntivo4'];
                $logoAggiuntivo4Dati = file_get_contents($logoAggiuntivo4->getPathname());
                $logoAggiuntivo4Exte = $logoAggiuntivo4->getClientOriginalExtension();
                $logoAggiuntivo4Add = 'data://text/plain;base64,' . base64_encode($logoAggiuntivo4Dati);

                $logoAggiuntivo4Scalato = $this->resizeToFit($logoAggiuntivo4Add, ($mw * $moltiplicatore), ($mh * $moltiplicatore));
            }

            if($data['orientamento']) {
                $pdf = new Fpdf('L', 'mm', 'A4');


                $pdf->AddPage();

                $pdf->SetAutoPageBreak(false);

                $pdf->Image($pathSfondo, 0, 48, 297,130, 'jpg');

                $pdf->SetMargins(10,10,10);

                $pdf->Image($pathEu, 10, 10, 0,28, 'png');

                $pdf->SetFont('Arial', 'B', 20);
                $pdf->SetY(52);
                $pdf->MultiCell(267,8,iconv('UTF-8', 'windows-1252//IGNORE', $data['nomeProgetto']));

                $H = $pdf->GetY();

                $pdf->SetY($H + 5);
                $pdf->SetFont('Arial', null, 12);
                $pdf->MultiCell(267,6,iconv('UTF-8', 'windows-1252//IGNORE', $data['obiettivoPrincipale']));

                $pdf->SetY(-23);

                $pdf->Image($pathLogo, null, null, 162,null, 'png');

                $pdf->Line(190,186,190,204);

                if($nrUploads > 0) {
                    $a = round(88 / $nrUploads / 2);
                    if($nrUploads <= 2) {
                        $posX = 180;
                    } else {
                        $posX = 187;
                    }

                    // loghi aggiuntivi
                    if(isset($data['logoAggiuntivo1'])) {
                        $posX += $a;
                        $pdf->Image($logoAggiuntivo1Add, $posX,186 + ((18 - $logoAggiuntivo1Scalato[1]) / 2), $logoAggiuntivo1Scalato[0], $logoAggiuntivo1Scalato[1], $logoAggiuntivo1Exte);
                        $posX += $a;
                    }
                    if(isset($data['logoAggiuntivo2'])) {
                        $posX += $a;
                        $pdf->Image($logoAggiuntivo2Add, $posX,186 + ((18 - $logoAggiuntivo2Scalato[1]) / 2), $logoAggiuntivo2Scalato[0], $logoAggiuntivo2Scalato[1], $logoAggiuntivo2Exte);
                        $posX += $a;
                    }
                    if(isset($data['logoAggiuntivo3'])) {
                        $posX += $a;
                        $pdf->Image($logoAggiuntivo3Add, $posX,186 + ((18 - $logoAggiuntivo3Scalato[1]) / 2), $logoAggiuntivo3Scalato[0], $logoAggiuntivo3Scalato[1], $logoAggiuntivo3Exte);
                        $posX += $a;
                    }
                    if(isset($data['logoAggiuntivo4'])) {
                        $posX += $a;
                        $pdf->Image($logoAggiuntivo4Add, $posX,186 + ((18 - $logoAggiuntivo4Scalato[1]) / 2), $logoAggiuntivo4Scalato[0], $logoAggiuntivo4Scalato[1], $logoAggiuntivo4Exte);
                    }
                }

                if ($stampa instanceof AzioneComunicazioneStampa) {
                    $stampa->setUltimaStampaUtenteId($utente);
                    $stampa->setUltimaStampaData(new DateTime());
                } else {
                    $stampa = new AzioneComunicazioneStampa();
                    $stampa->setRichiestaId($richiesta);
                    $stampa->setUltimaStampaUtenteId($utente);
                    $stampa->setTipoStampa($tipoStampa);
                    $stampa->setUltimaStampaData(new DateTime());
                }

                $this->getEm()->persist($stampa);
                $this->getEm()->flush();

                $pdf->Output('D', 'targa.pdf');
            } else {
                $pdf = new Fpdf('P', 'mm', 'A4');


                $pdf->AddPage();

                $pdf->SetAutoPageBreak(false);

                $pdf->Image($pathSfondo, 0, 48, 210,214, 'jpg');

                $pdf->SetMargins(10,10,10);

                $pdf->Image($pathEu, 10, 10, 0,28, 'png');

                $pdf->SetFont('Arial', 'B', 20);
                $pdf->SetY(52);
                $pdf->MultiCell(180,8,iconv('UTF-8', 'windows-1252//IGNORE', $data['nomeProgetto']));

                $H = $pdf->GetY();

                $pdf->SetY($H + 5);
                $pdf->SetFont('Arial', null, 12);
                $pdf->MultiCell(180,6,iconv('UTF-8', 'windows-1252//IGNORE', $data['obiettivoPrincipale']));

                $pdf->SetY(-21);

                $pdf->Image($pathLogo, null, null, 115,null, 'png');

                $pdf->Line(134,273,134,291);

                if($nrUploads > 0) {
                    $a = round(72 / $nrUploads / 2);

                    if($nrUploads <= 2) {
                        $posX = 126;
                    } else {
                        $posX = 126;
                    }

                    // loghi aggiuntivi
                    if(isset($data['logoAggiuntivo1'])) {
                        $posX += $a;
                        $pdf->Image($logoAggiuntivo1Add, $posX,276 + ((12 - $logoAggiuntivo1Scalato[1]) / 2), $logoAggiuntivo1Scalato[0], $logoAggiuntivo1Scalato[1], $logoAggiuntivo1Exte);
                        $posX += $a;
                    }
                    if(isset($data['logoAggiuntivo2'])) {
                        $posX += $a;
                        $pdf->Image($logoAggiuntivo2Add, $posX,276 + ((12 - $logoAggiuntivo2Scalato[1]) / 2), $logoAggiuntivo2Scalato[0], $logoAggiuntivo2Scalato[1], $logoAggiuntivo2Exte);
                        $posX += $a;
                    }
                    if(isset($data['logoAggiuntivo3'])) {
                        $posX += $a;
                        $pdf->Image($logoAggiuntivo3Add, $posX,276 + ((12 - $logoAggiuntivo3Scalato[1]) / 2), $logoAggiuntivo3Scalato[0], $logoAggiuntivo3Scalato[1], $logoAggiuntivo3Exte);
                        $posX += $a;
                    }
                    if(isset($data['logoAggiuntivo4'])) {
                        $posX += $a;
                        $pdf->Image($logoAggiuntivo4Add, $posX,276 + ((12 - $logoAggiuntivo4Scalato[1]) / 2), $logoAggiuntivo4Scalato[0], $logoAggiuntivo4Scalato[1], $logoAggiuntivo4Exte);
                    }
                }

                if ($stampa instanceof AzioneComunicazioneStampa) {
                    $stampa->setUltimaStampaUtenteId($utente);
                    $stampa->setUltimaStampaData(new DateTime());
                } else {
                    $stampa = new AzioneComunicazioneStampa();
                    $stampa->setRichiestaId($richiesta);
                    $stampa->setUltimaStampaUtenteId($utente);
                    $stampa->setTipoStampa($tipoStampa);
                    $stampa->setUltimaStampaData(new DateTime());
                }

                $this->getEm()->persist($stampa);
                $this->getEm()->flush();

                $pdf->Output('D', 'targa.pdf');
            }
        }

        return $this->render('AttuazioneControlloBundle:AzioniComunicazione:azioniComunicazioneTargaForm.html.twig', [
            'form' => $form->createView(),
            'richiesta' => $richiesta,
            'titolo' => $request->get('_route') == 'azioni_comunicazione_cartellone' ? 'Inserimento dati per cartellone temporaneo' : 'Inserimento dati per targa permanente',
            'azione' => $request->get('_route') == 'azioni_comunicazione_cartellone' ? 'Stampa cartellone temporaneo' : 'Stampa targa permanente',
        ]);
    }

    /**
     * @param $val
     *
     * @return float|int
     */
    private function pixelsToMM($val)
    {
        return $val * self::MM_IN_INCH / self::DPI;
    }

    /**
     * @param $val
     *
     * @return float|int
     */
    private function mmToPixel($val)
    {
        return $val / self::MM_IN_INCH * self::DPI;
    }

    /**
     * @param     $imgFilename
     *
     * @param int $maxW
     * @param int $maxH
     *
     * @return array
     */
    private function resizeToFit($imgFilename, $maxW = self::MAX_WIDTH, $maxH = self::MAX_HEIGHT)
    {
        list($width, $height) = getimagesize($imgFilename);
        $widthScale = $this->mmToPixel($maxW) / $width;
        $heightScale = $this->mmToPixel($maxH) / $height;
        $scale = min($widthScale, $heightScale);
        return array(
            round($this->pixelsToMM($scale * $width)),
            round($this->pixelsToMM($scale * $height))
        );
    }
}