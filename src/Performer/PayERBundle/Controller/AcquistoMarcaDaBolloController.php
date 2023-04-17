<?php
namespace Performer\PayERBundle\Controller;

use DateTime;
use Exception;
use Performer\PayERBundle\Entity\AcquistoMarcaDaBollo;
use Performer\PayERBundle\Entity\RichiestaAcquistoMarcaDaBollo;
use Performer\PayERBundle\Exception\AcquistoMarcaDaBolloInviata;
use Performer\PayERBundle\Service\EBolloInterface;
use Performer\PayERBundle\Type\RichiestaAcquistoMarcaDaBolloType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AcquistoMarcaDaBolloController
 */
class AcquistoMarcaDaBolloController extends Controller
{
    protected const SESSION_URL_RITORNO = 'payer.ebollo.url_ritorno';

    /**
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function acquista(Request $request): Response
    {
        $em = $this->getDoctrine()->getManager();
        $ebollo = $this->get(EBolloInterface::class);
        $richiesta = new RichiestaAcquistoMarcaDaBollo();
        $form = $this->createForm(RichiestaAcquistoMarcaDaBolloType::class, $richiesta, []);
        $submittedData = $request->query->all();
        $form->submit($submittedData);
        if ($form->isValid()) {
            $richiesta->setDataInvio(new DateTime());

            $em->persist($richiesta);
            $em->flush();

            $rid = $ebollo->send($richiesta);
            $richiesta->setRid($rid);
            $em->flush();

            $this->get('session')->set(self::SESSION_URL_RITORNO, $form->get('urlRitorno')->getData());

            return $ebollo->redirectToEbollo($rid);
        }

        throw new Exception('Invalid data submitted.');
    }

    /**
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function notifica(Request $request): Response
    {
        $ebollo = $this->get(EBolloInterface::class);
        $pid = $request->query->get('pid');
        $result = $ebollo->esitoCarrello($pid);
        $richiesta = $ebollo->handleEsitoRichiesta($result);
        return $ebollo->confermaRicezioneEsito($richiesta);
    }

    /**
     * @return RedirectResponse
     */
    public function ritorno(): RedirectResponse
    {
        $session = $this->get('session');
        $urlRitorno = $session->get(self::SESSION_URL_RITORNO);
        $session->remove(self::SESSION_URL_RITORNO);
        return new RedirectResponse($urlRitorno);
    }

    /**
     * @return RedirectResponse
     */
    public function indietro(): RedirectResponse
    {
        $session = $this->get('session');
        $urlRitorno = $session->get(self::SESSION_URL_RITORNO);
        $session->remove(self::SESSION_URL_RITORNO);
        return new RedirectResponse($urlRitorno);
    }
}