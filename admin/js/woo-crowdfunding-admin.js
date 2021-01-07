(function( $ ) {
	'use strict';

	// If contribution level stock is managed, stock status is hidden and stock quantity is shown
	function toggleQuantityInput() {
		$( '#cf-levels .level' ).each(function() {
			if ( $( this ).find( '.manage-stock' ).is( ':checked' ) ) {
				$( this ).find( '.stock-status' ).hide();
				$( this ).find( '.stock-quantity' ).show();
			} else {
				$( this ).find( '.stock-quantity' ).hide();
				$( this ).find( '.stock-status' ).show();
			}
		});
	}

	// Attaches click events to contribution levels controls
	function wireUpLevelButtons() {
		$( '.level-delete-button' ).off( 'click' ).click( deleteLevel );
		$( '.toggle-reward-details' ).off( 'click' ).click( toggleRewardDetails );
	}

	// Contribution level delete button functionality
	function deleteLevel() {
		var level = $( this ).parents( '.level' ).attr( 'rel' );
		$( '#level-' + level ).hide();
		$( '#_level_' + level + '_deleted' ).val( 1 );
	}

	// Contribution level show/hide details functionality
	function toggleRewardDetails() {
		var levelFields = $( this ).parent().next();

		if ( levelFields.is( ':visible' ) ) {
			levelFields.hide();
		} else {
			levelFields.show();
		}
	}

	$( document ).ready(function() {
		// jQuery UI Sortable init
		$( '#cf-levels' ).sortable({
			items:                '.level',
			cursor:               'move',
			axis:                 'y',
			handle:               '.sort',
			scrollSensitivity:    40,
			forcePlaceholderSize: true,
			helper:               'clone',
			opacity:              0.65,
			stop:                 function() {
				$( '#cf-levels .level' ).each(function( index, el ) {
					$( this ).find( '.level-menu-order' ).val( index );
				});
			}
		});

		// Adds manage stock checkbox functionality to newly created contribution levels
		$( 'body' ).delegate( '.manage-stock', 'change', function() {
			toggleQuantityInput();
		});

		toggleQuantityInput();

		// jQuery UI Datepicker init
		$( '#_cf_project_end_date' ).datepicker({
			dateFormat: 'yy-mm-dd'
		});

		// Creates new contribution level
		$( '#cf_level_add' ).click(function( e ) {
			e.preventDefault();
			var newLevel = $( '#new-level' ).clone();
			var next = parseInt( $( '#_cf_project_levels_count' ).val() );
			$( '#_cf_project_levels_count' ).val( next + 1 );
			newLevel.attr( 'id', 'level-' + next );
			newLevel.find('.level-menu-order').val( next );
			var cleanHtml = newLevel.html().replace( /new/g, next );
			newLevel.html( cleanHtml );
			newLevel.attr( 'rel', next );
			$( '#new-level' ).before( newLevel );
			newLevel.show();
			wireUpLevelButtons();
			$( '.level-fields' ).hide();
			newLevel.find( '.level-fields' ).show();
		});

		wireUpLevelButtons();
	});
})( jQuery );