$(function() {

	$('#tracker-monitor').tipsy({gravity: 'n', html: true, delayIn: 1000, delayOut: 500, fade: true});
	$('#tracker-summary').tipsy({gravity: 'w', delayIn: 1000, delayOut: 500, fade: true});
	$('#tracker-assigned_to').tipsy({gravity: 'w', delayIn: 1000, delayOut: 500, fade: true});
	$('#tracker-priority').tipsy({gravity: 'w', html: true, delayIn: 1000, delayOut: 500, fade: true});
	$('#tracker-status_id').tipsy({gravity: 'w', html: true, delayIn: 1000, delayOut: 500, fade: true});
	$('#tracker-description').tipsy({gravity: 'w', html: true, delayIn: 1000, delayOut: 500, fade: true});
	$('#tracker-canned_response').tipsy({gravity: 'w', html: true, delayIn: 1000, delayOut: 500, fade: true});
	$('#tracker-comment').tipsy({gravity: 'w', delayIn: 1000, delayOut: 500, fade: true});
	$('#tracker-new_artifact_type_id').tipsy({gravity: 'w', html:true, delayIn: 1000, delayOut: 500, fade: true});

	$('select.trove-nodes').tipsy({gravity: 'w', html:true, delayIn: 1000, delayOut: 500, fade: true});
	$('span.trove-nodes').tipsy({gravity: 'n', html:true, delayIn: 1000, delayOut: 500, fade: true});
});
