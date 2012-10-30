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

	$( '#refresh_stats_button' ).button({ disabled: true });

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

						 }
		}); // AJAX, LatestBreakDate
		
		var segment_total = [];
		
		// Segment processing of each world into time segments
		$.each(world_breakdate_array, function(world_index, world_item)
		{
			// Calculate the diff between last break date and last processed date
			// to determine how many segments to call
			// If the dates are the same, the segment is ommitted from processing
			var date_start = new Date.parse(world_item.last_date_processed);
			var date_current = date_start;
			var date_end = new Date.parse(world_item.latest_break_date);
			
			//alert("Start: " + date_start);
			//alert("Current: " + date_current);
			//alert("End: " + date_end);
			//alert("C/S: " + date_current.compareTo(date_start));
			//alert("C/E: " + date_current.compareTo(date_end));
			
			
			// Calculate how many months need to be processed
			segment_total[world_index] = 0 ;
			segment_current = 1;
			while(date_current.compareTo(date_end) == -1)
			{
				date_current = date_current.addDays(7);
				segment_total[world_index]++;
			}
			//alert("Total segments: " + segment_total[world_index]);
			
			var date_current = date_start; // reset the variable for processing
			
			var date_start = new Date.parse(world_item.last_date_processed);
			var date_current = date_start;
			var date_end = new Date.parse(world_item.latest_break_date);
			
			//alert("Current: " + date_current);
			//alert("C/S: " + date_current.compareTo(date_start));
			//alert("C/E: " + date_current.compareTo(date_end));
			
			// Process the current world, segmented by one week increments
			var page = 0; var max_pages = 0;
			while(date_current.compareTo(date_end) == -1)
			{
				debug = "W["+world_item.worldid+"]S["+segment_current+"/"+segment_total[world_index]+"]P["+page+"/?]";
				//alert("Inside...");
				//alert( debug );
				//segment_current = 0;
				var updated_users = 0;

				// Count how many pages of users there are
				//alert("SEND COUNT -> ["+world_item.worldid+"]["+$.format.date(date_current, "yyyy-MM-dd HH:mm:ss")+"]");
				$.ajax(
				{ url: 'inc/live/count_dirtyusers_byworld_bypage_daterange.php',
						data: { 
							world_id: world_item.worldid,
							start_date: $.format.date(date_current, "yyyy-MM-dd HH:mm:ss"),
							},
						type: 'POST',
						dataType: 'JSON',
						async: false,
						success: function(count_response)
								 {
									if(count_response[0]>0)
									{
										//alert(debug + 'OK COUNT - ' + count_response[0].player_count);
										max_pages = (Math.ceil( count_response[0].player_count / 10 ));
									}
									else
									{
										//alert("OK COUNT - (No Results)");
									}
								 },
						error: function(count_response)
								{
									alert(debug + "BAD COUNT -> ["+world_item.worldid+"]["+$.format.date(date_current, "yyyy-MM-dd HH:mm:ss")+"]");
									document.getElementById("refresh_stats_text").innerHTML = "Failed";
									max_pages = 0;
								}
						 
				}); // AJAX, Count how many pages of users there are
				
				page = 1;
				while(page <= max_pages)
				{
					debug = "W["+world_item.worldid+"]S["+segment_current+"/"+segment_total[world_index]+"]P["+page+"/"+max_pages+"]";
					//alert(debug);
					
					// AJAX, check next world/page/datesegment for users to update
					var getlist_response = 0;
					//alert("SEND GETLIST -> ["+world_item.worldid+"]["+page+"]["+$.format.date(date_current, "yyyy-MM-dd HH:mm:ss")+"]");
					$.ajax(
					{ url: 'inc/live/get_dirtyusers_byworld_bypage_daterange.php',
							data: { 
								world_id: world_item.worldid,
								page_num: page,
								start_date: $.format.date(date_current, "yyyy-MM-dd HH:mm:ss"),
								},
							type: 'POST',
							dataType: 'JSON',
							async: false,
							success: function(getlist_response)
									 {
											if($(getlist_response).size() > 0)
											{
												//var updated_users = 0;
												
												$.each(getlist_response, function(response_index, world_array)
												{ 
													//alert('Players ('+ $(world_array.player_list).size() +') : ' + world_array.player_list.join() );
													
													clicked_obj.switchClass( "ui-state-error", "ui-state-focus", 1000 );
													document.getElementById("refresh_stats_text").innerHTML = "Refreshing ["+segment_current+"/"+segment_total[world_index]+"]["+ page + "/"+ max_pages +"]";
													
													if( $(world_array.player_list).size() > 0 )
													{
														//alert("SEND UPDATE -> ["+world_item.worldid+"]["+world_array.player_list.join()+"]["+$.format.date(date_current, "yyyy-MM-dd HH:mm:ss")+"]");
														$.ajax(
														{ url: 'inc/live/update_newbreaks_byworld_playerlist_daterange.php',
																data: { 
																	world_id: world_item.worldid,
																	player_list: world_array.player_list.join(),
																	start_date: $.format.date(date_current, "yyyy-MM-dd HH:mm:ss"),
																	},
																type: 'POST',
																dataType: 'JSON',
																async: false,
																success: function(update_response)
																		 {
																			//alert(debug + "OK UPDATE - P[" + page + "] : U[" + update_response +"]");
																			updated_users += parseInt(update_response);
																		 },
																error: function(update_response)
																		{
																			alert(debug + "BAD UPDATE -> ["+world_item.worldid+"]["+world_array.player_list.join()+"]["+$.format.date(date_current, "yyyy-MM-dd HH:mm:ss")+"]");
																			alert(debug + "BAD UPDATE <- ["+update_response+"]");
																			document.getElementById("refresh_stats_text").innerHTML = "Failed";
																		}
																 
														}); // AJAX, update newbreaks, by world, by page
														
														/*
														$( "#refresh_stats_progressbar" ).progressbar({
															value: ((page / max_pages) * 100)
														});
														*/
														
													}
													else
													{
														alert(debug + "No players");	
													}
														
												});
												
												//alert('Updated: ' + updated_users);
												
												/*
												$( "#refresh_stats_progressbar" ).progressbar({
													value: 100
												});*/
												
												clicked_obj.switchClass( "ui-state-default", "ui-state-highlight", 1000 );
												clicked_obj.switchClass( "ui-state-error", "ui-state-highlight", 1000 );
												
												document.getElementById("refresh_stats_text").innerHTML = updated_users + " Users Updated";
												$( "#refresh_stats_records" ).val(updated_users);
											}
											else
											{
												document.getElementById("refresh_stats_text").innerHTML = "No Changes Detected";
											}
									 },
							error: function(getlist_response)
									{
											alert(debug + "BAD GETLIST");
											alert(getlist_response);
											document.getElementById("refresh_stats_text").innerHTML = "Failed";
									}
					}); // AJAX, check next world/page/datesegment for users to update
					
					page++;
					//alert ("Response size: " + $(getlist_response).size() );
				}

				//alert( "Progress: " + (segment_current / segment_total[world_index]) * 100);
				$( "#refresh_stats_progressbar" ).progressbar({
					value: ((segment_current) / segment_total[world_index]) * 100
				});

				// Advance the segment iterator
				date_current = date_current.addDays(7);
				segment_current ++;
				//date_current = date_end; // Stop after first segment, for debugging.
			}
			

			
			
			
		});

////////////////////////////////////////////////////////////////////////
		alert("END OF WORLD " + world_item.worldid);
		//return;
////////////////////////////////////////////////////////////////////////
		
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