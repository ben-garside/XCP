
table = $( '#userList' );

function redrawTable() {
	table.DataTable().ajax.reload()

}

function drawTable() {
	table.DataTable({
						"ajax": 'data/users.list.php'
					});
}

function addUser() {

}

function editUser(userId) {
	console.log( 'Edit: ' + userId );
	var modal = $('#dataModal');
    var butSend = $('#dataModalsendButton');
    var cancBut = $('#dataModalcancButton');
    modal.find('.modal-title').text('Edit user ' + userId + '.')

	modal.modal();
    
    cancBut.click(function() {
            modal.modal('hide')
            butSend.button('reset');
            cancBut.unbind(); //Remove on click listener
            butSend.unbind(); //Remove on click listener
        })


    // Get data to fill in form
    $.ajax({
        url: 'data/users.show.php',
        data: {id: userId}
    }).done(function( e ) {
        var data1 = '';
        var data2 = '';
        modal.find('.modal-title').text('Update user ' + e.username + '.')
        $.each(e, function(index, item){
            if(jQuery.type(item) === "object" ) {
                //Is list of roles
                $.each(item, function(id, val){
                    $( '#roles option[value="'+id+'"]' ).prop('selected', true)
                })
            } else {
                if($("#" + index).length != 0 ) {
                    $("#" + index).val(item);
                }                
            }
        })
        data1 = getData();
        butSend.click(function() {
            data2 = getData();
            butSend.button('loading');
            cancBut.addClass('disabled');
            $.ajax({
                url: 'data/users.edit.php',
                data: { id: userId,
                        info: data2
                      }
            }).done(function( e ) {
                resetModal();
            }) //END AJAX
        })
    }) //END AJAX


}

function resetModal() {
    var modal = $('#dataModal');
    var butSend = $('#dataModalsendButton');
    var cancBut = $('#dataModalcancButton');
    cancBut.unbind(); //Remove on click listener
    butSend.unbind(); //Remove on click listener
    modal.modal('hide');
    butSend.button('reset');
    cancBut.button('reset');
    cancBut.removeClass('disabled');
    redrawTable();
}

function deleteUser(userId) {
	console.log( 'Delete: ' + userId );
}

function getData() {
    var data = {}
    $.each($('#dataModal form input'), function (index, value) {    
        data[value.id] = value.value;
    })
    data['roles'] = $('#dataModal form select').val();
    return data;
}

$().ready(function() {
	drawTable()
});

