<?php
// LC : New file for ludotech -> not natively in pmb

// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// | creator : Eric ROBERT                                                    |
// | modified : ...                                                           |
// +-------------------------------------------------+
// $Id: import.ludotech.inc.php,v 1.28 2008/12/08 17:29:33 dbellamy Exp $
/* (mdarville)
 * séparation de l'action "import" des actions "integrer" et "integrerexpl" qui ne doivent plus travailler de la meme maniere
 */
if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

// d�finition du minimum n�c�ssaire
include("$include_path/marc_tables/$lang/empty_words");
include("$class_path/iso2709.class.php");
include("$class_path/author.class.php");
include("$class_path/serie.class.php");
include("$class_path/editor.class.php");
include("$class_path/collection.class.php");
include("$class_path/subcollection.class.php");
include("$class_path/origine_notice.class.php");
include("$class_path/audit.class.php");
require ("$base_path/catalog/z3950/notice.inc.php");
include("$class_path/expl.class.php");
include("$include_path/templates/expl.tpl.php");

include ("$class_path/z3950_notice.class.php");
include ("$include_path/db_param_ludotech.php");

// require_once("$base_path/catalog/z3950/func_other.inc.php");
require_once("$class_path/parametres_perso.class.php");


/* (mdarville)
 * recherche des données nécessaire pour la connexion distante
 */

$sql_ludotech = "SELECT * FROM z_ludotech WHERE id_ludotech = $id_ludotech";

$res_ludotech =mysql_query($sql_ludotech) or print mysql_error();

$data_ludotech =mysql_fetch_array($res_ludotech);
$ip_ludotech = $data_ludotech['ip_ludotech'];
$db_ludotech = $data_ludotech['nameDB_ludotech'];
$user_ludotech = $data_ludotech['user_ludotech'];
$pwd_ludotech = $data_ludotech['pwd_ludotech'];

/* (mdarville)
 * connexion à la base de données distante pour rechercher les données sur la notice
 */


global $dbh;

$ludoConnection = db_ludotech_connect($db_ludotech,$user_ludotech,$pwd_ludotech,$ip_ludotech, $dbh);


$sql_notice = "SELECT * FROM notices, origine_notice
               WHERE notice_id = $znotices_id
                 AND origine_catalogage = orinot_id
               LIMIT 0,1";

$res_notice = mysql_query($sql_notice);

$data_notice = mysql_fetch_array($res_notice);

// recupération de(s) editeur(s) liés à cette notice
$tab_publishers = array();

if($data_notice['ed1_id'] != 0 || $data_notice['ed2_id'] != 0){
    // création de la condition de la requête
    $cond_publishers = " WHERE ";
    if($data_notice['ed1_id'] != 0)
    {
        $ed_id = $data_notice["ed1_id"];
        $cond_publishers .= "ed_id = $ed_id ";
        if($data_notice['ed2_id'] != 0) {
            $ed_id = $data_notice["ed2_id"];
            $cond_publishers .= " OR ed_id = $ed_id";
        }
    }
    elseif($data_notice["ed2_id"] != 0){
       $ed_id = $data_notice["ed2_id"];
       $cond_publishers .= " ed_id = $ed_id";
    }

    $sql_publishers = "SELECT * FROM publishers ".$cond_publishers;

    $res_publishers = mysql_query($sql_publishers);
    $cpt_publishers = O;
    while($ligne_publishers = mysql_fetch_array($res_publishers)){
        $tab_publishers[] = array(
                                "name"  => $ligne_publishers['ed_name'],
                                "ville" => $ligne_publishers['ed_ville']
                            );
    }
    /*
    for($cpt_publishers = 0; $cpt_publishers < count($tab_publishers); $cpt_publishers++)
    {
        print "name : ".$tab_publishers[$cpt_publishers]["name"]."<br>";
        print "ville : ".$tab_publishers[$cpt_publishers]["ville"]."<br>";
    }
    die("stop");
     * 
     */
}


// recupération de tout les auteurs liés à cette notice.

$sql_author = "SELECT author_type, author_date, author_name, author_rejete, author_see, author_web, index_author, author_comment, author_lieu, author_ville, author_pays, author_subdivision, author_numero, responsability_type, responsability_fonction
               FROM responsability, authors
               WHERE responsability_notice = $znotices_id
                 AND responsability_author = author_id
               ORDER BY responsability_type ASC";

$res_author = mysql_query($sql_author);

$tab_authors = array();
$cpt_authors = 0;
while($data_author=mysql_fetch_array($res_author)) {
    

    $tab_authors[] = array(
                    "author_name"             => $data_author['author_name'],
                    "author_rejete"             => $data_author['author_rejete'],
                    "author_type"             => $data_author['author_type'],
                    "author_date"             => $data_author['author_date'],
                    "author_see"              => $data_author['author_see'],
                    "author_web"              => $data_author['author_web'],
                    "index_author"            => $data_author['index_author'],
                    "author_comment"          => $data_author['author_comment'],
                    "author_lieu"             => $data_author['auhtor_lieu'],
                    "auhtor_ville"            => $data_author['author_ville'],
                    "auhtor_pays"             => $data_author['auhtor_pays'],
                    "author_subdivision"      => $data_author['author_subdivision'],
                    "author_numero"           => $data_author['author_numero'],
                    "responsability_type"     => $data_author['responsability_type'],
                    "responsability_fonction" => $data_author['responsability_fonction']
                 );
    $cpt_authors++;
}

/*
for($cpt_authors  = 0;$cpt_authors < count($tab_authors); $cpt_authors++){
    print $tab_authors[$cpt_authors]['author_type']."<br>";
    print $tab_authors[$cpt_authors]['author_date']."<br>";
    print $tab_authors[$cpt_authors]['author_see']."<br>";
    print $tab_authors[$cpt_authors]['author_web']."<br>";
    print $tab_authors[$cpt_authors]['index_author']."<br>";
    print $tab_authors[$cpt_authors]['author_comment']."<br>";
    print $tab_authors[$cpt_authors]['author_lieu']."<br>";
    print $tab_authors[$cpt_authors]['author_ville']."<br>";
    print $tab_authors[$cpt_authors]['author_pays']."<br>";
    print $tab_authors[$cpt_authors]['author_subdivision']."<br>";
    print $tab_authors[$cpt_authors]['author_numero']."<br>";
    print $tab_authors[$cpt_authors]['responsability_type']."<br>";
    print $tab_authors[$cpt_authors]['responsabilitu_fonction']."<br>";
}

die("stop");
 * 
 */

/*
$sql_perso = "select name, type, datatype, notices_custom_small_text, notices_custom_text, notices_custom_integer, notices_custom_date, notices_custom_float from notices_custom INNER JOIN notices_custom_values on idchamp=notices_custom_champ where notices_custom_origine = " . $znotices_id;

$res_perso = mysql_query($sql_perso);

$persos = array();
while($data_persos=mysql_fetch_array($res_perso)) {
	$persos[] = array(
			"a" => $data_persos['notices_custom_small_text'], // to adapt
			"n" => $data_persos['name']);
}
*/

// categories
$sql_categories = "select num_noeud from notices_categories where notcateg_notice = " . $znotices_id;
$res_categories = mysql_query($sql_categories);

$categories = array();

while($data_categories=mysql_fetch_array($res_categories)) {
	$categorie = new categories($data_categories['num_noeud'], "fr_FR");
	$categories[] =
			array (
						"categ_id" => $data_categories['num_noeud'],
						"category" => $categorie,
						
			);
						
}


// parametres persos

$persoClasse = new parametres_perso("notices");
$persoClasse->get_values($znotices_id);
$persoFields = $persoClasse->show_editable_fields($znotices_id, true);

db_ludotech_close();

// reconnexion à la base de données locale.

$dbh = connection_mysql();

if (!$id_notice) {
	print "<h1>$msg[z3950_integr_catal]</h1>";
} else {
	print "<h1>$msg[notice_z3950_remplace_catal]</h1>";
}

if ($action != "integrerexpl") {
		if ($source == 'form') {
			$notice = new z3950_notice ('form');
		} else {
			// avant affichage du formulaire : d�tecter si notice d�j� pr�sente pour proposer MAJ
            /*
			$isbn_verif = traite_code_isbn($ligne['isbn']) ;
			$suite_rqt="";
			if (isISBN($isbn_verif)) {
				if (strlen($isbn_verif)==13)
					$suite_rqt=" or code='".formatISBN($isbn_verif,13)."' ";
				else $suite_rqt="or code='".formatISBN($isbn_verif,10)."' ";
			}
			if ($isbn_verif) {
				$requete = "SELECT notice_id FROM notices WHERE code='$isbn_verif' ".$suite_rqt;
				$myQuery = mysql_query($requete, $dbh);
				$temp_nb_notice = mysql_num_rows($myQuery) ;
				if ($temp_nb_notice) $not_id = mysql_result($myQuery, 0 ,0) ;
					else $not_id=0 ;
			}
             *
             */
			// if ($not_id) METTRE ICI TRAITEMENT DU CHOIX DU DOUBLON echo "<script> alert('Existe d�j�'); </script>" ;
			$notice = new z3950_notice ('normal', $data_notice, 0, $tab_authors, $tab_publishers);
			$notice->persos = $persoFields;
			$notice->categories = $categories;
		}
	}

	$integration_OK="PASFAIT";
	$integrationexpl_OK="PASFAIT";
	switch ($action) {
		case "integrer" :
			if (!$id_notice) {
				$res_integration = $notice->insert_in_database();
			} else {
				$res_integration = $notice->update_in_database($id_notice);
			}
			$new_notice=$res_integration[0];
			$num_notice=$res_integration[1];
			if (($new_notice==0) && ($num_notice==0)) $integration_OK="ECHEC";
			if (($new_notice==0) && ($num_notice!=0)) $integration_OK="EXISTAIT";
			if (($new_notice==1) && ($num_notice!=0)) $integration_OK="OK";
			if (($new_notice==2) && ($num_notice!=0)) $integration_OK="UPDATE_OK";
			if (($new_notice==1) && ($num_notice==0)) $integration_OK="NEWRATEE";
			break;

		case "integrerexpl" :
			if ($notice_nbr == 0) {
				$integration_OK = "ECHEC";
			} else {
				$integration_OK = "OK";
				$num_notice = $notice_nbr;
				$formlocid="f_ex_section".$f_ex_location ;
				$f_ex_section=$$formlocid;
				$res_integrationexpl = create_expl($f_ex_cb, $num_notice, $f_ex_typdoc, $f_ex_cote, $f_ex_section, $f_ex_statut, $f_ex_location, $f_ex_cstat, $f_ex_note, $f_ex_prix, $f_ex_owner, $f_ex_comment );
				$new_expl=$res_integrationexpl[0];
				$num_expl=$res_integrationexpl[1];
				if (($new_expl==0) && ($num_expl==0)) $integrationexpl_OK="ECHEC";
				if (($new_expl==0) && ($num_expl!=0)) $integrationexpl_OK="EXISTAIT";
				if (($new_expl==1) && ($num_expl!=0)) $integrationexpl_OK="OK";
				if (($new_expl==1) && ($num_expl==0)) $integrationexpl_OK="NEWRATEE";
			}
			break;
		}
		/* ----------------------------------- */

	$msg[z3950_integr_expl_ok]       = str_replace ("!!f_ex_cb!!", $f_ex_cb, $msg[z3950_integr_expl_ok]      );
	$msg[z3950_integr_expl_existait] = str_replace ("!!f_ex_cb!!", $f_ex_cb, $msg[z3950_integr_expl_existait]);
	$msg[z3950_integr_expl_newrate]  = str_replace ("!!f_ex_cb!!", $f_ex_cb, $msg[z3950_integr_expl_newrate] );
	$msg[z3950_integr_expl_echec]    = str_replace ("!!f_ex_cb!!", $f_ex_cb, $msg[z3950_integr_expl_echec]   );

	switch ($integrationexpl_OK) {
		case "OK" :
			print "<hr/><strong>$msg[z3950_integr_expl_ok]</strong>&nbsp;<a id='liensuite' href=\"javascript:top.document.location='./catalog.php?categ=edit_expl&id=$num_notice&cb=$f_ex_cb'\">$msg[z3950_integr_expl_levoir]</a>";
			print "<script type='text/javascript'>document.getElementById('liensuite').focus();</script>" ;
			break;
		case "EXISTAIT" :
			print "<hr/><strong>$msg[z3950_integr_expl_existait]</strong>&nbsp;<a id='liensuite' href=\"javascript:top.document.location='./catalog.php?categ=edit_expl&id=$num_notice&cb=$f_ex_cb'\">$msg[z3950_integr_expl_levoir]</a>";
			print "<script type='text/javascript'>document.getElementById('liensuite').focus();</script>" ;
			break;
		case "NEWRATE" :
			print "<hr/><strong>$msg[z3950_integr_expl_newrate]</strong>";
			break;
		case "ECHEC" :
			print "<hr/><strong>$msg[z3950_integr_expl_echec]</strong>";
			break;
	}

	switch ($integration_OK) {
		case "OK" :
			print "<hr/>
					<span class='msg-perio'>".$msg[z3950_integr_not_ok]."</span>
					&nbsp;<a id='liensuite' href=\"javascript:top.document.location='./catalog.php?categ=isbd&id=$num_notice'\">$msg[z3950_integr_not_lavoir]</a>";
			print "<script type='text/javascript'>document.getElementById('liensuite').focus();</script>" ;
			break;
		case "UPDATE_OK" :
			print "<hr/>
					<span class='msg-perio'>".$msg[z3950_update_not_ok]."</span>
					&nbsp;<a id='liensuite' href=\"javascript:top.document.location='./catalog.php?categ=isbd&id=$num_notice'\">$msg[z3950_integr_not_lavoir]</a>";
			print "<script type='text/javascript'>document.getElementById('liensuite').focus();</script>" ;
			break;
		case "EXISTAIT" :
			if ($action=="integrer") {
				print "<hr/>
					<span class='msg-perio'>".$msg[z3950_integr_not_existait]."</span>
					&nbsp;<a id='liensuite' href=\"javascript:top.document.location='./catalog.php?categ=isbd&id=$num_notice'\">$msg[z3950_integr_not_lavoir]</a>";
				print "<script type='text/javascript'>document.getElementById('liensuite').focus();</script>" ;
			}
			break;
		case "NEWRATE" :
			if ($action=="integrer") print "<hr/>
					<span class='msg-perio'>".$msg[z3950_integr_not_newrate]."</span>";
			break;
		case "ECHEC" :
			if ($action=="integrer") print "<hr/>
					<span class='msg-perio'>".$msg[z3950_integr_not_echec]."</span>";
			break;
	}

	if ($integration_OK == "PASFAIT") {
		echo $notice->get_form ("./catalog.php?categ=z3950&".
			"znotices_id=$znotices_id&last_query_id=$last_query_id&action=integrer&source=form&".
			"tri1=$tri1&id_ludotech=$id_ludotech", $id_notice);
	}
	if (($integration_OK == "OK") | ($integration_OK == "EXISTAIT") | ($integration_OK == "UPDATE_OK")) {
		print "<hr/>
					<span class='right'><a id='liensuite' href='./catalog.php?categ=z3950&action=display&last_query_id=".$last_query_id."&tri1=$tri1'>".$msg[z3950_retour_a_resultats]."</a></span>";
		print "<script type='text/javascript'>document.getElementById('liensuite').focus();</script>" ;

		//$nex = new exemplaire('', 0, $num_notice);
		//$nex->zexpl_form ('./catalog.php?categ=z3950&znotices_id='.$znotices_id.'&last_query_id='.$last_query_id.'&action=integrerexpl&notice_nbr='.$num_notice.'&tri1='.$tri1.'&tri2='.$tri2);
	}


//--------------------------------------------------------------------------------------------------------------






/* ---------------------------------------------------------------------------------------------------------------*/


/* (mdarville)
 * va rechercher s'il y a une fonction particulire a charger pour la recherche precise
 * donc va voir, pour le server concerné, s'il y a un modele particulier.
 */
/*
if ($notice_org) {
	$requete="select z_marc,fichier_func from z_notices, z_bib where znotices_id='".$notice_org."' and znotices_bib_id=bib_id";
	$resultat=mysql_query($requete);
	$notice_org=@mysql_result($resultat,0,0);
	$modele=@mysql_result($resultat,0,1);
}
 *
 */
/* (mdarville)
 * on aura jamais de modèle --> on chargera toujours que func_other.inc.php
 */
/*
if (!$modele) {
	if ($z3950_import_modele) {
		require_once($base_path."/catalog/z3950/".$z3950_import_modele);
	} else {
		require_once("func_other.inc.php");
	}
} else {
	require_once($base_path."/catalog/z3950/".$modele);
}

if (!$id_notice) {
	print "<h1>$msg[z3950_integr_catal]</h1>";
} else {
	print "<h1>$msg[notice_z3950_remplace_catal]</h1>";
}
 *
 */
/* (mdarville)
 * recherche des données sur la notice selectionnées --> plus à faire pour nous car on a deja les données précise pour tout
 * récupérer dans la base de données distante.
 */
/*
$resultat=mysql_query("select znotices_id, znotices_bib_id, isbd, isbn, titre, auteur, z_marc from z_notices where znotices_id='$znotices_id' AND znotices_query_id='$last_query_id'");

$integration_OK="";
$integrationexpl_OK="";

while (($ligne=mysql_fetch_array($resultat))) {
	//$id_notice=$ligne["znotices_id"];
	$znotices_id=$ligne["znotices_id"];

	/* r�cup�ration du format des notices retourn�es par la bib -/
	$znotices_bib_id=$ligne["znotices_bib_id"];
	$rqt_bib_id=mysql_query("select format from z_bib where bib_id='$znotices_bib_id'");
	while (($ligne_format=mysql_fetch_array($rqt_bib_id))) {
		$format=$ligne_format["format"];
	}

	$resultat_titre=$ligne["titre"];
	$resultat_auteur=$ligne["auteur"];
	$resultat_isbd=$ligne["isbd"];
	$test_resultat++;
	$lien = $resultat_titre." / ".$resultat_auteur;
	print pmb_bidi(zshow_isbd($resultat_isbd, $lien));

	if ($action != "integrerexpl") {
		if ($source == 'form') {
			$notice = new z3950_notice ('form');
		} else {
			// avant affichage du formulaire : d�tecter si notice d�j� pr�sente pour proposer MAJ
			$isbn_verif = traite_code_isbn($ligne['isbn']) ;
			$suite_rqt="";
			if (isISBN($isbn_verif)) {
				if (strlen($isbn_verif)==13)
					$suite_rqt=" or code='".formatISBN($isbn_verif,13)."' ";
				else $suite_rqt="or code='".formatISBN($isbn_verif,10)."' ";
			}
			if ($isbn_verif) {
				$requete = "SELECT notice_id FROM notices WHERE code='$isbn_verif' ".$suite_rqt;
				$myQuery = mysql_query($requete, $dbh);
				$temp_nb_notice = mysql_num_rows($myQuery) ;
				if ($temp_nb_notice) $not_id = mysql_result($myQuery, 0 ,0) ;
					else $not_id=0 ;
			}
			// if ($not_id) METTRE ICI TRAITEMENT DU CHOIX DU DOUBLON echo "<script> alert('Existe d�j�'); </script>" ;
			$notice = new z3950_notice ($format, $ligne['z_marc']);
		}
	}

	$integration_OK="PASFAIT";
	$integrationexpl_OK="PASFAIT";
	switch ($action) {
		case "integrer" :
			if (!$id_notice) {
				$res_integration = $notice->insert_in_database();
			} else {
				$res_integration = $notice->update_in_database($id_notice);
			}
			$new_notice=$res_integration[0];
			$num_notice=$res_integration[1];
			if (($new_notice==0) && ($num_notice==0)) $integration_OK="ECHEC";
			if (($new_notice==0) && ($num_notice!=0)) $integration_OK="EXISTAIT";
			if (($new_notice==1) && ($num_notice!=0)) $integration_OK="OK";
			if (($new_notice==2) && ($num_notice!=0)) $integration_OK="UPDATE_OK";
			if (($new_notice==1) && ($num_notice==0)) $integration_OK="NEWRATEE";
			break;

		case "integrerexpl" :
			if ($notice_nbr == 0) {
				$integration_OK = "ECHEC";
			} else {
				$integration_OK = "OK";
				$num_notice = $notice_nbr;
				$formlocid="f_ex_section".$f_ex_location ;
				$f_ex_section=$$formlocid;
				$res_integrationexpl = create_expl($f_ex_cb, $num_notice, $f_ex_typdoc, $f_ex_cote, $f_ex_section, $f_ex_statut, $f_ex_location, $f_ex_cstat, $f_ex_note, $f_ex_prix, $f_ex_owner, $f_ex_comment );
				$new_expl=$res_integrationexpl[0];
				$num_expl=$res_integrationexpl[1];
				if (($new_expl==0) && ($num_expl==0)) $integrationexpl_OK="ECHEC";
				if (($new_expl==0) && ($num_expl!=0)) $integrationexpl_OK="EXISTAIT";
				if (($new_expl==1) && ($num_expl!=0)) $integrationexpl_OK="OK";
				if (($new_expl==1) && ($num_expl==0)) $integrationexpl_OK="NEWRATEE";
			}
			break;
		}
		/* ----------------------------------- -/

	$msg[z3950_integr_expl_ok]       = str_replace ("!!f_ex_cb!!", $f_ex_cb, $msg[z3950_integr_expl_ok]      );
	$msg[z3950_integr_expl_existait] = str_replace ("!!f_ex_cb!!", $f_ex_cb, $msg[z3950_integr_expl_existait]);
	$msg[z3950_integr_expl_newrate]  = str_replace ("!!f_ex_cb!!", $f_ex_cb, $msg[z3950_integr_expl_newrate] );
	$msg[z3950_integr_expl_echec]    = str_replace ("!!f_ex_cb!!", $f_ex_cb, $msg[z3950_integr_expl_echec]   );

	switch ($integrationexpl_OK) {
		case "OK" :
			print "<hr/><strong>$msg[z3950_integr_expl_ok]</strong>&nbsp;<a id='liensuite' href=\"javascript:top.document.location='./catalog.php?categ=edit_expl&id=$num_notice&cb=$f_ex_cb'\">$msg[z3950_integr_expl_levoir]</a>";
			print "<script type='text/javascript'>document.getElementById('liensuite').focus();</script>" ;
			break;
		case "EXISTAIT" :
			print "<hr/><strong>$msg[z3950_integr_expl_existait]</strong>&nbsp;<a id='liensuite' href=\"javascript:top.document.location='./catalog.php?categ=edit_expl&id=$num_notice&cb=$f_ex_cb'\">$msg[z3950_integr_expl_levoir]</a>";
			print "<script type='text/javascript'>document.getElementById('liensuite').focus();</script>" ;
			break;
		case "NEWRATE" :
			print "<hr/><strong>$msg[z3950_integr_expl_newrate]</strong>";
			break;
		case "ECHEC" :
			print "<hr/><strong>$msg[z3950_integr_expl_echec]</strong>";
			break;
	}

	switch ($integration_OK) {
		case "OK" :
			print "<hr/>
					<span class='msg-perio'>".$msg[z3950_integr_not_ok]."</span>
					&nbsp;<a id='liensuite' href=\"javascript:top.document.location='./catalog.php?categ=isbd&id=$num_notice'\">$msg[z3950_integr_not_lavoir]</a>";
			print "<script type='text/javascript'>document.getElementById('liensuite').focus();</script>" ;
			break;
		case "UPDATE_OK" :
			print "<hr/>
					<span class='msg-perio'>".$msg[z3950_update_not_ok]."</span>
					&nbsp;<a id='liensuite' href=\"javascript:top.document.location='./catalog.php?categ=isbd&id=$num_notice'\">$msg[z3950_integr_not_lavoir]</a>";
			print "<script type='text/javascript'>document.getElementById('liensuite').focus();</script>" ;
			break;
		case "EXISTAIT" :
			if ($action=="integrer") {
				print "<hr/>
					<span class='msg-perio'>".$msg[z3950_integr_not_existait]."</span>
					&nbsp;<a id='liensuite' href=\"javascript:top.document.location='./catalog.php?categ=isbd&id=$num_notice'\">$msg[z3950_integr_not_lavoir]</a>";
				print "<script type='text/javascript'>document.getElementById('liensuite').focus();</script>" ;
			}
			break;
		case "NEWRATE" :
			if ($action=="integrer") print "<hr/>
					<span class='msg-perio'>".$msg[z3950_integr_not_newrate]."</span>";
			break;
		case "ECHEC" :
			if ($action=="integrer") print "<hr/>
					<span class='msg-perio'>".$msg[z3950_integr_not_echec]."</span>";
			break;
	}

	if ($integration_OK == "PASFAIT") {
		echo $notice->get_form ("./catalog.php?categ=z3950&".
			"znotices_id=$znotices_id&last_query_id=$last_query_id&action=integrer&source=form&".
			"tri1=$tri1&tri2=$tri2", $id_notice);
	}
	if (($integration_OK == "OK") | ($integration_OK == "EXISTAIT") | ($integration_OK == "UPDATE_OK")) {
		print "<hr/>
					<span class='right'><a id='liensuite' href='./catalog.php?categ=z3950&action=display&last_query_id=".$last_query_id."&tri1=auteur&tri2=auteur'>".$msg[z3950_retour_a_resultats]."</a></span>";
		print "<script type='text/javascript'>document.getElementById('liensuite').focus();</script>" ;

		//$nex = new exemplaire('', 0, $num_notice);
		//$nex->zexpl_form ('./catalog.php?categ=z3950&znotices_id='.$znotices_id.'&last_query_id='.$last_query_id.'&action=integrerexpl&notice_nbr='.$num_notice.'&tri1='.$tri1.'&tri2='.$tri2);
	}

} /* fin while */

