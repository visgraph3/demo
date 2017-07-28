<?php

// ----------------------------------------------------------------------------------
// RDFDBUtils : N3 
// ----------------------------------------------------------------------------------

/** 
 * This outputs N3 for a model
 * 
 * @version $Id: n3.php 523 2007-08-14 09:24:11Z cweiske $
 * @author   Gunnar AAstrand Grimnes <ggrimnes@csd.abdn.ac.uk>
 *
 **/


$needDB=true;
$needTables=true;
$needModel=true;

include("config.inc.php"); 
include("utils.php");

include("setup.php");
$model = $_GET['model'];


if ($db->modelExists($model)) { 
  $m=$db->getModel($model);
  $s=new N3Serializer();



  header("Content-Type: application/rdf+n3");
  header('Content-Disposition: inline; filename="'.$model.'.n3"');
  header('Expires: 0');
  header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
  header('Pragma: public');

  print $s->serialize($m);
}

?>