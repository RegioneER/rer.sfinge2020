<?php

namespace NotizieBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Menuitem;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use NotizieBundle\Entity\Notizia;
use NotizieBundle\Form\Entity\RicercaNotiziaAdmin;
use NotizieBundle\Form\NotiziaType;

/**
 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Notizie", route="lista_notizie")})
 */
class NotiziaController extends Controller
{

	/**
	 * @Route("/lista-notizie/{sort}/{direction}/{page}", defaults={"sort" = "p.id", "direction" = "asc", "page" = "1"}, name="lista_notizie")
	 * @Template()
	 * @Menuitem(menuAttivo = "listaNotizie")
	 * @PaginaInfo(titolo="Elenco Notizie", sottoTitolo="pagina per la gestione delle notizie")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="elenco notizie")})
	 */
	public function listaNotizieAction() {
		$ricerca = new RicercaNotiziaAdmin();
		$ricerca->setUtenteRicercante($this->getUser());
		$risultato = $this->get("ricerca")->ricerca($ricerca);

		return $this->render('NotizieBundle:Notizia:listaNotizie.html.twig', array('notizie' => $risultato["risultato"], "formRicerca" => $risultato["form_ricerca"], "filtro_attivo" => $risultato["filtro_attivo"] ));
	}

	/**
	 * @Route("/elenco_notizie_pulisci", name="elenco_notizie_admin_pulisci")
	 */
	public function elencoNotiziePulisciAction() {
		$this->get("ricerca")->pulisci(new RicercanotiziaAdmin());
		return $this->redirectToRoute("lista_notizie");
	}

    /**
     * Lists all Notizia entities.
     *
     * @Route("/lista-notizie", name="lista_notizie")
     * @Method("GET")
	 * 
     * @PaginaInfo(titolo="Notizie",sottoTitolo="lista di tutte le notizie pubblicate")
	 * @Menuitem(menuAttivo = "notizie_elenco")

    public function listaNotizieAction()
    {
        $em = $this->getDoctrine()->getManager();

        $notizie = $em->getRepository('NotizieBundle:Notizia')->findAll();

        return $this->render('NotizieBundle:Notizia:listaNotizie.html.twig', array(
            'notizie' => $notizie,
        ));
    }*/

	
	/**
     * @Route("/crea-notizia/", name="crea_notizia")
	 * @Template("NotizieBundle:Notizia:creaNotizia.html.twig")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Nuova")})
	 * @PaginaInfo(titolo="Nuova Notizia",sottoTitolo="pagina per creare una nuova notizia")
	 * @Menuitem(menuAttivo = "notizie_crea")
     */	
	public function creaNotiziaAction(Request $request) {
		$em = $this->getDoctrine()->getManager();
		
		$notizia = new Notizia();
		$options["url_indietro"] = $this->generateUrl("lista_notizie");
		$options["visibilita"] = array('ROLE_UTENTE'=>'ROLE_UTENTE','ROLE_UTENTE_PA'=>'ROLE_UTENTE_PA');
		
		$form = $this->createForm('NotizieBundle\Form\NotiziaType', $notizia, $options);
		
		if ($request->isMethod('POST')) {
			$form->handleRequest($request);
			
			if ($form->isValid()) {		
				try {
					$em->persist($notizia);
					$em->flush();
					$this->addFlash('success', "Notizia creata correttamente");
					return $this->redirect($this->generateUrl('lista_notizie'));
				}catch (\Exception $e) {
					$this->addFlash('error', $e->getMessage());
				}
			}
		}
		
		$form_params["form"] = $form->createView();
		return $form_params;
	}

	/**
     * @Route("/modifica-notizia/{id_notizia}", name="modifica_notizia")
	 * @Template("NotizieBundle:Notizia:modificaNotizia.html.twig")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Modifica notizia")})
	 * @PaginaInfo(titolo="Modifica Notizia",sottoTitolo="")
	 * @Menuitem(menuAttivo = "notizie_elenco")
     */	
    public function modificaNotiziaAction(Request $request, $id_notizia)
    {
		$em = $this->getDoctrine()->getManager();
		
		$notizia = $em->getRepository("NotizieBundle\Entity\Notizia")->findOneById($id_notizia);
		$options["url_indietro"] = $this->generateUrl("lista_notizie");
		$options["visibilita"] = array('ROLE_UTENTE'=>'ROLE_UTENTE','ROLE_UTENTE_PA'=>'ROLE_UTENTE_PA');
		
		$form = $this->createForm('NotizieBundle\Form\NotiziaType', $notizia, $options);
		
		if ($request->isMethod('POST')) {
			$form->handleRequest($request);
			
			if ($form->isValid()) {		
				try {
					$em->persist($notizia);
					$em->flush();
					$this->addFlash('success', "Notizia modificata correttamente");
					return $this->redirect($this->generateUrl('lista_notizie'));
				}catch (\Exception $e) {
					$this->addFlash('error', $e->getMessage());
				}
			}
		}
		
		$form_params["form"] = $form->createView();
		return $form_params;
    }

	/**
	 * @Route("/elimina-notizia/{id_notizia}", name="elimina_notizia")
     */
    public function eliminaNotiziaAction($id_notizia)
    {
		$this->get('base')->checkCsrf('token');
        $em = $this->getDoctrine()->getManager();
        $notizia = $em->getRepository("NotizieBundle\Entity\Notizia")->findOneById($id_notizia);

        if (!$notizia) {
            throw $this->createNotFoundException('Unable to find Notizia entity.');
        }
		try{
			$em->remove($notizia);
			$em->flush();
			$this->addFlash('success', "Notizia eliminata correttamente");
		} catch (\Exception $e) {
			$this->addFlash('error', $e->getMessage());
		}

        return $this->redirectToRoute('lista_notizie');
    }
	
	/**
	 * @Route("/{notizia_id}/testo_notizia_ajax", name="testo_notizia_ajax")
	 */
	public function testoNotiziaAjaxAction($notizia_id) {
		$em = $this->get('doctrine.orm.entity_manager');
		$r = $em->getRepository('NotizieBundle\Entity\Notizia');
		$notizia = $r->find($notizia_id);
		$dati = array();
		$dati['testo'] = is_null($notizia->getTesto()) ? '' : $notizia->getTesto();

		$json = json_encode($dati);

		return new \Symfony\Component\HttpFoundation\JsonResponse($dati);
	}

}
