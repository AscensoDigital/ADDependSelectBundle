$(document).ready(function () {
    $('body').on('change', 'select.depend-select', function(){
        if($(this).data('next-name')!=='') {
            var last_position= $(this).attr('id').lastIndexOf('_');
            var destino="#" + $(this).attr('id').substr(0,last_position+1) + $(this).data('next-name');
            if($(this).val()>0 || $(this).val().length>1 || ($(this).val().length===1 && $(this).val()[0]>0)) {
                $.ajax({url: Routing.generate('ad_depend_select_load',{},true),
                        type: 'POST', 
                        dataType: 'json', 
                        data: {id: $(this).val(), name: $(this).data('name'), entity: $(this).data('next-entity'), filtroExtra: ($(this).data('filtro-extra')!= undefined ? $(this).data('filtro-extra') : null)},
                        success: function (data) {

                            no="<option></option>";
                            $.each(data,function(i,e){
                                no+="<option value="+e.id+">"+e.nombre+"</option>";
                            });
                            $(destino).html(no);
                            if ($(destino).data('next-name') !== '') {
                                limpiaRecursiva($(destino).attr('id'));
                            }
                            if (1 === data.length) {
                                $(destino).val(data[0].id);
                                $(destino).change();
                            }
                        }
                });
            }
            else if($(this).data('next-name')!=='') {
                limpiaRecursiva($(this).attr('id'));
            }
        }
    });
    function limpiaRecursiva(id)
    {
        var last_position= id.lastIndexOf('_');
        var destino="#" + id.substr(0,last_position+1) + $("#" + id).data('next-name');
        no="<option></option>";
        $(destino).html(no);
        if($(destino).data('next-name')!=='')
            limpiaRecursiva($(destino).attr('id'));

    }
});
