<?php
namespace FaqBundle\Controller;

use BaseBundle\Controller\BaseController;
use FaqBundle\Entity\Faq;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PaginaBundle\Annotations\PaginaInfo;
use Symfony\Component\HttpFoundation\Response;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use FaqBundle\FaqType;
use Symfony\Component\HttpFoundation\Request;





class FaqController extends BaseController{

	/**
	 * @Route("/faq", name="faq")
	 * @Method("GET")
	 * @Template("FaqBundle:Faq:faq.html.twig")
	 * @PaginaInfo(titolo="FAQ", sottoTitolo="Domande Frequenti")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="FAQ", route="faq")})
	 */
	public function listaFaq() {

		$em = $this->getDoctrine()->getManager();

        $faq = $em->getRepository('FaqBundle:Faq')->findAll();

		return $this->render('FaqBundle:Faq:faq.html.twig', array(
			'faq' => $faq
		));

	}

	/**
     * @Route("/crea-faq", name="crea_faq")
	 * @Template("FaqBundle:Faq:creaFaq.html.twig")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Nuova")})
	 * @PaginaInfo(titolo="Nuova Faq",sottoTitolo="pagina per creare una nuova FAQ")
     */
	public function creaFaqAction(Request $request) {
		$em = $this->getDoctrine()->getManager();

		$faq = new Faq();
		$options["url_indietro"] = $this->generateUrl("visualizza_faq");
		$options["visibilita"] = array('ROLE_SUPER_ADMIN'=>'ROLE_SUPER_ADMIN');

		$form = $this->createForm('FaqBundle\Form\FaqType', $faq, $options);

		if ($request->isMethod('POST')) {
			$form->handleRequest($request);

			if ($form->isValid()) {
				try {
					$em->persist($faq);
					$em->flush();
					$this->addFlash('success', "FAQ creata correttamente");
					return $this->redirect($this->generateUrl('visualizza_faq', array("id_faq" => $faq->getId())));
				}catch (\Exception $e) {
					$this->addFlash('error', $e->getMessage());
				}
			}
		}

		$form_params["form"] = $form->createView();
		return $form_params;
	}

	/**
     * @Route("/visualizza-faq", name="visualizza_faq")
	 * @Method("GET")
	 * @Template("FaqBundle:Faq:visualizzaFaq.html.twig")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Visualizza faq", route="visualizza_faq", parametri={"id_faq"})})
	 * @PaginaInfo(titolo="Mostra faq",sottoTitolo="Pagina per mostrare una faq")
     */
    public function visualizzaFaqAction()

    {
		$em = $this->getDoctrine()->getManager();

		$faq = $em->getRepository('FaqBundle:Faq')->findAll();

		return $this->render('FaqBundle:Faq:visualizzaFaq.html.twig', array(
			'faq' => $faq
		));
       // return array('faq' => $faq);
    }

	/**
     * @Route("/modifica-faq/{id_faq}", name="modifica_faq")
	 * @Template("FaqBundle:Faq:modificaFaq.html.twig")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Modifica Faq")})
	 * @PaginaInfo(titolo="Modifica Faq",sottoTitolo="")
     */
    public function modificaFaqAction(Request $request, $id_faq)
    {
		$em = $this->getDoctrine()->getManager();

		$faq = $em->getRepository("FaqBundle\Entity\Faq")->findOneById($id_faq);
		$options["url_indietro"] = $this->generateUrl("visualizza_faq");
		$options["visibilita"] = array('ROLE_UTENTE'=>'ROLE_UTENTE','ROLE_UTENTE_PA'=>'ROLE_UTENTE_PA');

		$form = $this->createForm('FaqBundle\Form\FaqType', $faq, $options);

		if ($request->isMethod('POST')) {
			$form->handleRequest($request);

			if ($form->isValid()) {
				try {
					$em->persist($faq);
					$em->flush();
					$this->addFlash('success', "Faq modificata correttamente");
					return $this->redirect($this->generateUrl('visualizza_faq', array("id_faq" => $faq->getId())));
				}catch (\Exception $e) {
					$this->addFlash('error', $e->getMessage());
				}
			}
		}

		$form_params["form"] = $form->createView();
		return $form_params;
    }

	/**
	 * @Route("/elimina-faq/{id_faq}", name="elimina_faq")
     */
    public function eliminaFaqAction($id_faq)
    {
		$this->get('base')->checkCsrf('token');
        $em = $this->getDoctrine()->getManager();
        $faq = $em->getRepository("FaqBundle\Entity\Faq")->findOneById($id_faq);

        if (!$faq) {
            throw $this->createNotFoundException('Unable to find Faq entity.');
        }
		try{
			$em->remove($faq);
			$em->flush();
			$this->addFlash('success', "Faq eliminata correttamente");
		} catch (\Exception $e) {
			$this->addFlash('error', $e->getMessage());
		}

        return $this->redirectToRoute('visualizza_faq');
    }
	
}
