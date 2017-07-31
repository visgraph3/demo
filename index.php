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


if ( isset($_FILES["rdffile"]) || $_POST["adres_url"] ) {
	$r=new RdfParser();
	$n3p=new N3Parser();
	if( isset($_FILES["rdffile"]) && $_FILES["rdffile"]['name'] != '' ) {
		if ( preg_match("/n3$/",$_FILES["rdffile"]["name"]) || preg_match("/ttl$/",$_FILES["rdffile"]["name"]) ) {
			$m2=$n3p->generateModel($_FILES["rdffile"]["tmp_name"]);
		} else {
    		$m2=$r->generateModel($_FILES["rdffile"]["tmp_name"]);
    	};
    } else if( $_POST["adres_url"] ) {
    	file_put_contents("tmp.rdf", fopen("http://rdf-translator.appspot.com/convert/detect/pretty-xml/".$_POST['adres_url'], 'r'));

    	if ( preg_match("/n3$/",$_FILES["rdffile"]["name"]) || preg_match("/ttl$/",$_FILES["rdffile"]["name"]) ) {
    		$m2=$n3p->generateModel("tmp.rdf");
    	} else {
    		$m2=$r->generateModel("tmp.rdf");
    	};
    }

    $base = $m2->baseURI;
    $base = str_replace('#', '', $base);

    $base = $base.'-'.random_num(5);

    $m=new MemModel($base);
  	$db->putModel($m,$base);

  	$m=$db->getModel($base);

    $m->addModel($m2);

    session_start();
    $_SESSION["model"]=$base;

    // header("Location: ".$url);
}

if ( isset($_REQUEST["tripleadd"])) {
	if( $_SESSION['model'] ) {
		$model = $_SESSION['model'];
	} else {
		$model = $_GET['model'];
	}
	$m=$db->getModel($model);
	$nmsp=$m->getParsedNamespaces();

	// Temat
	$temat = $_REQUEST["subject"];

	$temat_array = explode(':', $temat);
	$temat_prefix = $temat_array[0];
	$temat_nazwa = $temat_array[1];

	if( $temat_nazwa && $temat_prefix != '_' ) {
		if (in_array($temat_prefix, $nmsp)) {
			$key = array_search($temat_prefix, $nmsp);
			$nowy_temat = $key.''.$temat_nazwa;
		} else {
			print "<div class='container'><div class='alert alert-danger' role='alert'>The given prefix does not exist!</div></div>\n";
		}
	} else {
		$nowy_temat = $temat;
	}

	// Predykat
	$predykat = $_REQUEST["predicate"];

	$predykat_array = explode(':', $predykat);
	$predykat_prefix = $predykat_array[0];
	$predykat_nazwa = $predykat_array[1];

	if( $predykat_nazwa && $predykat_prefix != '_' ) {
		if (in_array($predykat_prefix, $nmsp)) {
			$key = array_search($predykat_prefix, $nmsp);
			$nowy_predykat = $key.''.$predykat_nazwa;
		} else {
			print "<div class='container'><div class='alert alert-danger' role='alert'>The given prefix does not exist!</div></div>\n";
		}
	} else {
		$nowy_predykat = $predykat;
	}

	// Objekt
	$objekt = $_REQUEST["object"];

	$objekt_array = explode(':', $objekt);
	$objekt_prefix = $objekt_array[0];
	$objekt_nazwa = $objekt_array[1];

	if( $objekt_nazwa && $objekt_prefix != '_' ) {
		if (in_array($objekt_prefix, $nmsp)) {
			$key = array_search($objekt_prefix, $nmsp);
			$nowy_objekt = $key.''.$objekt_nazwa;
		} else {
			print "<div class='container'><div class='alert alert-danger' role='alert'>The given prefix does not exist!</div></div>\n";
		}
	} else {
		$nowy_objekt = $objekt;
	}


	if (isset($_REQUEST["subject_bnode"])) {
		$nowy_temat = str_replace('_:', '', $nowy_temat);
		$s=$nowy_temat!=""?new BlankNode($nowy_temat):null;
    } else {
		$s=$nowy_temat!=""?new Resource($nowy_temat):null;
    }

    $p=$nowy_predykat!=""?new Resource($nowy_predykat):null;

    if (isset($_REQUEST["object_literal"])) {
    	$nowy_objekt = str_replace( array('"', "'") , '', $nowy_objekt);
		$o=$nowy_objekt!=""?new Literal($nowy_objekt):null;
    } else if (isset($_REQUEST["object_bnode"])) {
  		$nowy_objekt = str_replace('_:', '', $nowy_objekt);
		$o=$nowy_objekt!=""?new BlankNode($nowy_objekt):null;
	} else {
		$o=$nowy_objekt!=""?new Resource($nowy_objekt):null;
	}

    if ($s==null || $o==null || $p==null) {
      print "<div class='container'><div class='alert alert-danger' role='alert'>You must fill all the fields!</div></div>\n";
    } else {
      $m->add(new Statement($s,$p,$o));
      print "<div class='container'><div class='alert alert-success' role='alert'>New node was added.</div></div>\n";
    }
}

if ( isset($_REQUEST["zapisz_graf"])) {
	$model = $_SESSION['model'];

	unset($_SESSION['model']);

	header("Location: ".$_SERVER['REQUEST_URI']."?model=".$model);
}

if( $_SESSION['model'] ) {
	$model = urlencode($_SESSION['model']);
} else {
	$model = urlencode($_GET['model']);
}
$url = 'http://visgraph3.org/rdfapi-php/tools/rdfdb-utils/rdf.php?model='.$model;

if ( !isset($_FILES['rdffile']) && !isset($_POST['adres_url']) && !isset($_REQUEST['tripleadd']) && !isset($_REQUEST['namespaceeadd']) ) {
	$model = $_SESSION['model'];
	if( $model ) {
		$m=$db->getModel($model);
		$m->delete();
	};

	unset($_SESSION['model']);
}

if ( isset($_REQUEST["namespaceeadd"])) {

	if( $_SESSION['model'] ) {
		$model = $_SESSION['model'];
	} else {
		$model = $_GET['model'];
	}

	$m=$db->getModel($model);

	if (isset($_REQUEST["namespace"])){
		$nmsp=$_REQUEST["namespace"];
	}else{
		$nmsp = null;
	}

	if (isset($_REQUEST["namespaceprefix"])){
		$prefix=$_REQUEST["namespaceprefix"];
	}else{
		$prefix = null;
	}

	if ($nmsp==null || $prefix==null) {
		print "<div class='container'><div class='alert alert-danger' role='alert'>You need to enter a prefix and IRI!</div></div>\n";
	} else {
	  	$m->addNamespace($prefix,$nmsp);
	  	print "<div class='container'><div class='alert alert-success' role='alert'>New prefix was added.</div></div>\n";
	}
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <meta property="og:description" name="description" content="My RDF graph">
    <meta property="og:image" content="http://scalar.usc.edu/anvc_site/wp-content/uploads/2012/04/feat_standards_inline.gif">

    <title>RDF</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/jquery.floating-social-share.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="css/awesome-bootstrap-checkbox.css">
    <link rel="stylesheet" href="css/fileinput.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/rwd.css">
  </head>
  <body <?php if( $_SESSION['model'] || $_GET['model'] ) echo 'class="model"';?> >

  <div class="container" id="main">

	<?php if( !isset($_SESSION["model"]) && !isset($_GET['model']) ) { ?>
    <form class="form-signin" action="" method="POST" enctype="multipart/form-data">
      <h3 class="form-signin-heading">Select file or choose URL</h3>

      <div class="form-group row">
        <label for="rdffile" class="col-sm-2 col-form-label">File</label>
        <div class="col-sm-10">
          <input type="file" class="form-control-file" id="rdffile" aria-describedby="fileHelp" name="rdffile">
        </div>
      </div>

      <div class="form-group row">
        <label for="adres_url" class="col-sm-2 col-form-label">URL</label>
        <div class="col-sm-10">
          <input type="text" class="form-control" id="adres_url" name="adres_url" placeholder="website address">
        </div>
      </div>

      <button class="btn btn-lg btn-primary btn-block" type="submit">Submit</button>

      <h5 class="form-signin-heading" style="margin-top:10px">Recent added</h5>
      	<ul>
		<?php
			$modele = array_slice( array_reverse($db->listModels()) , 0, 5);
			foreach( $modele as $mo) {
				$m=$mo["modelURI"];
		?>
  			<li><a href="?model=<?php print urlencode($m)?>"><?php print $m?></a></li>
		<?php } ?>
		</ul>
    </form>
    <?php } else { ?>
    <?php $uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2); ?>
	<ol class="breadcrumb">
		<li><a href="<?php echo 'http://' . $_SERVER['HTTP_HOST'] . $uri_parts[0]; ?>">&laquo; Home page</a></li>
	</ol>
		<div class="row">
			<div class="col-md-3">
			<!-- <button id="reload">RELOAD</button> -->
			<!-- <button id="zoom">ZOOM</button> -->
				<h3>Add prefix</h3>
				<form name="dumbadd" action="" id="add" method="post">
					<div class="form-group">
						<label for="namespaceprefix">Prefix</label>
						<input type="text" class="form-control" id="namespaceprefix" name="namespaceprefix">
					</div>

					<div class="form-group">
						<label for="namespace">IRI</label>
						<input type="text" class="form-control" id="namespace" name="namespace">
					</div>

					<button type="submit" class="btn btn-primary">Submit</button>
					<input type="hidden" name="namespaceeadd" value="true" />
					<input type="hidden" name="model" value="<?php if( $_SESSION['model'] ) echo $_SESSION['model']; else echo $_GET['model'] ;?>">
				</form>
			</div>
			<div class="col-md-9">
				<h3>Graph <form action="" method="post"><button id="zapisz" type="submit" class="btn btn-primary">Save</button> <div class="btn-group">
  <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    Download
  </button>
  <div class="dropdown-menu">
    <!-- <a class="dropdown-item" href="http://codedev.pl/visgraph3/ttl.php?model=<?php if( $_SESSION['model'] ) echo $_SESSION['model']; else echo $_GET['model'] ;?>">.ttl</a> -->
    <a class="dropdown-item" href="http://visgraph3.org/rdfapi-php/tools/rdfdb-utils/download_n3.php?model=<?php if( $_SESSION['model'] ) echo $_SESSION['model']; else echo $_GET['model'] ;?>">Turtle</a>
    <a target="_blank" class="dropdown-item" href="http://visgraph3.org/rdfapi-php/tools/rdfdb-utils/rdf.php?model=<?php if( $_SESSION['model'] ) echo $_SESSION['model']; else echo $_GET['model'] ;?>">RDF/XML</a>
  </div>
</div> <input type="hidden" name="zapisz_graf" value="true"></form></h3>
				<form method="get" action="." class="form-inline">
					<div class="form-check checkbox checkbox-primary" style="margin-right:10px">
						<input type="checkbox" checked class="form-check-input" id="properties">
						<label class="form-check-label" for="properties">
							Hide properties
						</label>
					</div>
					<div class="form-check checkbox checkbox-primary">

							<input type="checkbox" class="form-check-input" id="hidePredicates">

						<label class="form-check-label" for="hidePredicates">
							Hide predicates
							</label>
							<div id="preds" style="border: 1px solid black; position:absolute; display:none; color: white; background: rgba(0, 0, 0, 0.6);padding:15px;top:25px;left:0"></div>

					</div>
				</form>
				<div id='chart'></div>
			</div>
		</div>
    <?php } ?>

  </div>

  <div id="opcje_dodawania" class="modal-content" style="position:absolute;display:none">
  	<div class="btn-group-vertical" id="buttony">
	  	<a href="#" class="btn btn-primary btn-sm" id="dodaj_z">Add from</a>
	  	<a href="#" class="btn btn-primary btn-sm" id="dodaj_do">Add to</a>
	</div>
	<form name="dumbadd" action="" method="post" style="display:none">
		<h5></h5>
		<div class="form-group">
			<label for="subject">Subject</label>
			<input type="text" class="form-control" id="subject" name="subject" autocomplete="off" >
			<div class="checkbox" id="subject_bnode">
				<label>
					<input type="checkbox" name="subject_bnode"> bNode
				</label>
			</div>

		</div>

		<div class="form-group">
			<label for="predicate">Predicate</label>
			<input type="text" class="form-control" id="predicate" name="predicate" autocomplete="off" >
		</div>

		<div class="form-group">
			<label for="object">Object</label>
			<input type="text" class="form-control" id="object" name="object" autocomplete="off" >
			<div class="checkbox" id="object_bnode">
				<label>
					<input type="checkbox" name="object_bnode"> bNode
				</label>
			</div>
			<div class="checkbox" id="object_literal">
				<label>
					<input type="checkbox" name="object_literal"> literal
				</label>
			</div>
		</div>


		<button type="submit" class="btn btn-primary">Add</button>

		<input type="hidden" name="tripleadd" value="true" />
		<input type="hidden" name="model" value="<?php if( $_SESSION['model'] ) echo $_SESSION['model']; else echo $_GET['model'] ;?>">
	</form>
  </div>

    <script src="js/jquery.min.js"></script>
    <script src="js/tether.min.js"></script>
    <script src="js/bootstrap.min.js"></script>

    <script type="text/javascript" src="js/d3/d3.js"></script>
	<script type="text/javascript" src="js/d3/d3.layout.js"></script>
	<script type="text/javascript" src="js/d3/d3.geom.js"></script>

	<script type="text/javascript" src="js/fileinput.min.js"></script>

	<script type="text/javascript">
	var url = '<?=$url?>',
	    thisUrl = document.URL;
	</script>

    <script type="text/javascript" src='js/main.js'></script>

    <script type="text/javascript" src='js/jquery.floating-social-share.min.js'></script>
    <script>
	  $("body.model").floatingSocialShare({
	    buttons: [
	      "facebook", "google-plus", "linkedin", "pinterest", "reddit",
	      "stumbleupon", "telegram", "tumblr", "twitter", "vk", "whatsapp"
	    ],
	    twitter_counter: true,
	    text: "share with: ",
	    url: window.location.href
	  });

	  $(document).ready(function() {
	  	$("#rdffile").fileinput({
	  		'showPreview':false,
	  		'showRemove':false,
	  		'showUpload':false,
	  		'showCancel':false,
	  	});
	  });
	</script>
  </body>
</html>
