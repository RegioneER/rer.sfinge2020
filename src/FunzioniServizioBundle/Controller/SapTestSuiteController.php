<?php


namespace FunzioniServizioBundle\Controller;

use AnagraficheBundle\Entity\Persona;
use DateTime;
use Doctrine\ORM\EntityRepository;
use Exception;
use FascicoloBundle\Entity\IstanzaCampo;
use GeoBundle\Entity\GeoComune;
use GeoBundle\Entity\GeoStato;
use RichiesteBundle\Entity\Proponente;
use RichiesteBundle\Entity\Richiesta;
use RichiesteBundle\GestoriEsportazione\GestoreEsportazioneBando_135;
use RichiesteBundle\GestoriRichieste\GestoreRichiesteBando_150;
use RichiesteBundle\GestoriRichieste\GestoreRichiesteBando_151;
use RichiesteBundle\GestoriRichieste\GestoreRichiesteBando_164;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SfingeBundle\Entity\Procedura;
use SoggettoBundle\Entity\Soggetto;
use stdClass;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Class SapTestSuiteController
 *
 * @Route("/sap")
 */
class SapTestSuiteController extends Controller
{
    /**
     * @Route("/", name="sap_test_suite")
     *
     * @Security("has_role('ROLE_UTENTE_PA')")
     */
    public function indexAction()
    {
        /*if($this->container->get('kernel')->getEnvironment() !== 'dev') {
            $this->redirectToRoute('home');
        }*/

        return $this->render('FunzioniServizioBundle:Sap:index.html.twig');
    }

    /**
     * @Route("/check_iban/", name="sap_check_iban")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function checkIbanAction(Request $request)
    {
        $result = null;

        $form = $this->createFormBuilder()
            ->add('iban', TextType::class, ['required' => true])
            ->add('send', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $iban = $data['iban'];

            $result = $this->container->get('app.sap_service')->checkIban($iban, 'Dev');
        }

        return $this->render('FunzioniServizioBundle:Sap:checkIban.html.twig', [
            'form' => $form->createView(),
            'result' => $result,
        ]);
    }

    /**
     * @Route("/find_sogg/", name="sap_find_sogg")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function findSoggAction(Request $request)
    {
        $result = null;

        $form = $this->createFormBuilder()
            ->add('partitaIva', TextType::class, ['required' => true])
            ->add('ambiente', ChoiceType::class, [
                'choices' => ['Dev' => 'Dev', 'Prod' => 'Prod'],
                'label' => 'Ambiente',
                'placeholder' => '-',
                'required' => true,
                'constraints' => [
                    new NotBlank(['groups' => ['fake']]),
                ],
            ])
            ->add('send', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $partitaIva = $data['partitaIva'];

            $result = $this->container->get('app.sap_service')->ricercaBeneficiari($partitaIva, $data['ambiente']);
        }

        return $this->render('FunzioniServizioBundle:Sap:findSogg.html.twig', [
            'form' => $form->createView(),
            'result' => $result,
        ]);
    }

    /**
     * @Route("/view_sogg/", name="sap_view_sogg")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function viewSoggAction(Request $request)
    {
        $result = null;

        $form = $this->createFormBuilder()
            ->add('codiceSap', TextType::class, ['required' => true])
            ->add('ambiente', ChoiceType::class, [
                'choices' => ['Dev' => 'Dev', 'Prod' => 'Prod'],
                'label' => 'Ambiente',
                'placeholder' => '-',
                'required' => true,
                'constraints' => [
                    new NotBlank(['groups' => ['fake']]),
                    ],
            ])
            ->add('send', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $codiceSap = $data['codiceSap'];
            $result = $this->container->get('app.sap_service')->visualizzaBeneficiario($codiceSap, $data['ambiente']);
        }

        return $this->render('FunzioniServizioBundle:Sap:viewSogg.html.twig', [
            'form' => $form->createView(),
            'result' => $result,
        ]);
    }

    /**
     * @Route("/create_sogg/", name="sap_create_sogg")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function creaBeneficiario(Request $request)
    {
        $result = null;

        $form = $this->createFormBuilder()
            ->add('ragioneSociale', TextType::class, [
                'constraints' => [
                    new Length([
                        'max' => 140,
                        'min' => 1,
                   ]),
                ],
            ])
            ->add('codiceFiscale', TextType::class, [
                'constraints' => [
                    new Callback([
                        $this,
                        'validaLunghezzaCf',
                     ]),
                ],
            ])
            ->add('partitaIva', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Length([
                        'max' => 11,
                        'min' => 11,
                   ])
                ]
            ])
            ->add('zzCatEc', ChoiceType::class, [
                'choices' => $this->getCategoriaEconomicaSap(),
                'placeholder' => ' - ',
                'label' => 'Categoria Economica Sap',
                'choice_label' => function($choice, $key, $value) {
                    return $choice . ' - ' . $this->getCategoriaEconomicaSap()[$choice];
                }
            ])
            ->add('indirizzo', TextType::class, [])
            ->add('comune', EntityType::class, [
                'class' => GeoComune::class,
                'choice_label' => 'denominazione',
                'placeholder' => '-',
                'query_builder' => function (EntityRepository $repo) {
                    return $repo->createQueryBuilder('comuni')
                        ->where(
                            'comuni.cessato = 0',
                            'comuni.ceduto_legge_1989 = 0'
                        )
                        ->orderBy('comuni.denominazione')
                        ;
                },
            ])
            ->add('cap', TextType::class, [])
            ->add('stato', EntityType::class, [
                'class' => GeoStato::class,
                'choice_label' => 'denominazione',
                'placeholder' => '-',
                'query_builder' => function (EntityRepository $repo) {
                    return $repo->createQueryBuilder('stati')
                        ->orderBy('stati.denominazione');
                },
            ])
            ->add('telefono', TextType::class, [])
            ->add('fax', TextType::class, [])
            ->add('email', TextType::class, [])
            ->add('pec', TextType::class, [])
            ->add('zzCodCamComm', TextType::class, ['label' => 'Codice Camera di Commercio'])
            ->add('zzNumLocOpere', TextType::class, ['label' => 'Codice Localizzazione Opere'])
            ->add('zzNameLast', TextType::class, ['label' => 'Cognome'])
            ->add('zzNameFirst', TextType::class, ['label' => 'Nome'])
            ->add('gbdat', TextType::class, ['label' => 'Data Nascita (YYYYMMDD)'])
            ->add('sexkz', ChoiceType::class, ['label' => 'Sesso', 'choices' => ['M' => 'Maschio', 'F' => 'Femmina']])
            ->add('send', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $soggetto = new Soggetto();

            $soggetto->setDenominazione($data['ragioneSociale']);
            $soggetto->setCodiceFiscale($data['codiceFiscale']);
            $soggetto->setPartitaIva($data['partitaIva']);
            $soggetto->setVia($data['indirizzo']);
            $soggetto->setComune($data['comune']);
            $soggetto->setCap($data['cap']);
            $soggetto->setStato($data['stato']);
            $soggetto->setTel($data['telefono']);
            $soggetto->setFax($data['fax']);
            $soggetto->setEmail($data['email']);
            $soggetto->setEmailPec($data['pec']);
            $soggetto->zzCatEc = $data['zzCatEc'];

            $soggetto->flagPec       = null;
            $soggetto->smtpAddr      = null;
            $soggetto->regione       = null;
            $soggetto->zzCodCamComm  = null;
            $soggetto->zzNumLocOpere = null;
            $soggetto->zzNameLast    = null;
            $soggetto->zzNameFirst   = null;
            $soggetto->gbdat         = null;
            $soggetto->sexkz         = null;

            switch ($data['zzCatEc']) {
                case 211:
                case 212:
                case 213:
                case 215:
                case 220:
                case 221:
                case 222:
                case 223:
                case 430:
                case 431:
                    /*$soggetto->flagPec = 'S';
                    $soggetto->smtpAddr = $data['pec'];*/
                    $soggetto->region = $soggetto->getComune()->getProvincia()->getSiglaAutomobilistica();
                    $soggetto->zzCodCamComm = $data['zzCodCamComm'];
                    break;
                case 100:
                case 334:
                case 350:
                case 360:
                case 362:
                case 365:
                case 510:
                case 520:
                case 530:
                case 532:
                case 601:
                case 602:
                case 604:
                case 700:
                case 741:
                case 750:
                case 800:
                case 900:
                case 910:
                case 920:
                case 930:
                    $soggetto->region = $soggetto->getComune()->getProvincia()->getSiglaAutomobilistica();
                    break;
                case 310:
                case 320:
                case 330:
                    $soggetto->zzNumLocOpere = $data['zzNumLocOpere'];
                    break;
                case 210:
                    if(strlen($data['codiceFiscale']) == 16) {
                        $soggetto->zzNameLast = $data['zzNameLast'];
                        $soggetto->zzNameFirst = $data['zzNameFirst'];
                        $soggetto->gbdat = $data['gbdat'];
                        $soggetto->sexkz = $data['sexkz'];
                    }

                    $soggetto->region = $soggetto->getComune()->getProvincia()->getSiglaAutomobilistica();

                    break;
                case 224:
                case 231:
                case 232:
                case 410:
                case 411:
                case 531:
                    $soggetto->region = $soggetto->getComune()->getProvincia()->getSiglaAutomobilistica();
                    $soggetto->zzCodCamComm = $data['zzCodCamComm'];
                    break;
            }


            $result = $this->container->get('app.sap_service')->creaBeneficiari($soggetto, 'Dev');
        }

        return $this->render('FunzioniServizioBundle:Sap:createSogg.html.twig', [
            'form' => $form->createView(),
            'result' => $result,
        ]);
    }

    /**
     * @Route("/fatture_da_impegno/", name="sap_fatture_da_impegno")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function fattureDaImpegnoAction(Request $request)
    {
        $result = null;

        $form = $this->createFormBuilder()
            ->add('impegno', TextType::class, ['required' => true])
            ->add('posizione', TextType::class, ['required' => false])
            ->add('send', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $impegno = $data['impegno'];
            $posizione = $data['posizione'];

            $result = $this->container->get('app.sap_service')->fattureDaImpegno($impegno, $posizione,'Dev');
        }

        return $this->render('FunzioniServizioBundle:Sap:fattureDaImpegno.html.twig', [
            'form' => $form->createView(),
            'result' => $result,
        ]);
    }

    /**
     * @Route("/totalizzatore_impegno/", name="sap_totalizzatore_impegno")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function totalizzatoreImpegnoAction(Request $request)
    {
        $result = null;

        $form = $this->createFormBuilder()
            ->add('impegno', TextType::class, ['required' => true])
            ->add('send', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $impegno = $data['impegno'];

            $result = $this->container->get('app.sap_service')->totalizzatoriImpegno($impegno,'Dev');
        }

        return $this->render('FunzioniServizioBundle:Sap:totalizzatoreImpegno.html.twig', [
            'form' => $form->createView(),
            'result' => $result,
        ]);
    }

    /**
     * @Route("/crea_quietanze/", name="sap_crea_quietanze")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function creaQuietanze(Request $request)
    {
        $result = null;

        $form = $this->createFormBuilder()
            ->add('i_iban', TextType::class, [
                'label' => 'Iban',
            ])
            ->add('i_lifnr', TextType::class, [
                'label' => 'Numero sap beneficiario',
            ])
            ->add('i_swift', TextType::class, [
                'label' => 'Codice swift per banche estere',
                'required' => false,
            ])
            ->add('i_nome_banca_estera', TextType::class, [
                'label' => 'Nome banca estera',
                'required' => false,
            ])
            ->add('send', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $result = $this->container->get('app.sap_service')->creaQuietanza($data['i_iban'], $data['i_lifnr'], 20, $data['i_swift'], $data['i_nome_banca_estera'],'Dev');
        }

        return $this->render('FunzioniServizioBundle:Sap:creaQuietanze.html.twig', [
            'form' => $form->createView(),
            'result' => $result,
        ]);
    }

    /**
     * @Route("/crea_partite/", name="sap_crea_partite")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function creaPartite(Request $request)
    {
        $result = null;

        $form = $this->createFormBuilder()
            ->add('budat', TextType::class, [
                'label' => 'Data registrazione documento (YYYY-MM-DD)',
            ])
            ->add('bldat', TextType::class, [
                'label' => 'Data documento (YYYY-MM-DD)',
            ])
            ->add('xblnr', TextType::class, [
                'label' => 'Riferimento fattura',
            ])
            ->add('zz_num_loc', EntityType::class, [
                'class' => GeoComune::class,
                'choice_label' => 'denominazione',
                'label' => 'Comune localizzazione opere',
                'placeholder' => '-',
                'query_builder' => function (EntityRepository $repo) {
                    return $repo->createQueryBuilder('comuni')
                        ->where(
                            'comuni.cessato = 0',
                            'comuni.ceduto_legge_1989 = 0'
                        )
                        ->orderBy('comuni.denominazione')
                        ;
                },
            ])
            ->add('lifnr', TextType::class, [
                'label' => 'Numero sap beneficiario',
            ])
            ->add('kblnr', TextType::class, [
                'label' => 'Numero impegno',
            ])
            ->add('kblpos', TextType::class, [
                'label' => 'Posizione impegno',
            ])
            ->add('kostl', TextType::class, [
                'label' => 'Centro di costo',
                'required' => false,
            ])
            ->add('wrbtr', MoneyType::class, [
                'label' => 'Importo lordo',
            ])
            ->add('zzcup', TextType::class, [
                'label' => 'Cup',
                'required' => false,
            ])
            ->add('zzcig', TextType::class, [
                'label' => 'Cig',
                'required' => false,
            ])
            ->add('zfbdt', TextType::class, [
                'label' => 'Data base per calcolo scadenze (YYYY-MM-DD)',
                'required' => false,
            ])
            ->add('gg_scad', IntegerType::class, [
                'label' => 'Giornidi scadenza',
                'required' => false,
            ])
            ->add('i_note', TextareaType::class, [
                'label' => 'Note',
                'required' => false,
            ])
            ->add('send', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $result = $this->container->get('app.sap_service')->creaPartita($data, 'Dev');
        }

        return $this->render('FunzioniServizioBundle:Sap:creaPartite.html.twig', [
            'form' => $form->createView(),
            'result' => $result,
        ]);

    }

    /**
     * @Route("/capitoli_impegni_da_cup_cig/", name="sap_capitoli_impegni_cup_cig")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function capitoliImpegniDaCupCigAction(Request $request)
    {
        $result = null;

        $form = $this->createFormBuilder()
            ->add('cup', TextType::class)
            ->add('cig', TextType::class)
            ->add('send', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $cup = $data['cup'];
            $cig = $data['cig'];

            $result = $this->container->get('app.sap_service')->datiCapitoliImpegniDaCupEoCig($cup, $cig, 'Dev');
        }

        return $this->render('FunzioniServizioBundle:Sap:capitoliImpegniCupCig.html.twig', [
            'form' => $form->createView(),
            'result' => $result,
        ]);
    }

    /**
     * @Route("/dati_fatture_da_cup_cig/", name="sap_dati_fatture_cup_cig")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function datiFattureDaCupCigAction(Request $request)
    {
        $result = null;

        $form = $this->createFormBuilder()
            ->add('cup', TextType::class)
            ->add('cig', TextType::class)
            ->add('send', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $cup = $data['cup'];
            $cig = $data['cig'];

            $result = $this->container->get('app.sap_service')->datiFattureDaCupEoCig($cup, $cig, 'Dev');
        }

        return $this->render('FunzioniServizioBundle:Sap:datiFattureCupCig.html.twig', [
            'form' => $form->createView(),
            'result' => $result,
        ]);
    }

    /**
     * @Route("/dati_mandati_da_cup_cig/", name="sap_dati_mandati_cup_cig")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function datiMandatiDaCupCigAction(Request $request)
    {
        $result = null;

        $form = $this->createFormBuilder()
            ->add('cup', TextType::class)
            ->add('cig', TextType::class)
            ->add('send', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $cup = $data['cup'];
            $cig = $data['cig'];

            $result = $this->container->get('app.sap_service')->datiMandatiDaCupEoCig($cup, $cig, 'Dev');
        }

        return $this->render('FunzioniServizioBundle:Sap:datiFattureCupCig.html.twig', [
            'form' => $form->createView(),
            'result' => $result,
        ]);
    }

    /**
     * @return array
     */
    private function getCategoriaEconomicaSap()
    {
        return [
            '100' => 'Famiglie',
            '210' => 'Esercizio arti e professioni',
            '211' => 'Imprese private individuali',
            '212' => 'Imprese private societarie',
            '213' => 'Consorzi di imprese',
            '215' => 'Imprese agricole individuali',
            '220' => 'Imprese cooperative',
            '221' => 'Consorzi di cooperative',
            '222' => 'Imprese agricole societarie',
            '223' => 'Imprese agricole cooperative',
            '224' => 'Consorzi di imprese agricole',
            '231' => 'Altri Enti Pubblici Locali Econ.',
            '232' => 'Altri Enti Pubblici Locali Econ.',
            '233' => 'S.P.A. A Prevalente Capitale Pubblico Statale Indiretto',
            '310' => 'Comuni',
            '320' => 'Comunità montane',
            '330' => 'Province',
            '334' => 'Città metropolitane',
            '350' => 'Enti pubblici locali dell’amm.ne statale',
            '360' => 'Consorzi di enti locali',
            '362' => 'Istituzioni degli enti locali',
            '365' => 'Unioni di comuni',
            '410' => 'Aziende Speciali Degli Enti Locali',
            '411' => 'Aziende Pubbliche Di Servizi Alla Persona',
            '430' => 'Società a capitale pubblico locale',
            '431' => 'Società a prevalente capitale regionale',
            '510' => 'Aziende unità sanitarie locali e ospedaliere',
            '520' => 'Enti ed aziende regionali',
            '530' => 'Altri enti pubblici locali economici',
            '531' => 'Altri Enti Pubblici Locali Econ.',
            '532' => 'Enti a struttura associativa',
            '601' => 'Associazioni e istituz. Private senza fine di lucro',
            '602' => 'Ex altre associazioni (di categoria) – Codice chiuso',
            '604' => 'Fondazioni di livello subregionale',
            '700' => 'Consorzi di bonifica',
            '741' => 'Enti pubblici nazionali non econ.',
            '750' => 'Enti pubblici stranieri',
            '800' => 'Camere di commercio',
            '900' => 'Categoria per anagrafica pratiche di rimborso',
            '910' => 'Stato ed altri enti dell’amm.ne centrale',
            '920' => 'Regioni',
            '930' => 'Enti mutuo previdenziali',
        ];
    }

    /**
     * @param string                    $data
     * @param ExecutionContextInterface $context
     */
    public function validaLunghezzaCf(string $data, ExecutionContextInterface $context): void
    {
        if(strlen($data) !== 16 && strlen($data) !== 11) {
            $context->buildViolation('La lunghezza del campo può essere 11 o 16 caratteri')
                ->atPath('codiceFiscale')
                ->addViolation();
        }
    }

    /**
     * @param string $codiceFiscale
     * @param int $idSoggetto
     * @return array
     */
    public function getIdSoggetto(string $codiceFiscale, int $idSoggetto)
    {
        $retVal = ['esito' => 1, 'lifnr' => ''];
        $result = $this->container->get('app.sap_service')->ricercaBeneficiari($codiceFiscale, 'Dev');
        
        if ($result->E_RC === 0) {
            if ($result->E_BENEF->item instanceof stdClass) {
                $retVal['esito'] = 0;
                $retVal['lifnr'] = $result->E_BENEF->item->LIFNR;
            } elseif (is_array($result->E_BENEF->item)) {
                // Se la richiesta ha restituito più risultati vado ad eliminare quelli bloccati (SPERR=X)
                $arrayTmp = $result->E_BENEF->item;
                foreach ($result->E_BENEF->item as $key => $item) {
                    if ($item->SPERR == 'X') {
                        unset($arrayTmp[$key]);
                    }
                }
                
                if (count($arrayTmp) == 1) {
                    $key = array_key_first($arrayTmp);
                    $retVal['esito'] = 0;
                    $retVal['lifnr'] = $arrayTmp[$key]->LIFNR;
                } else {
                    // Se, anche dopo aver eliminato quelli bloccati, restano ancora più risultati,
                    // vado ad eliminare quelli con Categoria economica sap uguale a 900 (900 = Categoria per anagrafica pratiche di rimborso)
                    foreach ($arrayTmp as $key => $item) {
                        if ($item->ZZ_CAT_EC == 900) {
                            unset($arrayTmp[$key]);
                        }
                    }

                    if (count($arrayTmp) == 1) {
                        $key = array_key_first($arrayTmp);
                        $retVal['esito'] = 0;
                        $retVal['lifnr'] = $arrayTmp[$key]->LIFNR;
                    } else {
                        // Provo a vedere se tra i record trovati ce n'è uno con la categoria economica prevista
                        foreach ($arrayTmp as $key => $item) {
                            $em = $this->getDoctrine()->getManager();
                            /** @var Soggetto $soggetto */
                            $soggetto = $em->getRepository("SoggettoBundle:Soggetto")->find($idSoggetto);
                            
                            if ($soggetto) {
                                if ($item->ZZ_CAT_EC != $soggetto->getFormaGiuridica()->getCategoriaEconomicaSap()) {
                                    unset($arrayTmp[$key]);
                                }
                            }
                        }

                        if (count($arrayTmp) == 1) {
                            $key = array_key_first($arrayTmp);
                            $retVal['esito'] = 0;
                            $retVal['lifnr'] = 'ZZ-' . $arrayTmp[$key]->LIFNR;
                        } else {
                            // Troppi record, non si riesce a determinare quello corretto.
                            $retVal['esito'] = -1;
                        }
                    }
                }
            }
        }
        return $retVal;
    }
    
    /**
     * @Route("/popola_id_soggetti_bando/", name="popola_id_soggetti_bando")
     * @param Request $request
     * @return Response
     */
    public function popolaIdSoggettiBandoAction(Request $request)
    {
        ini_set('max_execution_time', -1);
        ini_set("memory_limit", "1024M");
        
        $success = [];
        $error = [];
        $soggettiBandoDaPopolare = null;
        
        $form = $this->createFormBuilder(null, ['validation_groups' => 'fake',])
            ->add('bando', EntityType::class, [
                'class' => Procedura::class,
                'required' => true,
                'constraints' => [
                    new NotBlank(['groups' => ['fake']]), 
                ],
                'choice_label' => 'titolo',
                'label' => 'Bando',
                'placeholder' => '-',
                'query_builder' => function (EntityRepository $repo) {
                    return $repo->createQueryBuilder('bando')
                        ->where('bando.id = 118');
                },
            ])
            ->add('limit', IntegerType::class, [
                'required' => false,
            ])
            ->add('send', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $valoreNonPresente = '-';
            $valoreMultiplo = '---';
            $em = $this->getDoctrine()->getManager();
            
            /** @var Soggetto[] $soggettiBando */
            $soggettiBando = $em->getRepository("SoggettoBundle:Soggetto")->getSoggettiBando($data['bando'], true, $data['limit']);
            foreach ($soggettiBando as $soggettoBando) {
                $result = $this->getIdSoggetto($soggettoBando['codice_fiscale'], $soggettoBando['id']);
                
                if ($result['esito'] === 1 && !empty($soggettoBando['partita_iva'])) {
                    // La ricerca tramite codice fiscale non ha dato risultati, provo tramite la partita iva
                    $result = $this->getIdSoggetto($soggettoBando['partita_iva'], $soggettoBando['id']);
                }
                
                $soggetto = $em->getRepository("SoggettoBundle:Soggetto")->find($soggettoBando['id']);
                if ($result['esito'] === 0) {
                    // Un solo record trovato
                    $soggetto->setLifnrSap($result['lifnr']);
                    $success[] = ['soggetto' => $soggettoBando, 'lifnr' => $result['lifnr']];
                } elseif ($result['esito'] === -1) {
                    // Record multipli trovati
                    $soggetto->setLifnrSap($valoreMultiplo);
                    $error[] = ['soggetto' => $soggettoBando, 'lifnr' => $valoreMultiplo];
                } else {
                    // Nessun record trovato
                    $soggetto->setLifnrSap($valoreNonPresente);
                    $error[] = ['soggetto' => $soggettoBando, 'lifnr' => $valoreNonPresente];
                }
                $em->persist($soggetto);
            }
            $em->flush();
            
            /** @var Soggetto[] $soggettiBando */
            $soggettiBandoDaPopolare = $em->getRepository("SoggettoBundle:Soggetto")->getSoggettiBando($data['bando']);
            $soggettiBandoDaPopolare = count($soggettiBandoDaPopolare);
        }

        return $this->render('FunzioniServizioBundle:Sap:popolaIdSoggettiBando.html.twig', [
            'form' => $form->createView(),
            'soggettiBandoDaPopolare' => $soggettiBandoDaPopolare,
            'success' => $success,
            'error' => $error,
        ]);
    }

    /**
     * @Route("/crea_soggetti_bando/", name="crea_soggetti_bando")
     * @param Request $request
     * @return Response
     */
    public function creaSoggettiBandoAction(Request $request)
    {
        ini_set('max_execution_time', -1);
        ini_set("memory_limit", "1024M");

        $success = [];
        $successGiaPresente = [];
        $error = [];

        $form = $this->createFormBuilder(null, ['validation_groups' => 'fake',])
            ->add('bando', EntityType::class, [
                'class' => Procedura::class,
                'required' => true,
                'constraints' => [
                    new NotBlank(['groups' => ['fake']]),
                ],
                'choice_label' => 'titolo',
                'label' => 'Bando',
                'placeholder' => '-',
                'query_builder' => function (EntityRepository $repo) {
                    return $repo->createQueryBuilder('bando')
                        ->where('bando.id IN (133, 134, 136, 138, 139, 149, 150, 151, 163, 164, 174)');
                },
            ])
            ->add('ambiente', ChoiceType::class, [
                'choices' => ['Dev' => 'Dev', 'Prod' => 'Prod'],
                'label' => 'Ambiente',
                'placeholder' => '-',
                'required' => true,
                'constraints' => [
                    new NotBlank(['groups' => ['fake']]),
                ],
            ])
            ->add('limit', IntegerType::class, [
                'required' => false,
            ])
            ->add('send', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $ambiente = $data['ambiente'];
            $em = $this->getDoctrine()->getManager();

            /** @var Soggetto[] $soggettiBando */
            $soggettiBando = $em->getRepository("SoggettoBundle:Soggetto")->getSoggettiBando($data['bando'], true, $data['limit']);
            foreach ($soggettiBando as $soggettoBando) {
                $result = $this->container->get('app.sap_service')->ricercaBeneficiari($soggettoBando['codice_fiscale'], $ambiente);
                if ($result->E_RC !== 0) {
                    // La ricerca tramite codice fiscale non ha dato risultati, provo tramite la partita iva
                    $result = $this->container->get('app.sap_service')->ricercaBeneficiari($soggettoBando['partita_iva'], $ambiente);
                }

                $soggetto = $em->getRepository("SoggettoBundle:Soggetto")->find($soggettoBando['id']);
                // Creo l’anagrafica se:
                // - non è stato trovato nè tramite codice fiscale nè tramite partita IVA
                // - c'è un unico soggetto presente con categoria economica uguale a 900 CATEGORIA PER ANAGRAFICA PRATICHE DI RIMBORSO o 100 FAMIGLIE
                if (($result->E_RC === 0 && $result->E_BENEF->item instanceof stdClass && ($result->E_BENEF->item->ZZ_CAT_EC == 900 || $result->E_BENEF->item->ZZ_CAT_EC == 100))
                    || $result->E_RC !== 0) {
                    $result = $this->creaSoggettoSap($soggetto->getId(), $ambiente);

                    if ($result['esito']) {
                        $soggetto->setLifnrSap($result['lifnr']);
                        $soggetto->setLifnrSapCreated(true);
                        $em->persist($soggetto);
                        $em->flush();
                        $success[] = ['soggetto' => $soggettoBando, 'lifnr' => $result['lifnr'], 'errori' => ''];
                    } else {
                        if (is_array($result['errori'][0])) {
                            $result['errori'] = $result['errori'][0];
                        }
                        $error[] = ['soggetto' => $soggettoBando, 'lifnr' => '', 'errori' => $result['errori']];
                    }
                } elseif ($result->E_RC === 0) {
                    if ($result->E_BENEF->item instanceof stdClass) {
                        // Il soggetto è già presente (e non ha categoria economica uguale a 900 o 100), vado a inserire il codice SAP (lifnr)
                        $soggetto->setLifnrSap($result->E_BENEF->item->LIFNR);
                        $em->persist($soggetto);
                        $em->flush();
                        $successGiaPresente[] = ['soggetto' => $soggettoBando, 'lifnr' => $result->E_BENEF->item->LIFNR, 'errori' => ''];
                    } elseif (is_array($result->E_BENEF->item)) {
                        // Sono presenti più anagrafiche
                        // 1. Se tutte le anagrafiche hanno la categoria economica SAP uguale a 900 vado a creare il soggetto
                        // 2. Se trovo una sola anagrafica non eliminata con codice economico del soggetto vado a prendere il suo lifnr

                        $anagraficheTrovate = [];
                        $anagraficheTrovateConCategoriaEconomica900 = 0;
                        $anagraficheTrovateConCategoriaEconomicaDelSoggetto = 0;
                        $lifnrSoggettoConCategoriaEconomicaCorretta = '';
                        foreach ($result->E_BENEF->item as $item) {
                            // *Non* vado a considerare le anagrafiche eliminate
                            if ($item->SPERR !== '') {
                                continue;
                            }

                            $categoriaEconomicaSoggetto = $soggetto->getFormaGiuridica()->getCategoriaEconomicaSap();
                            $anagraficheTrovate[] = [
                                'categoria_economica' => $item->ZZ_CAT_EC,
                            ];

                            if ($item->ZZ_CAT_EC == 900) {
                                $anagraficheTrovateConCategoriaEconomica900++;
                            }

                            if ($item->ZZ_CAT_EC == $categoriaEconomicaSoggetto) {
                                $anagraficheTrovateConCategoriaEconomicaDelSoggetto++;
                                $lifnrSoggettoConCategoriaEconomicaCorretta = $item->LIFNR;
                            }
                        }

                        $nrAnagraficheTrovate = count($anagraficheTrovate);
                        if ($nrAnagraficheTrovate == $anagraficheTrovateConCategoriaEconomica900) {
                            // 1. Se tutte le anagrafiche hanno la categoria economica SAP uguale a 900 vado a creare il soggetto
                            $result = $this->creaSoggettoSap($soggetto->getId(), $ambiente);

                            if ($result['esito']) {
                                $soggetto->setLifnrSap($result['lifnr']);
                                $soggetto->setLifnrSapCreated(true);
                                $em->persist($soggetto);
                                $em->flush();
                                $success[] = ['soggetto' => $soggettoBando, 'lifnr' => $result['lifnr'], 'errori' => ''];
                            } else {
                                if (is_array($result['errori'][0])) {
                                    $result['errori'] = $result['errori'][0];
                                }
                                $error[] = ['soggetto' => $soggettoBando, 'lifnr' => '', 'errori' => $result['errori']];
                            }
                        } else {
                            // 2. Se trovo una sola anagrafica non eliminata con codice economico del soggetto vado a prendere il suo lifnr
                            if ($anagraficheTrovateConCategoriaEconomicaDelSoggetto == 1) {
                                $soggetto->setLifnrSap($lifnrSoggettoConCategoriaEconomicaCorretta);
                                $em->persist($soggetto);
                                $em->flush();
                                $successGiaPresente[] = ['soggetto' => $soggettoBando, 'lifnr' => $lifnrSoggettoConCategoriaEconomicaCorretta, 'errori' => ''];
                            } else {
                                $error[] = ['soggetto' => $soggettoBando, 'lifnr' => '', 'errori' => 'Anagrafiche multiple con categoria economica diversa da quella richiesta'];
                            }
                        }
                    } else {
                        $error[] = ['soggetto' => $soggettoBando, 'lifnr' => '', 'errori' => 'Errore non previsto 1'];
                    }
                } else {
                    $error[] = ['soggetto' => $soggettoBando, 'lifnr' => '', 'errori' => 'Errore non previsto 2'];
                }
            }
        }

        return $this->render('FunzioniServizioBundle:Sap:creaSoggettiBando.html.twig', [
            'form' => $form->createView(),
            'success' => $success,
            'successGiaPresente' => $successGiaPresente,
            'error' => $error,
        ]);
    }

    /**
     * @Route("/crea_soggetti_bando_135/", name="crea_soggetti_bando_135")
     * @param Request $request
     * @return Response
     */
    public function creaSoggettiBando135Action(Request $request)
    {
        ini_set('max_execution_time', -1);
        ini_set("memory_limit", "1024M");

        $success = [];
        $successGiaPresente = [];
        $error = [];

        $form = $this->createFormBuilder(null, ['validation_groups' => 'fake',])
            ->add('bando', EntityType::class, [
                'class' => Procedura::class,
                'required' => true,
                'constraints' => [
                    new NotBlank(['groups' => ['fake']]),
                ],
                'choice_label' => 'titolo',
                'label' => 'Bando',
                'placeholder' => '-',
                'query_builder' => function (EntityRepository $repo) {
                    return $repo->createQueryBuilder('bando')
                        ->where('bando.id IN (135)');
                },
            ])
            ->add('ambiente', ChoiceType::class, [
                'choices' => ['Dev' => 'Dev', 'Prod' => 'Prod'],
                'label' => 'Ambiente',
                'placeholder' => '-',
                'required' => true,
                'constraints' => [
                    new NotBlank(['groups' => ['fake']]),
                ],
            ])
            ->add('limit', IntegerType::class, [
                'required' => false,
            ])
            ->add('send', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $em = $this->getDoctrine()->getManager();

            $soggettiBando = $this->getSoggettiBando135();

            foreach ($soggettiBando as $soggettoBando) {
                $result = $this->container->get('app.sap_service')->ricercaBeneficiari($soggettoBando['codice_fiscale'], $data['ambiente']);

                $frammento = $em->getRepository("FascicoloBundle:Frammento")
                    ->findOneBy(['alias' => 'form_stabilimento_balneare']);

                $istanzaFrammento = $em->getRepository("FascicoloBundle:IstanzaFrammento")
                    ->findOneBy(['istanzaPagina' => $soggettoBando['id'], 'frammento' => $frammento]);

                $campoLifNrSap = $em->getRepository("FascicoloBundle:Campo")->findOneBy(['alias' => 'stabilimento_balneare_lifnr_sap']);
                $istanzaCampoLifnrSap = $em->getRepository("FascicoloBundle:IstanzaCampo")
                    ->findOneBy(['campo' => $campoLifNrSap, 'istanzaFrammento' => $istanzaFrammento]);

                $campoLifNrSapCreated = $em->getRepository("FascicoloBundle:Campo")->findOneBy(['alias' => 'stabilimento_balneare_lifnr_sap_created']);
                $istanzaCampoLifnrSapCreated = $em->getRepository("FascicoloBundle:IstanzaCampo")
                    ->findOneBy(['campo' => $campoLifNrSapCreated, 'istanzaFrammento' => $istanzaFrammento]);

                if (empty($istanzaCampoLifnrSap)) {
                    $istanzaCampoLifnrSap = new IstanzaCampo();
                    $istanzaCampoLifnrSap->setCampo($campoLifNrSap);
                    $istanzaCampoLifnrSap->setDataCreazione(new DateTime());
                    $istanzaCampoLifnrSap->setDataModifica(new DateTime());
                    $istanzaCampoLifnrSap->setCreatoDa($this->getUser()->getUsername());
                    $istanzaCampoLifnrSap->setModificatoDa($this->getUser()->getUsername());
                    $istanzaCampoLifnrSap->setIstanzaFrammento($istanzaFrammento);
                }

                if (empty($istanzaCampoLifnrSapCreated)) {
                    $campoLifNrSapCreated = $em->getRepository("FascicoloBundle:Campo")->findOneBy(['alias' => 'stabilimento_balneare_lifnr_sap_created']);
                    $istanzaCampoLifnrSapCreated = new IstanzaCampo();
                    $istanzaCampoLifnrSapCreated->setCampo($campoLifNrSapCreated);
                    $istanzaCampoLifnrSapCreated->setCreatoDa($this->getUser()->getUsername());
                    $istanzaCampoLifnrSapCreated->setModificatoDa($this->getUser()->getUsername());
                    $istanzaCampoLifnrSapCreated->setIstanzaFrammento($istanzaFrammento);
                }

                if ($result->E_RC !== 0) {
                    // Il soggetto non è stato trovato nè tramite codice fiscale nè tramite partita IVA
                    // pertanto vado a creare il soggetto
                    $result = $this->creaSoggettoSapBando135($soggettoBando, $data['ambiente']);

                    if ($result['esito']) {
                        $istanzaCampoLifnrSap->setValore($result['lifnr']);
                        $istanzaCampoLifnrSap->setValoreRaw($result['lifnr']);

                        $istanzaCampoLifnrSapCreated->setValore(1);
                        $istanzaCampoLifnrSapCreated->setValoreRaw(1);

                        $em->persist($istanzaCampoLifnrSap);
                        $em->persist($istanzaCampoLifnrSapCreated);
                        $em->flush();
                        $success[] = ['soggetto' => $soggettoBando, 'lifnr' => $result['lifnr'], 'errori' => ''];
                    } else {
                        if (is_array($result['errori'][0])) {
                            $result['errori'] = $result['errori'][0];
                        }
                        $error[] = ['soggetto' => $soggettoBando, 'lifnr' => '', 'errori' => $result['errori']];
                    }
                } elseif ($result->E_RC === 0) {
                    if ($result->E_BENEF->item instanceof stdClass) {
                        // Il soggetto è già presente, vado a inserire il codice SAP (lifnr)
                        $istanzaCampoLifnrSap->setValore($result->E_BENEF->item->LIFNR);
                        $istanzaCampoLifnrSap->setValoreRaw($result->E_BENEF->item->LIFNR);

                        $istanzaCampoLifnrSapCreated->setValore(0);
                        $istanzaCampoLifnrSapCreated->setValoreRaw(0);

                        $em->persist($istanzaCampoLifnrSap);
                        $em->persist($istanzaCampoLifnrSapCreated);
                        $em->flush();

                        $successGiaPresente[] = ['soggetto' => $soggettoBando, 'lifnr' => $result->E_BENEF->item->LIFNR, 'errori' => ''];
                    } elseif (is_array($result->E_BENEF->item)) {
                        // Sono presenti più anagrafiche
                        // 1. Se tutte le anagrafiche hanno la categoria economica SAP uguale a 900 vado a creare il soggetto
                        // 2. Se trovo una sola anagrafica non eliminata con codice economico del soggetto vado a prendere il suo lifnr

                        $elencoLifNr = [];
                        $elencoCategorieEconomiche = [];
                        $anagraficheTrovate = [];
                        $anagraficheTrovateConCategoriaEconomica900 = 0;
                        $anagraficheTrovateConCategoriaEconomicaDelSoggetto = 0;
                        $lifnrSoggettoConCategoriaEconomicaCorretta = '';
                        foreach ($result->E_BENEF->item as $item) {
                            // *Non* vado a considerare le anagrafiche eliminate
                            if ($item->SPERR !== '') {
                                continue;
                            }

                            $categoriaEconomicaSoggetto =$soggettoBando['categoria_economica_sap'];
                            $elencoCategorieEconomiche[] = $item->ZZ_CAT_EC;
                            $elencoLifNr[] = $item->LIFNR;

                            $anagraficheTrovate[] = [
                                'categoria_economica' => $item->ZZ_CAT_EC,
                            ];

                            if ($item->ZZ_CAT_EC == 900) {
                                $anagraficheTrovateConCategoriaEconomica900++;
                            }

                            if ($item->ZZ_CAT_EC == $categoriaEconomicaSoggetto) {
                                $anagraficheTrovateConCategoriaEconomicaDelSoggetto++;
                                $lifnrSoggettoConCategoriaEconomicaCorretta = $item->LIFNR;
                            }
                        }

                        $nrAnagraficheTrovate = count($anagraficheTrovate);
                        if ($nrAnagraficheTrovate == $anagraficheTrovateConCategoriaEconomica900) {
                            // 1. Se tutte le anagrafiche hanno la categoria economica SAP uguale a 900 vado a creare il soggetto
                            $result = $this->creaSoggettoSapBando135($soggettoBando, $data['ambiente']);

                            if ($result['esito']) {
                                $istanzaCampoLifnrSap->setValore($result['lifnr']);
                                $istanzaCampoLifnrSap->setValoreRaw($result['lifnr']);

                                $istanzaCampoLifnrSapCreated->setValore(1);
                                $istanzaCampoLifnrSapCreated->setValoreRaw(1);

                                $em->persist($istanzaCampoLifnrSap);
                                $em->persist($istanzaCampoLifnrSapCreated);
                                $em->flush();
                                $success[] = ['soggetto' => $soggettoBando, 'lifnr' => $result['lifnr'], 'errori' => ''];
                            } else {
                                if (is_array($result['errori'][0])) {
                                    $result['errori'] = $result['errori'][0];
                                }
                                $error[] = ['soggetto' => $soggettoBando, 'lifnr' => '', 'errori' => $result['errori']];
                            }
                        } else {
                            // 2. Se trovo una sola anagrafica non eliminata con codice economico del soggetto vado a prendere il suo lifnr
                            if ($anagraficheTrovateConCategoriaEconomicaDelSoggetto == 1) {
                                $istanzaCampoLifnrSap->setValore($lifnrSoggettoConCategoriaEconomicaCorretta);
                                $istanzaCampoLifnrSap->setValoreRaw($lifnrSoggettoConCategoriaEconomicaCorretta);

                                $istanzaCampoLifnrSapCreated->setValore(0);
                                $istanzaCampoLifnrSapCreated->setValoreRaw(0);

                                $em->persist($istanzaCampoLifnrSap);
                                $em->persist($istanzaCampoLifnrSapCreated);
                                $em->flush();

                                $successGiaPresente[] = ['soggetto' => $soggettoBando, 'lifnr' => $lifnrSoggettoConCategoriaEconomicaCorretta, 'errori' => ''];
                            } else {
                                $error[] = ['soggetto' => $soggettoBando, 'lifnr' => '', 'errori' => 'Anagrafiche multiple con categoria economica diversa da quella richiesta'];
                            }
                        }
                    } else {
                        $error[] = ['soggetto' => $soggettoBando, 'lifnr' => '', 'errori' => 'Errore non previsto 1'];
                    }
                } else {
                    $error[] = ['soggetto' => $soggettoBando, 'lifnr' => '', 'errori' => 'Errore non previsto 2'];
                }
            }
        }

        return $this->render('FunzioniServizioBundle:Sap:creaSoggettiBando135.html.twig', [
            'form' => $form->createView(),
            'success' => $success,
            'successGiaPresente' => $successGiaPresente,
            'error' => $error,
        ]);
    }

    /**
     * @Route("/crea_persone_fisiche_bando/", name="crea_persone_fisiche_bando")
     * @param Request $request
     * @return Response
     */
    public function creaPersoneFisicheBandoAction(Request $request): Response
    {
        ini_set('max_execution_time', -1);
        ini_set("memory_limit", "1024M");

        $success = [];
        $successGiaPresente = [];
        $error = [];

        $form = $this->createFormBuilder(null, ['validation_groups' => 'fake',])
            ->add('bando', EntityType::class, [
                'class' => Procedura::class,
                'required' => true,
                'constraints' => [
                    new NotBlank(['groups' => ['fake']]),
                ],
                'choice_label' => 'titolo',
                'label' => 'Bando',
                'placeholder' => '-',
                'query_builder' => function (EntityRepository $repo) {
                    return $repo->createQueryBuilder('bando')
                        ->where('bando.id IN (139, 150, 151, 164, 171)');
                },
            ])
            ->add('limit', IntegerType::class, [
                'required' => false,
            ])
            ->add('send_produzione', SubmitType::class)
            ->add('send_test', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $data = $form->getData();
            $ambiente = "Dev";
            if ($form->getClickedButton()->getName() == 'send_produzione') {
                $ambiente = "Prod";
            }

            $istruttorieAmmesse = $em->getRepository("IstruttorieBundle:IstruttoriaRichiesta")
                ->getIstruttoriePerBando($data['bando']->getId(), true);

            $personeFisiche = [];
            foreach ($istruttorieAmmesse as $istruttoriaAmmessa) {
                $protocolli = [];
                if ($data['bando']->getId() == 150) {
                    $protocolli = GestoreRichiesteBando_150::PROTOCOLLI_RICHIESTE_APPROVATE_PERSONE_FISICHE;
                } elseif ($data['bando']->getId() == 151) {
                    $protocolli = GestoreRichiesteBando_151::PROTOCOLLI_RICHIESTE_APPROVATE_PERSONE_FISICHE;
                } elseif ($data['bando']->getId() == 164) {
                    $protocolli = GestoreRichiesteBando_164::PROTOCOLLI_RICHIESTE_APPROVATE_PERSONE_FISICHE;
                }

                if ($protocolli && !in_array($istruttoriaAmmessa->getRichiesta()->getProtocollo(), $protocolli)) {
                    continue;
                }

                $soggetto = $istruttoriaAmmessa->getRichiesta()->getMandatario()->getSoggetto();
                if ($data['bando']->getId() == 139) {
                    $persona = $em->getRepository("AnagraficheBundle:Persona")
                        ->getPersonaByUsername($istruttoriaAmmessa->getRichiesta()->getUtenteInvio());
                } else {
                    $persona = $em->getRepository("AnagraficheBundle:Persona")
                        ->findBy(['codice_fiscale' => $soggetto->getCodiceFiscale()]);
                }

                if (!empty($persona[0]->getLifnrSap()) || count($persona) != 1) {
                    continue;
                }

                if ($data['bando']->getId() == 139) {
                    if ($soggetto->getCodiceFiscale() != $persona[0]->getCodiceFiscale() && $soggetto->getLifnrSapCreated() == 1) {
                        $personeFisiche[] = $persona[0];
                    }
                } else {
                    $persona[0]->emailPec = null;
                    if (!empty($soggetto->getEmailPec())) {
                        $persona[0]->emailPec = $soggetto->getEmailPec();
                    }

                    $personeFisiche[] = $persona[0];
                }
            }

            if ($data['limit']) {
                $personeFisiche = array_slice($personeFisiche, 0, $data['limit']);
            }

            foreach ($personeFisiche as $personaFisica) {
                $ricerca = $this->container->get('app.sap_service')
                    ->ricercaBeneficiari($personaFisica->getCodiceFiscale(), $ambiente);

                if ($ricerca->E_RC === 0 && !is_array($ricerca->E_BENEF->item) && $ricerca->E_BENEF->item->ZZ_CAT_EC == 100) {
                    // La persona fisica è già presente, vado a inserire il codice SAP (lifnr)
                    $personaFisica->setLifnrSap($ricerca->E_BENEF->item->LIFNR);
                    $em->persist($personaFisica);
                    $em->flush();
                    $successGiaPresente[] = ['persona' => $personaFisica, 'lifnr' => $ricerca->E_BENEF->item->LIFNR, 'errori' => ''];
                } elseif ($ricerca->E_RC === 14 || ($ricerca->E_RC === 0 && !is_array($ricerca->E_BENEF->item) && $ricerca->E_BENEF->item->ZZ_CAT_EC != 100)) {
                    // Vado a creare la persona fisica
                    $esito = $this->creaPersonaFisicaSap($personaFisica, $ambiente);
                    if ($esito['esito']) {
                        $personaFisica->setLifnrSap($esito['lifnr']);
                        $personaFisica->setLifnrSapCreated(true);
                        $em->persist($personaFisica);
                        $em->flush();
                        $success[] = ['persona' => $personaFisica, 'lifnr' => $esito['lifnr'], 'errori' => ''];
                    } else {
                        if (is_array($esito['errori'][0])) {
                            $errori = $esito['errori'][0];
                        } else {
                            $errori = implode(' - ', $esito['errori']);
                        }
                        $error[] = ['persona' => $personaFisica, 'lifnr' => '', 'errori' => $errori];
                    }
                } elseif ($ricerca->E_RC === 0 && is_array($ricerca->E_BENEF->item)) {
                    $itemDiversiDa100 = 0;
                    foreach ($ricerca->E_BENEF->item as $item) {
                        if ($item->ZZ_CAT_EC != 100) {
                            $itemDiversiDa100++;
                        }
                    }

                    if (count($ricerca->E_BENEF->item) == $itemDiversiDa100) {
                        // Vado a creare la persona fisica
                        $esito = $this->creaPersonaFisicaSap($personaFisica, $ambiente);
                        if ($esito['esito']) {
                            $personaFisica->setLifnrSap($esito['lifnr']);
                            $personaFisica->setLifnrSapCreated(true);
                            $em->persist($personaFisica);
                            $em->flush();
                            $success[] = ['persona' => $personaFisica, 'lifnr' => $esito['lifnr'], 'errori' => ''];
                        } else {
                            $errori = '';
                            if (is_array($esito['errori'][0])) {
                                $errori = $esito['errori'][0];
                            }
                            $error[] = ['persona' => $personaFisica, 'lifnr' => '', 'errori' => $errori];
                        }
                    } else {
                        $error[] = ['persona' => $personaFisica, 'lifnr' => '', 'errori' => 'Errore 1'];
                    }
                } else {
                    $error[] = ['persona' => $personaFisica, 'lifnr' => '', 'errori' => $ricerca->E_RC];
                }
               /* } elseif ($result->E_RC === 0) {
                    if ($result->E_BENEF->item instanceof stdClass) {
                        // La persona fisica è già presente, vado a inserire il codice SAP (lifnr)
                        $personaFisica->setLifnrSap($result->E_BENEF->item->LIFNR);
                        $em->persist($personaFisica);
                        $em->flush();
                        $successGiaPresente[] = ['persona' => $personaFisica, 'lifnr' => $result->E_BENEF->item->LIFNR, 'errori' => ''];
                    } elseif (is_array($result->E_BENEF->item)) {
                        // Sono presenti più anagrafiche
                        $error[] = ['persona' => $personaFisica, 'lifnr' => '', 'errori' => 'Sono presenti più anagrafiche'];
                    } else {
                        $error[] = ['persona' => $personaFisica, 'lifnr' => '', 'errori' => 'Errore non previsto 1'];
                    }
                } else {
                    $error[] = ['persona' => $personaFisica, 'lifnr' => '', 'errori' => 'Errore non previsto 2'];
                }*/
            }
        }

        return $this->render('FunzioniServizioBundle:Sap:creaPersoneFisicheBando.html.twig', [
            'form' => $form->createView(),
            'success' => $success,
            'successGiaPresente' => $successGiaPresente,
            'error' => $error,
        ]);
    }

    /**
     * @param string $idSoggetto
     * @param string $env
     * @return array
     */
    public function creaSoggettoSap($idSoggetto = '', $env = 'Prod')
    {
        $em = $this->getDoctrine()->getManager();
        /** @var Soggetto $soggetto */
        $soggetto = $em->getRepository("SoggettoBundle:Soggetto")->find($idSoggetto);

        $soggettoCompleto = $soggetto->getSoggettoFesrPerCreazioneSoggettoSap();

        if ($soggettoCompleto['esito']) {
            $result = $this->container->get('app.sap_service')->creaBeneficiari($soggettoCompleto['soggetto'], $env);
            if ($result->E_RC === 0) {
                $retVal = ['esito' => true, 'lifnr' => $result->E_LIFNR, 'errori' => []];
            } else {
                if (property_exists($result->E_MESSAGES, 'item')) {
                    $retVal = ['esito' => false, 'lifnr' => null, 'errori' => [$result->E_MESSAGES->item]];
                } else {
                    $retVal = ['esito' => false, 'lifnr' => null, 'errori' => [$result->E_MESSAGES]];
                }
            }
        } else {
            $retVal = ['esito' => false, 'lifnr' => null, 'errori' => $soggettoCompleto['errori']];
        }
        return $retVal;
    }

    /**
     * @param Persona $persona
     * @param string $env
     * @return array
     */
    public function creaPersonaFisicaSap(Persona $persona, $env = 'Prod'): array
    {
        $personaCompleta = $persona->getPersonaSoggettoSap();
        if ($personaCompleta['esito']) {
            $result = $this->container->get('app.sap_service')->creaPersonaFisica($personaCompleta['persona'], $env);
            if ($result->E_RC === 0) {
                $retVal = ['esito' => true, 'lifnr' => $result->E_LIFNR, 'errori' => []];
            } else {
                $retVal = ['esito' => false, 'lifnr' => null, 'errori' => [$result->E_MESSAGES->item]];
            }
        } else {
            $retVal = ['esito' => false, 'lifnr' => null, 'errori' => $personaCompleta['errori']];
        }
        return $retVal;
    }

    /**
     * @param array $soggetto
     * @param string $env
     * @return array
     */
    public function creaSoggettoSapBando135(array $soggetto, $env = 'Prod')
    {
        $soggettoCompleto = $this->getSoggettoFesrPerCreazioneSoggettoSapBando135($soggetto);
        if ($soggettoCompleto['esito']) {
            $result = $this->container->get('app.sap_service')->creaBeneficiari($soggettoCompleto['soggetto'], $env);
            if ($result->E_RC === 0) {
                $retVal = ['esito' => true, 'lifnr' => $result->E_LIFNR, 'errori' => []];
            } else {
                $retVal = ['esito' => false, 'lifnr' => null, 'errori' => [$result->E_MESSAGES->item]];
            }
        } else {
            $retVal = ['esito' => false, 'lifnr' => null, 'errori' => $soggettoCompleto['errori']];
        }
        return $retVal;
    }

    /**
     * @Route("/mostra_soggetto_sap/{lifnr}/{env}", name="mostra_soggetto_sap")
     * @param string $lifnr
     * @param string $env
     * @return Response|null
     */
    public function mostraSoggettoSapAction(string $lifnr, string $env = 'Dev'): ?Response
    {
        $result = $this->container->get('app.sap_service')->visualizzaBeneficiario($lifnr, $env);
        return $this->render('FunzioniServizioBundle:Sap:mostraSoggettoSap.html.twig', [
            'result' => $result,
        ]);
    }

    /**
     * @Route("/verifica_soggetti_bando/", name="verifica_soggetti_bando")
     * @param Request $request
     * @return Response
     */
    public function verificaSoggettiBandoAction(Request $request)
    {
        $success = [];
        $error = [];

        $form = $this->createFormBuilder(null, ['validation_groups' => 'fake',])
            ->add('bando', EntityType::class, [
                'class' => Procedura::class,
                'required' => true,
                'constraints' => [
                    new NotBlank(['groups' => ['fake']]),
                ],
                'choice_label' => 'titolo',
                'label' => 'Bando',
                'placeholder' => '-',
                'query_builder' => function (EntityRepository $repo) {
                    return $repo->createQueryBuilder('bando')
                        ->where('bando.id IN (133, 134, 136, 139)');
                },
            ])
            ->add('ambiente', ChoiceType::class, [
                'choices' => ['Dev' => 'Dev', 'Prod' => 'Prod'],
                'label' => 'Ambiente',
                'placeholder' => '-',
                'required' => true,
                'constraints' => [
                    new NotBlank(['groups' => ['fake']]),
                ],
            ])
            ->add('senza_lifnr', ChoiceType::class, [
                'choices' => ['S' => 'Sì', 'N' => 'No'],
                'label' => 'Senza Lifnr',
                'placeholder' => '-',
                'required' => true,
                'constraints' => [
                    new NotBlank(['groups' => ['fake']]),
                ],
            ])
            ->add('limit', IntegerType::class, [
                'required' => false,
            ])
            ->add('send', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $senzaLifnr = $data['senza_lifnr'] == 'S' ? true : false;
            $em = $this->getDoctrine()->getManager();

            /** @var Soggetto[] $soggettiBando */
            $soggettiBando = $em->getRepository("SoggettoBundle:Soggetto")->getSoggettiBando($data['bando'], $senzaLifnr, $data['limit']);
            foreach ($soggettiBando as $soggettoBando) {
                $result = $this->container->get('app.sap_service')->ricercaBeneficiari($soggettoBando['codice_fiscale'], $data['ambiente']);

                if ($result->E_RC !== 0) {
                    // La ricerca tramite codice fiscale non ha dato risultati, provo tramite la partita iva
                    $result = $this->container->get('app.sap_service')->ricercaBeneficiari($soggettoBando['partita_iva'], $data['ambiente']);
                }

                if ($result->E_RC === 0) {
                    if ($result->E_BENEF->item instanceof stdClass) {
                        // E' già presente un unico soggetto
                        $success[] = [
                            'lifnr' => $result->E_BENEF->item->LIFNR,
                            'ragione_sociale' => $result->E_BENEF->item->ZNOME_RAG_SOC,
                            'codice_fiscale' => $result->E_BENEF->item->STCD1,
                            'categoria_economica' => $result->E_BENEF->item->ZZ_CAT_EC,
                            'comune' => $result->E_BENEF->item->MCOD3,
                            'eliminato' => $result->E_BENEF->item->SPERR,
                        ];
                    } elseif (is_array($result->E_BENEF->item)) {
                        // Sono presenti più soggetti
                        $elencoLifNr = [];
                        $elencoCategorieEconomiche = [];
                        foreach ($result->E_BENEF->item as $item) {
                            $elencoCategorieEconomiche[] = $item->ZZ_CAT_EC;
                            $elencoLifNr[] = $item->LIFNR;
                        }

                        $error[] = [
                            'soggetto' => $soggettoBando,
                            'categoria_economica' => implode(', ', $elencoCategorieEconomiche),
                            'lifnr' => implode(', ', $elencoLifNr),
                            'errori' => 'Anagrafiche multiple',
                        ];
                    } else {
                        $error[] = ['soggetto' => $soggettoBando, 'categoria_economica' => '', 'lifnr' => '', 'errori' => 'Errore non previsto'];
                    }
                } else {
                    $error[] = ['soggetto' => $soggettoBando, 'categoria_economica' => '', 'lifnr' => '', 'errori' => 'Soggetto non trovato'];
                }
            }
        }

        return $this->render('FunzioniServizioBundle:Sap:verificaSoggettiBando.html.twig', [
            'form' => $form->createView(),
            'success' => $success,
            'error' => $error,
        ]);
    }

    /**
     * @param bool $senzaLifnrSap
     * @param null $limit
     * @return array
     */
    public function getSoggettiBando135($senzaLifnrSap = true, $limit = null)
    {
        $idRichiesteDaEscludere = ['24544', '24545', '24583', '24591', '24601', '24936',];
        $objGestoreEsportazioneBando135 = new GestoreEsportazioneBando_135($this->container);
        $richieste = $objGestoreEsportazioneBando135->getRichiesteProcedura(null, $idRichiesteDaEscludere);

        $beneficiari = [];
        foreach ($richieste as $richiesta) {
            if (($senzaLifnrSap && empty($richiesta['lifnr_sap'])) || !$senzaLifnrSap) {
                $beneficiari[] = $richiesta;
            }
        }
        return $beneficiari;
    }


    /**
     * @param array $stabilimentoBalneare
     * @return array
     */
    public function getSoggettoFesrPerCreazioneSoggettoSapBando135(array $stabilimentoBalneare)
    {
        $em = $this->getDoctrine()->getManager();
        $retVal = ['esito' => true, 'soggetto' => null, 'errori' => []];
        if (empty($stabilimentoBalneare['ragione_sociale'])) {
            $retVal['esito'] = false;
            $retVal['errori'][] = 'Denominazione mancante';
        }

        if (empty($stabilimentoBalneare['codice_fiscale'])) {
            $retVal['esito'] = false;
            $retVal['errori'][] = 'Codice fiscale mancante';
        }

        if (empty($stabilimentoBalneare['sede_legale_indirizzo'])) {
            $retVal['esito'] = false;
            $retVal['errori'][] = 'Indirizzo mancante';
        }

        $sedeLegaleComune = $em->getRepository("GeoBundle:GeoComune")
            ->find($stabilimentoBalneare['sede_legale_comune_id']);
        if (empty($sedeLegaleComune)) {
            $retVal['esito'] = false;
            $retVal['errori'][] = 'Comune mancante';
        }

        if (empty($stabilimentoBalneare['sede_legale_cap'])) {
            $retVal['esito'] = false;
            $retVal['errori'][] = 'CAP mancante';
        }

        $sedeLegaleStato = $em->getRepository("GeoBundle:GeoStato")
            ->findOneBy(['denominazione' => $stabilimentoBalneare['sede_legale_stato']]);
        if (empty($sedeLegaleStato)) {
            $retVal['esito'] = false;
            $retVal['errori'][] = 'Stato mancante';
        }

        /*if (empty($stabilimentoBalneare['email'])) {
            $retVal['esito'] = false;
            $retVal['errori'][] = 'E-mail mancante';
        }*/

        if (empty($stabilimentoBalneare['email_pec'])) {
            $retVal['esito'] = false;
            $retVal['errori'][] = 'E-mail PEC mancante';
        }

        if (empty($stabilimentoBalneare['categoria_economica_sap'])) {
            $retVal['esito'] = false;
            $retVal['errori'][] = 'Categoria economica SAP mancante: ' . $stabilimentoBalneare['forma_giuridica'];
        }

        $soggetto = new Soggetto();
        if ($retVal['esito']) {
            $soggetto->setDenominazione($stabilimentoBalneare['ragione_sociale']);
            $soggetto->setCodiceFiscale($stabilimentoBalneare['codice_fiscale']);
            $soggetto->setPartitaIva('');
            $soggetto->setVia($stabilimentoBalneare['sede_legale_indirizzo'] . ($stabilimentoBalneare['sede_legale_numero_civico'] ? ' ' . $stabilimentoBalneare['sede_legale_numero_civico'] : ''));
            $soggetto->setComune($sedeLegaleComune);
            $soggetto->setCap($stabilimentoBalneare['sede_legale_cap']);
            $soggetto->setStato($sedeLegaleStato);
            $soggetto->setTel('');
            $soggetto->setFax('');
            $soggetto->setEmail('');
            $soggetto->setEmailPec($stabilimentoBalneare['email_pec']);
            $soggetto->zzCatEc = $stabilimentoBalneare['categoria_economica_sap'];

            $soggetto->flagPec = null;
            $soggetto->smtpAddr = null;
            $soggetto->regione = null;
            $soggetto->zzCodCamComm = null;
            $soggetto->zzNumLocOpere = null;
            $soggetto->zzNameLast = null;
            $soggetto->zzNameFirst = null;
            $soggetto->gbdat = null;
            $soggetto->sexkz = null;

            switch ($stabilimentoBalneare['categoria_economica_sap']) {
                case 211:
                case 212:
                case 213:
                case 215:
                case 220:
                case 221:
                case 222:
                case 224:
                case 223:
                case 231:
                case 232:
                case 233:
                case 410:
                case 411:
                case 430:
                case 431:
                case 531:
                    if (empty($stabilimentoBalneare['sede_legale_provincia'])) {
                        $retVal['esito'] = false;
                        $retVal['errori'][] = 'Provincia mancante';
                    } else {
                        $soggetto->region = $stabilimentoBalneare['sede_legale_provincia'];
                    }

                    if (empty($stabilimentoBalneare['numero_rea'])) {
                        $retVal['esito'] = false;
                        $retVal['errori'][] = 'Codice Rea mancante';
                    } else {
                        $soggetto->zzCodCamComm = $stabilimentoBalneare['numero_rea'];
                    }
                    break;

                case 100:
                case 334:
                case 350:
                case 360:
                case 362:
                case 365:
                case 510:
                case 520:
                case 530:
                case 532:
                case 601:
                case 602:
                case 604:
                case 700:
                case 741:
                case 750:
                case 800:
                case 900:
                case 910:
                case 920:
                case 930:
                if (empty($stabilimentoBalneare['sede_legale_provincia'])) {
                        $retVal['esito'] = false;
                        $retVal['errori'][] = 'Provincia mancante';
                    } else {
                        $soggetto->region = $stabilimentoBalneare['sede_legale_provincia'];
                    }
                    break;

                case 310:
                case 320:
                case 330:
                    // Comuni, Comunità montane, Province
                    //$soggetto->zzNumLocOpere = '';
                    break;

                case 210:
                    if ($stabilimentoBalneare['codice_fiscale'] == 'DNDSRN74M60C107B') {
                        $soggetto->zzNameLast = 'Dondi';
                        $soggetto->zzNameFirst = 'Sabrina';
                        $soggetto->gbdat = '1974-08-20';
                        $soggetto->sexkz = "2";
                    } else {
                        $retVal['esito'] = false;
                        $retVal['errori'][] = 'Specificare i dati del lavoratore autonomo';
                    }

                    if (empty($stabilimentoBalneare['sede_legale_provincia'])) {
                        $retVal['esito'] = false;
                        $retVal['errori'][] = 'Provincia mancante';
                    } else {
                        $soggetto->region = $stabilimentoBalneare['sede_legale_provincia'];
                    }
                    break;
            }

            $retVal['soggetto'] = $soggetto;
        }

        return $retVal;
    }

    /**
     * @Route("/popola_importi_bando_135/", name="popola_importi_bando_135")
     * @param Request $request
     * @return Response
     */
    public function popolaImportiBando135Action(Request $request)
    {
        $success = [];
        $successGiaPresente = [];
        $error = [];

        $form = $this->createFormBuilder(null, ['validation_groups' => 'fake',])
            ->add('bando', EntityType::class, [
                'class' => Procedura::class,
                'required' => true,
                'constraints' => [
                    new NotBlank(['groups' => ['fake']]),
                ],
                'choice_label' => 'titolo',
                'label' => 'Bando',
                'placeholder' => '-',
                'query_builder' => function (EntityRepository $repo) {
                    return $repo->createQueryBuilder('bando')
                        ->where('bando.id IN (135)');
                },
            ])
            ->add('send', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $arrayImporti = [
'173047' =>	 ['contributo' =>1392.33, 'percentuale' => ''],
'173053' =>	 ['contributo' =>1392.33, 'percentuale' => ''],
'173055' =>	 ['contributo' =>1392.33, 'percentuale' => ''],
'173057' =>	 ['contributo' =>1392.33, 'percentuale' => ''],
'173060' =>	 ['contributo' =>1392.32, 'percentuale' => ''],
'173063' =>	 ['contributo' =>1392.32, 'percentuale' => ''],
'173068' =>	 ['contributo' =>1392.33, 'percentuale' => ''],
'173070' =>	 ['contributo' =>1392.32, 'percentuale' => ''],
'173111' =>	 ['contributo' =>2227.73, 'percentuale' => ''],
'173112' =>	 ['contributo' =>2227.72, 'percentuale' => ''],
'173114' =>	 ['contributo' =>2227.72, 'percentuale' => ''],
'173115' =>	 ['contributo' =>2227.72, 'percentuale' => ''],
'173118' =>	 ['contributo' =>2227.72, 'percentuale' => ''],
'173532' =>	 ['contributo' =>3712.87, 'percentuale' => ''],
'173536' =>	 ['contributo' =>3712.87, 'percentuale' => ''],
'173593' =>	 ['contributo' =>1298.77, 'percentuale' => ''],
'173594' =>	 ['contributo' =>846.53 , 'percentuale' => ''],
'173596' =>	 ['contributo' =>987.99 , 'percentuale' => ''],
'173598' =>	 ['contributo' =>1294.31, 'percentuale' => ''],
'173600' =>	 ['contributo' =>1294.31, 'percentuale' => ''],
'173602' =>	 ['contributo' =>2334.65, 'percentuale' => ''],
'173603' =>	 ['contributo' =>1797.77, 'percentuale' => ''],
'173604' =>	 ['contributo' =>833.17 , 'percentuale' => ''],
'173606' =>	 ['contributo' =>451.11 , 'percentuale' => ''],
'173638' =>	 ['contributo' =>4259.40, 'percentuale' => ''],
'173639' =>	 ['contributo' =>1348.51, 'percentuale' => ''],
'173640' =>	 ['contributo' =>766.34 , 'percentuale' => ''],
'173645' =>	 ['contributo' =>1800.00, 'percentuale' => ''],
'173646' =>	 ['contributo' =>1805.94, 'percentuale' => ''],
'173647' =>	 ['contributo' =>902.97 , 'percentuale' => ''],
'173648' =>	 ['contributo' =>902.97 , 'percentuale' => ''],
'173649' =>	 ['contributo' =>3665.35, 'percentuale' => ''],
'173650' =>	 ['contributo' =>1081.19, 'percentuale' => ''],
'173651' =>	 ['contributo' =>1229.70, 'percentuale' => ''],
'173652' =>	 ['contributo' =>1021.78, 'percentuale' => ''],
'173655' =>	 ['contributo' =>914.84 , 'percentuale' => ''],
'173657' =>	 ['contributo' =>1883.17, 'percentuale' => ''],
'173660' =>	 ['contributo' =>766.34 , 'percentuale' => ''],
'173663' =>	 ['contributo' =>1532.67, 'percentuale' => ''],
'173665' =>	 ['contributo' =>772.28 , 'percentuale' => ''],
'173666' =>	 ['contributo' =>784.16 , 'percentuale' => ''],
'173667' =>	 ['contributo' =>796.04 , 'percentuale' => ''],
'173668' =>	 ['contributo' =>1009.90, 'percentuale' => ''],
'173669' =>	 ['contributo' =>1865.35, 'percentuale' => ''],
'173670' =>	 ['contributo' =>2453.46, 'percentuale' => ''],
'173671' =>	 ['contributo' =>154.46 , 'percentuale' => ''],
'173672' =>	 ['contributo' =>1396.04, 'percentuale' => ''],
'173673' =>	 ['contributo' =>2560.40, 'percentuale' => ''],
'173674' =>	 ['contributo' =>3249.50, 'percentuale' => ''],
'173675' =>	 ['contributo' =>1716.83, 'percentuale' => ''],
'173676' =>	 ['contributo' =>980.20 , 'percentuale' => ''],
'173677' =>	 ['contributo' =>920.79 , 'percentuale' => ''],
'173678' =>	 ['contributo' =>3677.23, 'percentuale' => ''],
'173679' =>	 ['contributo' =>1687.13, 'percentuale' => ''],
'173680' =>	 ['contributo' =>2910.89, 'percentuale' => ''],
'173681' =>	 ['contributo' =>2453.46, 'percentuale' => ''],
'173682' =>	 ['contributo' =>4140.59, 'percentuale' => ''],
'173683' =>	 ['contributo' =>1996.04, 'percentuale' => ''],
'172933' =>	 ['contributo' =>950.49 , 'percentuale' => ''],
'172935' =>	 ['contributo' =>784.16 , 'percentuale' => ''],
'172937' =>	 ['contributo' =>760.40 , 'percentuale' => ''],
'172939' =>	 ['contributo' =>760.40 , 'percentuale' => ''],
'172942' =>	 ['contributo' =>1211.88, 'percentuale' => ''],
'172943' =>	 ['contributo' =>1128.71, 'percentuale' => ''],
'172944' =>	 ['contributo' =>748.51 , 'percentuale' => ''],
'172945' =>	 ['contributo' =>772.28 , 'percentuale' => ''],
'172950' =>	 ['contributo' =>760.40 , 'percentuale' => ''],
'172957' =>	 ['contributo' =>772.28 , 'percentuale' => ''],
'172961' =>	 ['contributo' =>748.51 , 'percentuale' => ''],
'173040' =>	 ['contributo' =>1817.82, 'percentuale' => ''],
'173041' =>	 ['contributo' =>1081.19, 'percentuale' => ''],
'173042' =>	 ['contributo' =>855.45 , 'percentuale' => ''],
'173044' =>	 ['contributo' =>736.63 , 'percentuale' => ''],
'173045' =>	 ['contributo' =>784.16 , 'percentuale' => ''],
'173046' =>	 ['contributo' =>1152.47, 'percentuale' => ''],
'173048' =>	 ['contributo' =>796.04 , 'percentuale' => ''],
'173049' =>	 ['contributo' =>843.56 , 'percentuale' => ''],
'173050' =>	 ['contributo' =>902.97 , 'percentuale' => ''],
'173052' =>	 ['contributo' =>617.82 , 'percentuale' => ''],
'173054' =>	 ['contributo' =>1140.59, 'percentuale' => ''],
'173058' =>	 ['contributo' =>1128.71, 'percentuale' => ''],
'173059' =>	 ['contributo' =>855.45 , 'percentuale' => ''],
'173061' =>	 ['contributo' =>748.51 , 'percentuale' => ''],
'173065' =>	 ['contributo' =>605.94 , 'percentuale' => ''],
'173067' =>	 ['contributo' =>689.11 , 'percentuale' => ''],
'173069' =>	 ['contributo' =>736.63 , 'percentuale' => ''],
'173071' =>	 ['contributo' =>843.56 , 'percentuale' => ''],
'173075' =>	 ['contributo' =>1164.36, 'percentuale' => ''],
'173079' =>	 ['contributo' =>1556.44, 'percentuale' => ''],
'173080' =>	 ['contributo' =>855.45 , 'percentuale' => ''],
'173082' =>	 ['contributo' =>879.21 , 'percentuale' => ''],
'173084' =>	 ['contributo' =>855.45 , 'percentuale' => ''],
'173086' =>	 ['contributo' =>594.06 , 'percentuale' => ''],
'173088' =>	 ['contributo' =>629.70 , 'percentuale' => ''],
'173090' =>	 ['contributo' =>700.99 , 'percentuale' => ''],
'173091' =>	 ['contributo' =>998.02 , 'percentuale' => ''],
'173093' =>	 ['contributo' =>902.97 , 'percentuale' => ''],
'173096' =>	 ['contributo' =>1176.24, 'percentuale' => ''],
'173097' =>	 ['contributo' =>1271.29, 'percentuale' => ''],
'173098' =>	 ['contributo' =>1057.43, 'percentuale' => ''],
'173099' =>	 ['contributo' =>867.33 , 'percentuale' => ''],
'173100' =>	 ['contributo' =>914.85 , 'percentuale' => ''],
'173101' =>	 ['contributo' =>748.51 , 'percentuale' => ''],
'173102' =>	 ['contributo' =>831.68 , 'percentuale' => ''],
'173103' =>	 ['contributo' =>748.51 , 'percentuale' => ''],
'173104' =>	 ['contributo' =>855.45 , 'percentuale' => ''],
'173105' =>	 ['contributo' =>689.11 , 'percentuale' => ''],
'173109' =>	 ['contributo' =>1033.66, 'percentuale' => ''],
'173110' =>	 ['contributo' =>867.33 , 'percentuale' => ''],
'173116' =>	 ['contributo' =>724.75 , 'percentuale' => ''],
'173119' =>	 ['contributo' =>1093.07, 'percentuale' => ''],
'173120' =>	 ['contributo' =>558.42 , 'percentuale' => ''],
'173122' =>	 ['contributo' =>1021.78, 'percentuale' => ''],
'173123' =>	 ['contributo' =>986.14 , 'percentuale' => ''],
'173125' =>	 ['contributo' =>831.68 , 'percentuale' => ''],
'173126' =>	 ['contributo' =>784.16 , 'percentuale' => ''],
'173127' =>	 ['contributo' =>1449.50, 'percentuale' => ''],
'173128' =>	 ['contributo' =>855.45 , 'percentuale' => ''],
'173129' =>	 ['contributo' =>1223.76, 'percentuale' => ''],
'173130' =>	 ['contributo' =>724.75 , 'percentuale' => ''],
'173131' =>	 ['contributo' =>1093.07, 'percentuale' => ''],
'173132' =>	 ['contributo' =>962.38 , 'percentuale' => ''],
'173133' =>	 ['contributo' =>914.85 , 'percentuale' => ''],
'173134' =>	 ['contributo' =>748.51 , 'percentuale' => ''],
'173135' =>	 ['contributo' =>760.40 , 'percentuale' => ''],
'173136' =>	 ['contributo' =>736.63 , 'percentuale' => ''],
'173144' =>	 ['contributo' =>1211.88, 'percentuale' => ''],
'173150' =>	 ['contributo' =>891.09 , 'percentuale' => ''],
'173151' =>	 ['contributo' =>700.99 , 'percentuale' => ''],
'173152' =>	 ['contributo' =>807.92 , 'percentuale' => ''],
'173153' =>	 ['contributo' =>653.47 , 'percentuale' => ''],
'173154' =>	 ['contributo' =>1603.96, 'percentuale' => ''],
'173156' =>	 ['contributo' =>1235.64, 'percentuale' => ''],
'173158' =>	 ['contributo' =>855.45 , 'percentuale' => ''],
'173163' =>	 ['contributo' =>1128.71, 'percentuale' => ''],
'173166' =>	 ['contributo' =>724.75 , 'percentuale' => ''],
'173167' =>	 ['contributo' =>950.49 , 'percentuale' => ''],
'173174' =>	 ['contributo' =>974.26 , 'percentuale' => ''],
'173175' =>	 ['contributo' =>617.82 , 'percentuale' => ''],
'173176' =>	 ['contributo' =>986.14 , 'percentuale' => ''],
'173177' =>	 ['contributo' =>867.33 , 'percentuale' => ''],
'173179' =>	 ['contributo' =>819.80 , 'percentuale' => ''],
'173181' =>	 ['contributo' =>962.38 , 'percentuale' => ''],
'173185' =>	 ['contributo' =>879.21 , 'percentuale' => ''],
'173189' =>	 ['contributo' =>974.26 , 'percentuale' => ''],
'173202' =>	 ['contributo' =>796.04 , 'percentuale' => ''],
'173206' =>	 ['contributo' =>724.75 , 'percentuale' => ''],
'173212' =>	 ['contributo' =>2471.29, 'percentuale' => ''],
'173215' =>	 ['contributo' =>427.72 , 'percentuale' => ''],
'173216' =>	 ['contributo' =>974.26 , 'percentuale' => ''],
'173217' =>	 ['contributo' =>1057.43, 'percentuale' => ''],
'173218' =>	 ['contributo' =>1045.54, 'percentuale' => ''],
'173219' =>	 ['contributo' =>1009.90, 'percentuale' => ''],
'173220' =>	 ['contributo' =>582.18 , 'percentuale' => ''],
'173221' =>	 ['contributo' =>700.99 , 'percentuale' => ''],
'173222' =>	 ['contributo' =>855.45 , 'percentuale' => ''],
'173223' =>	 ['contributo' =>2506.93, 'percentuale' => ''],
'173225' =>	 ['contributo' =>617.82 , 'percentuale' => ''],
'173226' =>	 ['contributo' =>605.94 , 'percentuale' => ''],
'173228' =>	 ['contributo' =>1176.24, 'percentuale' => ''],
'173229' =>	 ['contributo' =>1045.54, 'percentuale' => ''],
'173231' =>	 ['contributo' =>926.73 , 'percentuale' => ''],
'173233' =>	 ['contributo' =>1021.78, 'percentuale' => ''],
'173235' =>	 ['contributo' =>1033.66, 'percentuale' => ''],
'173236' =>	 ['contributo' =>914.85 , 'percentuale' => ''],
'173237' =>	 ['contributo' =>974.26 , 'percentuale' => ''],
'173238' =>	 ['contributo' =>1069.31, 'percentuale' => ''],
'173331' =>	 ['contributo' =>1045.54, 'percentuale' => ''],
'173333' =>	 ['contributo' =>914.85 , 'percentuale' => ''],
'173334' =>	 ['contributo' =>974.26 , 'percentuale' => ''],
'173338' =>	 ['contributo' =>3053.46, 'percentuale' => ''],
'173344' =>	 ['contributo' =>594.06 , 'percentuale' => ''],
'173346' =>	 ['contributo' =>784.16 , 'percentuale' => ''],
'173347' =>	 ['contributo' =>914.85 , 'percentuale' => ''],
'173348' =>	 ['contributo' =>712.87 , 'percentuale' => ''],
'173349' =>	 ['contributo' =>724.75 , 'percentuale' => ''],
'173354' =>	 ['contributo' =>724.75 , 'percentuale' => ''],
'173369' =>	 ['contributo' =>582.18 , 'percentuale' => ''],
'173372' =>	 ['contributo' =>724.75 , 'percentuale' => ''],
'173377' =>	 ['contributo' =>1104.95, 'percentuale' => ''],
'173379' =>	 ['contributo' =>594.06 , 'percentuale' => ''],
'173382' =>	 ['contributo' =>154.45 , 'percentuale' => ''],
'173454' =>	 ['contributo' =>403.96 , 'percentuale' => ''],
'173455' =>	 ['contributo' =>510.89 , 'percentuale' => ''],
'173457' =>	 ['contributo' =>439.60 , 'percentuale' => ''],
'173458' =>	 ['contributo' =>427.72 , 'percentuale' => ''],
'173459' =>	 ['contributo' =>677.23 , 'percentuale' => ''],
'173465' =>	 ['contributo' =>415.84 , 'percentuale' => ''],
'173467' =>	 ['contributo' =>546.53 , 'percentuale' => ''],
'174692' =>	 ['contributo' =>3093.56, 'percentuale' => ''],
'174693' =>	 ['contributo' =>3712.87, 'percentuale' => ''],
'174745' =>	 ['contributo' =>1237.13, 'percentuale' => ''],
'174759' =>	 ['contributo' =>1237.13, 'percentuale' => ''],
'174931' =>	 ['contributo' =>5570.79, 'percentuale' => ''],
'173332' =>	 ['contributo' =>623.75 , 'percentuale' => ''],
'173339' =>	 ['contributo' =>651.61 , 'percentuale' => ''],
'173340' =>	 ['contributo' =>651.61 , 'percentuale' => ''],
'173341' =>	 ['contributo' =>657.18 , 'percentuale' => ''],
'173342' =>	 ['contributo' =>655.32 , 'percentuale' => ''],
'173343' =>	 ['contributo' =>651.61 , 'percentuale' => ''],
'173345' =>	 ['contributo' =>653.47 , 'percentuale' => ''],
'173357' =>	 ['contributo' =>666.46 , 'percentuale' => ''],
'173366' =>	 ['contributo' =>618.19 , 'percentuale' => ''],
'173375' =>	 ['contributo' =>634.90 , 'percentuale' => ''],
'173376' =>	 ['contributo' =>556.93 , 'percentuale' => ''],
'173378' =>	 ['contributo' =>861.39 , 'percentuale' => ''],
'173380' =>	 ['contributo' =>599.63 , 'percentuale' => ''],
'173381' =>	 ['contributo' =>573.64 , 'percentuale' => ''],
'173383' =>	 ['contributo' =>664.60 , 'percentuale' => ''],
'173385' =>	 ['contributo' =>564.36 , 'percentuale' => ''],
'173410' =>	 ['contributo' =>937.50 , 'percentuale' => ''],
'173414' =>	 ['contributo' =>724.01 , 'percentuale' => ''],
'173484' =>	 ['contributo' =>748.14 , 'percentuale' => ''],
'173485' =>	 ['contributo' =>588.49 , 'percentuale' => ''],
'173486' =>	 ['contributo' =>685.02 , 'percentuale' => ''],
'173494' =>	 ['contributo' =>436.26 , 'percentuale' => ''],
'173497' =>	 ['contributo' =>417.70 , 'percentuale' => ''],
'173499' =>	 ['contributo' =>725.87 , 'percentuale' => ''],
'173504' =>	 ['contributo' =>538.37 , 'percentuale' => ''],
'173541' =>	 ['contributo' =>538.37 , 'percentuale' => ''],
'173542' =>	 ['contributo' =>800.12 , 'percentuale' => ''],
'173543' =>	 ['contributo' =>504.95 , 'percentuale' => ''],
'173544' =>	 ['contributo' =>634.90 , 'percentuale' => ''],
'173608' =>	 ['contributo' =>2545.55, 'percentuale' => ''],
'173653' =>	 ['contributo' =>3712.87, 'percentuale' => ''],
'173654' =>	 ['contributo' =>406.93 , 'percentuale' => ''],
'173656' =>	 ['contributo' =>708.42 , 'percentuale' => ''],
'173658' =>	 ['contributo' =>424.75 , 'percentuale' => ''],
'173659' =>	 ['contributo' =>813.86 , 'percentuale' => ''],
'173661' =>	 ['contributo' =>2701.48, 'percentuale' => ''],
'173662' =>	 ['contributo' =>3170.79, 'percentuale' => ''],
'173664' =>	 ['contributo' =>366.83 , 'percentuale' => ''],
'175324' =>	 ['contributo' =>608.91 , 'percentuale' => ''],
'175331' =>	 ['contributo' =>779.70 , 'percentuale' => ''],
'175341' =>	 ['contributo' =>1058.17, 'percentuale' => ''],
'175345' =>	 ['contributo' =>779.70 , 'percentuale' => ''],
'175347' =>	 ['contributo' =>1373.76, 'percentuale' => ''],
'175351' =>	 ['contributo' =>538.37 , 'percentuale' => ''],
'175355' =>	 ['contributo' =>553.22 , 'percentuale' => ''],
'175358' =>	 ['contributo' =>839.11 , 'percentuale' => ''],
'175364' =>	 ['contributo' =>839.11 , 'percentuale' => ''],
'175370' =>	 ['contributo' =>1544.55, 'percentuale' => ''],
'175371' =>	 ['contributo' =>750.00 , 'percentuale' => ''],
'175396' =>	 ['contributo' =>750.00 , 'percentuale' => ''],
'175400' =>	 ['contributo' =>612.62 , 'percentuale' => ''],
'175401' =>	 ['contributo' =>612.62 , 'percentuale' => ''],
'175403' =>	 ['contributo' =>761.14 , 'percentuale' => ''],
'175404' =>	 ['contributo' =>612.62 , 'percentuale' => ''],
'175405' =>	 ['contributo' =>501.24 , 'percentuale' => ''],
'175406' =>	 ['contributo' =>608.91 , 'percentuale' => ''],
'175407' =>	 ['contributo' =>482.67 , 'percentuale' => ''],
'175408' =>	 ['contributo' =>865.10 , 'percentuale' => ''],
'175410' =>	 ['contributo' =>798.27 , 'percentuale' => ''],
'175411' =>	 ['contributo' =>983.91 , 'percentuale' => ''],
'175416' =>	 ['contributo' =>983.91 , 'percentuale' => ''],
'175418' =>	 ['contributo' =>909.65 , 'percentuale' => ''],
'175419' =>	 ['contributo' =>924.50 , 'percentuale' => ''],
'175421' =>	 ['contributo' =>1800.74, 'percentuale' => ''],
'175427' =>	 ['contributo' =>556.93 , 'percentuale' => ''],
'175428' =>	 ['contributo' =>426.99 , 'percentuale' => ''],
'175435' =>	 ['contributo' =>426.99 , 'percentuale' => ''],
'175439' =>	 ['contributo' =>4195.54, 'percentuale' => ''],
'175443' =>	 ['contributo' =>865.10 , 'percentuale' => ''],
'175445' =>	 ['contributo' =>883.66 , 'percentuale' => ''],
'175448' =>	 ['contributo' =>727.72 , 'percentuale' => ''],
'175449' =>	 ['contributo' =>1314.36, 'percentuale' => ''],
'175451' =>	 ['contributo' =>1191.83, 'percentuale' => ''],
'175453' =>	 ['contributo' =>1162.13, 'percentuale' => ''],
'175456' =>	 ['contributo' =>430.70 , 'percentuale' => ''],
'175458' =>	 ['contributo' =>660.89 , 'percentuale' => ''],
'175459' =>	 ['contributo' =>1633.66, 'percentuale' => ''],
'175462' =>	 ['contributo' =>779.70 , 'percentuale' => ''],
'174096' =>	 ['contributo' =>1195.54, 'percentuale' => ''],
'174261' =>	 ['contributo' =>1332.18, 'percentuale' => ''],
'174270' =>	 ['contributo' =>2134.90, 'percentuale' => ''],
'174278' =>	 ['contributo' =>990.59 , 'percentuale' => ''],
'174287' =>	 ['contributo' =>939.36 , 'percentuale' => ''],
'174302' =>	 ['contributo' =>409.90 , 'percentuale' => ''],
'174303' =>	 ['contributo' =>1417.57, 'percentuale' => ''],
'174312' =>	 ['contributo' =>1400.49, 'percentuale' => ''],
'174320' =>	 ['contributo' =>444.06 , 'percentuale' => ''],
'174323' =>	 ['contributo' =>888.12 , 'percentuale' => ''],
'174333' =>	 ['contributo' =>973.51 , 'percentuale' => ''],
'174348' =>	 ['contributo' =>495.30 , 'percentuale' => ''],
'174357' =>	 ['contributo' =>478.22 , 'percentuale' => ''],
'174358' =>	 ['contributo' =>495.30 , 'percentuale' => ''],
'174366' =>	 ['contributo' =>478.22 , 'percentuale' => ''],
'174377' =>	 ['contributo' =>478.22 , 'percentuale' => ''],
'174386' =>	 ['contributo' =>990.59 , 'percentuale' => ''],
'174395' =>	 ['contributo' =>495.30 , 'percentuale' => ''],
'174401' =>	 ['contributo' =>495.30 , 'percentuale' => ''],
'174511' =>	 ['contributo' =>375.74 , 'percentuale' => ''],
'174512' =>	 ['contributo' =>614.85 , 'percentuale' => ''],
'174513' =>	 ['contributo' =>853.96 , 'percentuale' => ''],
'174516' =>	 ['contributo' =>990.59 , 'percentuale' => ''],
'174519' =>	 ['contributo' =>495.30 , 'percentuale' => ''],
'174533' =>	 ['contributo' =>990.59 , 'percentuale' => ''],
'174537' =>	 ['contributo' =>495.30 , 'percentuale' => ''],
'174539' =>	 ['contributo' =>444.06 , 'percentuale' => ''],
'174543' =>	 ['contributo' =>871.04 , 'percentuale' => ''],
'174545' =>	 ['contributo' =>990.59 , 'percentuale' => ''],
'174551' =>	 ['contributo' =>649.01 , 'percentuale' => ''],
'174554' =>	 ['contributo' =>1178.46, 'percentuale' => ''],
'174555' =>	 ['contributo' =>666.09 , 'percentuale' => ''],
'174557' =>	 ['contributo' =>1520.05, 'percentuale' => ''],
'174559' =>	 ['contributo' =>529.46 , 'percentuale' => ''],
'174566' =>	 ['contributo' =>495.30 , 'percentuale' => ''],
'174571' =>	 ['contributo' =>529.46 , 'percentuale' => ''],
'174572' =>	 ['contributo' =>529.46 , 'percentuale' => ''],
'174574' =>	 ['contributo' =>392.82 , 'percentuale' => ''],
'174579' =>	 ['contributo' =>614.85 , 'percentuale' => ''],
'174581' =>	 ['contributo' =>836.88 , 'percentuale' => ''],
'174582' =>	 ['contributo' =>1349.26, 'percentuale' => ''],
'174590' =>	 ['contributo' =>1964.11, 'percentuale' => ''],
'174593' =>	 ['contributo' =>495.30 , 'percentuale' => ''],
'174598' =>	 ['contributo' =>990.59 , 'percentuale' => ''],
'174601' =>	 ['contributo' =>614.85 , 'percentuale' => ''],
'174609' =>	 ['contributo' =>871.05 , 'percentuale' => ''],
'174613' =>	 ['contributo' =>597.77 , 'percentuale' => ''],
'174615' =>	 ['contributo' =>580.69 , 'percentuale' => ''],
'174617' =>	 ['contributo' =>819.80 , 'percentuale' => ''],
'174619' =>	 ['contributo' =>1690.84, 'percentuale' => ''],
'174624' =>	 ['contributo' =>1007.67, 'percentuale' => ''],
'174627' =>	 ['contributo' =>700.25 , 'percentuale' => ''],
'174628' =>	 ['contributo' =>700.25 , 'percentuale' => ''],
'174630' =>	 ['contributo' =>751.48 , 'percentuale' => ''],
'174632' =>	 ['contributo' =>1605.44, 'percentuale' => ''],
'174633' =>	 ['contributo' =>1981.19, 'percentuale' => ''],
'174718' =>	 ['contributo' =>751.48 , 'percentuale' => ''],
'174722' =>	 ['contributo' =>546.53 , 'percentuale' => ''],
'174724' =>	 ['contributo' =>990.59 , 'percentuale' => ''],
'174731' =>	 ['contributo' =>990.59 , 'percentuale' => ''],
'174749' =>	 ['contributo' =>990.59 , 'percentuale' => ''],
'174751' =>	 ['contributo' =>495.30 , 'percentuale' => ''],
'174754' =>	 ['contributo' =>2237.38, 'percentuale' => ''],
'174756' =>	 ['contributo' =>734.41 , 'percentuale' => ''],
'174758' =>	 ['contributo' =>990.59 , 'percentuale' => ''],
'174765' =>	 ['contributo' =>512.38 , 'percentuale' => ''],
'174768' =>	 ['contributo' =>478.22 , 'percentuale' => ''],
'174771' =>	 ['contributo' =>495.30 , 'percentuale' => ''],
'174778' =>	 ['contributo' =>990.59 , 'percentuale' => ''],
'174782' =>	 ['contributo' =>990.59 , 'percentuale' => ''],
'174784' =>	 ['contributo' =>1075.99, 'percentuale' => ''],
'174786' =>	 ['contributo' =>1075.99, 'percentuale' => ''],
'174793' =>	 ['contributo' =>939.36 , 'percentuale' => ''],
'174795' =>	 ['contributo' =>785.64 , 'percentuale' => ''],
'174797' =>	 ['contributo' =>836.88 , 'percentuale' => ''],
'174800' =>	 ['contributo' =>4184.40, 'percentuale' => ''],
'174804' =>	 ['contributo' =>529.46 , 'percentuale' => ''],
'174805' =>	 ['contributo' =>649.01 , 'percentuale' => ''],
'174806' =>	 ['contributo' =>1298.02, 'percentuale' => ''],
'174807' =>	 ['contributo' =>495.30 , 'percentuale' => ''],
'174819' =>	 ['contributo' =>922.28 , 'percentuale' => ''],
'174824' =>	 ['contributo' =>871.04 , 'percentuale' => ''],
'174833' =>	 ['contributo' =>836.88 , 'percentuale' => ''],
'174836' =>	 ['contributo' =>1144.31, 'percentuale' => ''],
'174838' =>	 ['contributo' =>1246.78, 'percentuale' => ''],
'174841' =>	 ['contributo' =>1041.83, 'percentuale' => ''],
'174850' =>	 ['contributo' =>495.30 , 'percentuale' => ''],
'174851' =>	 ['contributo' =>495.30 , 'percentuale' => ''],
'174855' =>	 ['contributo' =>512.38 , 'percentuale' => ''],
'174859' =>	 ['contributo' =>495.30 , 'percentuale' => ''],
'174861' =>	 ['contributo' =>495.30 , 'percentuale' => ''],
'174863' =>	 ['contributo' =>495.30 , 'percentuale' => ''],
'174866' =>	 ['contributo' =>597.77 , 'percentuale' => ''],
'174867' =>	 ['contributo' =>495.30 , 'percentuale' => ''],
'174875' =>	 ['contributo' =>495.30 , 'percentuale' => ''],
'174876' =>	 ['contributo' =>495.30 , 'percentuale' => ''],
'174879' =>	 ['contributo' =>734.41 , 'percentuale' => ''],
'174883' =>	 ['contributo' =>495.30 , 'percentuale' => ''],
'174886' =>	 ['contributo' =>1673.76, 'percentuale' => ''],
'174888' =>	 ['contributo' =>1178.46, 'percentuale' => ''],
'174890' =>	 ['contributo' =>495.30 , 'percentuale' => ''],
'174892' =>	 ['contributo' =>495.30 , 'percentuale' => ''],
'174894' =>	 ['contributo' =>939.36 , 'percentuale' => ''],
'174895' =>	 ['contributo' =>1878.71, 'percentuale' => ''],
'174897' =>	 ['contributo' =>990.59 , 'percentuale' => ''],
'174901' =>	 ['contributo' =>2510.64, 'percentuale' => ''],
'174904' =>	 ['contributo' =>1485.89, 'percentuale' => ''],
'174948' =>	 ['contributo' =>392.82 , 'percentuale' => ''],
'174949' =>	 ['contributo' =>580.69 , 'percentuale' => ''],
'174953' =>	 ['contributo' =>717.33 , 'percentuale' => ''],
'174955' =>	 ['contributo' =>717.33 , 'percentuale' => ''],
'174958' =>	 ['contributo' =>444.06 , 'percentuale' => ''],
'174959' =>	 ['contributo' =>1024.75, 'percentuale' => ''],
'174961' =>	 ['contributo' =>649.01 , 'percentuale' => ''],
'174968' =>	 ['contributo' =>649.01 , 'percentuale' => ''],
'174970' =>	 ['contributo' =>597.77 , 'percentuale' => ''],
'174971' =>	 ['contributo' =>683.17 , 'percentuale' => ''],
'174972' =>	 ['contributo' =>580.69 , 'percentuale' => ''],
'174985' =>	 ['contributo' =>512.38 , 'percentuale' => ''],
'174999' =>	 ['contributo' =>3774.50, 'percentuale' => ''],
'175005' =>	 ['contributo' =>1827.47, 'percentuale' => ''],
'175008' =>	 ['contributo' =>905.20 , 'percentuale' => ''],
'175014' =>	 ['contributo' =>819.80 , 'percentuale' => ''],
'175016' =>	 ['contributo' =>973.51 , 'percentuale' => ''],
'175021' =>	 ['contributo' =>802.72 , 'percentuale' => ''],
'175022' =>	 ['contributo' =>819.80 , 'percentuale' => ''],
'175024' =>	 ['contributo' =>990.59 , 'percentuale' => ''],
'175025' =>	 ['contributo' =>1127.23, 'percentuale' => ''],
'175027' =>	 ['contributo' =>426.98 , 'percentuale' => ''],
'175029' =>	 ['contributo' =>529.46 , 'percentuale' => ''],
'175031' =>	 ['contributo' =>666.09 , 'percentuale' => ''],
'175032' =>	 ['contributo' =>871.04 , 'percentuale' => ''],
'175038' =>	 ['contributo' =>853.96 , 'percentuale' => ''],
'175050' =>	 ['contributo' =>649.01 , 'percentuale' => ''],
'175072' =>	 ['contributo' =>649.01 , 'percentuale' => ''],
'175075' =>	 ['contributo' =>495.30 , 'percentuale' => ''],
'175077' =>	 ['contributo' =>580.69 , 'percentuale' => ''],
'175079' =>	 ['contributo' =>426.98 , 'percentuale' => ''],
'175085' =>	 ['contributo' =>888.12 , 'percentuale' => ''],
'175086' =>	 ['contributo' =>614.85 , 'percentuale' => ''],
'175090' =>	 ['contributo' =>1041.83, 'percentuale' => ''],
'175094' =>	 ['contributo' =>649.01 , 'percentuale' => ''],
'175096' =>	 ['contributo' =>426.98 , 'percentuale' => ''],
'175100' =>	 ['contributo' =>990.59 , 'percentuale' => ''],
'175102' =>	 ['contributo' =>785.64 , 'percentuale' => ''],
'175103' =>	 ['contributo' =>546.53 , 'percentuale' => ''],
'175105' =>	 ['contributo' =>495.30 , 'percentuale' => ''],
'175176' =>	 ['contributo' =>512.38 , 'percentuale' => ''],
'175191' =>	 ['contributo' =>1571.29, 'percentuale' => ''],
'175196' =>	 ['contributo' =>597.77 , 'percentuale' => ''],
'175208' =>	 ['contributo' =>409.90 , 'percentuale' => ''],
'175210' =>	 ['contributo' =>563.61 , 'percentuale' => ''],
'175212' =>	 ['contributo' =>956.44 , 'percentuale' => ''],
'175214' =>	 ['contributo' =>580.69 , 'percentuale' => ''],
'175216' =>	 ['contributo' =>990.59 , 'percentuale' => ''],
'175219' =>	 ['contributo' =>597.77 , 'percentuale' => ''],
'175222' =>	 ['contributo' =>358.66 , 'percentuale' => ''],
'175226' =>	 ['contributo' =>819.80 , 'percentuale' => ''],
'175231' =>	 ['contributo' =>666.09 , 'percentuale' => ''],
'175236' =>	 ['contributo' =>392.82 , 'percentuale' => ''],
'175239' =>	 ['contributo' =>495.30 , 'percentuale' => ''],
'175241' =>	 ['contributo' =>580.69 , 'percentuale' => ''],
'175243' =>	 ['contributo' =>683.17 , 'percentuale' => ''],
'175246' =>	 ['contributo' =>751.48 , 'percentuale' => ''],
'175247' =>	 ['contributo' =>563.61 , 'percentuale' => ''],
'175249' =>	 ['contributo' =>1075.99, 'percentuale' => ''],
'175252' =>	 ['contributo' =>939.36 , 'percentuale' => ''],
'175253' =>	 ['contributo' =>666.09 , 'percentuale' => ''],
'175254' =>	 ['contributo' =>580.69 , 'percentuale' => ''],
'175257' =>	 ['contributo' =>717.33 , 'percentuale' => ''],
'175258' =>	 ['contributo' =>495.30 , 'percentuale' => ''],
'175261' =>	 ['contributo' =>1725.00, 'percentuale' => ''],
'175267' =>	 ['contributo' =>2613.12, 'percentuale' => ''],
'175268' =>	 ['contributo' =>751.48 , 'percentuale' => ''],
'175277' =>	 ['contributo' =>1605.44, 'percentuale' => ''],
'175282' =>	 ['contributo' =>888.12 , 'percentuale' => ''],
'175291' =>	 ['contributo' =>444.06 , 'percentuale' => ''],
'175292' =>	 ['contributo' =>939.36 , 'percentuale' => ''],
'175302' =>	 ['contributo' =>5038.36, 'percentuale' => ''],
'175307' =>	 ['contributo' =>631.93 , 'percentuale' => ''],
'175317' =>	 ['contributo' =>597.77 , 'percentuale' => ''],
'175320' =>	 ['contributo' =>1041.83, 'percentuale' => ''],
'175329' =>	 ['contributo' =>2339.85, 'percentuale' => ''],
'175339' =>	 ['contributo' =>1298.02, 'percentuale' => ''],
'175344' =>	 ['contributo' =>1417.57, 'percentuale' => ''],
'175346' =>	 ['contributo' =>939.36 , 'percentuale' => ''],
'175349' =>	 ['contributo' =>888.12 , 'percentuale' => ''],
'175357' =>	 ['contributo' =>444.06 , 'percentuale' => ''],
'175360' =>	 ['contributo' =>888.12 , 'percentuale' => ''],
'175366' =>	 ['contributo' =>563.61 , 'percentuale' => ''],
'175369' =>	 ['contributo' =>597.77 , 'percentuale' => ''],
'175454' =>	 ['contributo' =>1420.18, 'percentuale' => ''],
'175457' =>	 ['contributo' =>1420.18, 'percentuale' => ''],
'175460' =>	 ['contributo' =>1420.18, 'percentuale' => ''],
'175463' =>	 ['contributo' =>1420.18, 'percentuale' => ''],
'175465' =>	 ['contributo' =>1420.18, 'percentuale' => ''],
'175466' =>	 ['contributo' =>1420.18, 'percentuale' => ''],
'175468' =>	 ['contributo' =>1420.18, 'percentuale' => ''],
'175471' =>	 ['contributo' =>1420.18, 'percentuale' => ''],
'175474' =>	 ['contributo' =>1420.18, 'percentuale' => ''],
'175480' =>	 ['contributo' =>1420.18, 'percentuale' => ''],
'175484' =>	 ['contributo' =>1420.18, 'percentuale' => ''],
'175487' =>	 ['contributo' =>1420.18, 'percentuale' => ''],
'175490' =>	 ['contributo' =>1420.17, 'percentuale' => ''],
'175493' =>	 ['contributo' =>1420.17, 'percentuale' => ''],
'175494' =>	 ['contributo' =>1420.17, 'percentuale' => ''],
'175495' =>	 ['contributo' =>1420.17, 'percentuale' => ''],
'175499' =>	 ['contributo' =>1420.17, 'percentuale' => ''],
'175500' =>	 ['contributo' =>1420.17, 'percentuale' => ''],
'175503' =>	 ['contributo' =>1420.17, 'percentuale' => ''],
'175505' =>	 ['contributo' =>1420.17, 'percentuale' => ''],
'175511' =>	 ['contributo' =>1420.17, 'percentuale' => ''],
'175515' =>	 ['contributo' =>1420.17, 'percentuale' => ''],
'175516' =>	 ['contributo' =>1420.17, 'percentuale' => ''],
'175521' =>	 ['contributo' =>1420.17, 'percentuale' => ''],
'175524' =>	 ['contributo' =>1420.17, 'percentuale' => ''],
'175528' =>	 ['contributo' =>1420.17, 'percentuale' => ''],
'175529' =>	 ['contributo' =>1420.17, 'percentuale' => ''],
'175530' =>	 ['contributo' =>1420.17, 'percentuale' => ''],
'175534' =>	 ['contributo' =>1420.17, 'percentuale' => ''],
'175538' =>	 ['contributo' =>1420.17, 'percentuale' => ''],
'175542' =>	 ['contributo' =>1420.17, 'percentuale' => ''],
'175544' =>	 ['contributo' =>1420.17, 'percentuale' => ''],
'175545' =>	 ['contributo' =>1420.17, 'percentuale' => ''],
'175547' =>	 ['contributo' =>1420.17, 'percentuale' => ''],
'175550' =>	 ['contributo' =>1420.17, 'percentuale' => ''],
'175551' =>	 ['contributo' =>1420.17, 'percentuale' => ''],
'175553' =>	 ['contributo' =>1420.17, 'percentuale' => ''],
'175556' =>	 ['contributo' =>1420.17, 'percentuale' => ''],
'175558' =>	 ['contributo' =>2051.36, 'percentuale' => ''],
'175560' =>	 ['contributo' =>1420.17, 'percentuale' => ''],
'175561' =>	 ['contributo' =>1420.17, 'percentuale' => ''],
'175566' =>	 ['contributo' =>1420.17, 'percentuale' => ''],
'175569' =>	 ['contributo' =>1420.17, 'percentuale' => ''],
'175571' =>	 ['contributo' =>1420.17, 'percentuale' => ''],
'173710' =>	 ['contributo' =>3937.50, 'percentuale' => ''],
'173711' =>	 ['contributo' =>4665.22, 'percentuale' => ''],
'173712' =>	 ['contributo' =>3950.49, 'percentuale' => ''],
'173713' =>	 ['contributo' =>1325.49, 'percentuale' => ''],
'173714' =>	 ['contributo' =>1559.41, 'percentuale' => ''],
'173715' =>	 ['contributo' =>2079.21, 'percentuale' => ''],
'173716' =>	 ['contributo' =>1715.35, 'percentuale' => ''],
'173717' =>	 ['contributo' =>1624.38, 'percentuale' => ''],
'173718' =>	 ['contributo' =>2339.11, 'percentuale' => ''],
'173719' =>	 ['contributo' =>2014.23, 'percentuale' => ''],
'173720' =>	 ['contributo' =>2586.01, 'percentuale' => ''],
'173721' =>	 ['contributo' =>1754.33, 'percentuale' => ''],
'173722' =>	 ['contributo' =>1195.54, 'percentuale' => ''],
'173726' =>	 ['contributo' =>1832.30, 'percentuale' => ''],
'173730' =>	 ['contributo' =>1884.28, 'percentuale' => ''],
'173739' =>	 ['contributo' =>1247.52, 'percentuale' => ''],
'173743' =>	 ['contributo' =>1611.39, 'percentuale' => ''],
'173744' =>	 ['contributo' =>2118.19, 'percentuale' => ''],
'173745' =>	 ['contributo' =>2170.17, 'percentuale' => ''],
'173746' =>	 ['contributo' =>2053.22, 'percentuale' => ''],
'173747' =>	 ['contributo' =>2040.22, 'percentuale' => ''],
'173783' =>	 ['contributo' =>1455.45, 'percentuale' => ''],
'173784' =>	 ['contributo' =>4314.35, 'percentuale' => ''],
'173785' =>	 ['contributo' =>1832.30, 'percentuale' => ''],
'173786' =>	 ['contributo' =>1936.26, 'percentuale' => ''],
'173787' =>	 ['contributo' =>1546.41, 'percentuale' => ''],
'173788' =>	 ['contributo' =>1546.41, 'percentuale' => ''],
'173789' =>	 ['contributo' =>1078.60, 'percentuale' => ''],
'173790' =>	 ['contributo' =>1910.27, 'percentuale' => ''],
'173791' =>	 ['contributo' =>3456.68, 'percentuale' => ''],
'173793' =>	 ['contributo' =>2287.13, 'percentuale' => ''],
'173794' =>	 ['contributo' =>3053.84, 'percentuale' => ''],
'173795' =>	 ['contributo' =>1260.52, 'percentuale' => ''],
'173796' =>	 ['contributo' =>1728.34, 'percentuale' => ''],
'173797' =>	 ['contributo' =>2819.92, 'percentuale' => ''],
'173798' =>	 ['contributo' =>4106.43, 'percentuale' => ''],
'173800' =>	 ['contributo' =>2417.08, 'percentuale' => ''],
'173801' =>	 ['contributo' =>2170.17, 'percentuale' => ''],
'173802' =>	 ['contributo' =>2612.00, 'percentuale' => ''],
'173849' =>	 ['contributo' =>1806.31, 'percentuale' => ''],
'173889' =>	 ['contributo' =>1611.39, 'percentuale' => ''],
'173944' =>	 ['contributo' =>1806.31, 'percentuale' => ''],
'173963' =>	 ['contributo' =>1429.45, 'percentuale' => ''],
'173980' =>	 ['contributo' =>779.70 , 'percentuale' => ''],
'174041' =>	 ['contributo' =>1624.38, 'percentuale' => ''],
'174048' =>	 ['contributo' =>3001.86, 'percentuale' => ''],
'174056' =>	 ['contributo' =>3352.72, 'percentuale' => ''],
'174064' =>	 ['contributo' =>1455.45, 'percentuale' => ''],
'174081' =>	 ['contributo' =>2715.96, 'percentuale' => ''],
'174091' =>	 ['contributo' =>2326.11, 'percentuale' => ''],
'174097' =>	 ['contributo' =>2339.11, 'percentuale' => ''],
'174105' =>	 ['contributo' =>2495.05, 'percentuale' => ''],
'174107' =>	 ['contributo' =>1234.53, 'percentuale' => ''],
'174114' =>	 ['contributo' =>1806.31, 'percentuale' => ''],
'174122' =>	 ['contributo' =>1832.30, 'percentuale' => ''],
'174134' =>	 ['contributo' =>2131.19, 'percentuale' => ''],
'174138' =>	 ['contributo' =>1494.43, 'percentuale' => ''],
'174140' =>	 ['contributo' =>1741.34, 'percentuale' => ''],
'174148' =>	 ['contributo' =>1091.59, 'percentuale' => ''],
'174157' =>	 ['contributo' =>818.70 , 'percentuale' => ''],
'174161' =>	 ['contributo' =>1390.47, 'percentuale' => ''],
'174167' =>	 ['contributo' =>2430.07, 'percentuale' => ''],
'175224' =>	 ['contributo' =>928.21 , 'percentuale' => ''],
'175256' =>	 ['contributo' =>3991.34, 'percentuale' => ''],
'175266' =>	 ['contributo' =>1856.44, 'percentuale' => ''],
'175269' =>	 ['contributo' =>928.21 , 'percentuale' => ''],
'175274' =>	 ['contributo' =>4641.09, 'percentuale' => ''],
'175279' =>	 ['contributo' =>1856.43, 'percentuale' => ''],
'175312' =>	 ['contributo' =>2506.19, 'percentuale' => ''],
'175420' =>	 ['contributo' =>1856.44, 'percentuale' => ''],
'175609' =>	 ['contributo' =>1024.75, 'percentuale' => ''],
'175622' =>	 ['contributo' =>2194.30, 'percentuale' => ''],
'175623' =>	 ['contributo' =>1236.38, 'percentuale' => ''],
'175625' =>	 ['contributo' =>2405.94, 'percentuale' => ''],
'175627' =>	 ['contributo' =>980.20 , 'percentuale' => ''],
'175628' =>	 ['contributo' =>1035.89, 'percentuale' => ''],
'175629' =>	 ['contributo' =>1214.11, 'percentuale' => ''],
'175630' =>	 ['contributo' =>1058.17, 'percentuale' => ''],
'175631' =>	 ['contributo' =>1136.14, 'percentuale' => ''],
'175632' =>	 ['contributo' =>1113.86, 'percentuale' => ''],
'175633' =>	 ['contributo' =>991.33 , 'percentuale' => ''],
'175635' =>	 ['contributo' =>1559.41, 'percentuale' => ''],
'175636' =>	 ['contributo' =>1214.11, 'percentuale' => ''],
'175637' =>	 ['contributo' =>835.40 , 'percentuale' => ''],
'175639' =>	 ['contributo' =>969.06 , 'percentuale' => ''],
'175641' =>	 ['contributo' =>1035.89, 'percentuale' => ''],
'175644' =>	 ['contributo' =>980.20 , 'percentuale' => ''],
'175645' =>	 ['contributo' =>1236.38, 'percentuale' => ''],
'175647' =>	 ['contributo' =>1158.42, 'percentuale' => ''],
'175648' =>	 ['contributo' =>1236.38, 'percentuale' => ''],
'175649' =>	 ['contributo' =>1715.35, 'percentuale' => ''],
'175650' =>	 ['contributo' =>1525.99, 'percentuale' => ''],
'175651' =>	 ['contributo' =>4722.77, 'percentuale' => ''],
'175653' =>	 ['contributo' =>1681.93, 'percentuale' => ''],
'175654' =>	 ['contributo' =>1860.15, 'percentuale' => ''],
'175655' =>	 ['contributo' =>779.70 , 'percentuale' => ''],
'175656' =>	 ['contributo' =>1381.19, 'percentuale' => ''],
'175663' =>	 ['contributo' =>1370.05, 'percentuale' => ''],
'175665' =>	 ['contributo' =>1158.42, 'percentuale' => ''],
'175667' =>	 ['contributo' =>1269.80, 'percentuale' => ''],
'175668' =>	 ['contributo' =>1325.49, 'percentuale' => ''],
'175670' =>	 ['contributo' =>1392.33, 'percentuale' => ''],
'175673' =>	 ['contributo' =>5535.89, 'percentuale' => ''],
'175676' =>	 ['contributo' =>1436.88, 'percentuale' => ''],
'175678' =>	 ['contributo' =>1180.69, 'percentuale' => ''],
'175680' =>	 ['contributo' =>1147.28, 'percentuale' => ''],
'175682' =>	 ['contributo' =>1236.38, 'percentuale' => ''],
'175685' =>	 ['contributo' =>1314.36, 'percentuale' => ''],
'175686' =>	 ['contributo' =>1136.14, 'percentuale' => ''],
'175690' =>	 ['contributo' =>1236.38, 'percentuale' => ''],
'175693' =>	 ['contributo' =>1058.17, 'percentuale' => ''],
'175695' =>	 ['contributo' =>879.95 , 'percentuale' => ''],
'175699' =>	 ['contributo' =>946.77 , 'percentuale' => ''],
'175701' =>	 ['contributo' =>1102.72, 'percentuale' => ''],
'175704' =>	 ['contributo' =>1258.66, 'percentuale' => ''],
'175708' =>	 ['contributo' =>2016.09, 'percentuale' => ''],
'175712' =>	 ['contributo' =>4956.68, 'percentuale' => ''],
'175714' =>	 ['contributo' =>1292.08, 'percentuale' => ''],
'175719' =>	 ['contributo' =>1782.18, 'percentuale' => ''],
'175721' =>	 ['contributo' =>1125.00, 'percentuale' => ''],
'175723' =>	 ['contributo' =>935.64 , 'percentuale' => ''],
'175726' =>	 ['contributo' =>835.40 , 'percentuale' => ''],
'175728' =>	 ['contributo' =>1370.05, 'percentuale' => ''],
'175729' =>	 ['contributo' =>935.64 , 'percentuale' => ''],
'175730' =>	 ['contributo' =>1771.04, 'percentuale' => ''],
'175731' =>	 ['contributo' =>913.37 , 'percentuale' => ''],
'175739' =>	 ['contributo' =>4388.61, 'percentuale' => ''],
'175743' =>	 ['contributo' =>8788.36, 'percentuale' => ''],
'175745' =>	 ['contributo' =>1280.94, 'percentuale' => ''],
'175748' =>	 ['contributo' =>1704.21, 'percentuale' => ''],
'175749' =>	 ['contributo' =>1147.28, 'percentuale' => ''],
'175750' =>	 ['contributo' =>1793.32, 'percentuale' => ''],
'175753' =>	 ['contributo' =>1782.18, 'percentuale' => ''],
'175756' =>	 ['contributo' =>1782.18, 'percentuale' => ''],
'175761' =>	 ['contributo' =>1960.40, 'percentuale' => ''],
'175763' =>	 ['contributo' =>1993.81, 'percentuale' => ''],
'175768' =>	 ['contributo' =>1347.77, 'percentuale' => ''],
'175770' =>	 ['contributo' =>1214.11, 'percentuale' => ''],
'174762' =>	 ['contributo' =>1004.70, 'percentuale' => ''],
'174772' =>	 ['contributo' =>1225.25, 'percentuale' => ''],
'174774' =>	 ['contributo' =>1114.97, 'percentuale' => ''],
'174776' =>	 ['contributo' =>1016.96, 'percentuale' => ''],
'174777' =>	 ['contributo' =>1016.95, 'percentuale' => ''],
'174779' =>	 ['contributo' =>980.20 , 'percentuale' => ''],
'174783' =>	 ['contributo' =>1090.47, 'percentuale' => ''],
'174794' =>	 ['contributo' =>1029.21, 'percentuale' => ''],
'174796' =>	 ['contributo' =>943.44 , 'percentuale' => ''],
'174809' =>	 ['contributo' =>1078.22, 'percentuale' => ''],
'174811' =>	 ['contributo' =>980.20 , 'percentuale' => ''],
'174831' =>	 ['contributo' =>943.44 , 'percentuale' => ''],
'174840' =>	 ['contributo' =>943.44 , 'percentuale' => ''],
'174843' =>	 ['contributo' =>1960.40, 'percentuale' => ''],
'174852' =>	 ['contributo' =>992.44 , 'percentuale' => ''],
'174857' =>	 ['contributo' =>967.95 , 'percentuale' => ''],
'174860' =>	 ['contributo' =>943.44 , 'percentuale' => ''],
'174862' =>	 ['contributo' =>967.95 , 'percentuale' => ''],
'174864' =>	 ['contributo' =>869.93 , 'percentuale' => ''],
'174865' =>	 ['contributo' =>1139.48, 'percentuale' => ''],
'174868' =>	 ['contributo' =>796.41 , 'percentuale' => ''],
'174869' =>	 ['contributo' =>1004.70, 'percentuale' => ''],
'174885' =>	 ['contributo' =>980.20 , 'percentuale' => ''],
'174887' =>	 ['contributo' =>857.66 , 'percentuale' => ''],
'174889' =>	 ['contributo' =>1188.49, 'percentuale' => ''],
'174891' =>	 ['contributo' =>784.16 , 'percentuale' => ''],
'174893' =>	 ['contributo' =>955.69 , 'percentuale' => ''],
'174896' =>	 ['contributo' =>1200.74, 'percentuale' => ''],
'174898' =>	 ['contributo' =>1090.47, 'percentuale' => ''],
'174900' =>	 ['contributo' =>980.20 , 'percentuale' => ''],
'174903' =>	 ['contributo' =>1102.72, 'percentuale' => ''],
'174907' =>	 ['contributo' =>1102.72, 'percentuale' => ''],
'174908' =>	 ['contributo' =>882.18 , 'percentuale' => ''],
'174910' =>	 ['contributo' =>943.44 , 'percentuale' => ''],
'174913' =>	 ['contributo' =>906.68 , 'percentuale' => ''],
'174915' =>	 ['contributo' =>967.95 , 'percentuale' => ''],
'174916' =>	 ['contributo' =>1078.22, 'percentuale' => ''],
'174921' =>	 ['contributo' =>1078.22, 'percentuale' => ''],
'174936' =>	 ['contributo' =>1114.97, 'percentuale' => ''],
'174939' =>	 ['contributo' =>1078.22, 'percentuale' => ''],
'174940' =>	 ['contributo' =>1212.99, 'percentuale' => ''],
'174941' =>	 ['contributo' =>1090.47, 'percentuale' => ''],
'174942' =>	 ['contributo' =>1078.22, 'percentuale' => ''],
'174943' =>	 ['contributo' =>1090.47, 'percentuale' => ''],
'174944' =>	 ['contributo' =>1102.72, 'percentuale' => ''],
'174945' =>	 ['contributo' =>869.93 , 'percentuale' => ''],
'174947' =>	 ['contributo' =>1016.96, 'percentuale' => ''],
'174951' =>	 ['contributo' =>882.18 , 'percentuale' => ''],
'174957' =>	 ['contributo' =>980.20 , 'percentuale' => ''],
'174960' =>	 ['contributo' =>1163.98, 'percentuale' => ''],
'174963' =>	 ['contributo' =>796.41 , 'percentuale' => ''],
'174973' =>	 ['contributo' =>1151.73, 'percentuale' => ''],
'174982' =>	 ['contributo' =>869.93 , 'percentuale' => ''],
'174997' =>	 ['contributo' =>1053.71, 'percentuale' => ''],
'175007' =>	 ['contributo' =>833.17 , 'percentuale' => ''],
'175013' =>	 ['contributo' =>1065.96, 'percentuale' => ''],
'175018' =>	 ['contributo' =>1090.47, 'percentuale' => ''],
'175023' =>	 ['contributo' =>906.68 , 'percentuale' => ''],
'175028' =>	 ['contributo' =>955.69 , 'percentuale' => ''],
'175070' =>	 ['contributo' =>1016.96, 'percentuale' => ''],
'175073' =>	 ['contributo' =>1176.24, 'percentuale' => ''],
'175076' =>	 ['contributo' =>1176.24, 'percentuale' => ''],
'175080' =>	 ['contributo' =>2131.93, 'percentuale' => ''],
'175082' =>	 ['contributo' =>1801.11, 'percentuale' => ''],
'175084' =>	 ['contributo' =>1090.47, 'percentuale' => ''],
'175087' =>	 ['contributo' =>1249.75, 'percentuale' => ''],
'175092' =>	 ['contributo' =>2254.45, 'percentuale' => ''],
'175093' =>	 ['contributo' =>1090.47, 'percentuale' => ''],
'175097' =>	 ['contributo' =>1090.47, 'percentuale' => ''],
'175117' =>	 ['contributo' =>1188.49, 'percentuale' => ''],
'175118' =>	 ['contributo' =>955.69 , 'percentuale' => ''],
'175120' =>	 ['contributo' =>980.20 , 'percentuale' => ''],
'175121' =>	 ['contributo' =>2058.42, 'percentuale' => ''],
'175122' =>	 ['contributo' =>1041.46, 'percentuale' => ''],
'175125' =>	 ['contributo' =>1225.25, 'percentuale' => ''],
'175127' =>	 ['contributo' =>980.20 , 'percentuale' => ''],
'175128' =>	 ['contributo' =>967.95 , 'percentuale' => ''],
'175129' =>	 ['contributo' =>980.20 , 'percentuale' => ''],
'175132' =>	 ['contributo' =>1176.24, 'percentuale' => ''],
'175137' =>	 ['contributo' =>1151.73, 'percentuale' => ''],
'175138' =>	 ['contributo' =>1114.97, 'percentuale' => ''],
'175139' =>	 ['contributo' =>1225.25, 'percentuale' => ''],
'175140' =>	 ['contributo' =>1004.70, 'percentuale' => ''],
'175200' =>	 ['contributo' =>1090.47, 'percentuale' => ''],
'175209' =>	 ['contributo' =>967.95 , 'percentuale' => ''],
'175211' =>	 ['contributo' =>1262.00, 'percentuale' => ''],
'175213' =>	 ['contributo' =>1004.70, 'percentuale' => ''],
'175215' =>	 ['contributo' =>1151.73, 'percentuale' => ''],
'175220' =>	 ['contributo' =>1127.23, 'percentuale' => ''],
'175223' =>	 ['contributo' =>796.41 , 'percentuale' => ''],
'175228' =>	 ['contributo' =>1139.48, 'percentuale' => ''],
'175234' =>	 ['contributo' =>1127.23, 'percentuale' => ''],
'175238' =>	 ['contributo' =>1212.99, 'percentuale' => ''],
'175242' =>	 ['contributo' =>1065.96, 'percentuale' => ''],
'175255' =>	 ['contributo' =>943.44 , 'percentuale' => ''],
'175259' =>	 ['contributo' =>4312.87, 'percentuale' => ''],
'175264' =>	 ['contributo' =>1200.74, 'percentuale' => ''],
'175270' =>	 ['contributo' =>2327.97, 'percentuale' => ''],
'175272' =>	 ['contributo' =>1237.50, 'percentuale' => ''],
'175273' =>	 ['contributo' =>1274.26, 'percentuale' => ''],
'175276' =>	 ['contributo' =>980.20 , 'percentuale' => ''],
'175300' =>	 ['contributo' =>980.20 , 'percentuale' => ''],
'175310' =>	 ['contributo' =>1262.00, 'percentuale' => ''],
'175311' =>	 ['contributo' =>796.41 , 'percentuale' => ''],
'175313' =>	 ['contributo' =>796.41 , 'percentuale' => ''],
'175368' =>	 ['contributo' =>747.40 , 'percentuale' => ''],
'175372' =>	 ['contributo' =>2009.41, 'percentuale' => ''],
'175464' =>	 ['contributo' =>1065.96, 'percentuale' => ''],
'175391' =>	 ['contributo' =>1104.96, 'percentuale' => ''],
'175554' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175559' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175563' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175576' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175578' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175579' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175580' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175582' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175583' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175584' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175586' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175588' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175589' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175591' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175593' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175595' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175600' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175601' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175602' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175603' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175604' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175605' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175606' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175607' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175608' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175610' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175611' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175613' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175614' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175616' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175617' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175619' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175621' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175657' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175659' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175661' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175662' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175664' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175669' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175671' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175675' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175677' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175679' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175681' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175683' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175684' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175688' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175691' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175694' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175700' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175703' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175706' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175709' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175713' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175715' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175720' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175722' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175725' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175727' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175733' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175735' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175737' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175751' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175752' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175754' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175755' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175757' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175758' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175759' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175760' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175762' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175764' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175765' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175766' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175767' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175769' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175771' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175772' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175773' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175778' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175779' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175786' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175788' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175793' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175794' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175795' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175796' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175797' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175798' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175800' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175803' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175805' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175807' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175810' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175812' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175815' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175816' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175818' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175820' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175821' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175822' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175824' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175826' =>	 ['contributo' =>1093.44, 'percentuale' => ''],
'175829' =>	 ['contributo' =>1369.69, 'percentuale' => ''],
'172930' =>	 ['contributo' =>1862.38, 'percentuale' => ''],
'172936' =>	 ['contributo' =>1862.38, 'percentuale' => ''],
'172938' =>	 ['contributo' =>1241.58, 'percentuale' => ''],
'172940' =>	 ['contributo' =>1241.58, 'percentuale' => ''],
'172941' =>	 ['contributo' =>1241.58, 'percentuale' => ''],
'172954' =>	 ['contributo' =>1241.58, 'percentuale' => ''],
'172960' =>	 ['contributo' =>1241.58, 'percentuale' => ''],
'172989' =>	 ['contributo' =>1241.58, 'percentuale' => ''],
'172990' =>	 ['contributo' =>1241.58, 'percentuale' => ''],
'172991' =>	 ['contributo' =>1241.58, 'percentuale' => ''],
'172992' =>	 ['contributo' =>1241.58, 'percentuale' => ''],
'173020' =>	 ['contributo' =>2483.17, 'percentuale' => ''],
'173022' =>	 ['contributo' =>2368.81, 'percentuale' => ''],
'173023' =>	 ['contributo' =>3349.01, 'percentuale' => ''],
'173024' =>	 ['contributo' =>2711.88, 'percentuale' => ''],
'173025' =>	 ['contributo' =>1927.72, 'percentuale' => ''],
'174199' =>	 ['contributo' =>1241.58, 'percentuale' => ''],
'174225' =>	 ['contributo' =>1241.58, 'percentuale' => ''],
'174250' =>	 ['contributo' =>1241.58, 'percentuale' => ''],
'174268' =>	 ['contributo' =>1666.34, 'percentuale' => ''],
'174301' =>	 ['contributo' =>1715.35, 'percentuale' => ''],
'174311' =>	 ['contributo' =>1846.04, 'percentuale' => ''],
'174319' =>	 ['contributo' =>1568.33, 'percentuale' => ''],
'174330' =>	 ['contributo' =>3561.38, 'percentuale' => ''],
'174385' =>	 ['contributo' =>2842.57, 'percentuale' => ''],
'174393' =>	 ['contributo' =>1633.66, 'percentuale' => ''],
'174400' =>	 ['contributo' =>3920.79, 'percentuale' => ''],
'174402' =>	 ['contributo' =>2270.79, 'percentuale' => ''],
'174407' =>	 ['contributo' =>1780.69, 'percentuale' => ''],
'174411' =>	 ['contributo' =>1192.57, 'percentuale' => ''],
'174417' =>	 ['contributo' =>1895.05, 'percentuale' => ''],
'174418' =>	 ['contributo' =>3708.41, 'percentuale' => ''],
'174420' =>	 ['contributo' =>2156.44, 'percentuale' => ''],
'174422' =>	 ['contributo' =>1568.33, 'percentuale' => ''],
'174427' =>	 ['contributo' =>1600.99, 'percentuale' => ''],
'174428' =>	 ['contributo' =>1094.55, 'percentuale' => ''],
'174432' =>	 ['contributo' =>1519.31, 'percentuale' => ''],
'174433' =>	 ['contributo' =>1029.21, 'percentuale' => ''],
'174434' =>	 ['contributo' =>669.80 , 'percentuale' => ''],
'174435' =>	 ['contributo' =>1421.29, 'percentuale' => ''],
'174436' =>	 ['contributo' =>1519.31, 'percentuale' => ''],
'174437' =>	 ['contributo' =>849.50 , 'percentuale' => ''],
'174439' =>	 ['contributo' =>784.16 , 'percentuale' => ''],
'174442' =>	 ['contributo' =>784.16 , 'percentuale' => ''],
'174451' =>	 ['contributo' =>914.85 , 'percentuale' => ''],
'174452' =>	 ['contributo' =>571.79 , 'percentuale' => ''],
'174705' =>	 ['contributo' =>686.15 , 'percentuale' => ''],
'174706' =>	 ['contributo' =>816.83 , 'percentuale' => ''],
'174708' =>	 ['contributo' =>947.52 , 'percentuale' => ''],
'174709' =>	 ['contributo' =>1780.69, 'percentuale' => ''],
'174710' =>	 ['contributo' =>686.15 , 'percentuale' => ''],
'174711' =>	 ['contributo' =>1241.58, 'percentuale' => ''],
'174712' =>	 ['contributo' =>1862.38, 'percentuale' => ''],
'174713' =>	 ['contributo' =>1862.38, 'percentuale' => ''],
'174716' =>	 ['contributo' =>3708.41, 'percentuale' => ''],
'174723' =>	 ['contributo' =>1241.58, 'percentuale' => ''],
'174740' =>	 ['contributo' =>3561.38, 'percentuale' => ''],
'174747' =>	 ['contributo' =>3708.41, 'percentuale' => ''],
'174769' =>	 ['contributo' =>751.48 , 'percentuale' => ''],
'174785' =>	 ['contributo' =>1927.72, 'percentuale' => ''],
'175473' =>	 ['contributo' =>3708.41, 'percentuale' => ''],
'175476' =>	 ['contributo' =>2891.58, 'percentuale' => ''],
'175482' =>	 ['contributo' =>3790.10, 'percentuale' => ''],
'175485' =>	 ['contributo' =>1993.07, 'percentuale' => ''],
'175489' =>	 ['contributo' =>1192.57, 'percentuale' => ''],
'175491' =>	 ['contributo' =>1486.63, 'percentuale' => ''],
'175497' =>	 ['contributo' =>1666.34, 'percentuale' => ''],
'175502' =>	 ['contributo' =>2450.50, 'percentuale' => ''],
'175513' =>	 ['contributo' =>3316.34, 'percentuale' => ''],
'175517' =>	 ['contributo' =>3561.38, 'percentuale' => ''],
'175523' =>	 ['contributo' =>1699.01, 'percentuale' => ''],
'175532' =>	 ['contributo' =>1453.96, 'percentuale' => ''],
'175537' =>	 ['contributo' =>2368.81, 'percentuale' => ''],
'175540' =>	 ['contributo' =>1061.88, 'percentuale' => ''],
'175541' =>	 ['contributo' =>2189.11, 'percentuale' => ''],
'175548' =>	 ['contributo' =>1846.04, 'percentuale' => ''],
'175555' =>	 ['contributo' =>2368.81, 'percentuale' => ''],
'175564' =>	 ['contributo' =>1421.29, 'percentuale' => ''],
'175574' =>	 ['contributo' =>4982.67, 'percentuale' => ''],
'175581' =>	 ['contributo' =>2842.57, 'percentuale' => ''],
'175585' =>	 ['contributo' =>2483.17, 'percentuale' => ''],
'175587' =>	 ['contributo' =>1862.38, 'percentuale' => ''],
'175590' =>	 ['contributo' =>1862.38, 'percentuale' => ''],
'175592' =>	 ['contributo' =>1241.58, 'percentuale' => ''],
'175594' =>	 ['contributo' =>1241.58, 'percentuale' => ''],
'175937' =>	 ['contributo' =>669.80 , 'percentuale' => ''],
'175939' =>	 ['contributo' =>1715.35, 'percentuale' => ''],
'175956' =>	 ['contributo' =>1666.34, 'percentuale' => ''],
'173754' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'173755' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'173756' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'173757' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'174502' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'174507' =>	 ['contributo' =>1188.11, 'percentuale' => ''],
'174508' =>	 ['contributo' =>1188.11, 'percentuale' => ''],
'174509' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'174514' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'174517' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'174527' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'174534' =>	 ['contributo' =>1188.11, 'percentuale' => ''],
'174538' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'174541' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'174547' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'174556' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'174560' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'174570' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'174576' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'174580' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'174600' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'174612' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'174614' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'174616' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'174621' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'174625' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'174629' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'174631' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'174634' =>	 ['contributo' =>1188.11, 'percentuale' => ''],
'174636' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'174637' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'174654' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'174655' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'174656' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'174657' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'174658' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'174663' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'174666' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'174668' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'174669' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'174685' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'174688' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'174689' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'174690' =>	 ['contributo' =>1188.11, 'percentuale' => ''],
'174691' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'174974' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'174978' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'174990' =>	 ['contributo' =>1188.11, 'percentuale' => ''],
'175004' =>	 ['contributo' =>1188.11, 'percentuale' => ''],
'175009' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175015' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175033' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175040' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175048' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175049' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175052' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175061' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175071' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175074' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175078' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175081' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175083' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175089' =>	 ['contributo' =>1188.11, 'percentuale' => ''],
'175095' =>	 ['contributo' =>1188.11, 'percentuale' => ''],
'175098' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175101' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175109' =>	 ['contributo' =>1188.11, 'percentuale' => ''],
'175111' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175114' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175115' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175116' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175119' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175123' =>	 ['contributo' =>1188.11, 'percentuale' => ''],
'175126' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175130' =>	 ['contributo' =>1188.11, 'percentuale' => ''],
'175133' =>	 ['contributo' =>1188.11, 'percentuale' => ''],
'175134' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175141' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175142' =>	 ['contributo' =>1188.11, 'percentuale' => ''],
'175144' =>	 ['contributo' =>1188.11, 'percentuale' => ''],
'175147' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175149' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175150' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175152' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175153' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175154' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175157' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175160' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175164' =>	 ['contributo' =>1188.11, 'percentuale' => ''],
'175173' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175174' =>	 ['contributo' =>1188.11, 'percentuale' => ''],
'175175' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175182' =>	 ['contributo' =>1188.11, 'percentuale' => ''],
'175188' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175218' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175235' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175240' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175245' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175260' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175262' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175265' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175271' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175275' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175278' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175281' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175293' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175296' =>	 ['contributo' =>1188.11, 'percentuale' => ''],
'175305' =>	 ['contributo' =>1188.11, 'percentuale' => ''],
'175356' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175373' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175374' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175375' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175376' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175378' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175379' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175382' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175383' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175413' =>	 ['contributo' =>1188.11, 'percentuale' => ''],
'175426' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175431' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175441' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175450' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175452' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175455' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175461' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175467' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175469' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175470' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175475' =>	 ['contributo' =>1188.11, 'percentuale' => ''],
'175478' =>	 ['contributo' =>1188.11, 'percentuale' => ''],
'175483' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175488' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175519' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175526' =>	 ['contributo' =>1188.11, 'percentuale' => ''],
'175527' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175531' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175539' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175543' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175546' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175549' =>	 ['contributo' =>1188.11, 'percentuale' => ''],
'175552' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175557' =>	 ['contributo' =>1188.11, 'percentuale' => ''],
'175567' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175572' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175575' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175577' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175612' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175615' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175618' =>	 ['contributo' =>1188.11, 'percentuale' => ''],
'175620' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175624' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175626' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175634' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175640' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175646' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175692' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175705' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175710' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175718' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175724' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175732' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175736' =>	 ['contributo' =>1188.11, 'percentuale' => ''],
'175738' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175742' =>	 ['contributo' =>1188.11, 'percentuale' => ''],
'175747' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175781' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175791' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175799' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175802' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175804' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175806' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175809' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175814' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175819' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175846' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175847' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175850' =>	 ['contributo' =>1188.11, 'percentuale' => ''],
'175853' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175854' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175856' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175857' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175859' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175862' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175863' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175866' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175867' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175869' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175870' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175872' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175884' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175887' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175894' =>	 ['contributo' =>1188.11, 'percentuale' => ''],
'175898' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175900' =>	 ['contributo' =>1188.11, 'percentuale' => ''],
'175901' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175903' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175905' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175906' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175910' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175913' =>	 ['contributo' =>1188.12, 'percentuale' => ''],
'175801' =>	 ['contributo' =>755.20 , 'percentuale' => ''],
'175808' =>	 ['contributo' =>1321.04, 'percentuale' => ''],
'175823' =>	 ['contributo' =>2643.19, 'percentuale' => ''],
'175827' =>	 ['contributo' =>755.20 , 'percentuale' => ''],
'175836' =>	 ['contributo' =>5663.98, 'percentuale' => ''],
'175776' =>	 ['contributo' =>2818.07, 'percentuale' => ''],
'175784' =>	 ['contributo' =>1456.94, 'percentuale' => ''],
'175817' =>	 ['contributo' =>1163.98, 'percentuale' => ''],
'175828' =>	 ['contributo' =>1163.98, 'percentuale' => ''],
'175864' =>	 ['contributo' =>1843.44, 'percentuale' => ''],
'175865' =>	 ['contributo' =>2692.20, 'percentuale' => ''],
'175868' =>	 ['contributo' =>1135.39, 'percentuale' => ''],
'175871' =>	 ['contributo' =>1135.39, 'percentuale' => ''],
'175873' =>	 ['contributo' =>1135.40, 'percentuale' => ''],
'175899' =>	 ['contributo' =>1135.39, 'percentuale' => ''],
'175902' =>	 ['contributo' =>1135.39, 'percentuale' => ''],
'175904' =>	 ['contributo' =>1135.39, 'percentuale' => ''],
'175911' =>	 ['contributo' =>1135.39, 'percentuale' => ''],
'175914' =>	 ['contributo' =>1135.39, 'percentuale' => ''],
'175918' =>	 ['contributo' =>1135.39, 'percentuale' => ''],
'175920' =>	 ['contributo' =>1135.39, 'percentuale' => ''],
'175924' =>	 ['contributo' =>1135.39, 'percentuale' => ''],
'175925' =>	 ['contributo' =>1135.40, 'percentuale' => ''],
'175926' =>	 ['contributo' =>1135.40, 'percentuale' => ''],
'175928' =>	 ['contributo' =>1135.40, 'percentuale' => ''],
'175929' =>	 ['contributo' =>1135.40, 'percentuale' => ''],
'175930' =>	 ['contributo' =>1135.40, 'percentuale' => ''],
'175931' =>	 ['contributo' =>1131.31, 'percentuale' => ''],
'175932' =>	 ['contributo' =>1131.31, 'percentuale' => ''],
'175933' =>	 ['contributo' =>1131.31, 'percentuale' => ''],
'175934' =>	 ['contributo' =>1131.31, 'percentuale' => ''],
'175935' =>	 ['contributo' =>1135.40, 'percentuale' => ''],
'175936' =>	 ['contributo' =>1135.40, 'percentuale' => ''],
'175938' =>	 ['contributo' =>1135.40, 'percentuale' => ''],
'175940' =>	 ['contributo' =>1135.40, 'percentuale' => ''],
'175941' =>	 ['contributo' =>1135.40, 'percentuale' => ''],
'175942' =>	 ['contributo' =>1135.40, 'percentuale' => ''],
'175943' =>	 ['contributo' =>1135.40, 'percentuale' => ''],
'175944' =>	 ['contributo' =>1135.40, 'percentuale' => ''],
'175945' =>	 ['contributo' =>1135.40, 'percentuale' => ''],
'175946' =>	 ['contributo' =>1131.31, 'percentuale' => ''],
'175947' =>	 ['contributo' =>1131.31, 'percentuale' => ''],
'175949' =>	 ['contributo' =>1131.31, 'percentuale' => ''],
'176160' =>	 ['contributo' =>1135.39, 'percentuale' => ''],
'176162' =>	 ['contributo' =>1135.40, 'percentuale' => ''],
'176163' =>	 ['contributo' =>1135.40, 'percentuale' => ''],
'176171' =>	 ['contributo' =>1131.31, 'percentuale' => ''],

            ];

            foreach ($arrayImporti as $idStabilimento => $arrayDati) {
                $frammento = $em->getRepository("FascicoloBundle:Frammento")
                    ->findOneBy(['alias' => 'form_stabilimento_balneare']);

                $istanzaFrammento = $em->getRepository("FascicoloBundle:IstanzaFrammento")
                    ->findOneBy(['istanzaPagina' => $idStabilimento, 'frammento' => $frammento]);

                $campoImportoContributo = $em->getRepository("FascicoloBundle:Campo")->findOneBy(['alias' => 'stabilimento_balneare_importo_contributo']);
                $istanzaCampoImportoContributo = $em->getRepository("FascicoloBundle:IstanzaCampo")
                    ->findOneBy(['campo' => $campoImportoContributo, 'istanzaFrammento' => $istanzaFrammento]);

                $isCampoImportoContributoCreato = false;
                if (empty($istanzaCampoImportoContributo)) {
                    $istanzaCampoImportoContributo = new IstanzaCampo();
                    $istanzaCampoImportoContributo->setCampo($campoImportoContributo);
                    $istanzaCampoImportoContributo->setDataCreazione(new DateTime());
                    $istanzaCampoImportoContributo->setDataModifica(new DateTime());
                    $istanzaCampoImportoContributo->setCreatoDa($this->getUser()->getUsername());
                    $istanzaCampoImportoContributo->setModificatoDa($this->getUser()->getUsername());
                    $istanzaCampoImportoContributo->setIstanzaFrammento($istanzaFrammento);
                    $isCampoImportoContributoCreato = true;
                }

                $campoPercentualeContributo = $em->getRepository("FascicoloBundle:Campo")->findOneBy(['alias' => 'stabilimento_balneare_percentuale_contributo']);
                $istanzaCampoPercentualeContributo = $em->getRepository("FascicoloBundle:IstanzaCampo")
                    ->findOneBy(['campo' => $campoPercentualeContributo, 'istanzaFrammento' => $istanzaFrammento]);

                $isCampoPercentualeContributoCreato = false;
                if (empty($istanzaCampoPercentualeContributo)) {
                    $istanzaCampoPercentualeContributo = new IstanzaCampo();
                    $istanzaCampoPercentualeContributo->setCampo($campoPercentualeContributo);
                    $istanzaCampoPercentualeContributo->setDataCreazione(new DateTime());
                    $istanzaCampoPercentualeContributo->setDataModifica(new DateTime());
                    $istanzaCampoPercentualeContributo->setCreatoDa($this->getUser()->getUsername());
                    $istanzaCampoPercentualeContributo->setModificatoDa($this->getUser()->getUsername());
                    $istanzaCampoPercentualeContributo->setIstanzaFrammento($istanzaFrammento);
                    $isCampoPercentualeContributoCreato = true;
                }

                $istanzaCampoImportoContributo->setValore($arrayDati['contributo']);
                $istanzaCampoImportoContributo->setValoreRaw($arrayDati['contributo']);

                $istanzaCampoPercentualeContributo->setValore($arrayDati['percentuale']);
                $istanzaCampoPercentualeContributo->setValoreRaw($arrayDati['percentuale']);

                try {
                    $em->persist($istanzaCampoImportoContributo);
                    $em->persist($istanzaCampoPercentualeContributo);
                    $em->flush();

                    if ($isCampoImportoContributoCreato || $isCampoPercentualeContributoCreato) {
                        $success[] = [
                            'soggetto' => $idStabilimento,
                            'importo' => $arrayDati['contributo'],
                            'percentuale' => $arrayDati['percentuale'],
                            'errori' => ''];
                    } else {
                        $successGiaPresente[] = [
                            'soggetto' => $idStabilimento,
                            'importo' => $arrayDati['contributo'],
                            'percentuale' => $arrayDati['percentuale'],
                            'errori' => ''];
                    }


                } catch (Exception $e) {
                    $error[] = [
                        'soggetto' => $idStabilimento,
                        'importo' => $arrayDati['contributo'],
                        'percentuale' => $arrayDati['percentuale'],
                        'errori' => $e->getMessage()];
                }
            }
        }

        return $this->render('FunzioniServizioBundle:Sap:popolaImportiBando135.html.twig', [
            'form' => $form->createView(),
            'success' => $success,
            'successGiaPresente' => $successGiaPresente,
            'error' => $error,
        ]);
    }

    /**
     * @Route("/edit_sogg/", name="sap_edit_sogg")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function modificaBeneficiario(Request $request)
    {
        $result = null;

        $form = $this->createFormBuilder()
            ->add('lifnr', TextType::class, [
                'constraints' => [
                    new Length([
                        'max' => 140,
                        'min' => 1,
                    ]),
                ],
            ])
            ->add('ragioneSociale', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Length([
                        'max' => 140,
                        'min' => 1,
                    ]),
                ],
            ])
            ->add('categoriaEconomica', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Length([
                        'max' => 140,
                        'min' => 1,
                    ]),
                ],
            ])

            ->add('send_produzione', SubmitType::class)
            ->add('send_test', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $ambiente = "Dev";
            if ($form->getClickedButton()->getName() == 'send_produzione') {
                $ambiente = "Prod";
            }

            $em = $this->getDoctrine()->getManager();
            /** @var Soggetto $soggetto */
            $soggetto = $em->getRepository("SoggettoBundle:Soggetto")->findOneBy(['lifnr_sap' => $data['lifnr']]);
            if ($soggetto) {
                $soggetto->setDenominazione($data['ragioneSociale']);
                if (!empty($data['categoriaEconomica'])) {
                    $soggetto->getFormaGiuridica()->setCategoriaEconomicaSap($data['categoriaEconomica']);
                    if ($data['categoriaEconomica'] == 100) {
                        $soggetto->setRea('');
                        $soggetto->setPartitaIva('');
                    }
                }
                $result = $this->container->get('app.sap_service')->modificaBeneficiario($soggetto, $ambiente);
            }
        }

        return $this->render('FunzioniServizioBundle:Sap:editSogg.html.twig', [
            'form' => $form->createView(),
            'result' => $result,
        ]);
    }

    /**
     * @Route("/controllo_persone_fisiche_bando/", name="controllo_persone_fisiche_bando")
     * @param Request $request
     * @return Response
     */
    public function controlloPersoneFisicheBandoAction(Request $request): Response
    {
        ini_set('max_execution_time', -1);
        ini_set("memory_limit", "1024M");

        $soggettiGiaPresenti = [];
        $soggettiNonPresenti = [];
        $soggettiMultipli = [];

        $form = $this->createFormBuilder(null, ['validation_groups' => 'fake',])
            ->add('bando', EntityType::class, [
                'class' => Procedura::class,
                'required' => true,
                'constraints' => [
                    new NotBlank(['groups' => ['fake']]),
                ],
                'choice_label' => 'titolo',
                'label' => 'Bando',
                'placeholder' => '-',
                'query_builder' => function (EntityRepository $repo) {
                    return $repo->createQueryBuilder('bando')
                        ->where('bando.id IN (139)');
                },
            ])
            ->add('limit_da', IntegerType::class, [
                'label' => 'Record di partenza',
                'required' => false,
            ])
            ->add('limit_length', IntegerType::class, [
                'label' => 'Numero di record',
                'required' => false,
            ])
            ->add('send_produzione', SubmitType::class)
            ->add('send_test', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $data = $form->getData();
            $ambiente = "Dev";
            if ($form->getClickedButton()->getName() == 'send_produzione') {
                $ambiente = "Prod";
            }

            $istruttorieAmmesse = $em->getRepository("IstruttorieBundle:IstruttoriaRichiesta")
                ->getIstruttoriePerBando($data['bando']->getId(), true);

            if ($data['limit_da'] !== null && $data['limit_length'] !== null) {
                $istruttorieAmmesse = array_slice($istruttorieAmmesse, $data['limit_da'], $data['limit_length']);
            } elseif ($data['limit_length'] && $data['limit_da'] === null) {
                $istruttorieAmmesse = array_slice($istruttorieAmmesse, 0, $data['limit_length']);
            } elseif ($data['limit_length'] === null && $data['limit_da']) {
                die('Specificare anche il valore del secondo limite');
            }

            $personeFisiche = [];
            foreach ($istruttorieAmmesse as $istruttoriaAmmessa) {
                $soggetto = $istruttoriaAmmessa->getRichiesta()->getMandatario()->getSoggetto();
                $persona = $em->getRepository("AnagraficheBundle:Persona")
                    ->getPersonaByUsername($istruttoriaAmmessa->getRichiesta()->getUtenteInvio());

                if ($soggetto && $soggetto->getLifnrSapCreated() == 1 && $persona && empty($persona[0]->getLifnrSap())) {
                    $personaTmp = $em->getRepository("AnagraficheBundle:Persona")
                        ->getPersonaByUsername($istruttoriaAmmessa->getRichiesta()->getUtenteInvio());
                    $personaTmp = $personaTmp[0];
                    $personaTmp->id_richiesta = $istruttoriaAmmessa->getRichiesta()->getId();
                    $personaTmp->lifnr_sap_soggetto = $istruttoriaAmmessa->getRichiesta()->getMandatario()->getSoggetto()->getLifnrSap();
                    $personaTmp->soggetto_sap_created = $istruttoriaAmmessa->getRichiesta()->getMandatario()->getSoggetto()->getLifnrSapCreated();
                    $personaTmp->categoria_economica_fesr = $istruttoriaAmmessa->getRichiesta()->getMandatario()->getSoggetto()->getFormaGiuridica()->getCategoriaEconomicaSap();
                    $personeFisiche[] = $personaTmp;
                }
            }

            foreach ($personeFisiche as $personaFisica) {
                /** @var Persona $personaFisica */
                //dump($personaFisica);
                //$personaFisica = $personaFisica[0];
                /*if ($personaFisica->getCodiceFiscale() != 'MTTMRC58H06E844G') {
                   continue;
                }*/

                $result = $this->container->get('app.sap_service')
                    ->ricercaBeneficiari($personaFisica->getCodiceFiscale(), $ambiente);

                if ($result) {
                    if ($result->E_RC === 0) {
                        if (is_array($result->E_BENEF->item)) {
                            // Se la richiesta ha restituito più risultati vado ad eliminare quelli bloccati (SPERR=X)
                            $arrayTmp = $result->E_BENEF->item;
                            /*foreach ($result->E_BENEF->item as $key => $item) {
                                if ($item->SPERR == 'X') {
                                    unset($arrayTmp[$key]);
                                }
                            }

                            if (count($arrayTmp) > 1) {
                                // Se, anche dopo aver eliminato quelli bloccati, restano ancora più risultati,
                                // vado ad eliminare quelli con Categoria economica sap uguale a 900 (900 = Categoria per anagrafica pratiche di rimborso)
                                foreach ($arrayTmp as $key => $item) {
                                    if ($item->ZZ_CAT_EC == 900) {
                                        unset($arrayTmp[$key]);
                                    }
                                }
                            }*/

                            if (count($arrayTmp) > 1) {
                                $soggettiMultipli[] = [
                                    'persona' => $result->E_BENEF,
                                    'lifnr_sap_soggetto' => $personaFisica->lifnr_sap_soggetto,
                                    'soggetto_sap_created' => $personaFisica->soggetto_sap_created,
                                    'categoria_economica_fesr' => $personaFisica->categoria_economica_fesr,
                                    'id_richiesta' => $personaFisica->id_richiesta,
                                    'errori' => 'Soggetti multipli'
                                ];
                            /*} elseif (count($arrayTmp) == 1) {
                                $key = array_key_first($arrayTmp);
                                $soggettiGiaPresenti[] = [
                                    'persona' => $arrayTmp[$key],
                                    'lifnr_sap_soggetto' => $personaFisica->lifnr_sap_soggetto,
                                    'soggetto_sap_created' => $personaFisica->soggetto_sap_created,
                                    'id_richiesta' => $personaFisica->id_richiesta,
                                    'errori' => ''
                                ];*/
                            } else {
                                $soggettiNonPresenti[] = [
                                    'persona' => $personaFisica,
                                    'lifnr_sap_soggetto' => $personaFisica->lifnr_sap_soggetto,
                                    'soggetto_sap_created' => $personaFisica->soggetto_sap_created,
                                    'categoria_economica_fesr' => $personaFisica->categoria_economica_fesr,
                                    'id_richiesta' => $personaFisica->id_richiesta,
                                    'errori' => 'C’erano più soggetti ma erano tutti 900'
                                ];
                            }
                        } else {//dump($result);
                            $soggettiGiaPresenti[] = [
                                'persona' => $result->E_BENEF->item,
                                'lifnr_sap_soggetto' => $personaFisica->lifnr_sap_soggetto,
                                'soggetto_sap_created' => $personaFisica->soggetto_sap_created,
                                'categoria_economica_fesr' => $personaFisica->categoria_economica_fesr,
                                'id_richiesta' => $personaFisica->id_richiesta,
                                'errori' => ''];
                        }
                    } else {
                        $soggettiNonPresenti[] = ['persona' => $personaFisica,
                            'lifnr_sap_soggetto' => $personaFisica->lifnr_sap_soggetto,
                            'soggetto_sap_created' => $personaFisica->soggetto_sap_created,
                            'categoria_economica_fesr' => $personaFisica->categoria_economica_fesr,
                            'id_richiesta' => $personaFisica->id_richiesta,
                            'errori' => ''
                        ];
                    }
                }
            }
        }

        return $this->render('FunzioniServizioBundle:Sap:controlloPersoneFisicheBando.html.twig', [
            'form' => $form->createView(),
            'soggettiGiaPresenti' => $soggettiGiaPresenti,
            'soggettiNonPresenti' => $soggettiNonPresenti,
            'soggettiMultipli' => $soggettiMultipli,
        ]);
    }

    /**
     * @Route("/edit_soggetti_bando/{procedura_id}/{ambiente}/{limit_da}/{limit_length}", name="edit_soggetti_bando")
     * @ParamConverter("procedura", options={"mapping": {"procedura_id" : "id"}})
     * @param Procedura $procedura
     * @param string $ambiente
     * @param int $limit_da
     * @param int $limit_length
     * @return Response|null
     */
    public function modificaSoggettiBando(Procedura $procedura, $ambiente = 'Prod', $limit_da = 0, $limit_length = 10): ?Response
    {
        ini_set('max_execution_time', -1);
        ini_set("memory_limit", "1024M");

        $em = $this->getDoctrine()->getManager();

        /*$istruttorieAmmesse = $em->getRepository("IstruttorieBundle:IstruttoriaRichiesta")
            ->getIstruttoriePerBando($procedura->getId(), true);
        $istruttorieAmmesse = array_slice($istruttorieAmmesse, $limit_da, $limit_length);*/

        //$persone = $em->getRepository("AnagraficheBundle:Persona")->findBy(['lifnr_sap_created' => 1]);
        //$persone = array_slice($persone, $limit_da, $limit_length);
        $persone = $em->getRepository("AnagraficheBundle:Persona")->findBy(['codice_fiscale' => 'MNFSRN73L44H223L']);

        $retVal = [];
        //$cf = ['LZZMSM65L06C980D', 'BRCMRZ68D10F257B', 'GRLPLA62L08F257X'];
        foreach ($persone as $persona) {
            /*if (!in_array($persona->getCodiceFiscale(), $cf)) {
                continue;
            }*/
            /*$soggetto = $istruttoriaAmmessa->getRichiesta()->getMandatario()->getSoggetto();
            $utenteInvio = $em->getRepository("AnagraficheBundle:Persona")
                ->findOneBy(['codice_fiscale' => $istruttoriaAmmessa->getRichiesta()->getUtenteInvio()]);*/

            $ricerca = $this->container->get('app.sap_service')
                ->ricercaBeneficiari($persona->getCodiceFiscale(), $ambiente);

            if ($ricerca->E_RC !== 0) {
                die('ttt');
            }

            $visualizza = $this->container->get('app.sap_service')
                ->visualizzaBeneficiario($ricerca->E_BENEF->item->LIFNR, $ambiente);

            if ($visualizza && $visualizza->E_RC === 0) {
                $strAttuale = $visualizza->E_ZBEN->RAGIONE_SOCIALE . ' ' . $visualizza->E_ZBEN->STCD1 . ' '
                    . $visualizza->E_ZBEN->STREET . ' ' . $visualizza->E_ZBEN->POST_CODE1;

                $indirizzo = $persona->getLuogoResidenza()->getVia() . ' ' . $persona->getLuogoResidenza()->getNumeroCivico();
                $nomeCognome = $persona->getCognome() . ' ' . $persona->getNome();
                $strFutura = $nomeCognome . ' ' . $persona->getCodiceFiscale() . ' ' . $indirizzo . ' ' . $persona->getLuogoResidenza()->getCap();
                $strAttuale = strtoupper($strAttuale);
                $strFutura = strtoupper($strFutura);

                if ($strAttuale != $strFutura && $visualizza->E_ZBEN->ZZ_CAT_EC == 100) {
                    $retVal[] = [
                        /*'richiesta' => $istruttoriaAmmessa->getRichiesta(),
                        'soggetto' => $soggetto,*/
                        'persona' => $persona,
                        'datiSap' => $visualizza->E_ZBEN,
                        'strAttuale' => $strAttuale,
                        'strFutura' => $strFutura,
                    ];
                }
            }
        }

        return $this->render('FunzioniServizioBundle:Sap:editSoggettiBando.html.twig', [
            'result' => $retVal,
            'ambiente' => $ambiente,
        ]);
    }

    /**
     * @Route("/edit_sogg_to_persona_fisica/{richiesta_id}/{ambiente}", name="edit_sogg_to_persona_fisica")
     * @param $richiesta_id
     * @param $ambiente
     * @return Response|null
     */
    public function modificaSoggettoToPersonaFisica($richiesta_id, $ambiente): ?Response
    {
        $em = $this->getDoctrine()->getManager();
        $result = '';
        $richiesta = $em->getRepository("RichiesteBundle:Richiesta")->find($richiesta_id);
        if ($richiesta) {
            /** @var Soggetto $soggetto */
            $soggetto = $richiesta->getMandatario()->getSoggetto();
            $lifnr_sap = $soggetto->getLifnrSap();
            if ($soggetto) {
                $utenteInvio = $em->getRepository("AnagraficheBundle:Persona")
                    ->findOneBy(['codice_fiscale' => $richiesta->getUtenteInvio()]);

                if ($utenteInvio && !empty($utenteInvio->getCognome()) && !empty($utenteInvio->getNome()) ) {
                    $soggetto->setDenominazione($utenteInvio->getCognome() . ' ' . $utenteInvio->getNome());
                    $soggetto->getFormaGiuridica()->setCategoriaEconomicaSap(100);
                    //$soggetto->setRea('');
                    $soggetto->setPartitaIva('');
                    $result = $this->container->get('app.sap_service')->modificaBeneficiario($soggetto, $ambiente);
                    if ($result && $result->E_RC === 0) {
                        $utenteInvio->setLifnrSap($lifnr_sap);
                        $utenteInvio->setLifnrSapCreated(true);
                        $em->persist($utenteInvio);
                        $em->flush();

                        $soggetto->setLifnrSap(null);
                        $soggetto->setLifnrSapCreated(null);
                        $em->persist($soggetto);
                        $em->flush();
                    }
                }
            }
        }
        return $this->render('@FunzioniServizio/Sap/editSoggettoToPersonaFisica.html.twig', [
            'result' => $result,
        ]);
    }

    /**
     * @Route("/edit_sogg_indirizzo_persona_fisica/{persona_id}/{ambiente}", name="edit_sogg_indirizzo_persona_fisica")
     * @param persona_id
     * @param $ambiente
     * @return Response|null
     */
    public function modificaIndirizzoPersonaFisica($persona_id, $ambiente): ?Response
    {
        $em = $this->getDoctrine()->getManager();
        $result = '';
        $persona = $em->getRepository("AnagraficheBundle:Persona")->find($persona_id);
        if ($persona) {
            /** @var Richiesta $richiesta */
            $richiesta = $em->getRepository("RichiesteBundle:Richiesta")->findOneBy(['utente_invio' => $persona->getCodiceFiscale(), 'procedura' => 139]);
            /** @var Proponente $proponente */
            $proponente = $em->getRepository("RichiesteBundle:Proponente")->findOneBy(['richiesta' => $richiesta]);
            /** @var Soggetto $soggetto */
            $soggetto = $em->getRepository("SoggettoBundle:Soggetto")->find($proponente->getSoggetto()->getId());

            $ragioneSociale = $persona->getCognome() . ' ' . $persona->getNome();
            $indirizzo = $persona->getLuogoResidenza()->getVia() . ' ' . $persona->getLuogoResidenza()->getNumeroCivico();
            $comune = $persona->getLuogoResidenza()->getComune();
            $cap = $persona->getLuogoResidenza()->getCap();
            $stato = $persona->getLuogoResidenza()->getComune()->getProvincia()->getRegione()->getStato();

            if ($soggetto && !empty($ragioneSociale) && !empty($indirizzo)) {
                $soggetto->setLifnrSap($persona->getLifnrSap());
                $soggetto->setDenominazione($ragioneSociale);
                $soggetto->getFormaGiuridica()->setCategoriaEconomicaSap(100);
              //  $soggetto->setRea('');
                $soggetto->setPartitaIva('');
                $soggetto->setVia($indirizzo);
                $soggetto->setComune($comune);
                $soggetto->setCap($cap);
                $soggetto->setStato($stato);
                $soggetto->setTel($persona->getTelefonoPrincipale());
                $soggetto->setEmail($persona->getEmailPrincipale());

                $result = $this->container->get('app.sap_service')->modificaBeneficiario($soggetto, $ambiente);
            }
        }
        return $this->render('@FunzioniServizio/Sap/editSoggettoToPersonaFisica.html.twig', [
            'result' => $result,
        ]);
    }
}