<?php

namespace FascicoloBundle\Services;

use Doctrine\ORM\EntityManagerInterface;
use BaseBundle\Service\BaseService;
use Doctrine\Common\Collections\ArrayCollection;
use FascicoloBundle\Entity\IstanzaFrammento;
use FascicoloBundle\Entity\IstanzaPagina;
use Symfony\Component\HttpFoundation\Request;
use BaseBundle\Exception\SfingeException;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Response;
use FascicoloBundle\Entity\IstanzaCampo;
use Doctrine\Common\Collections\Collection;

/**
 * Description of IstanzaFascicolo
 *
 * @author aturdo
 */
class IstanzaFascicolo extends BaseService {

    protected $istanzeCampiIndicizzate;

    /**
     * @var EntityManagerInterface
     */
    protected $em;
    protected $router;
    protected $pagina;

    public function __construct($container) {
        parent::__construct($container);
        $this->istanzeCampiIndicizzate = array();
        $this->router = $this->container->get("router");
        $this->em = $this->container->get("doctrine")->getManager();
        $this->pagina = $this->container->get("pagina");
    }

    /**
     * Ritorna i valori presenti nell'IstanzaFascicolo al path specificato
     *
     * @param \FascicoloBundle\Entity\IstanzaFascicolo $istanzaFascicolo
     * @param string $path
     * @param boolean $chiave
     * @return array
     * @throws \Exception
     */
    public function get(\FascicoloBundle\Entity\IstanzaFascicolo $istanzaFascicolo, string $path, bool $valore = false): ?array {
        $currents = $this->getPathValue($istanzaFascicolo, $path);
        if ($currents->isEmpty()) {
            return null;
        }
        $results = array();
        if ($currents[0] instanceof IstanzaCampo) {
            $results[] = $this->getValoreIstanzeCampi($currents, $valore);
        } else {
            foreach ($currents as $current) {
                $results[] = $this->getValore($current, $valore);
            }
        }

        return $results;
    }

    public function getPathValue(\FascicoloBundle\Entity\IstanzaFascicolo $istanzaFascicolo, string $path): ArrayCollection {
        $aliases = \explode(".", $path);

        $currents = new ArrayCollection(array($istanzaFascicolo));
        foreach ($aliases as $alias) {
            $new_currents = new ArrayCollection();
            foreach ($currents as $current) {
                $temp_currents = $current->getByAlias($alias);
                if ($temp_currents instanceof Collection) {
                    foreach ($temp_currents as $temp_current) {
                        if (!\is_null($temp_current)) {
                            $new_currents->add($temp_current);
                        }
                    }
                } else {
                    if (!\is_null($temp_currents)) {
                        $new_currents->add($temp_currents);
                    }
                }
            }
            $currents = $new_currents;
        }

        return $currents;
    }

    /**
     * Ritorna il valore presente nell'IstanzaFascicolo al path specificato
     *
     * @param \FascicoloBundle\Entity\IstanzaFascicolo $istanzaFascicolo
     * @param string $path
     * @return mixed
     * @throws \Exception
     */
    public function getOne($istanzaFascicolo, $path, $valore = false) {
        $results = $this->get($istanzaFascicolo, $path, $valore) ?: [];

        $n_results = \count($results);

        switch ($n_results) {
            case 0:
                return null;
            case 1:
                return $results[0];
            default:
                throw new \Exception("Sono stati trovati più di un risultato");
        }
    }

    public function getValoreIstanzeCampi($istanzeCampo, $valore) {
        $metodo = $valore ? "getValore" : "getValoreRaw";
        foreach ($istanzeCampo as $istanzaCampo) {
            $multipla = $istanzaCampo->getCampo()->getMultiple();
            if ($multipla) {
                $result[] = $istanzaCampo->$metodo();
            } else {
                $result = $istanzaCampo->$metodo();
            }
        }

        return $result;
    }

    public function getValore($istanza, $valore = false) {
        if ($istanza instanceof IstanzaFrammento) {
            $result = array();
            foreach ($istanza->getIstanzeCampiIndicizzate() as $alias => $istanzeCampo) {
                $result[$alias] = $this->getValoreIstanzeCampi($istanzeCampo, $valore);
            }

            foreach ($istanza->getIstanzeSottoPagine() as $istanzaSottoPagina) {
                if (!$istanzaSottoPagina->isEmpty()) {
                    $maxMolteplicita = $istanzaSottoPagina->getPagina()->getMaxMolteplicita();
                    $multipla = $maxMolteplicita == 0 || $maxMolteplicita > 1;
                    if ($multipla) {
                        $result[$istanzaSottoPagina->getPagina()->getAlias()][] = $this->getValore($istanzaSottoPagina, $valore);
                    } else {
                        $result[$istanzaSottoPagina->getPagina()->getAlias()] = $this->getValore($istanzaSottoPagina, $valore);
                    }
                }
            }
            return $result;
        } elseif ($istanza instanceof IstanzaPagina) {
            $result = array();
            foreach ($istanza->getIstanzeFrammenti() as $istanzaFrammento) {
                if (!$istanzaFrammento->isEmpty()) {
                    $result[$istanzaFrammento->getFrammento()->getAlias()] = $this->getValore($istanzaFrammento, $valore);
                }
            }
            return $result;
        }
    }

    public function elenca($istanzaFascicolo) {
        $istanzapagina = $istanzaFascicolo->getIndice();
        return $this->elencaIstanzaPagina($istanzapagina, $istanzapagina->getPagina()->getAlias());
    }

    public function elencaIstanzaPagina($istanzaPagina, $alias) {
        $istanzeFrammenti = $istanzaPagina->getIstanzeFrammenti();

        $results = array();
        foreach ($istanzeFrammenti as $istanzaFrammento) {
            $results = array_merge($results, $this->elencaIstanzaFrammento($istanzaFrammento, $alias . "." . $istanzaFrammento->getFrammento()->getAlias()));
        }

        return $results;
    }

    public function elencaIstanzaFrammento($istanzaFrammento, $alias) {
        $results = array();
        foreach ($istanzaFrammento->getIstanzeCampi() as $istanzaCampo) {
            $results[] = array($alias . "." . $istanzaCampo->getCampo()->getAlias(), $istanzaCampo->getValore());
        }

        foreach ($istanzaFrammento->getIstanzeSottoPagine() as $istanzaSottoPagina) {
            $results = array_merge($results, $this->elencaIstanzaPagina($istanzaSottoPagina, $alias . "." . $istanzaSottoPagina->getPagina()->getAlias()));
        }

        return $results;
    }

    /**
     * Calcola le sotto pagine di un frammento tenendo conto delle callback presenza
     *
     * @param \FascicoloBundle\Entity\IstanzaFrammento $istanzaFrammento
     * @return array
     */
    public function sottoPagineFrammento($istanzaFrammento) {
        $frammento = $istanzaFrammento->getFrammento();
        $pathFrammento = $frammento->getPath();
        $istanzaFascicolo = $istanzaFrammento->getIstanzaPagina()->getIstanzaFascicolo();
        $fascicolo = $istanzaFascicolo->getFascicolo();
        $alias_fascicolo = $fascicolo->getIndice()->getAlias();
        $sottoPagine = array();

        foreach ($frammento->getSottoPagine() as $sottoPagina) {
            $callbackPresenzaPagina = $sottoPagina->getCallbackPresenza();
            if (!is_null($callbackPresenzaPagina)) {
                if (!$this->container->has("fascicolo.istanza." . $alias_fascicolo)) {
                    /**
                     * @todo: loggare errore
                     */
                    continue;
                }
                $servizioIstanzaFascicolo = $this->container->get("fascicolo.istanza." . $alias_fascicolo);
                if (method_exists($servizioIstanzaFascicolo, $callbackPresenzaPagina)) {
                    $presente = $servizioIstanzaFascicolo->$callbackPresenzaPagina($istanzaFascicolo, $pathFrammento . "." . $sottoPagina->getAlias());
                    if (!$presente) {
                        continue;
                    }
                }
            }

            $sottoPagine[] = $sottoPagina;
        }

        return $sottoPagine;
    }

    /**
     * Calcola i campi di un frammento tenendo conto delle callback presenza
     *
     * Attualmente non utilizzato, da controllare
     *
     * @param \FascicoloBundle\Entity\IstanzaFrammento $istanzaFrammento
     * @param boolean $evidenziato
     * @return array
     */
    public function campiFrammento($istanzaFrammento, $evidenziato = false) {
        $frammento = $istanzaFrammento->getFrammento();
        $pathFrammento = $frammento->getPath();
        $istanzaFascicolo = $istanzaFrammento->getIstanzaPagina()->getIstanzaFascicolo();
        $fascicolo = $istanzaFascicolo->getFascicolo();
        $alias_fascicolo = $fascicolo->getIndice()->getAlias();
        $campi = array();

        foreach ($frammento->getSottoPagine() as $sottoPagina) {
            $callbackPresenzaPagina = $sottoPagina->getCallbackPresenza();
            if (!is_null($callbackPresenzaPagina)) {
                if (!$this->container->has("fascicolo.istanza." . $alias_fascicolo)) {
                    /**
                     * @todo: loggare errore
                     */
                    continue;
                }
                $servizioIstanzaFascicolo = $this->container->get("fascicolo.istanza." . $alias_fascicolo);
                if (method_exists($servizioIstanzaFascicolo, $callbackPresenzaPagina)) {
                    $presente = $servizioIstanzaFascicolo->$callbackPresenzaPagina($istanzaFascicolo, $pathFrammento . "." . $sottoPagina->getAlias());
                    if (!$presente) {
                        continue;
                    }
                }
            }

            $campi[] = $sottoPagina;
        }

        return $campi;
    }

    /**
     * Valida un oggetto IstanzaPagina tenendo conto della molteplicità richiesta
     *
     * @param \FascicoloBundle\Entity\IstanzaPagina $istanzaPagina
     * @return boolean
     */
    public function validaMolteplicitaIstanzaPagina($istanzaPagina, $minore = true) {
        $pagina = $istanzaPagina->getPagina();
        $istanzaFrammentoContenitore = $istanzaPagina->getIstanzaFrammentoContenitore();

        if (!is_null($istanzaFrammentoContenitore)) {
            $istanzePagina = $istanzaFrammentoContenitore->getIstanzeSottoPagineByAlias($pagina->getAlias());

            if ($minore && $pagina->getMinMolteplicita() != 0 && $istanzePagina->count() < $pagina->getMinMolteplicita()) {
                return false;
            }

            if ($pagina->getMaxMolteplicita() != 0 && $istanzePagina->count() > $pagina->getMaxMolteplicita()) {
                return false;
            }
        }

        return true;
    }

    public function isPaginaPresente($pagina, $istanzaFascicolo) {
        $alias_fascicolo = $istanzaFascicolo->getFascicolo()->getIndice()->getAlias();
        $callbackPresenzaPagina = $pagina->getCallbackPresenza();
        if (!is_null($callbackPresenzaPagina)) {
            if (!$this->container->has("fascicolo.istanza." . $alias_fascicolo)) {
                /**
                 * @todo: loggare errore
                 */
                throw new \Exception("Servizio fascicolo non trovato");
            }

            $servizioIstanzaFascicolo = $this->container->get("fascicolo.istanza." . $alias_fascicolo);
            if (\method_exists($servizioIstanzaFascicolo, $callbackPresenzaPagina)) {
                return $servizioIstanzaFascicolo->$callbackPresenzaPagina($istanzaFascicolo, $pagina->getPath());
            } else {
                throw new \Exception("Servizio fascicolo non valido");
            }
        }

        return true;
    }

    /**
     * Valida un oggetto IstanzaPagina tenendo conto dei campi required,
     * dei vincoli e delle callback.
     *
     * @param \FascicoloBundle\Entity\IstanzaPagina $istanzaPagina
     * @return boolean
     */
    public function validaIstanzaPagina($istanzaPagina) {
        $esito = new \FascicoloBundle\Entity\EsitoValidazione();
        $esito->setEsito(true);
        $em = $this->container->get("doctrine")->getManager();
        $pagina = $istanzaPagina->getPagina();
        $alias_fascicolo = $istanzaPagina->getPagina()->getFascicolo(true)->getIndice()->getAlias();
        $istanzaFascicolo = $istanzaPagina->getIstanzaFascicolo();

        if (!$this->validaMolteplicitaIstanzaPagina($istanzaPagina)) {
            $esito->setEsito(false);
            $esito->addMessaggioSezione("Errore nel numero di volte in cui compare questa pagina");
            return $esito;
        }

        $path = $pagina->getPath();

        // gestione di un eventuale callback di presenza della pagina
        if (!$this->isPaginaPresente($pagina, $istanzaFascicolo)) {
            return $esito;
        }

        foreach ($pagina->getFrammenti() as $frammento) {
            $pathFrammento = $path . "." . $frammento->getAlias();
            $istanzeFrammenti = $em->getRepository("FascicoloBundle\Entity\IstanzaFrammento")->findBy(array("frammento" => $frammento->getId(), "istanzaPagina" => $istanzaPagina->getId()));

            // gestione di un eventuale callback di presenza del frammento
            $callbackPresenzaFrammento = $frammento->getCallbackPresenza();
            if (!is_null($callbackPresenzaFrammento)) {
                if (!$this->container->has("fascicolo.istanza." . $alias_fascicolo)) {
                    /**
                     * @todo: loggare errore
                     */
                    $esito->setEsito(false);
                    $esito->addMessaggioSezione("Errore nella validazione della pagina. Contattare l'assistenza");
                    $esito->addMessaggio("Errore nella validazione della pagina. Contattare l'assistenza");
                    return $esito;
                }
                $servizioIstanzaFascicolo = $this->container->get("fascicolo.istanza." . $alias_fascicolo);
                if (method_exists($servizioIstanzaFascicolo, $callbackPresenzaFrammento)) {
                    $presente = $servizioIstanzaFascicolo->$callbackPresenzaFrammento($istanzaFascicolo, $pathFrammento);
                    if (!$presente) {
                        continue;
                    }
                }
            }

            if ($istanzeFrammenti) {
                $istanzaFrammento = $istanzeFrammenti[0];
                $frammento = $istanzaFrammento->getFrammento();

                foreach ($frammento->getCampi() as $campo) {
                    $istanzeCampi = $em->getRepository("FascicoloBundle\Entity\IstanzaCampo")->findBy(array("campo" => $campo->getId(), "istanzaFrammento" => $istanzaFrammento->getId()));

                    // gestione di un eventuale callback di presenza del campo
                    $callbackPresenzaCampo = $campo->getCallbackPresenza();
                    if (!is_null($callbackPresenzaCampo)) {
                        if (!$this->container->has("fascicolo.istanza." . $alias_fascicolo)) {
                            /**
                             * @todo: loggare errore
                             */
                            $esito->setEsito(false);
                            $esito->addMessaggioSezione("Errore nella validazione della pagina. Contattare l'assistenza");
                            $esito->addMessaggio("Errore nella validazione della pagina. Contattare l'assistenza");
                            return $esito;
                        }
                        $servizioIstanzaFascicolo = $this->container->get("fascicolo.istanza." . $alias_fascicolo);
                        if (method_exists($servizioIstanzaFascicolo, $callbackPresenzaCampo)) {
                            $presente = $servizioIstanzaFascicolo->$callbackPresenzaCampo($istanzaFascicolo, $pathFrammento . "." . $campo->getAlias());
                            if (!$presente) {
                                continue;
                            }
                        }
                    }

                    if ($istanzeCampi) {
                        $servizio = $this->container->get("fascicolo.tipo." . $campo->getTipoCampo()->getCodice());
                        $errors = $servizio->validate($campo, $istanzeCampi, true);
                        if (count($errors) > 0) {
                            $esito->setEsito(false);
                            $esito->addMessaggioSezione("Alcuni campi non sono validi o completi");
                            foreach ($errors as $error) {
                                $esito->addMessaggio($error->getMessage());
                            }
                            return $esito;
                        }
                    } else if ($campo->getRequired()) {
                        $esito->setEsito(false);
                        $esito->addMessaggioSezione("Alcuni campi non sono validi o completi");
                        $esito->addMessaggio("Campo {$campo->getLabel()} obbligatorio");
                        return $esito;
                    }
                }

                foreach ($frammento->getSottoPagine() as $sottoPagina) {
                    if ($this->isPaginaPresente($sottoPagina, $istanzaFascicolo)) {
                        $istanzeSottoPagine = $istanzaFrammento->getIstanzeSottoPagineByAlias($sottoPagina->getAlias());

                        if ($sottoPagina->getMinMolteplicita() != 0 && $istanzeSottoPagine->count() < $sottoPagina->getMinMolteplicita()) {
                            $esito->setEsito(false);
                            $esito->addMessaggioSezione("Dati non completi o non validi");
                            $esito->addMessaggio("Dati non completi o non validi");
                            return $esito;
                        }

                        if ($sottoPagina->getMaxMolteplicita() != 0 && $istanzeSottoPagine->count() > $sottoPagina->getMaxMolteplicita()) {
                            $esito->setEsito(false);
                            $esito->addMessaggioSezione("Dati non completi o non validi");
                            $esito->addMessaggio("Dati non completi o non validi");
                            return $esito;
                        }
                    }
                }

                foreach ($istanzaFrammento->getIstanzeSottoPagine() as $istanzaSottoPagina) {
                    $esito_sottopagina = $this->validaIstanzaPagina($istanzaSottoPagina);
                    if (!$esito_sottopagina->getEsito()) {
                        $esito->setEsito(false);
                        $esito->addMessaggioSezione("Sono presenti degli errori nelle sottopagine");
                        $esito->addMessaggioSezione("Sono presenti degli errori nelle sottopagine");
                        return $esito;
                    }
                }
            } else {
                /**
                 * @todo: controllare che il frammento sia required (campi e sottopagine)
                 */
                $esito->setEsito(false);
                $esito->addMessaggioSezione("Errore nella validazione della pagina. Contattare l'assistenza");
                $esito->addMessaggio("Errore nella validazione della pagina. Contattare l'assistenza");
                return $esito;
            }
        }

        $callbackPagina = $pagina->getCallback();
        if (!is_null($callbackPagina)) {
            try {
                $servizioIstanzaFascicolo = $this->container->get("fascicolo.istanza." . $alias_fascicolo);
                if (method_exists($servizioIstanzaFascicolo, $callbackPagina)) {
                    $errors = $servizioIstanzaFascicolo->$callbackPagina($istanzaPagina);
                    if (count($errors) > 0) {
                        $esito->setEsito(false);
                        foreach ($errors as $error) {
                            $esito->addMessaggio($error->getMessage());
                            $esito->addMessaggioSezione($error->getMessage());
                        }
                    }
                }
            } catch (\Exception $ex) {
                $esito->setEsito(false);
                $esito->addMessaggioSezione("Errore nella validazione della pagina. Contattare l'assistenza");
                $esito->addMessaggio("Errore nella validazione della pagina. Contattare l'assistenza");
                return $esito;
            }
        }

        return $esito;
    }

    /**
     * Rimuove i campi vuoti da un frammento
     *
     * @param \FascicoloBundle\Entity\IstanzaFrammento $istanzaFrammento
     * @param array $istanzeCampi
     */
    public function filtraIstanzeCampo($istanzaFrammento, $istanzeCampi) {
        foreach ($istanzeCampi as $istanzaCampo) {
            if ((is_null($istanzaCampo->getValore()) || $istanzaCampo->getValore() == "")) {
                $istanzaFrammento->rimuoviIstanzaCampo($istanzaCampo);
            }
        }
    }

    /**
     * Calcola gli oggetti della classe IstanzaCampo da salvare a db
     *
     * @param \FascicoloBundle\Entity\IstanzaFrammento $istanzaFrammento
     * @param \FascicoloBundle\Entity\Campo $campo
     * @param array|mixed $valori
     * @return array
     */
    public function calcolaIstanzeCampo($istanzaFrammento, $campo, $valori) {
        // numero delle istanze da salvare
        $num_valori = is_array($valori) ? count($valori) : 1;

        // indicizzo le istanze già salvate in base al codice
        if (!isset($this->istanzeCampiIndicizzate[$istanzaFrammento->getId()])) {
            $this->istanzeCampiIndicizzate[$istanzaFrammento->getId()] = array();
            foreach ($istanzaFrammento->getIstanzeCampi() as $istanzaCampo) {
                $alias_campo_ciclo = $istanzaCampo->getCampo()->getAlias();
                if (!isset($this->istanzeCampiIndicizzate[$istanzaFrammento->getId()][$alias_campo_ciclo])) {
                    $this->istanzeCampiIndicizzate[$istanzaFrammento->getId()][$alias_campo_ciclo] = array();
                }

                $this->istanzeCampiIndicizzate[$istanzaFrammento->getId()][$alias_campo_ciclo][] = $istanzaCampo;
            }
        }

        $istanzeCampo = isset($this->istanzeCampiIndicizzate[$istanzaFrammento->getId()][$campo->getAlias()]) ? $this->istanzeCampiIndicizzate[$istanzaFrammento->getId()][$campo->getAlias()] : array();

        // elimino eventuali istanze superflue
        if (count($istanzeCampo) > $num_valori) {
            for ($i = $num_valori; $i < count($istanzeCampo); $i++) {
                $istanzaFrammento->rimuoviIstanzaCampo($istanzeCampo[$i]);
                unset($istanzeCampo[$i]);
            }
        }

        if (!is_array($valori)) {
            $valori = array($valori);
        }

        if ($num_valori > 0) {
            foreach ($valori as $chiave => $valore) {
                // recupero l'istanza del campo o ne creo una nuovo
                if (!isset($istanzeCampo[$chiave])) {
                    $istanzeCampo[$chiave] = new IstanzaCampo();
                    $istanzeCampo[$chiave]->setCampo($campo);
                    $istanzaFrammento->aggiungiIstanzaCampo($istanzeCampo[$chiave]);
                }

                $istanzeCampo[$chiave]->setValore($valore);

                $servizioTipoCampo = $this->container->get("fascicolo.tipo." . $campo->getTipoCampo()->getCodice());
                $istanzeCampo[$chiave]->setValoreRaw($servizioTipoCampo->calcolaValoreRaw($campo, $valore));
            }
        }

        return $istanzeCampo;
    }

    /**
     * Gestisce la visualizzazione di un oggetto IstanzaPagina,
     * costruendo il form, se necessario, e salvando opportunamente i dati
     * inseriti dall'utente.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param integer $id_istanza_pagina
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function istanzaPagina(Request $request, $id_istanza_pagina, $id_pagina, $id_istanza_frammento, $azione, $id_richiesta = null, $contesto = null) {
        if ($id_istanza_pagina != "-") {
            $istanzaPagina = $this->em->getRepository("FascicoloBundle\Entity\IstanzaPagina")->find($id_istanza_pagina);
            /**
             * @todo: aggiungere controlli
             */
            if ($azione == "elimina") {
                $this->container->get('base')->checkCsrf('token');
                $this->em->remove($istanzaPagina);

                // salvo i dati
                try {
                    $this->em->flush();
                    $this->addFlash('success', "Eliminazione effettuata correttamente");
                    return $this->redirect($this->generateUrl($this->getRouteIstanzaPagina(), array("id_istanza_pagina" => $istanzaPagina->getIstanzaPaginaContenitore()->getId())));
                } catch (\Exception $e) {
                    /**
                     * @todo: redirect errore
                     */
                }
            }
        } else if ($id_pagina != "-" && $id_istanza_frammento != "-") {
            $istanzaPagina = $this->em->getRepository("FascicoloBundle\Entity\IstanzaPagina")->findOneBy(array("istanzaFrammentoContenitore" => $id_istanza_frammento, "pagina" => $id_pagina));

            if (is_null($istanzaPagina) || $azione == "aggiungi") {
                $istanzaFrammento = $this->em->getRepository("FascicoloBundle\Entity\IstanzaFrammento")->find($id_istanza_frammento);
                $pagina = $this->em->getRepository("FascicoloBundle\Entity\Pagina")->find($id_pagina);

                /**
                 * @todo: aggiungere controlli
                 */
                $istanzaPagina = $this->creaIstanzaPagina($pagina, $istanzaFrammento);
                $this->em->persist($istanzaPagina);
            }
        }



        $istanzaFrammentoContenitore = $istanzaPagina->getIstanzaFrammentoContenitore();
        $pagina = $istanzaPagina->getPagina();
        $istanzaFascicolo = $istanzaPagina->getIstanzaFascicolo();
        $fascicolo = $istanzaFascicolo->getFascicolo();
        $alias_fascicolo = $fascicolo->getIndice()->getAlias();
        $path_pagina = $pagina->getPath();

        // dispatching dell'evento di accesso all'istanza del fascicolo
        $dispatcher = $this->container->get('event_dispatcher');
        $event = new \FascicoloBundle\Event\IstanzaFascicoloEvent($istanzaFascicolo);
        $response = $dispatcher->dispatch(\FascicoloBundle\Event\FascicoloEvents::FASCICOLO_ISTANZA, $event);

        // setting titolo e breadcrumb
        $this->pagina->setTitolo($pagina->getTitolo());

        $breadcrumb = array();
        $breadcrumb[] = array("titolo" => $pagina->getTitolo());
        $cursorePagina = $istanzaPagina;
        while ($cursorePagina->getIstanzaFrammentoContenitore()) {
            $cursorePagina = $cursorePagina->getIstanzaFrammentoContenitore()->getIstanzaPagina();
            $breadcrumb[] = array("titolo" => $cursorePagina->getPagina()->getTitolo(), "url" => $this->router->generate($this->getRouteIstanzaPagina(), array('id_istanza_pagina' => $cursorePagina->getId())));
        }

        foreach (array_reverse($breadcrumb) as $breadcrumb_ordinato) {
            if (isset($breadcrumb_ordinato["url"])) {
                $this->pagina->aggiungiElementoBreadcrumb($breadcrumb_ordinato["titolo"], $breadcrumb_ordinato["url"]);
            } else {
                $this->pagina->aggiungiElementoBreadcrumb($breadcrumb_ordinato["titolo"]);
            }
        }

        if ($azione == 'visualizza') {
            return new Response($this->renderView('FascicoloBundle:Default:visualizzaIstanzaPagina.html.twig', array(
                    "istanzaPagina" => $istanzaPagina,
                    "servizio" => $this,
                    "template" => $this->getTemplate($fascicolo),
                    "alias_fascicolo" => $alias_fascicolo,
                    "path_pagina" => $path_pagina,
                    "istanza_fascicolo" =>$istanzaFascicolo,
                )
            )
            );
        }

        // indicizzo le istanze dei frammenti in base al codice
        $dati_frammenti = array();
        foreach ($istanzaPagina->getIstanzeFrammenti() as $istanzaFrammento) {
            $dati_frammenti[$istanzaFrammento->getFrammento()->getAlias()] = $istanzaFrammento;
        }

        $form_builder = $this->createFormBuilder();

        $form_esistente = false;

        // per ciascun frammento previsto nella pagina
        foreach ($pagina->getFrammenti() as $frammento) {
            $callbackPresenzaFrammento = $frammento->getCallbackPresenza();

            if (!is_null($callbackPresenzaFrammento)) {
                if (!$this->container->has("fascicolo.istanza." . $alias_fascicolo)) {
                    /**
                     * @todo: loggare errore
                     */
                    return;
                }

                $servizioIstanzaFascicolo = $this->container->get("fascicolo.istanza." . $alias_fascicolo);
                if (method_exists($servizioIstanzaFascicolo, $callbackPresenzaFrammento)) {
                    $presente = $servizioIstanzaFascicolo->$callbackPresenzaFrammento($istanzaFascicolo, $path_pagina . "." . $frammento->getAlias());
                    if (!$presente) {
                        continue;
                    }
                }
            }

            // recupero l'istanza del frammento o ne creo una nuova
            if (isset($dati_frammenti[$frammento->getAlias()])) {
                $istanzaFrammento = $dati_frammenti[$frammento->getAlias()];
            } else {
                $istanzaFrammento = new IstanzaFrammento();
                $istanzaFrammento->setFrammento($frammento);
                $istanzaPagina->aggiungiIstanzaFrammento($istanzaFrammento);
                $dati_frammenti[$frammento->getAlias()] = $istanzaFrammento;
            }

            // aggiungo il frammento al form passando anche gli eventuali dati
            $options = array("attr" => array());
            $options["attr"]["tipo_frammento"] = $frammento->getTipoFrammento()->getCodice();
            $options["attr"]["istanza_frammento"] = $istanzaFrammento;
            $options["attr"]["container"] = $this->container;
            $options["attr"]["frammento"] = $frammento;
            $options["attr"]["dati"] = $istanzaFrammento;
            $options["attr"]["istanza_fascicolo"] = $istanzaFascicolo;
            $options["attr"]["path"] = $path_pagina . "." . $frammento->getAlias();
            $form_builder->add($frammento->getAlias(), "FascicoloBundle\Form\Type\InstanzaFrammentoType", $options);

            if (count($form_builder->get($frammento->getAlias())->all())) {
                $form_esistente = true;
            }
        }

        if ($form_esistente) {
            // aggiungo il bottone di salvataggio
            $form_builder->add('save', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', array(
                'attr' => array('class' => 'save'),
                'label' => 'Salva'
            ));
        }

        if (!$form_esistente || (!is_null($istanzaFrammentoContenitore) && $istanzaFrammentoContenitore->getFrammento()->getTipoFrammento()->getCodice() != "tabella")) {
            // salvo istanze se non contiene un form
            try {
                $this->em->flush();
            } catch (\Exception $e) {
                $errore = 1;
            }
        }

        $form = $form_builder->getForm();

        if ($request->isMethod('GET')) {
            if ($pagina->getCallback()) {
                try {
                    $servizioIstanzaFascicolo = $this->container->get("fascicolo.istanza." . $fascicolo->getIndice()->getAlias());
                    $violations = $servizioIstanzaFascicolo->{$pagina->getCallback()}($istanzaPagina);

                    foreach ($violations as $key => $violation) {
                        $currentForm = $form;
                        if ($violation->getPropertyPath()) {
                            $paths = explode(".", $violation->getPropertyPath());
                            $found = true;
                            foreach ($paths as $path) {
                                if ($currentForm->has($path)) {
                                    $currentForm = $currentForm->get($path);
                                } else {
                                    $found = false;
                                    break;
                                }
                            }
                            if ($found && $currentForm->has("valore")) {
                                $currentForm = $currentForm->get("valore");
                            } else {
                                $currentForm = $form;
                            }
                        }
                        $currentForm->addError(new FormError($violation->getMessage()));
                    }
                } catch (\Exception $e) {
                    $this->container->get("logger")->error($e->getMessage());
                    throw new SfingeException("Si è verificato un errore imprevisto. Si prega di contattare l'assistenza");
                }
            }
        }

        if ($request->isMethod("POST")) {

            $form->handleRequest($request);

            $form_request = $request->request->get('form');
            // ciclo tutti i pezzi del form nella request
            foreach ($pagina->getFrammenti() as $frammento) {
                if (!$frammento->getTipoFrammento()->getCampi()) {
                    continue;
                }

                $alias_frammento = $frammento->getAlias();
                $frammento_request = isset($form_request[$alias_frammento]) ? $form_request[$alias_frammento] : array($alias_frammento => array());

                // se non è un array, non è un frammento
                if (!is_array($frammento_request)) {
                    continue;
                }

                // recupero l'istanza del frammento o ne creo una nuova
                if (isset($dati_frammenti[$alias_frammento])) {
                    $istanzaFrammento = $dati_frammenti[$alias_frammento];
                } else {
                    $istanzaFrammento = new IstanzaFrammento();
                    $istanzaFrammento->setFrammento($frammento);
                    $istanzaPagina->aggiungiIstanzaFrammento($istanzaFrammento);
                }

                foreach ($frammento->getCampi() as $campo) {

                    $alias_campo = $campo->getAlias();
                    if (!$form->get($alias_frammento)->has($alias_campo)) {
                        continue;
                    }
                    $campo_request = isset($frammento_request[$alias_campo]) && isset($frammento_request[$alias_campo]['valore']) ? $frammento_request[$alias_campo] : array("valore" => "");
                    $istanzeCampo = $this->calcolaIstanzeCampo($istanzaFrammento, $campo, $campo_request['valore']);

                    $servizio = $this->container->get("fascicolo.tipo." . $campo->getTipoCampo()->getCodice());

                    $errori = $servizio->validate($campo, $istanzeCampo, true);

                    if (!is_null($errori) && $errori->count() > 0) {
                        $messages = array();

                        foreach ($form->get($alias_frammento)->get($alias_campo)->get("valore")->getErrors() as $form_error) {
                            $messages[] = $form_error->getMessage();
                        }

                        foreach ($errori as $errore) {
                            if (!in_array($errore->getMessage(), $messages)) {
                                $form->get($alias_frammento)->get($alias_campo)->get("valore")->addError(new FormError($errore->getMessage()));
                                $messages[] = $errore->getMessage();
                            }
                        }
                    }

                    $this->filtraIstanzeCampo($istanzaFrammento, $istanzeCampo);
                }
            }

            if ($pagina->getCallback()) {
                try {
                    $servizioIstanzaFascicolo = $this->container->get("fascicolo.istanza." . $fascicolo->getIndice()->getAlias());
                    $violations = $servizioIstanzaFascicolo->{$pagina->getCallback()}($istanzaPagina);

                    foreach ($violations as $key => $violation) {
                        $currentForm = $form;
                        if ($violation->getPropertyPath()) {
                            $paths = explode(".", $violation->getPropertyPath());
                            $found = true;
                            foreach ($paths as $path) {
                                if ($currentForm->has($path)) {
                                    $currentForm = $currentForm->get($path);
                                } else {
                                    $found = false;
                                    break;
                                }
                            }
                            if ($found && $currentForm->has("valore")) {
                                $currentForm = $currentForm->get("valore");
                            } else {
                                $currentForm = $form;
                            }
                        }

                        $currentForm->addError(new FormError($violation->getMessage()));
                    }
                } catch (\Exception $e) {
                    $this->container->get("logger")->error($e->getMessage());
                    throw new SfingeException("Si è verificato un errore imprevisto. Si prega di contattare l'assistenza");
                }
            }

            if (!$this->validaMolteplicitaIstanzaPagina($istanzaPagina, false)) {
                $form->addError(new FormError("Non puoi aggiungere un'altra riga"));
            }

            if ($form->isValid()) {
                // salvo i dati
                try {
                    if (!$istanzaPagina->isEmpty()) {
                        $this->em->flush();
                        $this->addFlash('success', "Modifiche salvate correttamente");
                    }
                    $istanzaPaginaToRedirect = is_null($istanzaPagina->getIstanzaPaginaContenitore()) ? $istanzaPagina : $istanzaPagina->getIstanzaPaginaContenitore();
                    if ($id_richiesta) {
                        return $this->redirect($this->router->generate($this->getRouteIstanzaPagina(), array("id_istanza_pagina" => $istanzaPaginaToRedirect->getId(), 'id_richiesta' => $id_richiesta)));
                    } else {
                        return $this->redirect($this->router->generate($this->getRouteIstanzaPagina(), array("id_istanza_pagina" => $istanzaPaginaToRedirect->getId())));
                    }
                } catch (\Exception $e) {
                    $this->addFlash('error', "Si è verificato un errore nel salvataggio delle modifiche");
                }
            } else {
                if ($istanzaPagina->getPagina()->getFrammentoContenitore() && $istanzaPagina->getPagina()->getFrammentoContenitore()->getTipoFrammento()->getCodice() != 'tabella') {
                    $this->em->flush();
                }
                $haErrori = false;
                foreach ($form->getErrors() as $error) {
                    $this->addFlash('error', $error->getMessage());
                    $haErrori = true;
                }

                if (!$haErrori) {
                    $this->addFlash('error', 'Dati non completi o non validi');
                }
            }
        }

        if ($request->isMethod('GET')) {
            foreach ($form->getErrors() as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }

        $template = $this->getTemplate($fascicolo);

        // Aggiungiamo il contesto perché lato pagamenti distrugge RSI e ricicliamo l'id_richiesta per mettere l'id_pagamento
        if ($contesto == 'PAGAMENTO') {
            $routeIndietro = $this->generateUrl("dettaglio_pagamento", ["id_pagamento" => $id_richiesta]);
        } elseif ($contesto == 'PROPONENTE') {
            if (is_null($pagina->getFrammentoContenitore())) {
                $routeIndietro = $this->generateUrl("elenco_proponenti", ["id_richiesta" => $id_richiesta]);
            } else {
                $istanzaPaginaToRedirect = is_null($istanzaPagina->getIstanzaPaginaContenitore()) ? $istanzaPagina : $istanzaPagina->getIstanzaPaginaContenitore();
                $routeIndietro = $this->router->generate($this->getRouteIstanzaPagina(), ["id_istanza_pagina" => $istanzaPaginaToRedirect->getId(), 'id_richiesta' => $id_richiesta]);
            }
        } else {
            if (is_null($pagina->getFrammentoContenitore())) {
                $routeIndietro = $this->generateUrl("dettaglio_richiesta", ["id_richiesta" => $id_richiesta]);
            } else {
                $istanzaPaginaToRedirect = is_null($istanzaPagina->getIstanzaPaginaContenitore()) ? $istanzaPagina : $istanzaPagina->getIstanzaPaginaContenitore();
                $routeIndietro = $this->router->generate($this->getRouteIstanzaPagina(), ["id_istanza_pagina" => $istanzaPaginaToRedirect->getId(), 'id_richiesta' => $id_richiesta]);
            }
        }

        return $this->render('FascicoloBundle:Default:istanzaPagina.html.twig', ["form" => $form->createView(), "servizio" => $this, "template" => $template, "routeIndietro" => $routeIndietro]);
    }

    /**
     * Ritorna il nome del file twig da estendere per la visualizzazione di una
     * IstanzaPagina, tenendo conto dell'eventuale configurazione nel fascicolo.
     *
     * @param \FascicoloBundle\Entity\Fascicolo $fascicolo
     * @return string
     */
    public function getTemplate($fascicolo) {
        $template = $fascicolo->getTemplate();
        if (is_null($template)) {
            return "::base.html.twig";
        } else {
            return $template;
        }
    }

    /**
     * Ritorna il nome della regola di routing da utilizzare per linkare la
     * pagina di visualizzazione di IstanzaPagina, tenendo conto di una
     * eventuale configurazione salvata nella variabile fascicolo.route_istanza_pagina
     * in sessione.
     *
     * @return string
     */
    public function getRouteIstanzaPagina() {
        $sessione = $this->container->get("session");

        if ($sessione->has("fascicolo.route_istanza_pagina")) {
            return $sessione->get("fascicolo.route_istanza_pagina");
        } else {
            return "istanza_pagina";
        }
    }

    public function creaIstanzaPagina($pagina, $istanza_frammento_contenitore): IstanzaPagina {
        $istanza_pagina = new IstanzaPagina();
        $istanza_pagina->setIstanzaFrammentoContenitore($istanza_frammento_contenitore);
        $istanza_pagina->setPagina($pagina);
        $istanza_frammento_contenitore->aggiungiIstanzaSottoPagina($istanza_pagina);

        return $istanza_pagina;
    }

    public function getDescriptive($istanzaFascicolo, $path, $valore = false): string {
        $valori = $this->get($istanzaFascicolo, $path, $valore);
        if (\is_null($valori)) {
            return "";
        } else {
            return \trim(\implode(" / ", $valori));
        }
    }

    /**
     * Ritorna i valori presenti nell'IstanzaFascicolo al path specificato
     * con l'id istanza pagina al posto dell'incrementale da 0 in su.
     *
     * @param \FascicoloBundle\Entity\IstanzaFascicolo $istanzaFascicolo
     * @param string $path
     * @param boolean $chiave
     * @return array
     * @throws \Exception
     */
    public function getWithIstanzaPaginaId(\FascicoloBundle\Entity\IstanzaFascicolo $istanzaFascicolo, string $path, bool $valore = false): ?array {
        $currents = $this->getPathValue($istanzaFascicolo, $path);

        if ($currents->isEmpty()) {
            return null;
        }
        $results = array();
        if ($currents[0] instanceof IstanzaCampo) {
            $results[$currents[0]->getId()] = $this->getValoreIstanzeCampi($currents, $valore);
        } else {
            foreach ($currents as $current) {
                $results[$current->getId()] = $this->getValore($current, $valore);
            }
        }

        return $results;
    }
}
