function testClick(xcpid, action_id) {
	//alert('cliked');
	showDataModal(xcpid, action_id, function(e){
		if( e ) {
			console.log('Carry on');
		} else {
			console.log('STOP!')
		}
		// do summin based on the return from the modal function
	});
	
}


function showDataModal(xcpid, action_id, callback) {
    var modal = $('#dataModal');
    var butSend = $('#dataModalsendButton');
    var cancBut = $('#dataModalcancButton');
    $('#dataModalLoader').show()
    $('#dataModalError').hide()
    $('form').html('');
    butSend.addClass('disabled');
    modal.modal({
        backdrop: 'static',
        keyboard: false
    })
    modal.find('.modal-title').text('Add data for: ' + xcpid)
    cancBut.click(function() {
            modal.modal('hide')
            butSend.button('reset');
            cancBut.unbind(); //Remove on click listener
            butSend.unbind(); //Remove on click listener
            callback();
        })
        //get action type
    $.ajax({
        url: 'data/activity.data.lookup.php',
        data: {
            type: 'getActionType',
            key: action_id
        },
    }).done(function(action_type) {
        console.log("success: " + action_type);
        switch (action_type) {
            case '1':
                //This is an edit of KEY/VALUE DATA
                //Get info for the form
                $.ajax({
                    url: 'testdata.php',
                    type: 'GET',
                    dataType: 'html',
                    data: {
                        xcpid: xcpid,
                        action_id: action_id
                    },
                }).done(function(data) {
                    //Set form title
                    $('#dataModalLoader').hide()
                    butSend.removeClass('disabled');
                    $('form').html(data);
                    // set some updates to remove help/error text
                    $('#dataModal').find('form').children(
                        '.form-group').focusin(function(
                        e) {
                        var errEll = '#err_' + $(e.target)
                            .attr('id')
                        $(errEll).html('')
                        $('#' + $(e.target).attr(
                            'id')).closest(
                            '.form-group').removeClass(
                            'has-error');
                        //$( '#' + $(e.target).attr('id') ).closest( '.form-group' ).removeClass('has-success');
                    });
                }).fail(function(data) {
                    $('#dataModalLoader').hide()
                    $('#dataModalError').find('#errorText')
                        .text('Unable to load form: ' +
                            data.status + " (" + data.statusText +
                            ")");
                    $('#dataModalError').fadeIn('slow');
                    butSend.button('error');
                })
                butSend.click(function(event) {
                    //Get form elements
                    var data = [];
                    $('#dataModal').find('form').children(
                        '.form-group').each(function(
                        index, value) {
                        var ell = $(value).find(
                            'input,textarea');
                        var nodeType = $(ell).prop(
                            'nodeName');
                        switch (nodeType) {
                            case 'INPUT':
                                var val = $(value).find(
                                    'input').val();
                                var id = $(value).find(
                                    'input').attr(
                                    'id');
                                var src = $(value).find(
                                    'input').attr(
                                    'data-source'
                                );
                                break;
                            case 'TEXTAREA':
                                var val = $(value).find(
                                        'textarea')
                                    .val();
                                var id = $(value).find(
                                        'textarea')
                                    .attr('id');
                                var src = $(value).find(
                                        'textarea')
                                    .attr(
                                        'data-source'
                                    );
                                break;
                            default:
                        }
                        data.push({
                            "id": id,
                            "value": val,
                            "source": src
                        });
                    });
                    butSend.addClass('disabled');
                    $.ajax({
                        url: 'testupdate.php',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            data: JSON.stringify(
                                data),
                            xcpid: xcpid,
                            action_id: action_id
                        },
                    }).done(function(updateData) {
                        if (updateData.dbStatus ==
                            "100") {
                            butSend.unbind(); //Remove on click listener
                            cancBut.unbind(); //Remove on click listener
                            modal.modal('hide')
                            callback(true);
                        } else if (updateData.dbStatus ==
                            "200") {
                            //validation error	
                            $('#dataModalError').find(
                                '#errorText').html(
                                '<strong>Uh oh,</strong> there were some errors...<br><span id="retErr"></span>'
                            );
                            $.each(updateData.details,
                                function(index,
                                    error) {
                                    if (error.status ==
                                        '301') {
                                        $(
                                            '#err_' +
                                            index
                                        ).html(
                                            '<i class="fa fa-exclamation"></i> ' +
                                            error
                                            .message
                                        );
                                        $('#' +
                                            index
                                        ).closest(
                                            '.form-group'
                                        ).addClass(
                                            'has-error'
                                        );
                                    }
                                    if (error.status ==
                                        '100') {
                                        $('#' +
                                            index
                                        ).closest(
                                            '.form-group'
                                        ).addClass(
                                            'has-success'
                                        );
                                    }
                                })
                            $('#dataModalError').fadeIn(
                                'slow');
                            butSend.removeClass(
                                'disabled');
                        } else {
                            butSend.button('error');
                            $('#dataModalError').find(
                                '#errorText').html(
                                'Unknown error: ' +
                                updateData.dbStatus +
                                " (" +
                                updateData.message +
                                ")<br>Please contact your Administrator."
                            );
                            $('#dataModalError').fadeIn(
                                'slow');
                        }
                    }).fail(function(data) {
                        butSend.button('error');
                        $('#dataModalError').find(
                            '#errorText').html(
                            'Network error: ' +
                            data.dbStatus +
                            " (" + data.statusText +
                            ")<br>Please contact your Administrator."
                        );
                        $('#dataModalError').fadeIn(
                            'slow');
                    })
                });
                break;
            case '2':
                //CHANGE TO SERIOUS DATA
                // TODO
                $('#dataModalLoader').hide()
                $('#dataModalError').find('#errorText').text(
                    'I have not done this bit yet');
                $('#dataModalError').fadeIn('slow');
                butSend.button('error');
                break;
            default:
                //default code block
                $('#dataModalLoader').hide()
                $('#dataModalError').find('#errorText').text(
                    'Unknown action: ' + action_id);
                $('#dataModalError').fadeIn('slow');
                butSend.button('error');
        }
    }).fail(function(data) {
        $('#dataModalLoader').hide()
        butSend.button('error');
        $('#dataModalError').find('#errorText').html(
            'Network error: ' + data.status + " (" + data.statusText +
            ")<br>Please contact your Administrator.");
        $('#dataModalError').fadeIn('slow');
    })
}