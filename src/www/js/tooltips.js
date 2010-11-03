jQuery(function() {

	jQuery('#tracker-monitor').tipsy({gravity: 'n', html: true, delayIn: 1000, delayOut: 500, fade: true});
	jQuery('#tracker-summary').tipsy({gravity: 'w', delayIn: 1000, delayOut: 500, fade: true});
	jQuery('#tracker-assigned_to').tipsy({gravity: 'w', delayIn: 1000, delayOut: 500, fade: true});
	jQuery('#tracker-priority').tipsy({gravity: 'w', html: true, delayIn: 1000, delayOut: 500, fade: true});
	jQuery('#tracker-status_id').tipsy({gravity: 'w', html: true, delayIn: 1000, delayOut: 500, fade: true});
	jQuery('#tracker-description').tipsy({gravity: 'w', html: true, delayIn: 1000, delayOut: 500, fade: true});
	jQuery('#tracker-canned_response').tipsy({gravity: 'w', html: true, delayIn: 1000, delayOut: 500, fade: true});
	jQuery('#tracker-comment').tipsy({gravity: 'w', delayIn: 1000, delayOut: 500, fade: true});
	jQuery('#tracker-new_artifact_type_id').tipsy({gravity: 'w', html:true, delayIn: 1000, delayOut: 500, fade: true});

	jQuery('select.trove-nodes').tipsy({gravity: 'w', html:true, delayIn: 1000, delayOut: 500, fade: true});
	jQuery('span.trove-nodes').tipsy({gravity: 'n', html:true, delayIn: 1000, delayOut: 500, fade: true});
});
