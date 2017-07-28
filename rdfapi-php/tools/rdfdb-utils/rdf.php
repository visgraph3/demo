<?php

// ----------------------------------------------------------------------------------
// RDFDBUtils : RDF/XML 
// ----------------------------------------------------------------------------------

/** 
 * This outputs RDF/XML for a model
 * 
 * @version $Id: rdf.php 523 2007-08-14 09:24:11Z cweiske $
 * @author   Gunnar AAstrand Grimnes <ggrimnes@csd.abdn.ac.uk>
 *
 **/

$mori = $_GET['model'];
$needDB=true;
$needTables=true;
$needModel=true;

include("config.inc.php"); 
include("utils.php");

include("setup.php");

$model = $_GET['model'];

if ($db->modelExists($model)) { 
  $m=$db->getModel($model);

  $s = new RdfSerializer();


  header("Content-Type: application/rdf+xml");
  header('Content-Disposition: inline; filename="'.$model.'.rdf"');
  header('Expires: 0');
  header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
  header('Pragma: public');

  print $s->serialize($m->getMemModel());
}

?>