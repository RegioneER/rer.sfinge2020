<?php

namespace FunzioniServizioBundle\Controller;

use AttuazioneControlloBundle\Entity\AttuazioneControlloRichiesta;
use DateTime;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Exception;
use IstruttorieBundle\Entity\EsitoIstruttoria;
use IstruttorieBundle\Entity\IstruttoriaAtcLog;
use IstruttorieBundle\Entity\IstruttoriaRichiesta;
use RichiesteBundle\Entity\Bando118\OggettoIrap;
use RichiesteBundle\Entity\Bando125\OggettoIrap2020;
use RichiesteBundle\Entity\Richiesta;
use RichiesteBundle\Entity\SedeOperativaRichiesta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SfingeBundle\Entity\Atto;
use SfingeBundle\Entity\PermessiProcedura;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\NotNull;

/**
 * Class FunzioniBandoIstruttoriaController
 *
 * @Route("/istruttoria")
 */
class FunzioniIstruttoriaController extends Controller
{
    const ARRAY_BANDI = [127, 133, 134, 135, 136, 139];
    const ARRAY_TIPOLOGIE_IDENTIFICATIVI = ['Protocollo' => 'protocollo', 'Codice fiscale' => 'codice_fiscale'];
    const ARRAY_SEPARATORI = [';' => ';', ',' => ','];

    /**
     * @Route("/", name="funzionalita_istruttoria")
     * @Security("has_role('ROLE_UTENTE_PA')")
     * @return Response|null
     */
    public function indexAction(): ?Response
    {
        return $this->render('FunzioniServizioBundle:Istruttoria:index.html.twig');
    }

    /**
     * @Route("/popola_cor/", name="popola_cor_istruttoria")
     * @Security("has_role('ROLE_UTENTE_PA')")
     *
     * @param Request $request
     * @return Response|null
     * @throws Exception
     */
    public function popolaCor(Request $request): ?Response
    {
        /** @var EntityManagerInterface $em */
        $em = $this->getDoctrine()->getManager();
        $bandi = $em->getRepository("SfingeBundle:Bando")->findBy(['id' => self::ARRAY_BANDI]);

        $array_bandi = [];
        foreach ($bandi as $bando) {
            $array_bandi[$bando->getId()] = $bando->getTitolo();
        }

        $form = $this->createFormBuilder()
            ->add('bando', ChoiceType::class, [
                'required' => true,
                'constraints' => [new NotNull()],
                'placeholder' => '-',
                'empty_value' => false,
                'choices' => $array_bandi,
            ])

            ->add('dati', TextareaType::class, [
                'label' => 'Dati',
                'required' => true,
                'constraints' => [new NotNull()],
                'attr' => ['cols' => '5', 'rows' => '5'],
            ])

            ->add('identificativo', ChoiceType::class, [
                'choices' => ['Protocollo' => 'protocollo', 'Codice fiscale' => 'codice_fiscale'],
                'choices_as_values' => true,
                'label' => 'Identificativo',
                'placeholder' => '-',
                'required' => true,
                'constraints' => [new NotNull()],
            ])

            ->add('separatore', ChoiceType::class, [
                'choices' => self::ARRAY_SEPARATORI,
                'choices_as_values' => true,
                'label' => 'Separatore',
                'placeholder' => '-',
                'required' => true,
                'constraints' => [new NotNull()],
            ])
            ->add('send', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);
        $arraySuccess = [];
        $arrayFail = [];
        if($form->isSubmitted() && $form->isValid()) {
            /** @var EntityManagerInterface $em */
            $em = $this->getDoctrine()->getManager();
            $data = $form->getData();
            $separatore = $data['separatore'];
            $bando_id = $data['bando'];
            $dati = trim($data['dati']);
            $dati = str_replace(' ', '', $dati);

            // Identificativo richiesta
            $identificativo = $data['identificativo'];
            if (!in_array($identificativo, self::ARRAY_TIPOLOGIE_IDENTIFICATIVI)) {
                throw new Exception("Tipologia identificativo non previsto");
            }

            // Separatore
            if (!in_array($separatore, self::ARRAY_SEPARATORI)) {
                throw new Exception("Tipologia identificativo non previsto");
            }

            // Id procedura
            if (!in_array($bando_id, self::ARRAY_BANDI)) {
                throw new Exception("Bando non previsto");
            }

            // Separo il contenuto della text-area in righe
            $righe = preg_split('/\r\n|[\r\n]/', $dati);

            $rsm = new ResultSetMappingBuilder($em);
            $arrayCampi = ['richiesta_id',];
            foreach ($arrayCampi as $campo ) {
                $rsm->addScalarResult($campo, $campo);
            }

            foreach ($righe as $riga) {
                $tmp = preg_split("/[$separatore]+/", $riga);

                try {
                    if ($identificativo == 'protocollo') {
                        $arrayProtocollo = explode('/', trim($tmp[0]));
                        if (count($arrayProtocollo) == 3) {
                            $sql = "SELECT richiesta_protocollo.richiesta_id
                            FROM richieste_protocollo AS richiesta_protocollo
                            JOIN richieste AS richiesta ON (richiesta_protocollo.richiesta_id = richiesta.id)
                            WHERE richiesta.stato_id IN (4,5) AND richiesta_protocollo.tipo = 'FINANZIAMENTO' AND richiesta_protocollo.stato = 'POST_PROTOCOLLAZIONE' AND richiesta_protocollo.data_cancellazione IS NULL
                                AND richiesta_protocollo.registro_pg = :registro_pg AND richiesta_protocollo.anno_pg = :anno_pg AND richiesta_protocollo.num_pg = :num_pg AND richiesta_protocollo.procedura_id = :bando_id
                            ORDER BY richiesta_protocollo.id DESC";

                            /** @var AbstractQuery $query */
                            $query = $em->createNativeQuery($sql, $rsm);
                            $query->setParameter('registro_pg', $arrayProtocollo[0]);
                            $query->setParameter('anno_pg', $arrayProtocollo[1]);
                            $query->setParameter('num_pg', $arrayProtocollo[2]);
                            $query->setParameter('bando_id', $bando_id);
                        } else {
                            throw new Exception("Protocollo non corretto");
                        }
                    } else {
                        $sql = "SELECT richiesta.id AS richiesta_id
                            FROM richieste AS richiesta
                            JOIN proponenti AS proponente ON (richiesta.id = proponente.richiesta_id AND proponente.mandatario = 1 AND proponente.data_cancellazione IS NULL)
                            JOIN soggetti AS soggetto ON (proponente.soggetto_id = soggetto.id AND soggetto.data_cancellazione IS NULL)
                            WHERE richiesta.stato_id IN (4,5) AND soggetto.codice_fiscale = :codice_fiscale AND richiesta.procedura_id = :bando_id";

                        /** @var AbstractQuery $query */
                        $query = $em->createNativeQuery($sql, $rsm);
                        $query->setParameter('codice_fiscale', trim($tmp[0]));
                        $query->setParameter('bando_id', $bando_id);
                    }

                    $richieste = $query->getResult();

                    if (count($richieste) > 1) {
                        throw new Exception("Troppe richieste trovate (" . count($richieste) . ")");
                    }

                    if (!empty($richieste) && $richieste[0]['richiesta_id']) {
                        $richiesta_id = $richieste[0]['richiesta_id'];
                        /** @var Richiesta $richiesta */
                        $richiesta = $em->getRepository("RichiesteBundle:Richiesta")->find($richiesta_id);

                        if ($richiesta->getProcedura()->getId() == $bando_id) {
                            /** @var OggettoIrap|OggettoIrap2020 $oggettoRichiesta */
                            $istruttoriaRichiesta = $richiesta->getIstruttoria();

                            if ($istruttoriaRichiesta->getCor()) {
                                throw new Exception("Cor già presente: " . $istruttoriaRichiesta->getCor());
                            }

                            $istruttoriaRichiesta->setCor(trim($tmp[1]));
                            $em->persist($istruttoriaRichiesta);
                            $arraySuccess[] = [
                                'protocollo' => $tmp[0],
                                'cor' => $tmp[1],
                                'id_richiesta' => $richiesta->getId(),
                            ];
                        } else {
                            throw new Exception("Il numero di protocollo non è di una richiesta del bando selezionato");
                        }
                    } else {
                        throw new Exception("richiesta_id non presente");
                    }
                } catch (Exception $e) {
                    $arrayFail[] = ['protocollo' => $tmp[0], 'errore' => $e->getMessage()];
                }
            }
            $em->flush();
        }

        return $this->render('FunzioniServizioBundle:Istruttoria:popolaCor.html.twig', [
            'form' => $form->createView(),
            'arraySuccess' => $arraySuccess,
            'arrayFail' => $arrayFail,
        ]);
    }

    /**
     * @Route("/porta_in_atc/", name="porta_in_atc_istruttoria")
     * @Security("has_role('ROLE_UTENTE_PA')")
     * @param Request $request
     * @return Response|null
     */
    public function portaInAtc(Request $request): ?Response
    {
        /** @var EntityManagerInterface $em */
        $em = $this->getDoctrine()->getManager();
        $bandi = $em->getRepository("SfingeBundle:Bando")->findBy(['id' => self::ARRAY_BANDI]);
        $counter = null;
        
        $array_bandi = [];
        foreach ($bandi as $bando) {
            $array_bandi[$bando->getId()] = $bando->getId() . ' - ' . $bando->getTitolo();
        }
        
        $form = $this->createFormBuilder()
            ->add('bando', ChoiceType::class, [
                'required' => true,
                'constraints' => [new NotNull()],
                'placeholder' => '-',
                'empty_value' => false,
                'choices' => $array_bandi,
            ])
            
            ->add('atto', EntityType::class, [
                'class' => Atto::class,
                'required' => true,
                'constraints' => [new NotNull()],
                'placeholder' => '-',
                'choice_label' => function (Atto $atto) {
                    $procedura = $atto->getProcedura();
                    return $procedura->getId() . ' --- ' . $procedura->getTitolo() . ' --- ' . $atto;
                },
                'empty_value' => false,
                'query_builder' => function (EntityRepository $repo) {
                    return $repo->createQueryBuilder('atti')
                        ->join("atti.procedura", "procedura");

                },
            ])

            ->add('validatore', EntityType::class, [
                'class' => PermessiProcedura::class,
                'required' => true,
                'constraints' => [new NotNull()],
                'choice_label' => 'utente.persona',
                'choice_value' => 'utente.id',
                'placeholder' => '-',
                'query_builder' => function (EntityRepository $repo) {
                    return $repo->createQueryBuilder('permessi_procedura')
                        ->join("permessi_procedura.utente", "utente")
                        ->join("utente.persona", "persona")
                        ->join("permessi_procedura.procedura", "procedura");
                },
            ])
            ->add('send', SubmitType::class)
            ->getForm();

        /** @var EntityManagerInterface $em */
        $em = $this->getDoctrine()->getManager();
        $rsm = new ResultSetMappingBuilder($em);
        $arrayCampi = ['id', 'protocollo', 'esito_istruttoria'];
        $richieste = [];

        foreach ($arrayCampi as $campo ) {
            $rsm->addScalarResult($campo, $campo);
        }

        foreach (self::ARRAY_BANDI as $procedura_id) {
            $sql = "SELECT 
                    richiesta.id, 
                    COALESCE(
                        (
                            SELECT CONCAT(
                                COALESCE(richiesta_protocollo.registro_pg, ''), '/',
                                COALESCE(richiesta_protocollo.anno_pg, ''), '/',
                                COALESCE(richiesta_protocollo.num_pg, '')
                            )
                            FROM richieste_protocollo AS richiesta_protocollo
                            WHERE richiesta.id = richiesta_protocollo.richiesta_id AND richiesta_protocollo.tipo = 'FINANZIAMENTO' 
                              AND richiesta_protocollo.stato = 'POST_PROTOCOLLAZIONE' AND richiesta_protocollo.data_cancellazione IS NULL
                            ORDER BY richiesta_protocollo.id DESC
                            LIMIT 0,1
                        ),
                    '-') AS protocollo,
                    istruttoria_esito.codice AS esito_istruttoria

                FROM richieste AS richiesta
                JOIN istruttorie_richieste AS istrutotria_richiesta ON (richiesta.id = istrutotria_richiesta.richiesta_id)
                LEFT JOIN istruttorie_esiti AS istruttoria_esito ON (istrutotria_richiesta.esito_id = istruttoria_esito.id)
                LEFT JOIN attuazione_controllo_richieste AS attuazione_controllo_richiesta ON (richiesta.id = attuazione_controllo_richiesta.richiesta_id)
                JOIN oggetti_richiesta AS oggetto_richiesta ON (richiesta.id = oggetto_richiesta.richiesta_id AND oggetto_richiesta.data_cancellazione IS NULL)
                WHERE richiesta.procedura_id = :procedura_id AND istrutotria_richiesta.esito_id IS NOT NULL 
                  AND istruttoria_esito.codice = :esito_istruttoria AND attuazione_controllo_richiesta.id IS NULL 
            ";

            $query = $em->createNativeQuery($sql, $rsm);
            $query->setParameter('procedura_id', $procedura_id);
            $query->setParameter('esito_istruttoria', EsitoIstruttoria::AMMESSO);
            $richieste[$procedura_id] = $query->getResult();
        }

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $counter = 0;
            $data = $form->getData();
            $procedura_id = $data['bando'];

            /** @var PermessiProcedura $validatore */
            $validatore = $data['validatore'];
            foreach ($richieste[$procedura_id] as $richiesta) {
                $oggettoRichiesta = $em->getRepository("RichiesteBundle:Richiesta")->find($richiesta['id']);
                /** @var IstruttoriaRichiesta $istruttoria */
                $istruttoria = $oggettoRichiesta->getIstruttoria();

                /** @var SedeOperativaRichiesta[] $sedi */
                $sedi = $oggettoRichiesta->getRichiesta()->getSediOperative();
                /** @var Atto $atto */
                $atto = $em->getRepository("SfingeBundle:Atto")->find($data['atto']);

                $contributo = 0;
                $costo = 0;

                if ($procedura_id == 127) {
                    foreach ($sedi as $sede) {
                        $contributo = bcadd($contributo, $sede->getContributoSede(), 2);
                        $costo = bcadd($costo, $sede->getImportoSede(), 2);
                    }
                } else {
                    $contributo = $istruttoria->getContributoAmmesso();
                    $costo = $istruttoria->getCostoAmmesso();
                }

                // Imposto solo i campi che sono necessari
                // Non imposto le date di progetto.
                $istruttoria->setAmmissibilitaAtto(1);
                $istruttoria->setConcessione(1);
                $istruttoria->setAttoConcessioneAtc($data['atto']);
                $istruttoria->setAttoAmmissibilitaAtc($data['atto']);
                $istruttoria->setImpegnoAmmesso($contributo);
                $istruttoria->setCostoAmmesso($costo);
                $istruttoria->setContributoAmmesso($contributo);

                $istruttoria->setDataContributo($atto->getDataPubblicazione());
                $istruttoria->setDataImpegno($atto->getDataPubblicazione());

                $istruttoria->setDataVerbalizzazione($atto->getDataPubblicazione());

                $istruttoria->setValidazioneAtc(true);
                $istruttoria->setUtenteValidatoreAtc($validatore->getUtente());
                $istruttoria->setDataValidazioneAtc(new DateTime());

                // Genero il record per ATC
                /** @var Richiesta $richiesta */
                $richiesta = $istruttoria->getRichiesta();

                $attuazioneControlloRichiesta = new AttuazioneControlloRichiesta();
                $attuazioneControlloRichiesta->setContributoAccettato(1);
                $richiesta->setAttuazioneControllo($attuazioneControlloRichiesta);

                // Creo il log
                $logIstruttoria = new IstruttoriaAtcLog();
                $logIstruttoria->setIstruttoriaRichiesta($istruttoria);
                $logIstruttoria->setOggetto('ATC_VALIDA');
                $logIstruttoria->setUtente($validatore->getUtente());
                $logIstruttoria->setData(new DateTime());
                $logIstruttoria->setAmmissibilitaAtto($istruttoria->getAmmissibilitaAtto());
                $logIstruttoria->setConcessione($istruttoria->getConcessione());
                $logIstruttoria->setContributoAmmesso($contributo);
                $logIstruttoria->setDataContributo($istruttoria->getDataContributo());
                $logIstruttoria->setImpegnoAmmesso($istruttoria->getImpegnoAmmesso());
                $logIstruttoria->setDataImpegno($istruttoria->getDataImpegno());
                $logIstruttoria->setAttoModificaConcessioneAtc($istruttoria->getAttoModificaConcessioneAtc());
                $istruttoria->addIstruttoriaAtcLog($logIstruttoria);

                $em->persist($richiesta);
                $counter++;
            }
            $em->flush();
        }

        return $this->render('FunzioniServizioBundle:Istruttoria:portaInAtc.html.twig', [
            'form' => $form->createView(),
            'richieste' => $richieste,
            'counter' => $counter,
        ]);
    }
}
