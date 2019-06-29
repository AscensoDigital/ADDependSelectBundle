<?php

namespace AscensoDigital\DependSelectBundle\Form\EventListener;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\FormInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

/**
 * Description of AddNivelesFieldSubscriber
 *
 * @author claudio
 */
class AddNivelesFieldSubscriber implements EventSubscriberInterface {
    
    private $options;
    private $niveles;
    private $om;
    
    public function __construct(ObjectManager $om, $niveles, $options) {
        $this->niveles= $niveles;
        $this->options=$options;
        $this->om=$om;
    }

    public static function getSubscribedEvents() {
        return array(FormEvents::PRE_SUBMIT => 'preSubmit',FormEvents::PRE_SET_DATA => 'preSetData');
    }

    public function preSetData(FormEvent $event) {
        $data = $event->getData();
        $form = $event->getForm();
        $last_nivel_id=\count($this->niveles)-1;
        /*if(is_array($data)){
            for($i=$last_nivel_id;0<=$i;$i--) {
                if(isset($this->niveles[$i+1]['default']) && !is_null($this->niveles[$i+1]['default'])) {
                    $metodo='get'.$this->niveles[$i]['class'];
                    if(is_array($this->niveles[$i+1]['default'])) {
                        $defs=array();
                        foreach($this->niveles[$i+1]['default'] as $def) {
                            $defs[]=$def->$metodo();
                        }
                        $this->niveles[$i]['default']=$defs;
                    }
                    else {
                        $this->niveles[$i]['default']=$this->niveles[$i+1]['default']->$metodo();
                    }
                }
                elseif(isset($data[$this->niveles[$i]['name']]) && $data[$this->niveles[$i]['name']]>0) {
                    $ret=$this->om->getRepository($this->niveles[$i]['entity'])->findById($data[$this->niveles[$i]['name']]);
                    $this->niveles[$i]['default']= (count($ret)==1 ? $ret[0] : $ret);
                }
                else {
                    $this->niveles[$i]['default']= null;
                }
            }
        }
        else {*/
            $this->niveles[$last_nivel_id]['default']=$data ? $data : null;
            for($i=$last_nivel_id-1;0<=$i;$i--) {
               $metodo='get'.$this->niveles[$i]['class'];
               $this->niveles[$i]['default']= is_null($this->niveles[$i+1]['default']) ? null : $this->niveles[$i+1]['default']->$metodo();
            }
        //}
        
        $this->customizeForm($form);
    }
    
    public function preSubmit(FormEvent $event) {
        $data = $event->getData();
        $form = $event->getForm();
        for($i=\count($this->niveles)-1;0<=$i;$i--) {
            if(isset($this->niveles[$i+1]['default']) && !is_null($this->niveles[$i+1]['default'])) {
                $metodo='get'.$this->niveles[$i]['class'];
                if(is_array($this->niveles[$i+1]['default'])) {
                    if(count($this->niveles[$i+1]['default'])==1) {
                        $this->niveles[$i]['default']= $this->niveles[$i+1]['default'][0]->$metodo();
                    }
                    else {
                        $defs=array();
                        foreach($this->niveles[$i+1]['default'] as $def) {
                            $defs[]=$def->$metodo();
                        }
                        $this->niveles[$i]['default']=$defs;
                    }
                }
                else {
                    $this->niveles[$i]['default']= $this->niveles[$i+1]['default']->$metodo();
                }
            }
            elseif(isset($data[$this->niveles[$i]['name']]) && $data[$this->niveles[$i]['name']]>0) {
                $ret = $this->om->getRepository($this->niveles[$i]['entity'])->findBy(array('id' => $data[$this->niveles[$i]['name']]));
                $this->niveles[$i]['default']= (count($ret)==1 ? $ret[0] : $ret);
            }
            else {
                $this->niveles[$i]['default']= null;
            }
        }
        
        $this->customizeForm($form);
    }
    
    protected function customizeForm(FormInterface $form)
    {
       $n_nivel=0;
        foreach($this->niveles as $nivel) {
            if(0==$n_nivel) {
                $opciones=array(
                    'placeholder' => '',
                    'class' => $nivel['entity'],
                    'attr' => array(
                        'data-name' => $nivel['name'],
                        'data-next-entity' => $this->niveles[$n_nivel+1]['entity'],
                        'data-next-name' => $this->niveles[$n_nivel+1]['name'],
                        'class' => 'depend-select'
                        )
                    );
                $opciones = $this->procesaOptions($opciones, $nivel);
                if (isset($this->options['query_builder'])) $opciones['query_builder'] = $this->options['query_builder'];
                $form->add($nivel['name'], EntityType::class, $opciones);
            }
            else {
                $metodo='findBy'.$this->niveles[$n_nivel-1]['class'];
                $opciones=array(
                    'placeholder' => '',
                    'class' => $nivel['entity'],
                    'attr' => array(
                        'data-name' => $nivel['name'],
                        'data-next-entity' => isset($this->niveles[$n_nivel+1]) ? $this->niveles[$n_nivel+1]['entity'] : '',
                        'data-next-name' => isset($this->niveles[$n_nivel+1]) ? $this->niveles[$n_nivel+1]['name'] : '',
                        'class' => 'depend-select'
                        ),
                  );
                $opciones = $this->procesaOptions($opciones, $nivel);
                if(isset($opciones['attr']['data-filtro-extra'])){
                    $opciones['choices'] = is_null($this->niveles[$n_nivel-1]['default']) ? array() : $this->om->getRepository($nivel['entity'])->$metodo($this->niveles[$n_nivel-1]['default'],$opciones['attr']['data-filtro-extra']);
                }
                else {
                    $opciones['choices'] = is_null($this->niveles[$n_nivel-1]['default']) ? array() : $this->om->getRepository($nivel['entity'])->$metodo($this->niveles[$n_nivel-1]['default']);
                }
                $form->add($nivel['name'], EntityType::class, $opciones);
            }
            $n_nivel++;
        }
    }

    private function procesaOptions($opciones, $nivel)
    {
        if (isset($this->options['multiple'])) $opciones['multiple'] = $this->options['multiple'];
        if (isset($this->options['required'])) $opciones['required'] = $this->options['required'];
        if (isset($this->options['expanded'])) $opciones['expanded'] = $this->options['expanded'];
        if (isset($this->options['attr'])) {
            $opciones['attr'] = array_merge_recursive($opciones['attr'], $this->options['attr']);
            if (isset($opciones['attr']['class']) && is_array($opciones['attr']['class'])) {
                $opciones['attr']['class'] = implode(' ', $opciones['attr']['class']);
            }
        }
        if (isset($nivel['label'])) $opciones['label'] = $nivel['label'];
        return $opciones;
    }
}
