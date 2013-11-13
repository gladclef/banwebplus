typePopupNotifications = function() {
	this.init = function() {
		if (this.hasBeenInitialized) {
			return;
		}
		
		this.jwindow = $(window);
		$("body").append("<div id='popup_div'></div>");
		this.notifications = [];
		this.jwindow.resize(this.windowResized);
		this.hasBeenInitialized = true;
	}
	
	this.addNotification = function(innerHTML) {
		this.init();
		
		var right = 15;
		var top = 15;
		$.each(this.notifications, function(k,v) {
			if (v === null)
				return;
			top += v.getHeight()+5;
		});
		
		var o_notification = new typePopupNotification();
		var id = this.notifications.length;
		o_notification.init(innerHTML, {top:(top+"px"), right:(right+"px")}, id);
		this.notifications.push(o_notification);
		return id;
	}
	
	this.removeNotification = function(index) {
		this.init();
		
		if (typeof(this.notifications[index]) == 'object' && this.notifications[index] !== null) {
			this.notifications[index].remove();
			this.notifications[index] = null;
			var top = 15;
			$.each(this.notifications, function(k,v) {
				if (v === null)
					return;
				v.setAnimate({top:(top+"px")});
				top += v.getHeight()+5;
			});
			return true;
		}

		return false;
	}
	
	this.mouseHover = function(element) {
		var jelement = $(element);
		if (!jelement.hasClass('hover')) {
			jelement.addClass('hover');
		}
	}

	this.mouseOut = function(element) {
		var jelement = $(element);
		if (jelement.hasClass('hover')) {
			jelement.removeClass('hover');
		}
	}
	
	this.windowResized = function(event) {
		o_popup_notifications.init();
		
	}
}

typePopupNotification = function() {
	
	/**
     * Initialize the popup notification
	 * @param string innerHTML the inner html of the popup, in text format
	 * @param object css       the style of the popup, in object form (eg {left:'0px', top:'0px'})
	 */
	this.init = function(innerHTML, css, id) {
		this.innerHTML = innerHTML;
		this.jelement = null;
		this.id = id;

		this.create(innerHTML, css, id);
		this.popup();
	}

	this.create = function(innerHTML, css, id) {
		var removeCode = "<div class='popup_notification_closebutton' onmouseover='o_popup_notifications.mouseHover(this);' onmouseout='o_popup_notifications.mouseOut(this);' onclick='o_popup_notifications.removeNotification("+id+");'><div style='position:relative; left:4px;'>&#x2716</div></div>";
		this.jelement = $("<div class='popup_notification' onmouseover='o_popup_notifications.mouseHover(this);' onmouseout='o_popup_notifications.mouseOut(this);'>"+removeCode+innerHTML+"</div>");
		$("#popup_div").append(this.jelement);
		this.jelement.css(css);
	}

	this.popup = function() {
		this.jelement.css({ opacity:0 });
		this.jelement.animate({ opacity:1 }, 300);
	}
	
	this.getHeight = function() {
		var height = parseInt(this.jelement.height());
		height += parseInt(this.jelement.css("padding-top"));
		height += parseInt(this.jelement.css("padding-bottom"));
		return height;
	}

	this.remove = function() {
		this.jelement.stop(true,true);
		this.jelement.animate({ opacity:0 }, 300);
		this.jelement.remove();
	}

	this.setAnimate = function(css) {
		this.jelement.stop(true,true);
		this.jelement.animate(css, 300);
	}
}

o_popup_notifications = new typePopupNotifications();