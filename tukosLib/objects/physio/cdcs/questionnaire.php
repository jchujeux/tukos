<?php
namespace TukosLib\Objects\Physio\Cdcs;

use TukosLib\Utils\Feedback;
use TukosLib\Objects\Questionnaire as ParentQuestionnaire;
use TukosLib\TukosFramework as Tfk;

trait Questionnaire {
	
	use ParentQuestionnaire;
	
	protected static $mapping = [
		0 => ['object' => 'physiocdcs', 'col' => 'questionnairetime', 'method' => 'dateTime'],
		1 => ['object' => 'physiopatients', 'col' => 'name', 'method' => 'capitalize'],
		2 => ['object' => 'physiopatients', 'col' => 'firstname', 'method' => 'capitalize'],
		3 => ['object' => 'physiopatients', 'col' => 'profession'],
		4 => ['object' => 'physiopatients', 'col' => 'telmobile', 'method' => 'phoneNumber'],
		6 => ['object' => 'physiopatients', 'col' => 'birthdate', 'method' => 'date'],
		7 => ['object' => 'physiopatients', 'col' => 'height', 'method' => 'height'],
		8 => ['object' => 'physiopatients', 'col' => 'weight', 'method' => 'weight'],
		9 => ['object' => 'physiocdcs', 'col' => 'clubyesorno', 'method' => 'yesOrNo'],
		10 => ['object' => 'physiocdcs', 'col' => 'specialty'],
		11 => ['object' => 'physiocdcs', 'col' => 'specialtysince'],
		12 => ['object' => 'physiocdcs', 'col' => 'trainingweek'],
		13 => ['object' => 'physiocdcs', 'col' => 'sportsgoal'],
		14 => ['object' => 'physiocdcs', 'col' => 'reason'],
		15 => ['object' => 'physiocdcs', 'col' => 'painstart'],
		16 => ['object' => 'physiocdcs', 'col' => 'painwhere'],
		17 => ['object' => 'physiocdcs', 'col' => 'painwhen'],
		18 => ['object' => 'physiocdcs', 'col' => 'painhow'],
		19 => ['object' => 'physiocdcs', 'col' => 'painevolution'],
		20 => ['object' => 'physiocdcs', 'col' => 'paindailyyesorno', 'method' => 'yesOrNo'],
		21 => ['object' => 'physiocdcs', 'col' => 'recentchanges'],
		22 => ['object' => 'physiocdcs', 'col' => 'orthosolesyesorno', 'method' => 'yesOrNo'],
		23 => ['object' => 'physiocdcs', 'col' => 'orthosolessince'],
		24 => ['object' => 'physiocdcs', 'col' => 'orthosoleseaseyesorno', 'method' => 'yesOrNo'],
		25 => ['object' => 'physiocdcs', 'col' => 'shoes'],
		26 => ['object' => 'physiocdcs', 'col' => 'antecedents'],
		27 => ['object' => 'physiocdcs', 'col' => 'exams'],
		28 => ['object' => 'physiopatients', 'col' => 'email'],
		29 => ['object' => 'physiocdcs', 'col' => 'clubname'],
		30 => ['object' => 'physiopatients', 'col' => 'sex', 'method' => 'sex'],
		31 => ['object' => 'physiocdcs', 'col' => 'breakfastyesorno', 'method' => 'yesOrNo'],
		32 => ['object' => 'physiocdcs', 'col' => 'vegetables'],
		33 => ['object' => 'physiocdcs', 'col' => 'fruits'],
		34 => ['object' => 'physiocdcs', 'col' => 'friedfat'],
		35 => ['object' => 'physiocdcs', 'col' => 'water'],
		36 => ['object' => 'physiocdcs', 'col' => 'alcool'],
		37 => ['object' => 'physiocdcs', 'col' => 'snack'],
		38 => ['object' => 'physiocdcs', 'col' => 'foodrace'],
	];
}
?>