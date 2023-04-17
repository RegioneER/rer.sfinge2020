<?php

namespace SfingeBundle\Form;

use BaseBundle\Form\CommonType;
use SfingeBundle\Entity\UtenteRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProceduraType extends CommonType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $disabled = $options["disabled"];

        $builder->add('responsabile', self::entity, [
            'class' => 'SfingeBundle:Utente',
            'required' => true,
            'disabled' => $disabled,
            'placeholder' => '-',
            'query_builder' => function (UtenteRepository $repo) {
                return $repo->cercaUtentiPAQb();
            },
        ]);

        $builder->add('rup', self::entity, [
            'class' => 'SfingeBundle:Utente',
            'required' => true,
            'disabled' => $disabled,
            'placeholder' => '-',
            'query_builder' => function (UtenteRepository $repo) {
                return $repo->cercaUtentiPAQb();
            },
        ]);

        // Deve rimanere questo campo??
        $builder->add('fase', self::entity, [
            'class' => 'SfingeBundle:Fase',
            'required' => true,
            'disabled' => $disabled,
            'placeholder' => '-',
            'choice_label' => 'descrizione',
        ]);
        $builder->add('atto', self::entity, [
            'class' => 'SfingeBundle:Atto',
            'required' => true,
            'disabled' => $disabled,
            'placeholder' => '-',
        ]);
        $builder->add('titolo', self::text, ['required' => true, 'label' => 'Titolo', 'disabled' => $disabled]);
        $builder->add('risorse_disponibili', self::importo, ['required' => true, 'label' => 'Risorse disponibili', 'disabled' => $disabled, 'scale' => 2, 'grouping' => true]);

        $asse_array = [
            'class' => 'SfingeBundle:Asse',
            'required' => true,
            'disabled' => $disabled,
            'placeholder' => '-',
        ];
        if (!$disabled) {
            $asse_array['choices'] = $options["assi"];
        }
        $builder->add('asse', self::entity, $asse_array);

        // Attualmente obiettivi specifici e azioni vengono selezionati dall'elenco completo(nel menu a tendina compaiono tuttigli obiettivi specifici e non solo quelli relativi all'asse selezionata).
        // Quando avremo un quadro completo della situazione ripristineremo i controlli javascript e caricheremo obiettivi e azioni in base alle selezioni precedenti.

        // $asse_id = !is_null($asse) ? $asse->getId() : NULL;

        $builder->add('obiettivi_specifici', self::entity, [
            'class' => 'SfingeBundle:ObiettivoSpecifico',
            'required' => true,
            'disabled' => $disabled,
            'placeholder' => '-',
            'multiple' => true,
            'query_builder' => function (\Doctrine\ORM\EntityRepository $repo) {
                return $repo->createQueryBuilder('o')
                        ->orderBy('o.codice', 'ASC');
            }, ]);

        //$obiettivo_specifico_id = !is_null($obiettivo_specifico) ? $obiettivo_specifico->getId() : NULL;

        $builder->add('azioni', self::entity, [
            'class' => 'SfingeBundle\Entity\Azione',
            'required' => true,
            'disabled' => $disabled,
            'placeholder' => '-',
            'multiple' => true,
            'query_builder' => function (\Doctrine\ORM\EntityRepository $repo) {
                return $repo->createQueryBuilder('a')
                        ->orderBy('a.codice', 'ASC');
            }, ]);

        $builder->add('amministrazione_emittente', self::entity, [
            'class' => 'SfingeBundle:TipoAmministrazioneEmittente',
            'choice_label' => 'descrizione',
            'required' => true,
            'disabled' => $disabled,
            'placeholder' => '-',
        ]);

        $builder->add('tipo_iter', self::entity, [
            'class' => 'SfingeBundle:TipoIter',
            'choice_label' => 'descrizione',
            'required' => true,
            'disabled' => $disabled,
            'placeholder' => '-',
            'label' => 'Fase procedurale',
        ]);

        $builder->add('tipo_finanziamento', self::entity, [
            'class' => 'SfingeBundle:TipoFinanziamento',
            'choice_label' => 'descrizione',
            'required' => true,
            'disabled' => $disabled,
            'placeholder' => '-',
        ]);
        $builder->add('tipo_aiuto', self::entity, [
            'multiple' => true,
            'class' => 'SfingeBundle:TipoAiuto',
            'choice_label' => 'descrizione',
            'required' => true,
            'disabled' => $disabled,
            'placeholder' => '-',
        ]);
        $builder->add('anno_programmazione', self::choice, [
            'choices' => [
                '2014' => '2014',
                '2015' => '2015',
                '2016' => '2016',
                '2017' => '2017',
                '2018' => '2018',
                '2019' => '2019',
                '2020' => '2020',
                '2021' => '2021',
                '2022' => '2022',
                '2023' => '2023',
                '2024' => '2024',
            ],
            'choices_as_values' => true,
            'placeholder' => '-',
            'required' => true,
            'disabled' => $disabled,
        ]);

        $builder->add('codice_cci', self::text, [
            "label" => "CCI",
            "required" => true,
            'disabled' => true,
        ]);

        $builder->add('fondo', self::text, [
            "label" => "Fondo",
            "required" => true,
            'disabled' => true,
        ]);

        $builder->add('categoria_regione', self::text, [
            "label" => "Categoria della regione",
            "required" => true,
            'disabled' => true,
        ]);

        $builder->add('sportello', self::checkbox, [
            'label' => "A sportello",
            'required' => false,
            'disabled' => $disabled,
        ]);

        $builder->add('aiuto_stato', self::checkbox, [
            'label' => "Aiuto di stato",
            'required' => false,
            'disabled' => $disabled,
        ]);

        $builder->add('generatore_entrate', self::checkbox, [
            'label' => "Progetto generatore di entrate",
            'required' => false,
            'disabled' => $disabled,
        ]);
        
        $builder->add('spese_ammissibili_forfettario', self::checkbox, [
            'label' => "Spese ammissibili stabilite sulla base di un tasso forfettario",
            'required' => false,
            'disabled' => $disabled,
        ]);
        
        $builder->add('spese_pubbliche_forfettario', self::checkbox, [
            'label' => "Spesa pubblica/spese ammissibili sulla base di un tasso forfettario",
            'required' => false,
            'disabled' => $disabled,
        ]);

        $builder->add('organismo', self::text, [
            "label" => "Organismo che il rilascia il documento",
            "required" => true,
            'disabled' => true,
        ]);

        $builder->add('priorita_procedura', self::entity, [
            'class' => 'SfingeBundle:PrioritaProcedura',
            'choice_label' => 'descrizione',
            'required' => true,
            'disabled' => $disabled,
            'placeholder' => '-',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => 'SfingeBundle\Entity\Procedura',
            'assi' => [],
            'em' => null,
        ]);
        $resolver->setRequired("url_indietro");
    }
}
