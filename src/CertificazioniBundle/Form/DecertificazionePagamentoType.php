<?php

namespace CertificazioniBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class DecertificazionePagamentoType extends CommonType {
    protected $entityManager;

    public function __construct(\Doctrine\ORM\EntityManager $objectManager) {
        $this->entityManager = $objectManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('importo', self::importo, [
            "label" => "Importo decertificazione",
            "required" => true,
            'constraints' => [new \Symfony\Component\Validator\Constraints\Range(["max" => 0.00, "min" => -($options["importo_decertificabile"]), "minMessage" => "Questo valore dovrebbe essere negativo, ma superiore all'importo decertificabile"])],
        ]);

        $builder->add('importo_irregolare', self::importo, [
            "label" => "Importo Irregolare (se irregolarità)",
            "required" => false,
        ]);

        $builder->add('nota_decertificazione', self::textarea, [
            'label' => "Nota",
            'required' => false,
        ]);

        $builder->add('ritiro', self::checkbox, [
            'label' => "Per ritiro",
            'required' => false,
        ]);

        $builder->add('recupero', self::checkbox, [
            'label' => "Per recupero",
            'required' => false,
        ]);

        $builder->add('irregolarita', self::checkbox, [
            'label' => "Irregolarità",
            'required' => false,
        ]);

        $builder->add('articolo_137', self::checkbox, [
            "label" => "importi sospesi art. 137, co 2",
            "required" => false,
        ]);

        $builder->add('segnalazione_ada', self::checkbox, [
            "label" => "Segnalazione ada",
            "required" => false,
        ]);

        $anni = $this->entityManager->getRepository("CertificazioniBundle\Entity\CertificazionePagamento")->getAnniContabili();
        $builder->add('anno_contabile_precedente', self::choice, [
            'choices_as_values' => true,
            'label' => "Anno contabile (può essere anche un anno contabile precedente in riferimento all'appendice 2 delle chiusure dei conti)",
            'choices' => array_combine($anni, $anni), //in modo che value e label coincidano come valore
            'expanded' => false,
            'required' => true,
            'placeholder' => "-",
            'constraints' => array(new NotNull())
        ]);

        $builder->add("pulsanti", self::salva_indietro, ["url" => $options["url_indietro"]]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => 'CertificazioniBundle\Entity\CertificazionePagamento',
        ]);

        $resolver->setRequired("url_indietro");
        $resolver->setRequired("importo_decertificabile");
    }
}
