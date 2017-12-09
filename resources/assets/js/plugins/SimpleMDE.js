(function ($) {
	$.fn.SimpleMDE = function () {
		this.each(function() {
			var simplemde = new SimpleMDE({
				element: this,
				status: false
			});
		});
	};
	
	jQuery(document).ready(function () {
		$('[data-type="simplemde"]').SimpleMDE();
	});
})(jQuery);