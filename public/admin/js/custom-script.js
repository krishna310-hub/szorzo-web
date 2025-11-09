$(document).on('click', '.destroy', function (event) {
    var url     = $(this).attr('data-route');
    var type    = $(this).attr('data-type') ?? '';
    $.confirm( {
        columnClass: 'small', containerFluid: true,
        title: 'Are you sure?', content: 'Are you sure want to delete this entry?',
        type: 'blue',
        typeAnimated: true,  buttons: {
            Delete: {
                text: 'Yes, delete it!', btnClass: 'btn-danger', action: function() {
                    $.ajax({
                        url: url,
                        type: 'DELETE',
                        data: {type: type},
                        beforeSend: function(){
                            $('#loading-image').removeClass('d-none');
                        },
                        success: function(response){
                            if (response.status) {
                                toastr.success(response.message);
                            } else {
                                toastr.error(response.message);
                            }
                            $('#loading-image').addClass('d-none');
                            location.reload();
                        },
                        error: function(XMLHttpRequest, textStatus, errorThrown) {
                            toastr.error(errorThrown);
                            $('#loading-image').addClass('d-none');
                            location.reload();
                        }
                    });
                }
            }, cancel: {
                text: 'Cancel', btnClass: 'btn-primary', action: function () {
                    // location.reload();
                },

            }
        }
    });
});

$(document).on('click', '.destroy-ajax', function (event) {
    var url     = $(this).attr('data-route');
    var type    = $(this).attr('data-type') ?? '';
    $.confirm( {
        columnClass: 'small', containerFluid: true,
        title: 'Are you sure?', content: 'Are you sure want to delete this entry?',
        type: 'blue',
        typeAnimated: true,  buttons: {
            Delete: {
                text: 'Yes, delete it!', btnClass: 'btn-danger', action: function() {
                    $.ajax({
                        url: url,
                        type: 'DELETE',
                        data: {type: type},
                        beforeSend: function(){
                            // $('#loading-image').removeClass('d-none');
                        },
                        success: function(response){
                            if (response.status) {
                                toastr.success(response.message);
                            } else {
                                toastr.error(response.message);
                            }
                            $('#scroll-vertical').DataTable().ajax.reload();
                        },
                        error: function(XMLHttpRequest, textStatus, errorThrown) {
                            toastr.error(errorThrown);
                            $('#scroll-vertical').DataTable().ajax.reload();
                        }
                    });
                }
            }, cancel: {
                text: 'Cancel', btnClass: 'btn-primary', action: function () {
                    // location.reload();
                },

            }
        }
    });
});

$( function() {

    $(document).on('click', '.status', function (event) {
        var url     = $(this).attr('data-route');
        var type    = "single";
        $.confirm( {
            columnClass: 'small', containerFluid: true,
            title: 'Are you sure?', content: 'Are you sure want to change this entry?',
            type: 'green',
            typeAnimated: true,
             buttons: {
                Delete: {
                    text: 'Yes, change it!', btnClass: 'btn-primary', action: function() {
                        $.ajax({
                            url: url,
                            type: 'GET',
                            data: {type:type},
                            beforeSend: function(){
                                $('#loading-image').removeClass('d-none');
                            },
                            success: function(response){
                                if (response.status) {
                                    toastr.success(response.message);
                                } else {
                                    toastr.error(response.message);
                                }
                                $('#loading-image').addClass('d-none');
                                location.reload();
                            },
                            error: function(XMLHttpRequest, textStatus, errorThrown) {
                                toastr.error(errorThrown);
                                $('#loading-image').addClass('d-none');
                                location.reload();
                            }
                        });
                    }
                }, cancel: {
                    text: 'Cancel', btnClass: 'btn-danger', action: function () {},
                }
            }
        });
    });

} );
$(document).on('click', '.remove', function (event) {
    var url     = $(this).attr('data-route');
    var type    = "single";

    $.confirm( {
        columnClass: 'small', containerFluid: true,
        title: 'Are you sure?', content: 'Are you sure want to delete this entry?',
        type: 'blue',
        typeAnimated: true,  buttons: {
            Delete: {
                text: 'Yes, delete it!', btnClass: 'btn-danger', action: function() {
                    $.ajax({
                        url: url,
                        type: 'DELETE',
                        data: {type:type},
                        beforeSend: function(){
                            $('#loading-image').removeClass('d-none');
                        },
                        success: function(response){
                            if (response.status) {
                                toastr.success(response.message);
                            } else {
                                toastr.error(response.message);
                            }
                            $('#loading-image').addClass('d-none');
                            location.reload();
                        },
                        error: function(XMLHttpRequest, textStatus, errorThrown) {
                            toastr.error(errorThrown);
                            $('#loading-image').addClass('d-none');
                            location.reload();
                        }
                    });
                }
            }, cancel: {
                text: 'Cancel', btnClass: 'btn-primary', action: function () {},
            }
        }
    });
});

$(document).on('click','.common_model',function() {
    let error_response      = $(this).data('errors') || '';
    let url                 = $(this).attr('data-url') || '';
    let title               = $(this).attr('data-title') || '';
    let size                = $(this).attr('data-size') ? 'modal-dialog ' + $(this).attr('data-size') : 'modal-dialog modal-md';
    $.ajax({
        type: "GET",
        url: url,
        beforeSend: function () {
            $("#loading-image1").removeClass("d-none");
            $('.common_model_content').parent().parent().parent().attr('class','');
        },
        success: function (data) {
            $('.common_model_content').html(data);
            $('.common_model_content').parent().parent().find('#exampleModalLabel').text(title);
            $('.common_model_content').parent().parent().parent().addClass(size);
            $('#common_model').modal('show');
        },
        complete: function (){
            $("#loading-image1").addClass("d-none");

            if(error_response){
                $.each(error_response, function(field, errors) {
                    $.each(errors, function(index, errorMessage) {
                        $('#'+field+'-error').text(errorMessage);
                    });
                });
            }
            // use to popup multi select
            const selectEl = document.querySelector('#choices-multiple-remove-button');
            if (selectEl) {
                new Choices(selectEl, {
                    removeItemButton: true,
                    shouldSort: false,
                });
            }

            // $(".tags_select_edit").select2({
            //     closeOnSelect: false
            // });
        },
        error: function ( XMLHttpRequest, textStatus, errorThrown ) {
            $("#loading-image1").addClass("d-none");
        },
    });
});

$(document).on('click','.show_image',function() {

    let url       = $(this).data('url');
    let title     = $(this).data('title');
    let alt       = $(this).data('alt') ?? '';
    let view_type = $(this).data('view_type');

    let size       =  $(this).data('size') ? 'modal-dialog ' + $(this).data('size') : 'modal-dialog modal-md';

    if(url){
        let div;
        if(view_type == 'iframe'){
            div =  '<div class="mt-3" ><iframe src="'+ url +'" alt="'+ alt +'" width="100%" height="350"></iframe></div>';
        } else {
            div =  '<div class="mt-3" ><img src="'+ url +'" alt="'+ alt +'" width="100%" height="350"></div>';
        }

        $.dialog({
            title: title,
            content: div,
            position: {
                my: "left center",
                at: "left center"
             }
        });
    }
});

$(document).ready(function () {
    $(".toggle-password").on("click", function () {
        var input = $("#password");
        var icon = $(this);
        var type = input.attr("type") === "password" ? "text" : "password";

        input.attr("type", type);

        icon.toggleClass("ri-eye-fill ri-eye-off-fill");
    });
});
