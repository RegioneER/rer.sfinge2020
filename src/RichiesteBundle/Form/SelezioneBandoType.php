<?php

namespace RichiesteBundle\Form;

use BaseBundle\Form\CommonType;
use RichiesteBundle\GestoriRichieste\GestoreRichiesteBando_98;
use RichiesteBundle\Security\RichiestaLegge14Voter;
use SfingeBundle\Entity\Bando;
use SoggettoBundle\Entity\Azienda;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use SfingeBundle\Entity\BandoRepository;
use BaseBundle\Entity\StatoRichiesta;
use RichiesteBundle\Form\Entity\RicercaBandoManifestazione;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class SelezioneBandoType extends CommonType {

    /** @var AuthorizationCheckerInterface */
    protected $auth;

    /** @var ContainerInterface */
    protected $container;

    /**
     * SelezioneBandoType constructor.
     *
     * @param AuthorizationCheckerInterface $auth
     * @param ContainerInterface            $container
     */
    public function __construct(AuthorizationCheckerInterface $auth, ContainerInterface $container)
    {
        $this->auth = $auth;
        $this->container = $container;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('procedura', self::entity, array(
			'class' => 'SfingeBundle\Entity\Bando',
			'expanded' => true,
			'multiple' => false,
			'required' => true,
			'label' => false,
			'query_builder' => function(BandoRepository $er){
				$ricerca = new RicercaBandoManifestazione();
				$ricerca->setStatoProcedura("IN_CORSO");
				$ricerca->setStato("APERTO");

				$qb = $er->cercaBandiQueryBuilder($ricerca);
				$expr = $qb->expr();
				return $qb->andWhere(
					$expr->orX(
						$expr->isNull('b.numero_massimo_richieste_procedura'),
						$expr->gt('b.numero_massimo_richieste_procedura', // '('.$countQb->getDQL().')')
							"(
								SELECT COUNT(r.id)
								FROM RichiesteBundle:Richiesta r 
								INNER JOIN r.stato stati
								WHERE r.procedura = b
								AND stati.codice in (:stati)
								AND (
									(b.attuale_finestra_temporale_presentazione IS NOT NULL AND r.finestra_temporale = b.attuale_finestra_temporale_presentazione) 
									 OR (b.attuale_finestra_temporale_presentazione IS NULL)
									)
							)"
						)
					)
				)
				->setParameter(":stati", [StatoRichiesta::PRE_PROTOCOLLATA, StatoRichiesta::PRE_INVIATA_PA]);
				;
			},
			'choice_attr' => function (Bando $bando, $key, $idbando) {
			    if($idbando == 98) {
			        /** @var Azienda $soggettoSession */
			        $soggettoSession = $this->container->get('request_stack')->getCurrentRequest()->getSession()->get('_soggetto');
			        $soggetto = $this->container->get('doctrine.orm.entity_manager')->getRepository('SoggettoBundle:Soggetto')->find($soggettoSession->getId());

			        /** @var GestoreRichiesteBando_98 $gestoreRichieste */
                    $gestoreRichieste = $this->container->get("gestore_richieste")->getGestore($bando->getProcedura());
                    /** @var array $richiesteDaSoggetto */
                    $richiesteDaSoggetto = $gestoreRichieste->getRichiesteDaSoggetto($soggetto->getId(), $bando->getProcedura()->getId(), $bando->getProcedura()->getAttualeFinestraTemporalePresentazione());

                    if(count($richiesteDaSoggetto) > 0) {
                        if(!$this->auth->isGranted(RichiestaLegge14Voter::PRESENT, ['richieste' => $richiesteDaSoggetto, 'bando' => $bando])) {
                            return ['disabled' => 'disabled'];
                        }
                    }
                    return [];
                }
                return [];
            },
		));

		$builder->add('pulsanti', self::salva_indietro, array("url" => $options["url_indietro"], 'disabled' => false));
	}

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'readonly' => false
		));
		$resolver->setRequired("url_indietro");
	}

}
