$.urlParam = function(name){
    var results = new RegExp('[\?&amp;]' + name + '=([^&amp;#]*)').exec(window.location.href);
    return results[1] || 0;
}

$(document).ready(function() {
console.log('data/activity.audit.php?xcpid=' + $.urlParam('xcpid'));
$('#audit_table').DataTable( { 	"ajax": 'data/activity.audit.php?xcpid=' + $.urlParam('xcpid'),
													"bAutoWidth": false,
													"order": [[ 0, "desc" ]],
													"pageLength": 10,
													"dom" : 'rt<"panel-footer"p>',
												});

	$('#table_collated').DataTable( {  "ajax": "data/data.php",
									"bAutoWidth": true,
									"order": [[ 0, "asc" ]],
									"pageLength": 10,
								});


})




