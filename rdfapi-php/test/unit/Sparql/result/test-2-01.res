$result = array();
$arr    = array();
$arr1   = array();
$arr2   = array();
$arr3   = array();



$res1 = new Resource("http://rdf.hp.com/r-2");
$res2 = new Resource("http://rdf.hp.com/p-1");
$res3 = new Resource("http://rdf.hp.com/r-1");
$res8 = new Resource("http://rdf.hp.com/p-2");


$res4 = new Literal("v-2-1");
$res5 = new Literal("v-1-2");
$res6 = new Literal("v-1-1");
$res7 = new Literal("v-2-2");







$arr["?x"]=$res1;
$arr["?y"]=$res2;
$arr["?z"]=$res4;


$arr1["?x"]=$res3;
$arr1["?y"]=$res8;
$arr1["?z"]=$res5;


$arr2["?x"]=$res3;
$arr2["?y"]=$res2;
$arr2["?z"]=$res6;

$arr3["?x"]=$res1;
$arr3["?y"]=$res8;
$arr3["?z"]=$res7;








$result["rowcount"]=3;
$result["hits"]=4;
$result["part"][]=$arr;
$result["part"][]=$arr1;
$result["part"][]=$arr2;
$result["part"][]=$arr3;



