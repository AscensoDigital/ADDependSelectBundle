<?php

namespace AscensoWeb\DependSelectBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller {

    public function indexAction(Request $request) {
        $resp=array();
        if ($request->isXmlHttpRequest()) {
            if($request->get('id')>0) {
                $id=$request->get('id');
                if(is_array($id) && $id[0]=="") {
                    unset($id[0]);
                }
                $em = $this->getDoctrine()->getManager();
                $metodo='findBy'.ucfirst($request->get('name'));

                if(!is_null($request->get('filtroExtra')) && $request->get('filtroExtra')>0) {
                    $opciones = $em->getRepository($request->get('entity'))->$metodo($id,$request->get('filtroExtra'));
                }
                else {
                    $opciones = $em->getRepository($request->get('entity'))->$metodo($id);
                }
                foreach ($opciones as $opcion) {
                    $resp[]=array('id' => $opcion->getId(), 'nombre' => $opcion->__toString());
                }
                if(0==count($resp)){
                    $resp[]=array('id' => '', 'nombre' => 'Sin Disponibilidad');
                }
            }
        }
        return new Response(json_encode($resp));
    }
}
