<!DOCTYPE html>
<html>
<head>
<?php include('head.php'); ?>
<title>Générateur de validation / formulaire pour codeIgniter</title>
<script type="text/javascript" src="<?php echo base_url()?>js/generator.js"></script>
<script type="text/javascript" src="<?php echo base_url()?>js/jquery-1.6.4.js"></script>
</head>
<body>
<?php echo $this->lang->line('form_select_database'); ?>
<select name='choixBase' id='choixBase' onchange="javascript : choixTable(this.value)">
	<?php foreach($bases as $base) : ?>
		<option value="<?php echo $base; ?>"><?php echo $base; ?></option>
	<?php endforeach; ?>
</select>
<div id='listerTables'></div>
<div id='infosTable'></div>
<div id='codeGenere'></div>
</body>
</html>