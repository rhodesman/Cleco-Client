$(document).ready(function() {

	//Features
	if($('#overview').length){
		$('#overview').waypoint({
	    	handler: function(direction) {
	      	  var target = $(this.element);
	      	  if (direction === 'down') {
		      	target.addClass('is-animated');
				this.destroy();
			  }
		    },
		    offset: '90%'
		});	
	}
	
	//Features
	if($('#waypoint-feature-1').length){
		$('#waypoint-feature-1').waypoint({
	    	handler: function(direction) {
	      	  var target = $(this.element);
	      	  if (direction === 'down') {
		      	target.addClass('is-animated');
				this.destroy();
			  }
		    },
		    offset: '80%'
		});	
	}
	
	if($('#waypoint-feature-2').length){
		$('#waypoint-feature-2').waypoint({
	    	handler: function(direction) {
	      	  var target = $(this.element);
	      	  if (direction === 'down') {
		      	target.addClass('is-animated');
				this.destroy();
			  }
		    },
		    offset: '80%'
		});	
	}	
	
	if($('#waypoint-feature-3').length){
		$('#waypoint-feature-3').waypoint({
	    	handler: function(direction) {
	      	  var target = $(this.element);
	      	  if (direction === 'down') {
		      	target.addClass('is-animated');
				this.destroy();
			  }
		    },
		    offset: '80%'
		});	
	}	
	
	
	//Cordless
	if($('#waypoint-cordless').length){
		$('#waypoint-cordless').waypoint({
	    	handler: function(direction) {
	      	  var target = $(this.element);
	      	  if (direction === 'down') {
		      	target.addClass('is-animated');
				this.destroy();
			  }
		    },
		    offset: '90%'
		});	
	}	
	
	
	//Demo
	if($('#waypoint-demo').length){
		$('#waypoint-demo').waypoint({
	    	handler: function(direction) {
	      	  var target = $(this.element);
	      	  if (direction === 'down') {
		      	target.addClass('is-animated');
				this.destroy();
			  }
		    },
		    offset: '80%'
		});	
	}	
	
	//Demo Tab
	if($('#product-details').length){
		$('#product-details').waypoint({
	    	handler: function(direction) {
		      $('.popout-tab').toggleClass('is-visible');
		    },
		    offset: '35%'
		});	
	}
	
	if($('#contact').length){
		$('#contact').waypoint({
	    	handler: function(direction) {
		      $('.popout-tab').toggleClass('is-hidden');
		    },
		    offset: '35%'
		});	
	}		

});