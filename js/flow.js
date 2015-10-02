count = 1 
function addRuleInput(ruleset) {
	$( '#xx' ).parent().clone().appendTo($( '.ruleset_' + ruleset )).html(function(index,html){return html.replace(/xx/g,"a" + count).replace(/yy/g, ruleset);}).fadeIn()
	setUpdates();
	count = count + 1;
}

function updateStatusDesc(statusId, name, desc) {
	console.log(statusId + ' ' + name + ' ' + desc);
	$.ajax({
		url: 'data/activity.mapping.update.php',
		data: {	type: 'info', id: statusId, name: name, desc: desc}
	})
	.done(function( e ) {
		console.log( "success: " + e );
		$( '#name_form' ).addClass('has-success').addClass('has-feedback')
		$( '#feedbackSuccess_name' ).fadeIn('slow');
		$( '#inputSuccess3Status_name' ).fadeIn('slow');
		$( '#feedbackSuccess_name' ).delay( 300 ).fadeOut('slow');
		$( '#inputSuccess3Status_name' ).delay( 300 ).fadeOut('slow', function() {
			$( '#name_form' ).removeClass('has-success').fadeIn('fast');
		});
		$( '#desc_form' ).addClass('has-success').addClass('has-feedback')
		$( '#feedbackSuccess_desc' ).fadeIn('slow');
		$( '#inputSuccess3Status_desc' ).fadeIn('slow');
		$( '#feedbackSuccess_desc' ).delay( 300 ).fadeOut('slow');
		$( '#inputSuccess3Status_desc' ).delay( 300 ).fadeOut('slow', function() {
			$( '#desc_form' ).removeClass('has-success').fadeIn('fast');
		});
	})
	.fail(function( e ) {
		console.log(e);
	})
	
}


function deleteStage(act, status) {
	var modal = $('#deleteModal');
	var butDelete = $('#dataModaldelButton');
	var cancBut = $('#dataModaldelcancButton');
	modal.find('.modal-title').text('Delete user ' + act + ":" + status + '?')
	modal.modal();
	butDelete.click(function() {
            butDelete.button('loading');
            cancBut.addClass('disabled');
            $.ajax({
                url: 'data/status.edit.php',
                data: { id: act + "," + status,
                        delete: true
                      }
            }).done(function( e ) {
                modal.modal('hide');
                location.reload();
            }) //END AJAX
        })
}

function updateRule(id, value, assign, action) {
	console.log(id + ' ' + value + ' ' + assign + ' ' + action);
	$.ajax({
		url: 'data/activity.mapping.update.php', // TODO
		data: {	type: 'rule', id: id, value: value, assign: assign, action: action}
	})
	.done(function( e ) {
		console.log( "success: " + e );
		$( '#'+id ).addClass('has-success')
		$( '#'+id ).next().addClass('has-success')
		$( '#ok_' +id ).fadeIn()
		setTimeout(function () {
            $( '#'+id ).removeClass('has-success');
            $( '#'+id ).next().removeClass('has-success')
            $( '#ok_' +id ).fadeOut()
        }, 2000);

	})
	.fail(function( e ) {
		console.log( "error" + e );
		$( '#'+id ).addClass('has-error');
		$( '#'+id ).next().addClass('has-error');
		$( '#err_' +id ).fadeIn()
		setTimeout(function () {
			$( '#'+id ).removeClass('has-error');
			$( '#'+id ).next().removeClass('has-error');
			$( '#err_' +id ).fadeOut()
		}, 2000);
	})
	
}

function removeRule(input) {
	var id = input.split("_")[1];
	var idType = input.split("_")[0];
	console.log(id);
	console.log(idType);
	switch (idType) {
	 	case 'editRule':
	 		console.log('delete: EDIT');
	 		$.ajax({
	 			url: 'data/activity.mapping.update.php',
	 			data: {	type: 'delete', id: id}
	 		})
	 		.done(function( e ) {
	 			console.log( "success: " + e );
	 			$('#' + id).parent().fadeOut('slow', function(){
	 				$('#' + id).parent().remove()
	 			})
	 		})
	 		.fail(function( e ) {
	 			console.log( "error" + e );
				$( '#'+id ).addClass('has-error');
				$( '#'+id ).next().addClass('has-error');
				$( '#err_' +id ).fadeIn()
				setTimeout(function () {
					$( '#'+id ).removeClass('has-error');
					$( '#'+id ).next().removeClass('has-error');
					$( '#err_' +id ).fadeOut()
				}, 2000);
	 		})
	 		break;
	 	case 'addRule':
	 		//
			console.log('delete: ' + id);
			$('#' + id).parent().fadeOut('300', function(){
				$('#' + id).parent().remove();
			})
			setBase()
			break;
	 }
}

function addRule(id, valueTo, valueFrom, assign, set, action) {

	console.log(id + ' ' + valueTo + ' ' + valueFrom + ' ' + assign + ' ' + set + ' ' + action);
	$.ajax({
		url: 'data/activity.mapping.update.php',
		data: {	type: 'addRule',toStage: valueTo, fromStage: valueFrom, assign: assign, set: set}
	})
	.done(function( e ) {
		console.log( "addRule: success: " + e );
		$( '#' + id ).find( 'select' ).attr('disabled', true);
		$( '#' + id ).next().find( 'select' ).attr('disabled', true);
		$( '#' + id ).next().next().find( 'input' ).attr('disabled', true);
		$( '#' + id ).next().next().next().find( 'button' ).attr('disabled', true);
		location.reload();
	})
	.fail(function( e ) {
		$( '#'+id ).addClass('has-error');
		$( '#'+id ).next().addClass('has-error');
		$( '#err_' +id ).fadeIn()
		setTimeout(function () {
			$( '#'+id ).removeClass('has-error');
			$( '#'+id ).next().removeClass('has-error');
			$( '#err_' +id ).fadeOut()
		}, 2000);
		console.log( "addRule: error" + e );
	})
}

function addStage() {
    var modal = $('#addModal');
    var butSend = $('#dataModalsendButton');
    var cancBut = $('#dataModalcancButton');
    $('#dataModalError').hide()
    modal.modal({
        backdrop: 'static',
        keyboard: false
    })

    cancBut.click(function() {
        modal.modal('hide')
        butSend.button('reset');
        cancBut.unbind(); //Remove on click listener
        butSend.unbind(); //Remove on click listener
    })

    butSend.click(function(event) {
    	//get data
    	var dataOut = [];
    	$( '#addModal' ).find( 'input, textarea, select' ).each(function(index, value){
    		id = $(value).attr('id');
    		va = $(value).val();
    		dataOut[id] = va;
    	})
    	console.log(dataOut);
    	$.ajax({
    		url: 'data/activity.mapping.update.php',
    		data: {type: 'addStage', act: dataOut['act'], status: dataOut['status'], name: dataOut['Name'], description: dataOut['Description']},
    	})
    	.done(function( e ) {
    		console.log("success");
    		console.log( e );
    		if(e == 'OK'){
    			//GOOD, carry on. Close the modal and redirect to new stage
    			window.location.href = window.location.href + "?stage=" + dataOut['act'] + "," + dataOut['status'];
    		} else {
    			// there was an error
				$('#dataModalError').find('#errorText').text( e );
				$('#dataModalError').fadeIn('slow');
    		}
    	})
    	.fail(function() {
    		console.log("error");
    		// there was an error
    	})
    	
    });
}

function update() {

    //Set updated values
    updateDescRequired = false;
    requireUpdateRule = false;
	updateData = {
		statusId: $( '#statusId' ).val(),
		name: $( '#name' ).val(),
		desc: $( '#description' ).val(),
		rules: []
	}
    $( '.editRule' ).each(function(index, el) {
		var id = $(el).find( 'div' ).attr( "id" );
		var action = $( '#action_' + id ).val();
		var stage  = $( '#activity_' + id ).val() + ':' + $( '#status_' + id ).val();
		var assign = $( '#assign_' + id ).is(':checked');
		updateData['rules'].push({id: id, value: stage, assign: assign, action: action});
	});

	//check if the name or description have chnaged, update if required
	if(updateData['name'] != initialData['name'] || updateData['desc'] != initialData['desc']) {
		updateStatusDesc(updateData['statusId'], updateData['name'], updateData['desc'])
	}

	// loop all rules to check for changes
	$.each(updateData['rules'], function(index, item) {
		$.each(item, function(key, item1) {
			if(item1 != initialData['rules'][index][key]){
				requireUpdateRule = true;
				console.log( 'Changed' )
			}
		});
		if(requireUpdateRule) {
			updateRule(updateData['rules'][index].id, updateData['rules'][index].value, updateData['rules'][index].assign, updateData['rules'][index].action);
			requireUpdateRule = false;
		}
	});

	// Look for any additional rules that might have been created and add them
	$.each($('.addRule'), function(index, el) {
		var id = $(el).find( 'div' ).attr( "id" );
		if(id != 'xx') {
			var action = $( '#action_' + id ).val();
			var stage  = $( '#activity_' + id ).val() + ':' + $( '#status_' + id ).val();
			var assign = $( '#assign_' + id ).is(':checked');
			var ruleSet = $( '#' + id ).parent().parent().attr('class').replace('ruleset_','')
			addRule(id, stage, $( '#currentStage' ).html(), assign, ruleSet, action)
		}
	});

	// set base data
	setBase()
}

function setBase() {
//Set initial values
	initialData = {
		name: $( '#name' ).val(),
		desc: $( '#description' ).val(),
		rules: []
	}
    $( '.editRule' ).each(function(index, el) {
		var id = $(el).find( 'div' ).attr( "id" );
		var action = $( '#action_' + id ).val();
		var stage  = $( '#activity_' + id ).val() + ':' + $( '#status_' + id ).val();
		var assign = $( '#assign_' + id ).is(':checked');
		initialData['rules'].push({id: id, value: stage, assign: assign, action: action});
	});
}

function setUpdates() {
	$( '.activity' ).unbind();
	$( '.activity' ).change(function( e ){ 
		var actButton = $(e.target)
		var staButton = $(e.target).parent().parent().find( '.status' )
		actVal = actButton.val()
		console.log( staButton.val() );
		$.ajax({
			url: 'data/activity.data.lookup.php',
			dataType: 'JSON',
			data: {	type: 'getStatuses',
					key: actVal},
		})
		.done(function( data ) {
			console.log("success");
			console.log( data );
			var optionsAsString = "<option disabled selected value=''>Status</option>";
			$.each(data, function(index, value){
				optionsAsString += "<option value='" + value + "'>" + value + "</option>";
			})
			staButton.html( optionsAsString )
		})
		.fail(function( data) {
			staButton.html( "<option disabled selected value=''>Status</option>" )
			console.log("error");
		})
	});
}

$(function() {
	setBase();
	setUpdates();

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

});
