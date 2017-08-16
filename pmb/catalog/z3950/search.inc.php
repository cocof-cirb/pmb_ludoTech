<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: search.inc.php,v 1.13 2015-04-03 11:16:22 jpermanne Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

print "<h1>$msg[z3950_recherche]</h1>";

$crit1 = $_COOKIE['PMB-Z3950-criterion1'];
//$crit2 = $_COOKIE['PMB-Z3950-criterion2'];
//$bool1 = $_COOKIE['PMB-Z3950-boolean'];
$clause = $_COOKIE['PMB-Z3950-clause'];

/* default values */
if ($crit1 == '') $crit1 = 'isbn';
//if ($bool1 == '') $bool1 = 'ET';

if($issn){
	$crit1 = 'issn';
	$isbn = $issn;
}

if ($clause != "") 
	$bibli_selectionees = explode(",",$clause);
else 
	$bibli_selectionees = array();

$select_bib="";
$requete_bib = "SELECT id_ludotech, libelle_ludotech FROM z_ludotech ORDER BY libelle_ludotech ";
$res_bib = pmb_mysql_query($requete_bib, $dbh);

while(($liste_bib=pmb_mysql_fetch_object($res_bib))) {
	
	$pos = array_search($liste_bib->id_ludotech, $bibli_selectionees);

	if ($pos === false) { 
		$select_bib.= "<input type='checkbox' name='bibli[]' value='".
			$liste_bib->id_ludotech."' class='checkbox' />&nbsp;".
			$liste_bib->libelle_ludotech."\n";
	} else {
		$select_bib.= "<input type='checkbox' name='bibli[]' value='".
			$liste_bib->id_ludotech."' checked class='checkbox' />&nbsp;".
			$liste_bib->libelle_ludotech."\n";
	}
	
	$select_bib.="<br />";
}

$z3950_search_tpl = str_replace('!!liste_bib!!', $select_bib, $z3950_search_tpl);
$z3950_search_tpl = str_replace('!!isbn!!', $isbn, $z3950_search_tpl);
$z3950_search_tpl = str_replace('!!id_notice!!', $id_notice, $z3950_search_tpl);
$z3950_search_tpl = str_replace('!!crit1!!', z_gen_combo_box ($crit1,"crit1"), $z3950_search_tpl);
/*
$z3950_search_tpl = str_replace('!!crit2!!', z_gen_combo_box ($crit2,"crit2"), $z3950_search_tpl);
$z3950_search_tpl = str_replace("<option value='$bool1'>", "<option value='$bool1' selected>", $z3950_search_tpl);
*/

print $z3950_search_tpl ;
