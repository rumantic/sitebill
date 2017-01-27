(function($) {
	$.fn.slider = function(options) {
		options = $.extend({
			prevBtnSelector: '.prev',
			nextBtnSelector: '.next',
			containerSelector: '.container',
			duration: 1500
		}, options);

		return this.each(function() {
			var $this = $(this),
				container = $this.find(options.containerSelector),
				ul = container.find('ul'),
				li = ul.find('li'),
				widths = [],
				summaryWidth = 0,
				count = li.length,
				text = ul.html(),
				prevBtn = $this.find(options.prevBtnSelector),
				nextBtn = $this.find(options.nextBtnSelector),
				index = 0;

			li.each(function() {
				var $this = $(this),
					width = $this.outerWidth(true);
				widths.push(width);
				summaryWidth += width;
			});

			ul.html(text + text + text);
			ul.css('margin-left', summaryWidth * -1);

			function prev() {
				var width = 0, i = 0;
				if (ul.is(':animated')) {
					return false;
				}

				index--;

				for (; i < index; i++) {
					width += widths[i];
				}

				if (index < 0) {
					width = -widths[widths.length - 1];
				}

				ul.animate({
					'margin-left': (summaryWidth + width) * -1
				}, options.duration, function() {
					if (index < 0) {
						index = count - 1;
						width = 0;
						for (; i < index; i++) {
							width += widths[i];
						}
						ul.css('margin-left', (summaryWidth + width) * -1);
					}
				});				
				return false; 
			}

			function next() {
				var width = 0, i = 0;
				if (ul.is(':animated')) {
					return false;
				}
				
				index++;
				
				for (; i < index; i++) {
					width += widths[i];
				}

				ul.animate({
					'margin-left': (summaryWidth + width) * -1
				}, options.duration, function() {
					if (index > count - 1) {
						index = 0;
						ul.css('margin-left', summaryWidth * -1);
					}
				});
				return false;
			}
			
			//$(document).on('click', options.prevBtnSelector, prev);
			//$(document).on('click', options.nextBtnSelector, next);

			prevBtn.click(prev);
			nextBtn.click(next);

			$(window).keydown(function(e) {
				if (e.keyCode == 39) {
					next();
				}
				if (e.keyCode == 37) {
					prev();
				}
			});
		});
	}
})(jQuery);