<?php

namespace RichiesteBundle\Controller;

use BaseBundle\Controller\BaseController;
use BaseBundle\Service\SpreadsheetFactory;
use DocumentoBundle\Entity\DocumentoFile;
use DocumentoBundle\Entity\TipologiaDocumento;
use DocumentoBundle\Form\Type\DocumentoFileType;
use PaginaBundle\Annotations\Menuitem;
use PaginaBundle\Annotations\PaginaInfo;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use RichiesteBundle\Entity\RichiestaCupBatch;
use RichiesteBundle\Service\GestoreRichiestaCupBatch;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/richiestacupbatch")
 * @Template("RichiesteBundle:richiestecupbatch")
 */
class RichiestaCupBatchController extends BaseController {
    protected function getRestoreRichiestaCupBatchService(): GestoreRichiestaCupBatch {
        $GestoreRichiestaCupBatch = $this->get('gestore_richiesta_cup_batch');
        return $GestoreRichiestaCupBatch;
    }

    /**
     * Lists all RichiestaCupBatch entities.
     *
     * @Route("/", name="richiestacupbatch_index")
     * @Method("GET")
     */
    public function indexAction() {
        return $this->getRestoreRichiestaCupBatchService()->findAll(true);
    }

    /**
     * Finds and displays a RichiestaCupBatch entity.
     *
     * @Route("show/{id}", name="richiestacupbatch_show")
     * @Method("GET")
     */
    public function showAction(RichiestaCupBatch $richiestaCupBatch) {
        return $this->getRestoreRichiestaCupBatchService()->show($richiestaCupBatch);
    }

    /**
     * Displays a form to edit an existing RichiestaCupBatch entity.
     *
     * @Route("associa_risposta/{id}", name="richiestacupbatch_associa_risposta")
     * @Template("RichiesteBundle:richiestecupbatch:associa_risposta.html.twig")
     * @Method({"GET", "POST"})
     */
    public function associaRispostaAction(Request $request, RichiestaCupBatch $richiestaCupBatch) {
        $options["CIPE_BATCH"] = $this->getDoctrine()->getRepository(\get_class(new \DocumentoBundle\Entity\TipologiaDocumento()))->findOneBy(["codice" => 'CIPE_BATCH']);
        $options["tipo_tracciato"] = "codici_cup";

        $editForm = $this->createForm('RichiesteBundle\Form\RichiestaCupBatchType', $richiestaCupBatch, $options);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $RispostaRichiestaCupBatch = $richiestaCupBatch->getCupBatchDocumentoRisposta();
            return $this->getRestoreRichiestaCupBatchService()->associaRispostaRichiestaCupBatch($richiestaCupBatch, $RispostaRichiestaCupBatch);
        }

        return  [
            'richiestaCupBatch' => $richiestaCupBatch,
            'edit_form' => $editForm->createView(),
        ];
    }

    /**
     * Displays a form to edit an existing RichiestaCupBatch entity.
     *
     * @Route("associa_scarto/{id}", name="richiestacupbatch_associa_scarto")
     * @Template("RichiesteBundle:richiestecupbatch:associa_risposta.html.twig")
     * @Method({"GET", "POST"})
     */
    public function associaScartoAction(Request $request, RichiestaCupBatch $richiestaCupBatch) {
        $options["CIPE_BATCH"] = $this->getDoctrine()->getRepository(\get_class(new \DocumentoBundle\Entity\TipologiaDocumento()))->findOneBy(["codice" => 'CIPE_BATCH']);
        $options["tipo_tracciato"] = "scarti";

        $editForm = $this->createForm('RichiesteBundle\Form\RichiestaCupBatchType', $richiestaCupBatch, $options);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $ScartiCupBatch = $richiestaCupBatch->getCupBatchDocumentoScarto();
            return $this->getRestoreRichiestaCupBatchService()->associaScartiCupBatch($richiestaCupBatch, $ScartiCupBatch);
        }

        return  [
            'richiestaCupBatch' => $richiestaCupBatch,
            'edit_form' => $editForm->createView(),
        ];
    }

    /**
     * @Route("/coda_cup", name="coda_cup")
     * @Menuitem(menuAttivo="coda-cup")
     * @Template("RichiesteBundle:richiestecupbatch:codaCUP.html.twig")
     * @PaginaInfo(titolo="Popola coda CUP", sottoTitolo="Permette di aggiungere le domande alla coda CUP da uno spreadsheet")
     */
    public function codaCUPAction(Request $request): array {
        $tipoDoc = $this->getEm()->getRepository(TipologiaDocumento::class)->findOneBy(['codice' => 'CODA_CUP']);
        $form = $this->createForm(DocumentoFileType::class, null, ['tipo' => $tipoDoc]);
        $form->add('invia', SubmitType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var DocumentoFile $data */
            $data = $form->getData();
            $data = $this->get('documenti')->carica($data, false);
            /** @var SpreadsheetFactory $factory */
            $factory = $this->container->get('phpoffice.spreadsheet');
            $excel = $factory->readDocumento($data);
            /** @var Worksheet $sheet */
            foreach ($excel->getAllSheets() as $sheet) {
                try {
                    $maxRow = $sheet->getHighestRow();
                    $celleId = $sheet->rangeToArray("A2:A${maxRow}");
                    $ids = \array_map(function (array $celle) {
                        return \reset($celle);
                    }, $celleId);
                    $ids = \array_filter($ids, function ($value) {
                        return true == $value;
                    });
                    $query = "UPDATE IstruttorieBundle:IstruttoriaRichiesta as i
                    SET i.richiedi_cup = 1
                    WHERE i.richiesta IN (:ids)";
                    $res = $this->getEm()
                    ->createQuery($query)
                    ->execute([
                        'ids' => $ids,
                    ]);
                    $this->addSuccess("Le seguendi domande sono state aggiunte alla coda: " . \implode(', ', $ids));
                    $this->addSuccess("Domande modificate: ${res}");
                } catch (\Exception $e) {
                    $this->container->get('logger')->error($e->getMessage());
                    $this->addError("Errore durante l'operazione");
                }
            }
        }

        return  [
            'form' => $form->createView(),
        ];
    }
}
