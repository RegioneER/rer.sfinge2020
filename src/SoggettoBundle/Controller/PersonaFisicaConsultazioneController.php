<?php
namespace SoggettoBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SoggettoBundle\Entity\PersonaFisica;
use PaginaBundle\Annotations\Menuitem;
use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use BaseBundle\Annotation\ControlloAccesso;
use Symfony\Component\HttpFoundation\Request;

class PersonaFisicaConsultazioneController extends SoggettoBaseController
{
	/**
	 * @Route("/persona_fisica_visualizza/{id_soggetto}", name="persona_fisica_visualizza")
	 * @Template("SoggettoBundle:Soggetto:personaFisica.html.twig")
	 * @Menuitem(menuAttivo = "elencoSoggettiGiuridici")
	 * @PaginaInfo(titolo="Dettagli persona fisica",sottoTitolo="pagina per visualizzare i dati di una persona fisica")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="elenco persone fisiche", route="elenco_aziende"), @ElementoBreadcrumb(testo="Dettagli persona fisica")})
	 * @ControlloAccesso(contesto="soggetto", classe="SoggettoBundle:Soggetto", opzioni={"id" = "id_soggetto"}, azione="show")
	 */
	public function visualizzaAziendaAction(Request $request, $id_soggetto): array
    {
		$em = $this->getDoctrine()->getManager();

		/** @var PersonaFisica $personaFisica */
		$personaFisica = $em->getRepository('SoggettoBundle:Soggetto')->findOneById($id_soggetto);

		$options["readonly"] = true;
		$options["url_indietro"] = $this->generateUrl("elenco_soggetti_giuridici");
        $options["tipo"] = $personaFisica->getTipoByFormaGiuridica();

		$form = $this->createForm('SoggettoBundle\Form\PersonaFisicaType', $personaFisica, $options);

        if(!$personaFisica->isFormaGiuridicaCoerente()) {
            $this->addFlash('warning', 'Attenzione! La forma giuridica indicata potrebbe non essere corretta.');
        }

		$form_params["form"] = $form->createView();
		$form_params["legale_rappresentate"] = $personaFisica;

		return $form_params;
	}
}
