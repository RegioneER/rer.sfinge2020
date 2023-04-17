<?php

/**
* @author lfontana
*/
namespace MonitoraggioBundle\Form;
use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

class MonitoraggioConfigurazioneEsportazioneProceduraType extends CommonType
{
    /**
     * {@inheritdoc}
     */
     public function buildForm(FormBuilderInterface $builder, array $options)
     {
         parent::buildForm($builder, $options);
         $builder->add('monitoraggio_configurazione_esportazione_tavole', self::collection, array(
            'entry_type' => 'MonitoraggioBundle\Form\MonitoraggioConfigurazioneEsportazioneTavoleType',
            'label' => false,
         ));
        
     }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults(array(
            'data_class' => 'MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneProcedura',
        ));
    }


    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        usort($view['monitoraggio_configurazione_esportazione_tavole']->children, function (FormView $a, FormView $b) {
            /** @var Photo $objectA */
            $posA = $a->vars['value']->getSortOrder();
            /** @var Photo $objectB */
            $posB = $b->vars['value']->getSortOrder();
    
           
    
            if ($posA == $posB) {
                return 0;
            }
    
            return ($posA < $posB) ? -1 : 1;
        });
    }
    
}
