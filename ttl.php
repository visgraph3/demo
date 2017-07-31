<?php
	$model = urlencode($_GET['model']);
	$u = 'http://visgraph3.org/rdfapi-php/tools/rdfdb-utils/rdf.php?model='.$model;
	include_once('namespaces.php');
	include_once('arc2/ARC2.php');

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $u);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
	curl_setopt($ch, CURLOPT_HTTPHEADER,array ("Accept: text/turtle, text/n3; q=0.9, application/turtle; q=0.8, application/n-triples; q=0.7, application/rdf+xml; q=0.6, application/json; q=0.4, */*; q=0.1"));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$data = curl_exec($ch);

	$parser = ARC2::getRDFParser();
	$parser->parse($u, $data);
	$triples = $parser->getTriples();

	$ser = ARC2::getTurtleSerializer(array('ns' => $ns));

	$index = ARC2::getSimpleIndex($triples, false) ;

	header("Content-Type: text/turtle");
	header('Content-Disposition: inline; filename="'.$model.'.ttl"');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');

	foreach( $index as $temat=>$value ) {
		foreach( $value as $predyktor=>$value2 ) {
			foreach( $value2 as $objekt ) {
				$typ = $objekt['type'];
				$datatype = $objekt['datatype'];

				if( $typ == 'literal' ) {
					echo '<'.$temat.'> <'.$predyktor.'> "'.$objekt['value'].'"'.(($datatype)?'^^<'.$datatype.'>':"").' .';
					printf("\n");
				} else {
					echo '<'.$temat.'> <'.$predyktor.'> <'.$objekt['value'].'> .';
					printf("\n");
				}
			}
		}
	}
?>
