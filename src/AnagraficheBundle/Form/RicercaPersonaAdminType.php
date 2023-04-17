<?php

namespace AnagraficheBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RicercaPersonaAdminType extends CommonType {
    private $ruoli;

    /**
     * RicercaPersonaAdminType constructor.
     */
    public function __construct() {
        $this->ruoli = [
            "ROLE_UTENTE" => "ROLE_UTENTE",
            "ROLE_UTENTE_PA" => "ROLE_UTENTE_PA",
            "ROLE_MANAGER_PA" => "ROLE_MANAGER_PA",
            "ROLE_ADMIN_PA" => "ROLE_ADMIN_PA",
        ];
    }

    public function getName() {
        return "ricerca_persona_admin";
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $utenteRicercante = $options['data']->getUtenteRicercante();
        if ($utenteRicercante) {
            if ($utenteRicercante->hasRole('ROLE_SUPER_ADMIN')) {
                $this->ruoli[] = 'ROLE_SUPER_ADMIN';
            }
        }

        parent::buildForm($builder, $options);
        //dati utente
        $builder->add('email', self::email, ['required' => false, 'label' => 'Email']);
        $builder->add('username', self::text, ['required' => false, 'label' => 'Username']);
        $builder->add('ruolo', self::choice, [
			'choices_as_values' => true,
			'choices' => $this->ruoli, 
			'required' => false
		]);

        //dati persona
        $builder->add('nome', self::text, ['required' => false, 'label' => 'Nome']);
        $builder->add('cognome', self::text, ['required' => false, 'label' => 'Cognome']);
        $builder->add('codice_fiscale', self::text, ['required' => false, 'label' => 'Codice fiscale']);

        //dati soggetto
        $builder->add('soggetto_id', self::integer, ['required' => false, 'label' => 'Id soggetto']);
        $builder->add('soggetto_denominazione', self::text, ['required' => false, 'label' => 'Denominazione']);
        $builder->add('soggetto_piva', self::text, ['required' => false, 'label' => 'Piva']);

        $builder->add('soggetto_incarico', self::entity, [
            'class' => 'SoggettoBundle:TipoIncarico',
            'choice_label' => 'descrizione',
            'required' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => 'AnagraficheBundle\Form\Entity\RicercaPersonaAdmin',
        ]);
    }
}
