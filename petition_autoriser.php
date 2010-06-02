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


// fonction pour le pipeline
function petition_autoriser() {}

// Moderer la petition ?
// = modifier l'article correspondant
// = droits par defaut sinon (admin complet pour moderation de tout)
// http://doc.spip.org/@autoriser_modererpetition_dist
function autoriser_modererpetition_dist($faire, $type, $id, $qui, $opt) {
	return
		autoriser('modifier', $type, $id, $qui, $opt);
}

// Modifier une signature ?
// = jamais !
// http://doc.spip.org/@autoriser_signature_modifier_dist
function autoriser_signature_modifier_dist($faire, $type, $id, $qui, $opt) {
	return
		false;
}

function autoriser_petitions_reactions_bouton_dist($faire, $type='', $id=0, $qui = NULL, $opt = NULL){
	return sql_countsel('spip_signatures')>0;
}

?>
