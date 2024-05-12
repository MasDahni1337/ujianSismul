
const domain = window.location.host.match(/localhost/g) ? `http://${window.location.host}` : `https://${window.location.host}`;
function btnDelete(id){
    $('#deleteID').val(id);
    $('#deleteModal').modal('show');
}

function deleteAll(){
    $('#deleteAllModal').modal('show');
}
$(document).ready(function() {
    var table = $('#products').DataTable({
        "processing": false,
        "serverSide": true,
        "ajax": {
            "url": "/list",
            "type": "POST",
            error: function(xhr, error, code)
            {
                console.error("DataTable error: ", xhr);
            }
        },
        "columns": [
            { data: "name" },
            { data: "price" },
            { 
                "data": "foto",
                render: function(data, type, row, meta) {
                    var strData = "-";
                    if(data != null){
                        strData = `<img class="img-thumbnail rounded" style="max-width:40%;" src="/product/photo/${data}"></img>`
                    }
                    return strData;
                }
            },
            {
                data: "id",
                "render": function(data, type, row, meta) {
                    return `<button class="btn btn-success btn-edit" data-id="${data}" data-bs-toggle="modal" data-bs-target="#productModal">Edit</button>
                            <button type="button" onclick="btnDelete('${data}')" class="btn btn-danger btn-delete" data-id="${data}">Delete</button>`;
                }
            }
        ]
    });
    $('#productForm').validate({
        submitHandler: function(form) {
            var formData = new FormData();
            var productId = $('#productId').val();
            formData.append('name', $('#name').val()); 
            formData.append('price', $('#price').val());
            if (productId) {
                formData.append('productId', productId);
            }else{
                formData.append('photo', $('#photo').prop('files')[0]);
            }
            var actionUrl = productId ? `${domain}/update` : `${domain}/save`;
            $.ajax({
                url: actionUrl,
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(data) {
                    console.log(data);
                    if(data.status == 200){
                        $('#productModal').modal('hide');
                        table.ajax.reload();
                        toastr.success(data.message);
                    }else{
                        toastr.error(data.message);
                    }
                },
                error: function(xhr, error, code){
                    console.error(xhr);
                }
            });
        }
    });
    $('#productModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var productId = button.data('id');
        var modal = $(this);
        if (productId) {
            $('#photo').removeAttr('required');
        } else {
            $('#photo').attr('required', 'required');
        }
        if (productId) {
            $.ajax({
                url: `${domain}/getProduct/${productId}`,
                type: 'GET',
                success: function(data) {
                    console.log(data)
                    $("#photoProd").show();
                    $("#srcPhoto").attr("src", `/product/photo/${data.data.foto}`);
                    modal.find('.modal-title').text('Edit Product');
                    modal.find('#name').val(data.data.name);
                    modal.find('#price').val(data.data.price);
                    modal.find('#productId').val(data.data.id);
                },
                error: function(xhr, error, code){
                    console.error(xhr);
                }
            });
        } else {
            modal.find('.modal-title').text('Add Product');
            $("#photoProd").hide();
            $('#productForm')[0].reset();
            $("#srcPhoto").attr("src", ``);
            modal.find('#productId').val('');
        }
    });

    $('#deleteForm').on('submit', function(e) {
        e.preventDefault(); 
        var deleteID = $('#deleteID').val();
        if (!deleteID) {
            return;
        }
        $.ajax({
            url: `/singleDelete/${deleteID}`,
            type: 'POST',
            success: function(response) {
                console.log(response);
                if(response.status == 200){
                    $('#deleteModal').modal('hide');
                    table.ajax.reload();
                    toastr.success(response.message);
                }else{
                    toastr.error(data.message);
                }
            },
            error: function(xhr, status, error) {
                console.error(xhr);
            }
        });
    });

    $('#deleteAllForm').on('submit', function(e) {
        e.preventDefault(); 
        $.ajax({
            url: `/batchDelete`,
            type: 'POST',
            success: function(response) {
                console.log(response);
                if(response.status == 200){
                    $('#deleteAllModal').modal('hide');
                    table.ajax.reload();
                    toastr.success(response.message);
                }else{
                    toastr.error(data.message);
                }
            },
            error: function(xhr, status, error) {
                console.error(xhr);
            }
        });
    });
});