<?php

/***************************************************************************\
 *  SPIP, Systeme de publication pour l'internet                           *
 *                                                                         *
 *  Copyright (c) 2001-2009                                                *
 *  Arnaud Martin, Antoine Pitrou, Philippe Riviere, Emmanuel Saint-James  *
 *                                                                         *
 *  Ce programme est un logiciel libre distribue sous licence GNU/GPL.     *
 *  Pour plus de details voir le fichier COPYING.txt ou l'aide en ligne.   *
\***************************************************************************/


// #PETITION
// retourne '' si l'article courant n'a pas de petition
// le texte de celle-ci sinon (et ' ' si il est vide)
// cf FORMULAIRE_PETITION

// http://doc.spip.org/@balise_PETITION_dist
function balise_PETITION_dist ($p) {
	$nom = $p->id_boucle;
	$p->code = "quete_petitions(" .
			champ_sql('id_article', $p) .
			",'" .
			$p->boucles[$nom]->type_requete .
			"','" .
			$nom .
			"','" .
			$p->boucles[$nom]->sql_serveur .
			"', \$Cache)";
	$p->interdire_scripts = false;
	return $p;
}

# retourne le champ 'texte' d'une petition
if(!function_exists('quete_petitions')) {
function quete_petitions($id_article, $table, $id_boucle, $serveur, &$cache) {
	$retour = sql_getfetsel('texte', 'spip_petitions',("id_article=".intval($id_article)),'',array(),'','', $serveur);

	if ($retour === NULL) return '';
	# cette page est invalidee par toute petition
	$cache['varia']['pet'.$id_article] = 1;
	# ne pas retourner '' car le texte sert aussi de presence
	return $retour ? $retour : ' ';
}
}

?>