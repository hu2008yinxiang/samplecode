(function(win) {
	win.jQuery(function() {
		var value = 0;
		var fEle;
		var curParts = win.jQuery.grep(win.location.pathname.split('/'),
				function(content, index) {
					return content.length > 0;
				});
		win.jQuery('.navbar ul.nav > li')
				.each(
						function(index, element) {
							element = win.jQuery(element);
							element.removeClass('active');
							var a = element.children('a');
							var url = a[0];
							if (url === undefined
									|| url.getAttribute('href')[0] == '#') {
								return;
							}
							if (url.href == win.location.href) {
								value = 999;
								fEle = element;
							}
							if (url.hostname == win.location.hostname) {
								var parts = win.jQuery.grep(url.pathname
										.split('/'), function(content, index) {
									return content.length > 0;
								});
								var v = 0;
								var cParts = curParts.slice(0);
								while (parts.length > 0
										&& parts.shift() == cParts.shift()) {
									++v;
								}
								if (v > value) {
									value = v;
									fEle = element;
								}
							}
						});
		if (fEle !== undefined)
			fEle.addClass('active');
	});
})(window);