$(document).ready(function() {

	$('.rotate-point-trigger').on('click', function(e) {
		var container = $(this).parents('.feature-container');
		var target = $(this).data('point');	
		var video = $('#rotate-point-' + target).find('video');
		if($(this).hasClass('no-video')){
			if($(this).hasClass('static')){
				
				container.find('.rotate-point-inner').fadeOut(200);
				$('#rotate-point-' + target).fadeIn(200);
				
			}else{
				var sprite = container.find('.product-sprite');
				var handle = container.find('.handle');
				
				container.find('.rotate-point-inner').fadeOut(200);
				
				var currTrans = $(sprite).css('transform').split(/[()]/)[1];
				if(currTrans){
					var posX = currTrans.split(',')[4];
				}else {
					var posX = 0;
				}
				var containerWidth = container.find('.product-viewer').width();
				var countX = Math.abs(posX) / containerWidth;
				var percent = countX * 4.16666666666666667;
					
				for(var i = 0; i < countX; i++) {
					var count = countX;
					setTimeout(function() {
						count--;
						var newPercent = 0 - (count * 4.16666666666666667);
				        sprite.css({
					        '-webkit-transform': 'translateX(' + newPercent + '%)',
					        '-ms-transform': 'translateX(' + newPercent + '%)',
							'transform': 'translateX(' + newPercent + '%)',
						});
				    }, 50 * i);
				}
				
				var animDur = countX * 50;
				
				handle.addClass("hey");
				handle.animate({
					'left': '0'
				}, animDur)
					
				
				
				setTimeout(function(){
					$(container).find('.cd-product-viewer-wrapper').removeClass('no-points');
					$('#rotate-point-' + target).fadeIn(200);
				}, animDur);
			}

		}else {
			if($(this).hasClass('static')){
				
				container.find('.rotate-point-inner').fadeOut(200);
				$('#rotate-point-' + target).fadeIn(200);
				video.get(0).load();
				video.get(0).play();
			}else {
				var sprite = container.find('.product-sprite');
				var handle = container.find('.handle');
				
				container.find('.rotate-point-inner').fadeOut(200);
				
				var currTrans = $(sprite).css('transform').split(/[()]/)[1];
				if(currTrans){
					var posX = currTrans.split(',')[4];
				}else {
					var posX = 0;
				}
				var containerWidth = container.find('.product-viewer').width();
				var countX = Math.abs(posX) / containerWidth;
				var percent = countX * 4.16666666666666667;
					
				for(var i = 0; i < countX; i++) {
					var count = countX;
					setTimeout(function() {
						count--;
						var newPercent = 0 - (count * 4.16666666666666667);
				        sprite.css({
					        '-webkit-transform': 'translateX(' + newPercent + '%)',
					        '-ms-transform': 'translateX(' + newPercent + '%)',
							'transform': 'translateX(' + newPercent + '%)',
						});
				    }, 50 * i);
				}
				
				var animDur = countX * 50;
				
				handle.addClass("hey");
				handle.animate({
					'left': '0'
				}, animDur)
					
				
				
				setTimeout(function(){
					$(container).find('.cd-product-viewer-wrapper').removeClass('no-points');
					$('#rotate-point-' + target).fadeIn(200);
					video.get(0).load();
					video.get(0).play();
		
				}, animDur);
			}
		}
		
		
	});
	
	$('.rotate-point-inner').on('click', '.close', function(e) {
		if($(e.delegateTarget).hasClass('no-video')){
			$( e.delegateTarget ).fadeOut(200); 
		}else{
			$( e.delegateTarget ).fadeOut(200); 
			var video = $(e.delegateTarget).find('video');
			$( e.delegateTarget ).fadeOut(200); 
			setTimeout(function(){
				video.get(0).pause();
			}, 100);
		}
		
	});
	

});