<?php
function random_num($size) {
	$alpha_key = '';
	$keys = range('A', 'Z');

	for ($i = 0; $i < 2; $i++) {
		$alpha_key .= $keys[array_rand($keys)];
	}

	$length = $size - 2;

	$key = '';
	$keys = range(0, 9);

	for ($i = 0; $i < $length; $i++) {
		$key .= $keys[array_rand($keys)];
	}

	return $alpha_key . $key;
}
$needDB=true;
$needTables=true;

include("config.inc.php");
include("utils.php");
include("setup.php");

if ( isset($_FILES["rdffile"])) { 
	$r=new RdfParser();
    $m2=$r->generateModel($_FILES["rdffile"]["tmp_name"]);

    $base = $m2->baseURI;
    $base = str_replace('#', '', $base);

    $base = $base.'-'.random_num(5);

    $m=new MemModel($base);
  	$db->putModel($m,$base);

  	$m=$db->getModel($base);

  	$r=new RdfParser();
    $m2=$r->generateModel($_FILES["rdffile"]["tmp_name"]);

    $m->addModel($m2);
}
?>

<h2>Upload RDF</h2>
<form method="post" enctype="multipart/form-data" > 
	  
<input type="hidden" name="MAX_FILE_SIZE" value="30000000" />
<span class="tableheader">File:</span><br/> <input type="file" name="rdffile" class="okbutton"/><br/>
<input type="submit" class="okbutton" value="Ok"/> 

</form>