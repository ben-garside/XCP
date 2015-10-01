
table = $( '#userList' );

function redrawTable() {
	table.DataTable().ajax.reload()
}

function drawTable() {
	table.DataTable({
		"ajax": 'data/users.list.php',
		"dom": 'lrtifp'
	});
}

$('#passModal').on('hidden.bs.modal', function () {
	var modal = $('#passModal')
	var butDelete = $('#passModalsendButton');
	var cancBut = $('#passModalcancButton');
    cancBut.unbind(); //Remove on click listener
    butDelete.unbind(); //Remove on click listener
    modal.modal('hide');
    cancBut.button('reset');
    butDelete.button('reset');
    cancBut.removeClass('disabled');
    redrawTable();
});

$('#deleteModal').on('hidden.bs.modal', function () {
	var modal = $('#deleteModal');
	var butDelete = $('#dataModaldelButton');
	var cancBut = $('#dataModaldelcancButton');
    cancBut.unbind(); //Remove on click listener
    butDelete.unbind(); //Remove on click listener
    modal.modal('hide');
    cancBut.button('reset');
    butDelete.button('reset');
    cancBut.removeClass('disabled');
    redrawTable();
});

$('#dataModal').on('hidden.bs.modal', function () {
    var modal = $('#dataModal');
    var butSend = $('#dataModalsendButton');
    var cancBut = $('#dataModalcancButton');
    cancBut.unbind(); //Remove on click listener
    butSend.unbind(); //Remove on click listener
    modal.modal('hide');
    butSend.button('reset');
    cancBut.button('reset');
    cancBut.removeClass('disabled');
    $('#dataModal form').trigger("reset");
    $('#password').parent( '.form-group' ).remove()
    redrawTable();
})

function addUser() {
	console.log('Add user...');
	var modal = $('#dataModal');
    var butSend = $('#dataModalsendButton');
    var cancBut = $('#dataModalcancButton');
    modal.find('.modal-title').text('Add new user.')
    $('#dataModal form').append('<div class="form-group"><label for="password">Password</label><input type="password" class="form-control" id="password" placeholder="Password"></div>')
	modal.modal();

    cancBut.click(function() {
           modal.modal('hide');
        })

    butSend.click(function() {
        data2 = getData();
        butSend.button('loading');
        cancBut.addClass('disabled');
        $.ajax({
            url: 'data/users.edit.php',
            data: { add: true,
                    info: data2
                  }
        }).done(function( e ) {
        	console.log( e )
            modal.modal('hide');
        }) //END AJAX
    })
}

function editUser(userId) {
	var modal = $('#dataModal');
    var butSend = $('#dataModalsendButton');
    var cancBut = $('#dataModalcancButton');
    modal.find('.modal-title').text('Edit user ' + userId + '.')
	modal.modal();
    cancBut.click(function() {
            modal.modal('hide');
        })
	$(document).keyup(function(e) {
    	if (e.keyCode == 27) {
        	modal.modal('hide');
    	}
    })
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
                modal.modal('hide');
            }) //END AJAX
        })
    }) //END AJAX
}

function changePassword(userId) {
	console.log('Edit password: ' + userId);
	var modal = $( '#passModal' );
    var butSend = $('#passModalsendButton');
    var cancBut = $('#passModalcancButton');
    modal.find('.modal-title').text('Chnage password for ' + userId + '.')
    modal.modal();
    butSend.click(function( e ) {
	    $.ajax({
	        url: 'data/users.edit.php',
	        data: { id: userId,
	                password: $('#password').val()
	              }
	    }).done(function( e ) {
	        console.log( e );
	        modal.modal('hide');
	    }) //END AJAX
    });
}

function deleteUser(userId) {
	var modal = $('#deleteModal');
	var butDelete = $('#dataModaldelButton');
	var cancBut = $('#dataModaldelcancButton');
	modal.find('.modal-title').text('Delete user ' + userId + '?')
	modal.modal();
	butDelete.click(function() {
            butDelete.button('loading');
            cancBut.addClass('disabled');
            $.ajax({
                url: 'data/users.edit.php',
                data: { id: userId,
                        delete: true
                      }
            }).done(function( e ) {
                modal.modal('hide');
            }) //END AJAX
        })
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

