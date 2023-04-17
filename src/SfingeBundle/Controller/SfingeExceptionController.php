<?php
/**
 * Created by PhpStorm.
 * User: rstronati
 * Date: 22/02/16
 * Time: 09:46
 */

namespace SfingeBundle\Controller;

use Symfony\Bundle\TwigBundle\Controller\ExceptionController;
use Symfony\Component\HttpKernel\Exception\FlattenException;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;

/**
 * 
 */
class SfingeExceptionController extends ExceptionController
{
    public function __construct(\Twig_Environment $twig, $debug)
    {
        parent::__construct($twig,$debug);
    }

    /**
     * @PaginaInfo(titolo="Errore",sottoTitolo="")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Errore")})
     */
    public function showAction(Request $request, FlattenException $exception, DebugLoggerInterface $logger = null)
    {
        //mettere log o quant'altro ci faccia piacere
        return parent::showAction($request,$exception,$logger);
    }
}