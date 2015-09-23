
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
    modal.find('.modal-title').text('Update user details.')

	modal.modal();

    cancBut.click(function() {
            modal.modal('hide')
            butSend.button('reset');
            cancBut.unbind(); //Remove on click listener
            butSend.unbind(); //Remove on click listener
        })
    // Get data to fil in form
    $.ajax({
        url: 'data/',
        data: {
            type: 'getActionType',
            key: action_id
        },
    }).done(function(action_type) {

    })


}

function deleteUser(userId) {
	console.log( 'Delete: ' + userId );
}

$().ready(function() {
	drawTable()
});

