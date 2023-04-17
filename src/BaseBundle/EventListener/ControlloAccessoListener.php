<?php

namespace BaseBundle\EventListener;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Annotations\Reader;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @author Antonio Turdo <aturdo@schema31.it>
 */
class ControlloAccessoListener {
    /**
     * @var Reader
     */
    private $reader;
    /**
     * @var Registry
     */
    private $doctrine;
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
        $this->reader = $this->container->get("annotation_reader");
        $this->doctrine = $this->container->get("doctrine");
    }

    /**
     * This event will fire during any controller call
     */
    public function onKernelController(FilterControllerEvent $event) {
        if (!is_array($controller = $event->getController())) {
            return;
        }

        $request = $event->getRequest();

        $em = $this->doctrine->getManager();

        $object = new \ReflectionObject($controller[0]);
        $method = $object->getMethod($controller[1]);

        $methodAnnotations = $this->reader->getMethodAnnotations($method);
        $accessoConsentito = true;
        foreach ($methodAnnotations as $methodAnnotation) {
            if ($methodAnnotation instanceof \BaseBundle\Annotation\ControlloAccesso) {
                $accessoConsentito = false;
                $annotazioneReflection = new \ReflectionObject($methodAnnotation);
                $contesto = $annotazioneReflection->getProperty("contesto")->getValue($methodAnnotation);
                $azione = $annotazioneReflection->getProperty("azione")->getValue($methodAnnotation);
                $classe = $annotazioneReflection->getProperty("classe")->getValue($methodAnnotation);
                $opzioni = $annotazioneReflection->getProperty("opzioni")->getValue($methodAnnotation);

                if (is_null($azione)) {
                    $azione = "all";
                }

                $parametri = [];
                foreach ($opzioni as $db => $url) {
                    if ($request->attributes->has($url)) {
                        $parametri[$db] = $request->attributes->get($url);
                    }
                }

                $risorsa = $em->getRepository($classe)->findOneBy($parametri);

                if ($risorsa) {
                    $oggettoContesto = $this->container->get('contesto')->getContestoRisorsa($risorsa, $contesto);
                    if ($oggettoContesto && $this->container->get('security.authorization_checker')->isGranted($azione, $oggettoContesto)) {
                        $accessoConsentito = true;
                        break;
                    }
                }
            }
        }

        if (!$accessoConsentito) {
            throw new AccessDeniedException();
        }
    }
}
