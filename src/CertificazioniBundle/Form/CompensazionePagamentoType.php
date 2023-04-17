<?php

namespace CertificazioniBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class CompensazionePagamentoType extends CommonType {

    protected $entityManager;

    public function __construct(\Doctrine\ORM\EntityManager $objectManager) {
        $this->entityManager = $objectManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('importo_compensazione', self::importo, [
            "label" => "Importo compensazione",
            "required" => true,
            'constraints' => array(new NotNull()),
        ]);

        $builder->add('ritiro', self::checkbox, [
            'label' => "Per ritiro",
            'required' => false,
        ]);

        $builder->add('recupero', self::checkbox, [
            'label' => "Per recupero",
            'required' => false,
        ]);
        
        $builder->add('taglio_ada', self::checkbox, [
            'label' => "Taglio ada",
            'required' => false,
        ]);

        $anni = $this->entityManager->getRepository("CertificazioniBundle\Entity\CertificazionePagamento")->getAnniContabili();
        $builder->add('anno_contabile', self::choice, [
            'choices_as_values' => true,
            'label' => "Anno contabile (può essere anche un anno contabile precedente in riferimento all'appendice 2 delle chiusure dei conti)",
            'choices' => array_combine($anni, $anni), //in modo che value e label coincidano come valore
            'expanded' => false,
            'required' => true,
            'placeholder' => "-",
            'constraints' => array(new NotNull())
        ]);
        
         $builder->add('note', self::textarea, [
            'label' => "Nota",
            'required' => false,
        ]);

        $builder->add("pulsanti", self::salva_indietro, ["url" => $options["url_indietro"]]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => 'CertificazioniBundle\Entity\CompensazionePagamento',
        ]);

        $resolver->setRequired("url_indietro");
    }

}
