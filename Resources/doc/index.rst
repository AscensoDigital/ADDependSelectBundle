1 Instalación
=============

composer require ascensodigital/depend-select-bundle

2 Configuración
===============

Paso 1: Activar Bundle
-----------------------

// config/bundles.php

//...
    AscensoDigital\DependSelectBundle\ADDependSelectBundle::class => ['all' => true],
//...

Paso 2: Instalar las rutas
--------------------------

# config/routes/ad_depend_select.yaml

ad_depend_select:
    resource: "@ADDependSelectBundle/Resources/config/routing.yml"


Paso 3: Generar js con fos-js-router
------------------------------------

$ php bin/console fos:js:dump


Paso 4: Mover archivo generado a carpeta public
-----------------------------------------------

$ mv web/js/fos_js_routes.js public/js/.

Paso 5: Agregar scripts a templates base
-----------------------------------------
Por defecto usa jquery para funcionar. Por simplicidad se agrega al template base,
ahora se pueden agregar solo a las actions que lo requieran.

{# templates/base.html.twig #}

    {# ... #}
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="{{ asset('bundles/fosjsrouting/js/router.js') }}"></script>
    <script src="{{ asset('js/fos_js_routes.js') }}"></script>
    <script src="{{ asset('bundles/addependselect/js/jq.dependselect.js') }}"></script>
</body>

3 Uso
=====

Paso 1: En el creador de un formulario
--------------------------------------

    $builder
    //...
        ->add('comuna', DependSelectType::class, [
            'niveles' => [
                    ['entity' => 'App:Region', 'name' => 'Region'],
                    ['entity' => 'App:Provincia', 'name' => 'Provincia']
                    ['entity' => 'App:Comuna', 'name' => 'Comuna']
                ],
            'query_builder' => function(EntityRepository $er) {
                return $er->getQueryBuilder();
            },
        ]),
    //...

El query_builder solo se usa para el nivel superior de la dependencia por defecto obtiene todos los registros.

Para el resto de los niveles se requiere el metodo en el repository findBy{NivelSuperior}($idNivelSuperiroSeleccionado)


Paso 2: Clases Repository
-------------------------

Dependiendo del modelo y la lógica del negocio, puede que no sea necesario implementar ningùn metodo adicional y
funcionar con los metodos que vienen por defecto en el repository de una entity.

En clase repository del Nivel Superior (App:Region)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

En caso de usar el parametro 'query_builder'.

// src/Repositoy/RegionRepository
// ...
    public function getQueryBuilder() {
        return $this->getQueryBuilder('r');
    }

Aca es importante retornar el objeto QueryBuilder, NO el resultado de una query.


En clase repository de los siguientes niveles
---------------------------------------------

En caso de haber una relacion directa el metodo viene por defecto implementado por Symfony,

en el caso que no haya una relacion directa implementar por ejemplo para App:Provincia

// src/Repositoy/ProvinciaRepository
// ...
    public function findByRegion($regionId) {
        return $this->getQueryBuilder('p')
                    ->where('p.region=:regionId')
                    ->setParameter(':regionId',$regionId)
                    ->getQuery()
                    ->getResult()
    }


En caso el caso de App:Comuna (3er Nivel)

// src/Repositoy/ComunaRepository
// ...
    public function findByProvincia($provinciaId) {
        return $this->getQueryBuilder('c')
                    ->where('c.provincia=:provinciaId')
                    ->setParameter(':provinciaId',$provinciaId)
                    ->getQuery()
                    ->getResult()
    }

Paso 3: Clases Entity
---------------------

se requieren los metodos getId() y __toString() para todos los niveles.

Y del Nivel 2 al "n", adicional el metodo get{ClaseNivelSuperior}(); dependiendo del modelo y la lógica de negocio,
puede que no sea necesario implementar ningún método adicional, dado que esten creados por los getter de tus propiedades.

En este caso para App:Comuna

// src/Entity/Comuna.php

// ...
    public function getProvincia() {
        // tu logica para obtener la provincia
    }
//...

y para App:Provincia

// src/Entity/Provincia.php

// ...
    public function getRegion() {
        // tu logica para obtener la region
    }
//...