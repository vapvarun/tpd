// ( function ( $ ) {

// 	"use strict";

// 	window.BuddyBossChildTheme = {

// 		init: function () {
// 			setTimeout( function() {
// 				this.videoProgression();
// 			}, 5000 );
// 		},

// 		videoProgression: function(){
// 				var iframe = $('.ld-video iframe');
// 				var player = new Vimeo.Player(iframe);
// 				player.on('ended', function() {
// 					let url = $('.ld-table-list-item-preview').attr('href');
// 					window.location.href = url;
// 				});
// 		},
// 	}

// 	$( document ).on(
// 		'ready',
// 		function () {
// 			BuddyBossChildTheme.init();

// 		}
// 	);

// })( jQuery );