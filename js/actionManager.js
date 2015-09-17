count = 1 
function addRuleInput(ruleset) {
	$( '#xx' ).parent().clone().appendTo($( '.rules' )).html(function(index,html){return html.replace(/xx/g,"a" + count).replace(/yy/g, ruleset);}).fadeIn()
	setUpdates();
	$( '#a' + count ).parent().animate({'backgroundColor': '#62C5A7'}).animate({'backgroundColor': ''})

	count = count + 1;	
}

function updateStatusDesc(id, action_type, action_title, action_name, action_description) {
	$.ajax({
		url: 'data/action.mapping.update.php',
		data: {	type: 'info', id: id, action_type: action_type, action_title: action_title, action_name: action_name, action_description: action_description}
	})
	.done(function( e ) {
		console.log( "success: " + e );
		$( '#action_title' ).parent().addClass('has-success');
		$( '#action_description' ).parent().addClass('has-success');
		$( '#action_name' ).parent().addClass('has-success');
		setTimeout(function () {
		$( '#action_title' ).parent().removeClass('has-success');
		$( '#action_description' ).parent().removeClass('has-success');
		$( '#action_name' ).parent().removeClass('has-success');
        }, 1000);
	})
	.fail(function( e ) {
		console.log(e);
	})
	
}

function newAction(){
	$.ajax({
		url: 'data/activity.data.lookup.php',
		data: {type: 'getNewAction'},
	})
	.done(function( id ) {
		console.log("success");
   		window.location.href = window.location.href + "?id=" + id;

	})
	.fail(function() {
		console.log("error");
	})
	
}

function updateField(data) {
	console.log('updating...');
	console.log(data);
	var id = data.field_id;
	delete data["field_id"]
	$.ajax({
		url: 'data/action.mapping.update.php', // TODO
		data: {	type: 'field', id: id, data: data}
	})
	.done(function( e ) {
		console.log( "success: " + e );
		$( '#'+id ).parent().find( 'input,select' ).parent().addClass('has-success');
		$( '#ok_' +id ).fadeIn()
		setTimeout(function () {
            $( '#'+id ).parent().find( 'input,select' ).parent().removeClass('has-success');
            $( '#ok_' +id ).fadeOut()
        }, 1000);

	})
	.fail(function( e ) {
		// console.log( "error" + e );
		$( '#'+id ).parent().find( 'input,select' ).parent().addClass('has-error');
		$( '#err_' +id ).fadeIn()
		setTimeout(function () {
            $( '#'+id ).parent().find( 'input,select' ).parent().removeClass('has-error');
            $( '#err_' +id ).fadeOut()
        }, 1000);
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
	 			url: 'data/action.mapping.update.php',
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

function addField(id, data) {
	console.log('adding...');
	console.log(data);
	console.log(id);
	$.ajax({
		url: 'data/action.mapping.update.php',
		data: {	type: 'addRule',data: data}
	})
	.done(function( e ) {
		console.log( "addRule: success: " + e );
		$( '#'+id ).parent().find( 'select' ).attr('disabled', true);
		$( '#'+id ).parent().find( 'input' ).attr('disabled', true);
		$( '#'+id ).parent().find( 'button' ).attr('disabled', true);
		location.reload();
	})
	.fail(function( e ) {
		$( '#'+id ).parent().find( 'input,select' ).parent().addClass('has-error');
		$( '#err_' +id ).fadeIn()
		setTimeout(function () {
			$( '#'+id ).parent().find( 'input,select' ).parent().removeClass('has-error');
			$( '#err_' +id ).fadeOut()
		}, 2000);
		console.log( "addRule: error" + e );
	})
}

function update() {

    //Set updated values
    updateDescRequired = false;
    requireUpdateRule = false;
	updateData = {
		action_title: $( '#action_title' ).val(),
		action_name: $( '#action_name' ).val(),
		action_description: $( '#action_description' ).val(),
		rules: []
	}
    $( '.editRule' ).each(function(index, el) {
		var id = $(el).find( 'div' ).attr( "id" );
		var test = {};
		if(id != 'xx') {
			$( "input[id$="+id+"],select[id$="+id+"]" ).each(function(index, val){
				var ellement = $(val);
				var key = ellement.attr('id').replace('_'+id,'');
				if(ellement.attr('type') == 'checkbox'){
					var value = ellement.is(':checked');
				} else{
					var value = ellement.val();
				}
				test[key] = value;
			})
			updateData['rules'].push(test);
		}
	});
	console.log( 'updateData' );
	console.log( updateData );

	//check if the name or description have chnaged, update if required
	if(updateData['action_name'] != initialData['action_name'] || updateData['action_description'] != initialData['action_description'] || updateData['action_title'] != initialData['action_title']) {
		updateStatusDesc($( '#action_id' ).val(), $( '#action_type' ).val(), updateData['action_title'], updateData['action_name'], updateData['action_description'])
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
			updateField(updateData['rules'][index]);
			requireUpdateRule = false;
		}
	});

	// Look for any additional rules that might have been created and add them
	$.each($('.addRule'), function(index, el) {
		var id = $(el).find( 'div' ).attr( "id" );
		var data = {};
		if(id != 'xx') {
			$( "input[id$="+id+"],select[id$="+id+"]" ).each(function(index, val){
				var ellement = $(val);
				var key = ellement.attr('id').replace('_'+id,'');
				if(key != 'field_id'){
					if(ellement.attr('type') == 'checkbox'){
						var value = ellement.is(':checked');
					} else{
						var value = ellement.val();
					}
					data[key] = value;					
				}

			})
			addField(id, data)
		}
	});

	// set base data
	setBase()
}

function setBase() {
//Set initial values
	initialData = {
		action_title: $( '#action_title' ).val(),
		action_name: $( '#action_name' ).val(),
		action_description: $( '#action_description' ).val(),
		rules: []
	}
    $( '.editRule' ).each(function(index, el) {
		var id = $(el).find( 'div' ).attr( "id" );
		var test = {};
		if(id != 'xx') {
			$( "input[id$="+id+"],select[id$="+id+"]" ).each(function(index, val){
				var ellement = $(val);
				var key = ellement.attr('id').replace('_'+id,'');
				if(ellement.attr('type') == 'checkbox'){
					var value = ellement.is(':checked');
				} else{
					var value = ellement.val();
				}
				test[key] = value;
			})
			initialData['rules'].push(test);
		}
	});
	console.log( 'initialData' );
	console.log( initialData );
}

function setUpdates() {
	$( '.activity' ).unbind();
	$( '.activity' ).change(function( e ){ 
		var actButton = $(e.target)
		var staButton = $(e.target).parent().parent().find( '.status' )
		actVal = actButton.val()
		console.log( staButton.val() );
		// $.ajax({
		// 	url: 'data/action.mapping.update.php',
		// 	dataType: 'JSON',
		// 	data: {	type: 'getStatuses',
		// 			key: actVal},
		// })
		// .done(function( data ) {
		// 	console.log("success");
		// 	console.log( data );
		// 	var optionsAsString = "<option disabled selected value=''>Status</option>";
		// 	$.each(data, function(index, value){
		// 		optionsAsString += "<option value='" + value + "'>" + value + "</option>";
		// 	})
		// 	staButton.html( optionsAsString )
		// })
		// .fail(function( data) {
		// 	staButton.html( "<option disabled selected value=''>Status</option>" )
		// 	console.log("error");
		// })
	});
}

$(function() {
	setBase();
	setUpdates();
	$('[data-toggle="popover"]').popover()

});
