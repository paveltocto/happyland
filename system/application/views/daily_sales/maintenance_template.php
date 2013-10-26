<!doctype html>
<html>
<head>
  <meta charset='utf-8'>
  <title>Load &amp; Save (Ajax) - Handsontable</title>
  <script src="http://localhost/happyland/assets/js/lib/jquery.js"></script>
  <script src="http://localhost/happyland/assets/js/dist/jquery.handsontable.full.js"></script>
  <script src="http://localhost/happyland/assets/js/happy/daily.sales.js"></script>
  <link rel="stylesheet" media="screen" href="http://localhost/happyland/assets/css/dist/jquery.handsontable.full.css">
</head>
<body>

<input type="hidden" name="url-site" id="url-site" value="<?php echo site_url('daily_sales_controller/processForm');?>" />
<input type="hidden" name="url-data-operators" id="url-data-operators" value="<?php echo site_url('daily_sales_controller/getJSONOperators');?>" />
<button name="load">Load</button>
<button name="save">Save</button>
<label>Autosave</label>
<div id="error_message"></div>
<div id="example1"></div>
<button name="dump" data-dump="#example1" title="Prints current data source to Firebug/Chrome Dev Tools">
  Dump data to console
</button>


</body>