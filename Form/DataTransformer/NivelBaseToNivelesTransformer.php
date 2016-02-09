<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace AscensoDigital\DependSelectBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
/**
 * Description of NivelBaseToNivelesTransformer
 *
 * @author claudio
 */
class NivelBaseToNivelesTransformer implements DataTransformerInterface{

    /**
     *
     * @var array
     */
    private $niveles;

    /**
     *
     * @param array $niveles
     */
    public function __construct(array $niveles)
    {
        $this->niveles=$niveles;
    }

    /**
     *
     * @param Object $value
     */
    public function transform($value) {
        $result = array();
        if (!(null === $value)) {
            $niv_inv=array_reverse($this->niveles);
            $anterior=null;
            foreach ($niv_inv as $nivel) {
                if (is_null($anterior)) {
                    $result[$nivel['name']]=$value;
                    $anterior=$value;
                } else {
                    $metodo='get'.$nivel['class'];
                    $anterior = $anterior->$metodo();
                    $result[$nivel['name']]=$anterior;
                }
            }
        }
        return $result;
    }

      public function reverseTransform($value) {
          if (!$value) {
              return null;
          }
          return $value[$this->niveles[count($this->niveles)-1]['name']];
    }
}
