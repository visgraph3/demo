<?php
$needDB=true;
$needTables=true;
include("rdfapi-php/tools/rdfdb-utils/config.inc.php");
include("rdfapi-php/tools/rdfdb-utils/utils.php");
include("rdfapi-php/tools/rdfdb-utils/setup.php");


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

$url = $_SERVER['REQUEST_URI'];


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

    header("Location: ".$url."?model=".$base);

}


if ( isset($_REQUEST["tripleadd"])) {
	$m=$db->getModel($_GET['model']);

    $s=$_REQUEST["subject"]!=""?new Resource($_REQUEST["subject"]):null;
    $p=$_REQUEST["predicate"]!=""?new Resource($_REQUEST["predicate"]):null;
    $o=$_REQUEST["object"]!=""?new Resource($_REQUEST["object"]):null;

    if ($s==null || $o==null || $p==null) {
      print "<div span='warning'>Musisz wypełnić wszystkie pola!</div>\n";
    } else {
      $m->add(new Statement($s,$p,$o));
    }
  }


$model = urlencode($_GET['model']);
$url = 'http://visgraph3.org/RDF/rdfapi-php/tools/rdfdb-utils/rdf.php?model='.$model;


?>
<!DOCTYPE html>
<html
xmlns:foaf="http://xmlns.com/foaf/0.1/"
xmlns:dc="http://purl.org/dc/elements/1.1/">
<head>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js" type="text/javascript"></script>
<script type="text/javascript" src="js/d3/d3.js"></script>
<script type="text/javascript" src="js/d3/d3.layout.js"></script>
<script type="text/javascript" src="js/d3/d3.geom.js"></script>
<script type="text/javascript">
var url = '<?=$url?>',
    thisUrl = document.URL;
</script>
<title>RDF</title>
<style>
.control_button{
  fill: #000;
  stroke: #000;
  border-radius: 4px;
}
.edit {
	width: 20%;
	float: left;

}
.graf {
	float: left;
	width: 80%;
}
form#add {
	padding: 15px;
}
input[type=text] {
	width: 100%;
}
</style>
</head>
<body>

<h2>Upload RDF</h2>
<form method="post" enctype="multipart/form-data">

<input type="hidden" name="MAX_FILE_SIZE" value="30000000" />
<span class="tableheader">File:</span><br/> <input type="file" name="rdffile" class="okbutton"/><br/>
<input type="submit" class="okbutton" value="Ok"/>

</form>





<div class="container">
	<div class="edit">
		<h1>Adding triples</h1>
	   <form name="dumbadd" method="get" id="add">
	       <input type="hidden" name="tripleadd" value="true" />
	       <input type="hidden" name="model" value="<?php echo $_GET['model'];?>"

	   <span class="tableheader">Subject:</span><br/>
	   <input type="text" name="subject" size="120" />


	   <span class="tableheader">Predicate:</span><br/>
	   <input type="text" name="predicate" size="120" /><br/>

	   <span class="tableheader">Object:</span><br/>
	   <input type="text" name="object" size="120" />

	   <input type="submit" value="Dodaj" class="okbutton"/>
	   </form>
	</div>
	<div class="graf">
		<form method="get" action="." class="form-inline">
		<input type="checkbox" checked id="properties"/>
		  <label>Hide properties</label>
		<input type="checkbox" id="hidePredicates"/>
		  <label>Hide predicates</label>
		<div id="preds" style="border: 1px solid black; position:absolute; display:none; color: white; background: rgba(0, 0, 0, 0.6);;"></div>
		</form>
		<div style="border-width: 1px; border-style: solid;min-height:500px;height:100%" id='chart'></div>
	</div>

</div>
<script type="text/javascript" src='js/main.js'>
</script>

</body>
</html>
