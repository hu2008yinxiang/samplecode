/**
 * 
 */
(function(win) {
	win.jQuery(function() {
		win.jQueryLoaded = win.jQuery;
		if (win.jQuery.isArray(win._jQueryLoadedCallbacks)) {
			var cb = undefined;
			var cbs = win._jQueryLoadedCallbacks;
			win._jQueryLoadedCallbacks = undefined;
			while ((cb = cbs.pop()) != undefined) {
				win.jQuery(cb);
			}
		}
	});
})(window);
