$(function() {
    $('.delete').click(function (){
        Swal.fire({
            title: confirmDelete,
            text: "Już tego nie cofniesz!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Tak, usuń',
            cancelButtonText: 'Nie usuwaj'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    method: "DELETE",
                    // url:"http://shop.test/users/"
                    // url: $(this).data("id")
                    // url: "{{ url('users') }}/" + $(this).data("id")
                    // url: deleteUrl + $(_this).data("id")
                    url:deleteUrl + $(this).data("id")
                })
                .done(function ( data ) {
                    window.location.reload();
                })
                .fail(function ( data ) {
                    console.log( data.responseJSON.message );
                    Swal.fire({
                        icon: data.responseJSON.status,
                        title: 'Ups...!',
                        text: data.responseJSON.message
                    })
                });
            }
        })
    });
});
