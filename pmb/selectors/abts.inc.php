<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: abts.inc.php,v 1.4 2015-04-03 11:16:20 jpermanne Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

// la variable $caller, pass�e par l'URL, contient le nom du form appelant
$base_url = "./select.php?what=abts&caller=$caller&param1=$param1&param2=$param2&no_display=$no_display&bt_ajouter=$bt_ajouter&dyn=$dyn&callback=$callback&infield=$infield"
		."&max_field=".$max_field."&field_id=".$field_id."&field_name_id=".$field_name_id."&add_field=".$add_field;

// contenu popup s�lection auteur
require('./selectors/templates/abts.tpl.php');

// affichage du header
print $sel_header;

// traitement en entr�e des requ�tes utilisateur
if ($deb_rech) $f_user_input = $deb_rech ;
if($f_user_input=="" && $user_input=="") {
	$user_input='';
} else {
	// traitement de la saisie utilisateur
	if ($user_input) $f_user_input=$user_input;
	if (($f_user_input)&&(!$user_input)) $user_input=$f_user_input;	
}

$bouton_ajouter="";
		
switch($action) {
	default:
		$sel_search_form = str_replace("!!bouton_ajouter!!", $bouton_ajouter, $sel_search_form);
		$sel_search_form = str_replace("!!deb_rech!!", htmlentities(stripslashes($f_user_input),ENT_QUOTES,$charset), $sel_search_form);
		print $sel_search_form;
		print $jscript;
		show_results($dbh, $user_input, $nbr_lignes, $page);
		break;
	}

// affichage des membres de la page
function show_results($dbh, $user_input, $nbr_lignes=0, $page=0, $id = 0) {
	global $nb_per_page;
	global $base_url;
	global $caller;
	global $no_display;
 	global $charset;
	global $msg;
	global $callback;
	global $param1;
	// on r�cup�re le nombre de lignes qui vont bien
//	if($param1) $restrict=" and abt_id  not in (select num_serialcirc_abt from serialcirc) ";
	if (!$id) {
		if($user_input=="") {
			$requete = "SELECT COUNT(1) FROM abts_abts where abt_id!='$no_display' $restrict";
		} else {
			$requete="select count(distinct abt_id) from abts_abts where abt_name like '%abt%' and abt_id!='$no_display' $restrict" ;
		}
		$res = pmb_mysql_query($requete, $dbh);
		$nbr_lignes = pmb_mysql_result($res, 0, 0);
	} else $nbr_lignes=1;
	
	if (!$page) $page=1;
	$debut = ($page-1)*$nb_per_page;

	if($nbr_lignes) {
		// on lance la vraie requ�te
		if (!$id) {
			if($user_input=="") {
				$requete = "SELECT * FROM abts_abts where abt_id!='$no_display' $restrict ";
				$requete .= "ORDER BY abt_name LIMIT $debut,$nb_per_page ";
			} else {
				$requete = "SELECT * FROM abts_abts where  abt_name like '%$user_input%' and abt_id!='$no_display' $restrict";
				$requete .= "ORDER BY abt_name LIMIT $debut,$nb_per_page ";
			}
		} else $requete="select * from abts_abts where abt_id='".$id."'";		
		print "<table>";
		$res = @pmb_mysql_query($requete, $dbh);
		while(($abt=pmb_mysql_fetch_object($res))) {
			$circlist_info="";
			$flag_circlist_info=0;
			$requete="select id_serialcirc from serialcirc where num_serialcirc_abt=".$abt->abt_id;
			$res_circlist=pmb_mysql_query($requete);
			print "<tr><td>";
			if (pmb_mysql_num_rows($res_circlist)) {
				$circlist_info="<img align='top' title='".$msg["serialcirc_img_info"]."'  height='18' width='18' alt='".$msg["serialcirc_img_info"]."' src='./images/icon_a.gif'>";
				$flag_circlist_info=1;
			}	
			print "$circlist_info</td><td width='100%'>";
			$entry = $abt->abt_name;
			print pmb_bidi("
			<a href='#' onclick=\"set_parent('$caller', '$abt->abt_id', '".htmlentities(addslashes($entry),ENT_QUOTES,$charset)."','$callback',$flag_circlist_info)\">
				$entry</a>");
			
			print "</td>";
		}
		print "</table>";
		pmb_mysql_free_result($res);

		// constitution des liens

		$nbepages = ceil($nbr_lignes/$nb_per_page);
		$suivante = $page+1;
		$precedente = $page-1;

		// affichage pagination
		print "<div class='row'>&nbsp;<hr /></div><div align='center'>";
		$url_base = $base_url."&user_input=".rawurlencode(stripslashes($user_input));
		$nav_bar = aff_pagination ($url_base, $nbr_lignes, $nb_per_page, $page, 10, false, true) ;
		print $nav_bar;
		print "</div>";
	}
}

print $sel_footer;