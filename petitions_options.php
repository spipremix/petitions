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

if (!defined("_ECRIRE_INC_VERSION")) return;

// si signature de petition, l'enregistrer avant d'afficher la page
// afin que celle-ci contienne la signature
if (isset($_GET['var_confirm'])) {
	$reponse_confirmation = charger_fonction('reponse_confirmation','formulaires/signature');
	$reponse_confirmation($_GET['var_confirm']);
}


?>