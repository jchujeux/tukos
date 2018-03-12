<?php
namespace TukosLib\Objects\Sports\Programs;

use TukosLib\Utils\Feedback;
use TukosLib\Objects\Questionnaire as ParentQuestionnaire;
use TukosLib\TukosFramework as Tfk;

trait Questionnaire {
	
	use ParentQuestionnaire;
	
	protected static $mapping = [
		0 => ['object' => 'sptprograms', 'col' => 'questionnairetime', 'method' => 'dateTime'],
		1 => ['object' => 'sptathletes', 'col' => 'name', 'method' => 'capitalize'],
		2 => ['object' => 'sptathletes', 'col' => 'firstname', 'method' => 'capitalize'],
		3 => ['object' => 'sptathletes', 'col' => 'telmobile', 'method' => 'phoneNumber'],
		4 => ['object' => 'sptathletes', 'col' => 'email'],
		5 => ['object' => 'sptathletes', 'col' => 'birthdate', 'method' => 'date'],
		6 => ['object' => 'sptathletes', 'col' => 'weight', 'method' => 'weight'],
		7 => ['object' => 'sptathletes', 'col' => 'height', 'method' => 'height'],
		8 => ['object' => 'sptathletes', 'col' => 'postaladdress'],
		9 => ['object' => 'sptathletes', 'col' => 'profession'],
		10 => ['object' => 'sptathletes', 'col' => 'maritalstatus'],
		11 => ['object' => 'sptprograms', 'col' => 'comments', 'append' => '<b>Palmarès: </b>'],
		12 => ['object' => 'sptprograms', 'col' => 'comments', 'append' => "<p><b>Semaine d'entraînement type: </b>"],
		13 => ['object' => 'sptprograms', 'col' => 'comments', 'append' => "<p><b>Nombre d'heures d'entraînement: </b>"],
		14 => ['object' => 'sptprograms', 'col' => 'comments', 'append' => "<p><b>Nombre d'années de pratique: </b>"],
		15 => ['object' => 'sptathletes', 'col' => 'antecedents'],
		16 => ['object' => 'sptprograms', 'col' => 'comments', 'append' => "<p><b>Qualité de sommeil: </b>"],
		17 => ['object' => 'sptprograms', 'col' => 'comments', 'append' => "<p><b>Qualité d'alimentation: </b>"],
		18 => ['object' => 'sptprograms', 'col' => 'comments', 'append' => "<p><b>Objectif(s) principaux cette année: </b>"],
		19 => ['object' => 'sptprograms', 'col' => 'comments', 'append' => "<p><b>Objectifs secondaires de la saison: </b>"],
		20 => ['object' => 'sptprograms', 'col' => 'comments', 'append' => "<p><b>Disponibilité pour s'entraîner dans la semaine: </b>"],
		21 => ['object' => 'sptprograms', 'col' => 'comments', 'append' => "<p><b>Autres sports possibles pour l'entraînement: </b>"],
		22 => ['object' => 'sptprograms', 'col' => 'comments', 'append' => "<p><b>Points forts: </b>"],
		23 => ['object' => 'sptprograms', 'col' => 'comments', 'append' => "<p><b>Points faibles: </b>"],
		24 => ['object' => 'sptprograms', 'col' => 'comments', 'append' => "<p><b>VMA, FCrepos, FCmax: </b>"],
		25 => ['object' => 'sptprograms', 'col' => 'comments', 'append' => "<p><b>Formule de planification choisie: </b>"],
		26 => ['object' => 'sptprograms', 'col' => 'fromdate', 'method' => 'date'],
		27 => ['object' => 'sptprograms', 'col' => 'todate', 'method' => 'date'],
		28 => ['object' => 'sptprograms', 'col' => 'comments', 'method' => 'yesOrNo', 'append' => "<p><b>Montre cardio ? </b>"],
		29 => ['object' => 'sptprograms', 'col' => 'comments', 'append' => "<p><b>Marque, lien et identifiant pour la platrforme Web: </b>"]
	];
}
?>