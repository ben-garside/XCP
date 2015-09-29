
table = $( '#actList' );

function redrawTable() {
	table.DataTable().ajax.reload()
}

function drawTable() {
	table.DataTable({
		"ajax": 'data/activities.list.php',
		"dom": 'lrtifp'
	});
}

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

function addActivity() {
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
            url: 'data/activities.edit.php',
            data: { add: true,
                    info: data2
                  }
        }).done(function( e ) {
        	console.log( e )
            modal.modal('hide');
        }) //END AJAX
    })
}

function editActivity(actId) {
	var modal = $('#dataModal');
    var butSend = $('#dataModalsendButton');
    var cancBut = $('#dataModalcancButton');
    modal.find('.modal-title').text('Edit user ' + actId + '.')
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
        url: 'data/activities.show.php',
        data: {id: actId}
    }).done(function( e ) {
        console.log(e);
        var data1 = '';
        var data2 = '';
        modal.find('.modal-title').text('Update activity ' + e.SHORT_NAME + '.')
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
                url: 'data/activities.edit.php',
                data: { id: actId,
                        info: data2
                      }
            }).done(function( e ) {
                console.log( e );
                modal.modal('hide');
            }) //END AJAX
        })
    }) //END AJAX
}

function deleteActivity(actId) {
	var modal = $('#deleteModal');
	var butDelete = $('#dataModaldelButton');
	var cancBut = $('#dataModaldelcancButton');
	modal.find('.modal-title').text('Delete user ' + actId + '?')
	modal.modal();
	butDelete.click(function() {
            butDelete.button('loading');
            cancBut.addClass('disabled');
            $.ajax({
                url: 'data/activities.edit.php',
                data: { id: actId,
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

