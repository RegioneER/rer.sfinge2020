<?php

namespace BaseBundle\Service;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use SfingeBundle\Entity\UtentePersonaDTO;

/**
 * Servizio che cerca di standardizare la ricerca nel sistema
 * Il servizio ha un metodo ricerca che genera la form di ricerca,effettua la query e ritorna i risultati
 */
class RicercaService extends BaseService {
    /**
     * @var RegistryInterface
     */
    protected $doctrine;
    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * @var PaginatorInterface
     */
    protected $paginator;
    /**
     * @var Request
     */
    protected $request;
    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var IAttributiRicerca
     */
    protected $data;

    /**
     * @var int[]
     */
    protected $listaElementiPerPagina = [
        '5' => 5,
        '10' => 10,
        '25' => 25,
        '50' => 50,
        '75' => 75,
        '100' => 100,
    ];

    public function __construct(ContainerInterface $serviceContainer) {
        parent::__construct($serviceContainer);
        $this->doctrine = $serviceContainer->get("doctrine");
        $this->session = $serviceContainer->get("session");
        $this->paginator = $serviceContainer->get("knp_paginator");
        $this->request = $serviceContainer->get("request");
        $this->formFactory = $serviceContainer->get('form.factory');
    }

    public function pulisci(IAttributiRicerca $attributiRicerca): void {
        $this->session->remove($this->getNomeOggettoInSessione($attributiRicerca));
    }

    protected function getValore($class_name, $valore) {
        $annotation_reader = $this->container->get("annotation_reader");
        $propReflection = new \ReflectionClass($class_name);
        $annotazione_entity = $annotation_reader->getClassAnnotation($propReflection, "Doctrine\ORM\Mapping\Entity");
        if ($annotazione_entity) {
            if (method_exists($valore, "getId") && !\is_null($valore->getId())) {
                $new_valore = $this->doctrine->getRepository(\get_class($valore))->find($valore->getId());
            } else {
                $new_valore = $this->doctrine->getManager()->merge($valore);
            }
            return $new_valore;
        }
        return null;
    }

    public function ricerca(IAttributiRicerca $datiRicerca, array $options = []): array {
        $nomeOggettoInSession = $this->getNomeOggettoInSessione($datiRicerca);
        $typeReflection = new \ReflectionClass($datiRicerca->getType());

        $this->data = $this->session->get($nomeOggettoInSession, $datiRicerca);
        if ($this->session->has($nomeOggettoInSession)) {
            $this->rendeManaged();
        }

        $this->data->mergeFreshData($datiRicerca);
        $numElementi = $this->getNumeroElementiPerPagina($this->data);
        if (!in_array($numElementi, $this->listaElementiPerPagina)) {
            $this->listaElementiPerPagina["$numElementi"] = $numElementi;
            asort($this->listaElementiPerPagina);
        }

        $this->data->setNumeroElementi($numElementi);

        $formTypeString = $typeReflection->getName();
        $formRicerca = $this->formFactory->create($formTypeString, $this->data, $options);

        if ($this->data->mostraNumeroElementi()) {
            $formRicerca->add('numero_elementi', ChoiceType::class, [
                'required' => false,
                'choices' => $this->listaElementiPerPagina,
                'choices_as_values' => true,
                'placeholder' => false,
                'label' => 'Elementi per pagina',
            ]);
        }

        $nomeParametroPagina = $datiRicerca->getNomeParametroPagina();
        $nomeParametroPagina = $nomeParametroPagina ?? "page";
        $numeroPaginaRicerca = $this->request->attributes->getInt($nomeParametroPagina, 1);
        $formRicerca->handleRequest($this->request);
        if ($formRicerca->isSubmitted()) {
            $this->data = $formRicerca->getData();
            $this->session->set($nomeOggettoInSession, $this->data);
            $numeroPaginaRicerca = 1;
        }

        $ricercaVuota = $this->data->isRicercaVuota();

        $filtroAttivo = $this->session->has($nomeOggettoInSession);
        $this->data->setFiltroAttivo($filtroAttivo);

        if ($ricercaVuota && false === $this->data->getConsentiRicercaVuota()) {
            $pagination_object = [];
        } else {
            $repository = $this->doctrine->getRepository($datiRicerca->getNomeRepository());
            $pagination_object = call_user_func([$repository, $datiRicerca->getNomeMetodoRepository()], $this->data);
        }

        $pagination = $this->paginator->paginate(
            $pagination_object,
            $numeroPaginaRicerca,
            $this->data->getNumeroElementi()
        );

        return [
            "risultato" => $pagination,
            "form_ricerca" => $formRicerca->createView(),
            "filtro_attivo" => $filtroAttivo,
        ];
    }

    /** nel caso si ripeschi un oggetto dalla sessione le entity sono tutte detached
     * quindi nel caso si ricerchi usando oggetti di tipo Entity cerco di farne il merge
     * in caso di errore del tipo "Entities passed to the choice field must be managed"
     * controlla che la base class non abbia attributi private
     */
    protected function rendeManaged(): void {
        $modelloReflection = new \ReflectionClass($this->data);
        $currentProps = $modelloReflection->getProperties();
        $parent = $modelloReflection->getParentClass();
        $parentProps = $parent ? $parent->getProperties(): [];
        $props = \array_merge($currentProps, $parentProps);
        
        foreach ($props as $prop) {
            $prop->setAccessible(true);
            $valore = $prop->getValue($this->data);

            if (!\is_object($valore) || $valore instanceof \DateTime) {
                continue;
            }

            if ($valore instanceof Collection) {
                $new_valore = new ArrayCollection();
                foreach ($valore as $val) {
                    $new_valore_elemento = $this->getNewValore($val);
                    if (!\is_null($new_valore)) {
                        $new_valore->add($new_valore_elemento);
                    }
                }
            } else {
                $new_valore = $valore instanceof UtentePersonaDTO ? $valore : $this->getNewValore($valore);
            }
            $prop->setValue($this->data, $new_valore);
        }
    }

    protected function getNewValore($valore) {
        $proxy_class_name = \get_class($valore);
        $class_name = $this->doctrine->getManager()->getClassMetadata($proxy_class_name)->rootEntityName;
        $new_valore = $this->getValore($class_name, $valore);

        return $new_valore;
    }

    private function getNumeroElementiPerPagina($datiRicerca): int {
        $numeroElementiMassimo = $this->container->getParameter('paginatore.max_num_elementi');
        $numeroElementiDefault = $this->container->getParameter('paginatore.num_elementi');
        $numeroElementiPerPagina = $datiRicerca->getNumeroElementiPerPagina();
        $numElementi = $datiRicerca->getNumeroElementi();
        $numElementi = $numElementi ??
                $numeroElementiPerPagina ??
                $numeroElementiDefault ??
                0;

        $numElementi = !$datiRicerca->getBypassMaxElementiPerPagina() &&
            $numElementi >= $numeroElementiMassimo ?
                $numeroElementiMassimo :
                $numElementi;

        return $numElementi;
    }

    private function getNomeOggettoInSessione(IAttributiRicerca $attributiRicerca): string {
        $modelloReflection = new \ReflectionClass($attributiRicerca);
        return $modelloReflection->getName();
    }
}
