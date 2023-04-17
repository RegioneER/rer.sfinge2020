<?php
namespace DocumentoBundle\Controller;

use BaseBundle\Controller\BaseController;
use DocumentoBundle\Entity\TipologiaDocumento;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends BaseController {

    /**
     * @Route("/scarica/{path_codificato}", name="scarica")
     */
    public function scaricaAction($path_codificato) {
        $file_path = $this->get("funzioni_utili")->decid($path_codificato);

        return $this->get("documenti")->scaricaDaPath($file_path);
    }

    /**
     * @Route("/scarica_originale/{path_codificato}", name="scarica_originale")
     */
    public function scaricaOriginaleAction($path_codificato) {
        try {
            $file_path = $this->get("funzioni_utili")->decid($path_codificato);
            return $this->get("documenti")->scaricaOriginaleDaPath($file_path);
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirect($this->generateUrl('home'));
        }
    }

    /**
     * @Route("/mostra_estensione_documento/{id_documento}", name="mostra_estensione_documento")
     */
    public function mostraEstensioneDocumento($id_documento) {
        $documento = $this->getEm()->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->find($id_documento);
        $html = $this->get("funzioni_utili")->getEstensioniFormattate($documento);
        return new Response($html);
    }

    /**
     * @Route("/scarica_da_path/{path_codificato}", name="scarica_da_path")
     * 
     * @param $path_codificato
     * @return Response
     * @throws \Exception
     */
    public function scaricaDaPathAction($path_codificato) {
        $file_path = $this->get("funzioni_utili")->decid($path_codificato);
        return $this->get("documenti")->scaricaDaPathRaw($file_path);
    }

    /**
     * @Route("/is_documento_dropzone/{id_documento}", name="is_documento_dropzone")
     */
    public function isDocumentoDropzone($id_documento): JsonResponse
    {
        /** @var TipologiaDocumento $documento */
        $documento = $this->getEm()->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->find($id_documento);
        return new JsonResponse($documento->isDropzone());
    }

    /**
     * @Route("/get_informazioni_documento_dropzone/{id_documento}", name="get_informazioni_documento_dropzone")
     */
    public function getInformazioniDocumentoDropzone($id_documento): Response
    {
        $tipologiaDocumento = $this->getEm()->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->find($id_documento);
        $html = $this->get("funzioni_utili")->getInformazioniDocumentoDropzone($tipologiaDocumento);
        return new JsonResponse($html);
    }
}
