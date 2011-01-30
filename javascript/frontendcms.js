jQuery(document).ready(
	function() {
		jQuery("a.greybox").click(
		function(){
				var t = jQuery(this).attr("title");
				jQuery.GB_show(
					jQuery(this).attr("href"), {
						height: 600,
						width: 800,
						animation: false,
						overlay_clickable: false,
						callback: function() {self.parent.location.reload();},
						caption: t
					}
				);
				return false;
			}
		);
	}
);