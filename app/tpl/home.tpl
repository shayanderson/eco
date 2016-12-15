<?php include PATH_TEMPLATE_GLOBAL . 'header.tpl'; ?>
<h1>Eco</h1>
<?=eco::flash()->get('error')?>
<p>
	<b>Eco is <i><?=$status?></i></b>
</p>
<?php include PATH_TEMPLATE_GLOBAL . 'footer.tpl'; ?>