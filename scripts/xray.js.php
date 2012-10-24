<script type="text/javascript">
$(function(){
	$('.ui-state-default').hover(
		function(){ $(this).addClass('ui-state-hover'); }, 
		function(){ $(this).removeClass('ui-state-hover'); }
	);
	$('.ui-state-default').click(function(){ $(this).toggleClass('ui-state-active'); });
	$('.icons').append(' <a href="#">Toggle text</a>').find('a').click(function(){ $('.icon-collection li span.text').toggle(); return false; }).trigger('click');
	$( "#tabs" ).tabs();
	
////////////////////////////////
// Top Ratios
////////////////////////////////

<?php $stone_threshold_options = array(0, 100, 250, 500, 750, 1000); $stone_threshold_default = 500; ?>
	$(function() {
		var valMap = [<?php echo implode(", ", $stone_threshold_options); ?>];
		$("#stone_threshold_slider").slider({
			min: 0,
			max: valMap.length - 1,
			value: <?php 
				if(isset($_POST['stone_threshold']))
				{
					$stone_threshold_default = $_POST['stone_threshold'];
				}
				foreach($stone_threshold_options as $index => $item)
				{
					if($item == $stone_threshold_default){echo $index;}
				}
			?>,
			slide: function(event, ui) {                        
					$("#amount").val( valMap[ui.value] + " Stones");
					$("#stone_threshold").val( valMap[ui.value] );
			},
			change: function(event, ui) { $( "#Get_Ratios_ByWorldID_form" ).submit(); },
		});
		$("#amount").val( valMap[$("#stone_threshold_slider").slider("value")] + " Stones");
	});

	$( "#sort_by_radio" ).buttonset();
	$( "#worldid_radio" ).buttonset();
	$( "#limit_results_radio" ).buttonset();
	
	$( "#sort_by_radio" ).change(function(){ $( "#Get_Ratios_ByWorldID_form" ).submit(); });
	$( "#worldid_radio" ).change(function(){ $( "#Get_Ratios_ByWorldID_form" ).submit(); });
	$( "#limit_results_radio" ).change(function(){ $( "#Get_Ratios_ByWorldID_form" ).submit(); });

	$( '#refresh_stats_button' ).button();

	$( '#refresh_stats_button' ).click(function()
	{
		var clicked_obj = $(this);
		
		clicked_obj.switchClass( "ui-state-default", "ui-state-error", 1000 );
		clicked_obj.switchClass( "ui-state-highlight", "ui-state-error", 1000 );
		clicked_obj.button();
		document.getElementById("refresh_stats_text").innerHTML = "Checking...";
		
		$( "#sort_by_radio" ).buttonset("disable");
		$( "#worldid_radio" ).buttonset("disable");
		$( "#limit_results_radio" ).buttonset("disable");
		$("#stone_threshold_slider").slider("disable");

		$( "#refresh_stats_progressbar" ).progressbar({
			value: 0,
			disabled: false
		});
		
		// Get a list of worlds, and the last break date in each
		
		var world_breakdate_array;
		
		$.ajax(
		{ url: 'inc/live/get_world_latestbreakdate.php',
				data: { 
					world_id: 'ALL',
					},
				type: 'POST',
				dataType: 'json',
				async: false,
				success: function(response)
						 {
								//alert(response);
								world_breakdate_array = response;
								if(response > 0)
								{

								}

						 }
		}); // AJAX, LatestBreakDate
		
		var count_month = [];
		
		// Segment processing of each world into time segments
		$.each(world_breakdate_array, function(world_index, world_item)
		{
			// Calculate the diff between last break date and last processed date
			// to determine how many segments to call
			// If the dates are the same, the month is ommitted from processing
			var date_start = new Date.parse(world_item.last_date_processed);
			var date_current = date_start;
			var date_end = new Date.parse(world_item.latest_break_date);
			
			//alert("Start: " + start);
			//alert("Current: " + current);
			//alert("End: " + end);
			//alert(current.compareTo(end));
			
			
			// Calculate how many months need to be processed
			count_month[world_index] = 0 ;
			while(date_current.compareTo(end) == -1)
			{
				date_current = date_current.addMonths(1);
				count_month[world_index]++;
			}
			alert("Total months: " + count_month[world_index]);
			
			date_current = date_start; // reset the variable for processing
			// Process the current world, segmented by one month increments
			while(date_current.compareTo(end) == -1)
			{
				$.ajax(
				{ url: 'inc/live/count_dirtyusers_byworld_bydate.php',
						data: { 
							world_id: world_item.world_id,  /// TODO: Check that this is passed correctly from JS -> PHP
							start_date: date_current,
							},
						type: 'POST',
						dataType: 'JSON',
						async: false,
						success: function(response)
								 {
										//alert(clicked_obj.attr('id'));
										//alert(response);
										
										clicked_obj.switchClass( "ui-state-error", "ui-state-focus", 1000 );
										document.getElementById("refresh_stats_text").innerHTML = "Refreshing...";
		
										if($(response).size() > 0)
										{
											var updated_users = 0;
											
											$.each(response, function(response_index, world_array)
											{ 
												//alert('world_id' + ': ' + world_array.world_id); 
												//alert('player_count' + ': ' + world_array.player_count); 
												
												var page = 1;
												var max_pages = (Math.ceil(world_array.player_count / 10 ));
												
												//alert( 'Total Pages: ' + max_pages );
												while(page <= max_pages )
												{
													//alert( 'Current Page: ' + page );
													$.ajax(
													{ url: 'inc/live/update_newbreaks_byworld_bypage.php',
															data: { 
																world_id: world_array.world_id,
																page_num: page,
																},
															type: 'GET',
															dataType: 'JSON',
															async: false,
															success: function(response)
																	 {
																		//alert( 'Current Page: ' + page );
																		//alert('OK - ' + response);
																		updated_users += response;
																		//alert('OK - ' + updated_users);
																	 },
															error: function(response)
																	 {
																		//alert('ERROR - ' + response);
																	 }
															 
													}); // AJAX, update newbreaks, by world, by page
		
												//	$( "#refresh_stats_progressbar" ).progressbar.value((page / max_pages) * 100);
											
											$( "#refresh_stats_progressbar" ).progressbar({
												value: ((page / max_pages) * 100)
											});
													
													page++;
												}
		
											});
											
											//alert('Updated: ' + updated_users);
											
											$( "#refresh_stats_progressbar" ).progressbar({
												value: 100
											});
											
											clicked_obj.switchClass( "ui-state-default", "ui-state-highlight", 1000 );
											clicked_obj.switchClass( "ui-state-error", "ui-state-highlight", 1000 );
											
											document.getElementById("refresh_stats_text").innerHTML = updated_users + " Users Updated";
											$( "#refresh_stats_records" ).val(updated_users);
										}
										else
										{
											document.getElementById("refresh_stats_text").innerHTML = "No Changes Detected";
										}
		
										
										if(response.message == "HOST OK")
										{
		
										} else {
		/*
									
											document.getElementById("source_db_error_main").innerHTML = "An error occurred while validating MySQL Server.<BR>Please check the information and try again.";
											document.getElementById("source_db_error_specific").innerHTML = response.message;
											$( "#db_setup_error_dialog" ).dialog({
												autoOpen: true,
												width: 500,
												modal: false,
												buttons: {
													Ok: function() {
														$( this ).dialog( "close" );
													}
												}
											});
											
		*/
										}
								 }
				}); // AJAX, get all worlds

				// Advance the month iterator
				date_current = date_current.addMonths(1);
			}
			

			
			
			
		});

////////////////////////////////////////////////////////////////////////
		alert("STOP");
		return;
////////////////////////////////////////////////////////////////////////

		// Get a list of worlds, and how many new users there are to process in each

		
		// Paginate each list of users into groups

		
		// Deprecated method
		/*
		$.ajax(
		{ url: 'inc/live/update_newbreaks.php',
				dataType: 'json',
				success: function(response, data)
						 {
								//alert(clicked_obj.attr('id'));
								//alert(response);
								
								clicked_obj.switchClass( "ui-state-default", "ui-state-highlight", 1000 );
								clicked_obj.switchClass( "ui-state-error", "ui-state-highlight", 1000 );
								if(response > 0)
								{
									document.getElementById("refresh_stats_text").innerHTML = response + " Users Updated";
									$( "#refresh_stats_records" ).val(response);
									$( "#Get_Ratios_ByWorldID_form" ).submit();
								}
								else
								{
									document.getElementById("refresh_stats_text").innerHTML = "No Changes Detected";
									//$( "#Get_Ratios_ByWorldID_form" ).submit();
								}

								
								if(response.message == "HOST OK")
								{

								} else {

							
									document.getElementById("source_db_error_main").innerHTML = "An error occurred while validating MySQL Server.<BR>Please check the information and try again.";
									document.getElementById("source_db_error_specific").innerHTML = response.message;
									$( "#db_setup_error_dialog" ).dialog({
										autoOpen: true,
										width: 500,
										modal: false,
										buttons: {
											Ok: function() {
												$( this ).dialog( "close" );
											}
										}
									});
									

								}
						 }
		}); // AJAX
		*/
		
	});

});

	
////////////////////////////////
// Single Player Stats
////////////////////////////////
	$( '#xsingle_scan_player_now' ).button()
	.click(function()
	{
		var clicked_obj = $(this);
		//alert(clicked_obj.attr('id'));
		$.ajax(
				{ url: 'inc/live/update_playerinfo.php',
						data: { 
							player_id: <?php echo $player_id; ?>,
							},
						type: 'GET',
						dataType: 'json',
						success: function(response)
								 {
										clicked_obj.switchClass( "ui-state-default", "ui-state-highlight", 1000 );
										clicked_obj.switchClass( "ui-state-error", "ui-state-highlight", 1000 );
										//document.getElementById("xsingle_scan_player_now").innerHTML = " Completed ";
										//alert("OK");
										//alert(response);
										location.reload(true);

								 },
						error: function(response)
								{
										//alert("BAD");
										//alert(response);
								}
				}); // AJAX

	});
	
			// Deprecated method
		/*
		$.ajax(
		{ url: 'inc/live/update_newbreaks.php',
				dataType: 'json',
				success: function(response, data)
						 {
								//alert(clicked_obj.attr('id'));
								//alert(response);
								
								clicked_obj.switchClass( "ui-state-default", "ui-state-highlight", 1000 );
								clicked_obj.switchClass( "ui-state-error", "ui-state-highlight", 1000 );
								if(response > 0)
								{
									document.getElementById("refresh_stats_text").innerHTML = response + " Users Updated";
									$( "#refresh_stats_records" ).val(response);
									$( "#Get_Ratios_ByWorldID_form" ).submit();
								}
								else
								{
									document.getElementById("refresh_stats_text").innerHTML = "No Changes Detected";
									//$( "#Get_Ratios_ByWorldID_form" ).submit();
								}

								
								if(response.message == "HOST OK")
								{

								} else {

							
									document.getElementById("source_db_error_main").innerHTML = "An error occurred while validating MySQL Server.<BR>Please check the information and try again.";
									document.getElementById("source_db_error_specific").innerHTML = response.message;
									$( "#db_setup_error_dialog" ).dialog({
										autoOpen: true,
										width: 500,
										modal: false,
										buttons: {
											Ok: function() {
												$( this ).dialog( "close" );
											}
										}
									});
									

								}
						 }
		}); // AJAX
		*/

});


</script>