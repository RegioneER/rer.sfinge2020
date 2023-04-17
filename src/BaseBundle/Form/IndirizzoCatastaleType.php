<?php

namespace BaseBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityManager;

class IndirizzoCatastaleType extends CommonType {

    protected $entityManager;

    public function __construct(EntityManager $objectManager) {
        $this->entityManager = $objectManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $read_only = $options["readonly"];
        $disabled = $options["readonly"];
        $indirizzo = $options["dataIndirizzo"];

        if ($read_only == true) {
            $attr = array('readonly' => 'readonly');
        } else {
            $attr = array();
        }

        $builder->add('via', self::text, array('label' => 'Indirizzo', 'disabled' => $disabled, 'attr' => $attr));
        $builder->add('numero_civico', self::text, array('label' => 'Numero civico', 'disabled' => $disabled, 'attr' => $attr));

        $builder->add('provincia', self::entity, array(
            'class' => 'GeoBundle\Entity\GeoProvincia',
            'choices' => $this->entityManager->getRepository("GeoBundle\Entity\GeoProvincia")->provinceList($options["id_regione"], "non-cessate"),
            'choice_label' => 'denominazione',
            'placeholder' => '-',
            'required' => true,
            'label' => 'Provincia',
            'disabled' => $disabled,
            'attr' => $attr
            )
        );

        $provincia_id = \method_exists($indirizzo, "getProvincia") ? $indirizzo->getProvincia() : $indirizzo->provincia;

        $builder->add('comune', self::entity, array(
            'class' => 'GeoBundle\Entity\GeoComune',
            'choices' => $this->entityManager->getRepository("GeoBundle\Entity\GeoComune")->comuniList(empty($provincia_id) ? "1" : $provincia_id, "non-cessati", "non-ceduti"),
            'choice_label' => 'denominazione',
            'placeholder' => '-',
            'required' => true,
            'label' => 'Comune',
            'disabled' => $disabled,
            'attr' => $attr
            )
        );

        $builder->add('cap', self::text, array('required' => true, 'max_length' => 5, 'label' => 'CAP', 'disabled' => $disabled, 'attr' => $attr));

        if ($options["completo"] == true) {
            $builder->add('foglio', self::text, array('required' => true, 'label' => 'Foglio', 'disabled' => $disabled, 'attr' => $attr));

            $builder->add('particella', self::text, array('required' => true, 'label' => 'Particella', 'disabled' => $disabled, 'attr' => $attr));

            $builder->add('subalterno', self::text, array('required' => true, 'label' => 'Subalterno/i', 'disabled' => $disabled, 'attr' => $attr));
        }
        $builder->add('disabilitaCombo', self::hidden, array('data' => $read_only));

        $builder->add('pulsanti', self::salva_indietro, array("url" => $options["url_indietro"], 'disabled' => false));
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'BaseBundle\Entity\IndirizzoCatastale',
            'readonly' => false,
            'id_regione' => null,
            'completo' => true,
            "validation_groups" => ['bando_5', 'bando_58', 'bando_107_a', 'bando_189'],
        ));
        $resolver->setRequired("readonly");
        $resolver->setRequired("dataIndirizzo");
        $resolver->setRequired("url_indietro");
    }

}
