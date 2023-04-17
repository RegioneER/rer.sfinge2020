<?php

namespace RichiesteBundle\GestoriRichiestePA;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;

abstract class Azione
{

    const ROTTA = 'procedura_pa_azione';
    /**
     * @var RouterInterface
     */
    protected $router;

    protected $titolo;

    protected $url;

    protected $attr = array();

    /**
     * @var IRiepilogoRichiesta
     */
    protected $riepilogo;

    protected $nomeAzione;

    public function __construct(RouterInterface $router, IRiepilogoRichiesta $riepilogo, $nome_azione)
    {
        $this->riepilogo = $riepilogo;
        $this->router = $router;
        $this->nomeAzione = $nome_azione;
        $this->url = $this->generateUrl(self::ROTTA,array(
            'id_richiesta' => $this->getRichiesta()->getid(),
            'nome_azione' => $nome_azione, 
        ));
    }

    /**
     * @return bool true -> Visualizza elemento
     */
    abstract public function isVisibile();

    /**
     * @return self
     */
    public function addAttr($attr, $value)
    {
        $this->attr[$attr] = $value;

        return $this;
    }

    /**
     * @return self
     */
    public function removeAttr($attr)
    {
        unset($this->attr[$attr]);

        return $this;
    }

    public function getUrl(){
        return $this->url;
    }
    

    /**
     * @return array
     */
    public function getAttr()
    {
        return $this->attr;
    }

    /**
     * @return string
     */
    public function getTitolo()
    {
        return $this->titolo;
    }

    /**
     * @return self
     */
    public function setTitolo($titolo)
    {
        $this->titolo = $titolo;

        return $this;
    }

    /**
     * @return \RichiesteBundle\Entity\Richiesta
     */
    protected function getRichiesta()
    {
        return $this->riepilogo->getRichiesta();
    }

    public function toVoceMenu()
    {
        return array(
            'label' => $this->titolo,
            'path' => $this->url,
            
        );
    }

    public function getNomeAzione(){
        return $this->nomeAzione;
    }

    /**
     * @return Response Risultato da inviare al client
     */
    abstract public function getRisultatoEsecuzione();

    /**
	 * Returns a RedirectResponse to the given URL.
	 *
	 * @param string $url    The URL to redirect to
	 * @param int    $status The status code to use for the Response
	 *
	 * @return RedirectResponse
	 */
	protected function redirect($url, $status = 302) {
		return new RedirectResponse($url, $status);
    }
    
    /**
	 * Returns a RedirectResponse to the given route with the given parameters.
	 *
	 * @param string $route      The name of the route
	 * @param array  $parameters An array of parameters
	 * @param int    $status     The status code to use for the Response
	 *
	 * @return RedirectResponse
	 */
	protected function redirectToRoute($route, array $parameters = array(), $status = 302) {
		return $this->redirect($this->generateUrl($route, $parameters), $status);
    }
    
    /**
	 * Generates a URL from the given parameters.
	 *
	 * @param string $route         The name of the route
	 * @param mixed  $parameters    An array of parameters
	 * @param int    $referenceType The type of reference (one of the constants in UrlGeneratorInterface)
	 *
	 * @return string The generated URL
	 *
	 * @see UrlGeneratorInterface
	 */
	protected function generateUrl($route, $parameters = array(), $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH) {
		return $this->router->generate($route, $parameters, $referenceType);
	}

}
