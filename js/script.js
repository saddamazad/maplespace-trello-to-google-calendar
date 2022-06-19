(function( $ ) {
 
    "use strict";
     
    $(document).ready( function() {		
		let tokenArray = window.location.hash.split("token=");
		if( ("settings_page_mpc-settings" == pagenow) && (undefined !== tokenArray[1]) && (! tokenArray[1].includes("error")) ) {
			let token = tokenArray[1];

			// save the token in the DB options table
			$.ajax({
				type: "POST",
				dataType: "json",
				url: mpcObj.ajaxurl,			
				data: { action: "save-trello-token", token: token },
				beforeSubmit: function() {
					//do something if needed
				},
				success: function(res){
					if( res.success ) {
						window.location = '/wp-admin/options-general.php?page=mpc-settings';
					}
				}
			});
		}
		
		$("#trello_board").on("change", function() {
			if( $(this).val() != "" ) {
				let boardId = $(this).val();
				
				$.ajax({
					type: "POST",
					dataType: "json",
					url: mpcObj.ajaxurl,			
					data: { action: "get-trello-boards-lists", board_id: boardId },
					beforeSubmit: function() {
						//do something if needed
					},
					success: function(res){
						if( res.success ) {
							$("#trello_list").find('option').not(':first').remove();
							$("#trello_list").append( res.html );
						}
					}
				});
			} else {
				$("#trello_list").find('option').not(':first').remove();
			}
		});
	}); 
 
})(jQuery);