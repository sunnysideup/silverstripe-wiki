<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" >
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<!-- below is a hack to get the JS going -->
	<script type="text/javascript">
		window.onresize = function(){}
	</script>
	<script type="text/javascript" src="...prototype.js?m=1231712421"></script>
	<script type="text/javascript" src="...behaviour.js?m=1234929439"></script>
	<script type="text/javascript" src="...prototype_improvements.js?m=1226545801"></script>
	<!-- end of hack -->
	<% base_tag %>
	<title>Edit :: $Title</title>
</head>
<body class="frontEndCMS">

	<% if SavedSuccess %>
	<div id="ThankYouMessage">
		<p id="saved">Thank you for updating.
		<a id="close" href="#" onclick="self.parent.tb_remove();self.parent.location.reload();">Close</a></p>
	</div>
	<% end_if %>
	<div id="right" class="right">
		<% include EditPageForm %>
	</div>
</body>
</html>

