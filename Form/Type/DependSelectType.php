<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace AscensoDigital\DependSelectBundle\Form\Type;

use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\FormBuilderInterface;

use AscensoDigital\DependSelectBundle\Form\DataTransformer\NivelBaseToNivelesTransformer;
use AscensoDigital\DependSelectBundle\Form\EventListener\AddNivelesFieldSubscriber;

/**
 * Description of DependSelectType
 *
 * @author claudio
 */
class DependSelectType extends AbstractType{

    /**
     * @var ObjectManager
     */
    private $om;
    /**
     * @var Logger
     */
    private $logger;
    
    /**
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om, $logger) {
        $this->om = $om;
        $this->logger= $logger;
    }


   /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $niveles = $this->normalizarNiveles($options['niveles'], isset($options['labels']) ? $options['labels'] : array());
      unset($options['niveles']);
      if(isset($options['labels'])){
          unset($options['labels']);
      }
      $builder->addEventSubscriber(new AddNivelesFieldSubscriber($this->om, $niveles, $options));

        $builder->addModelTransformer(new NivelBaseToNivelesTransformer($niveles));
    }

    public function configureOptions(OptionsResolver $resolver)
    {   
        $resolver->setRequired(array(
            'niveles'
        ));
        $resolver->setDefined(array(
            'multiple',
            'expanded',
            'query_builder',
            'labels'
        ));
        $resolver->setDefaults(array(
            'niveles' => array()
        ));
    }

  public function getParent()
  {
      return 'form';
  }

  public function getName() {
    return 'dependselect';
  }

    private function normalizarNiveles($niveles, $labels)
    {
    $result=array();
    $nivel_id=0;
        foreach ($niveles as $nivel) {
      $this->logger->info("Nivel: ".$nivel);
      $entity= is_array($nivel) && isset($nivel['entity']) ? $nivel['entity'] : $nivel;
      $result[$nivel_id]['entity']=$entity;
      $tmpclass=  explode(':', $entity);
      $class=$tmpclass[count($tmpclass)-1];
      $result[$nivel_id]['class']=$class;
      $this->logger->info("Class: ".$class);
      $name= is_array($nivel) && isset($nivel['name']) ? $nivel['name'] : strtolower(substr($class,0,1)).substr($class,1);
      $result[$nivel_id]['name']=$name;
      if(isset($labels[$nivel_id])) {
          $result[$nivel_id]['label']=$labels[$nivel_id];
      }
      $nivel_id++;
    }
    return $result;
  }
}

?>
