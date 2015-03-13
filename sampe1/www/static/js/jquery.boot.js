/**
 * 
 */
(function(win) {
	win._jQueryLoadedCallbacks = ( win._jQueryLoadedCallbacks instanceof Array )? win._jQueryLoadedCallbacks : [];
	win.jQueryLoaded = function(callback) {
		win._jQueryLoadedCallbacks.push(callback);
	};
})(window);