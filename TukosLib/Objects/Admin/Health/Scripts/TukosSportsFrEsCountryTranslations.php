<?php
/**
 * 
 */
namespace TukosLib\Objects\Admin\Health\Scripts;

use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\HtmlUtilities as HUtl;
use Zend\Console\Getopt;
use TukosLib\Objects\Directory;
use TukosLib\Store\Store;
use TukosLib\TukosFramework as Tfk;

class TukosSportsFrEsCountryTranslations {

    function __construct($parameters){ 
        $appConfig    = Tfk::$registry->get('appConfig');
        $user         = Tfk::$registry->get('user');

        $objectsStore = Tfk::$registry->get('objectsStore');
        $tukosModel   = Tfk::$registry->get('tukosModel');
        try{
            $options = new Getopt([
                'class=s'      => 'this class name',
                'parentid-s'   => 'parent id (optional, default is user->id())',
                'parentTable-s'=> 'parent script table (optional, required if parentid is not a users)',
            ]);
            try {
                $objectName = 'translations';
                $objectModel = $objectsStore->objectModel($objectName);
                $items = [
                   'ABW' => ['Aruba','Aruba'],'AFG' => ['Afghanistan','Afganistán'],'AGO' => ['Angola','Angola'],'AIA' => ['Anguilla','Anguila'],'ALA' => ['Åland(les Îles)','Åland, Islas'], 
                   'ALB' => ['Albanie','Albania'],'AND' => ['Andorre','Andorra'],'ARE' => ['Émirats arabes unis','Emiratos Árabes Unidos '],'ARG' => ['Argentine','Argentina'],
                   'ARM' => ['Arménie','Armenia'],'ASM' => ['Samoa américaines','Samoa Americana'],'ATA' => ['Antarctique','Antártida'],
                   'ATF' => ['Terres australes françaises','Tierras Australes Francesas '],'ATG' => ['Antigua-et-Barbuda','Antigua y Barbuda'],'AUS' => ['Australie','Australia'],
                   'AUT' => ['Autriche','Austria'],'AZE' => ['Azerbaïdjan','Azerbaiyán'],'BDI' => ['Burundi','Burundi'],'BEL' => ['Belgique','Bélgica'],'BEN' => ['Bénin','Benin'],
                   'BES' => ['Bonaire, Saint-Eustache et Saba','Bonaire, San Eustaquio y Saba'],'BFA' => ['Burkina Faso','Burkina Faso'],'BGD' => ['Bangladesh','Bangladesh'],'BGR' => ['Bulgarie','Bulgaria'],
                   'BHR' => ['Bahreïn','Bahrein'],'BHS' => ['Bahamas','Bahamas '],'BIH' => ['Bosnie-Herzégovine','Bosnia y Herzegovina'],'BLM' => ['Saint-Barthélemy','Saint Barthélemy'],
                   'BLR' => ['Bélarus','Belarús'],'BLZ' => ['Belize','Belice'],'BMU' => ['Bermudes','Bermudas'],'BOL' => ['Bolivie (État plurinational de)','Bolivia (Estado Plurinacional de)'],
                   'BRA' => ['Brésil','Brasil'],'BRB' => ['Barbade','Barbados'],'BRN' => ['Brunéi Darussalam','Brunei Darussalam'],'BTN' => ['Bhoutan','Bhután'],
                   'BVT' => ['Bouvet (l\'Île)','Bouvet, Isla'],'BWA' => ['Botswana','Botswana'],'CAF' => ['République centrafricaine','República Centroafricana '],'CAN' => ['Canada','Canadá'],
                   'CCK' => ['Cocos (les Îles)/ Keeling (les Îles)','Cocos / Keeling,  Islas'],'CHE' => ['Suisse','Suiza'],'CHL' => ['Chili','Chile'],'CHN' => ['Chine','China'],
                   'CIV' => ['Côte d\'Ivoire','Côte d\'Ivoire'],'CMR' => ['Cameroun','Camerún'],'COD' => ['Congo (la République démocratique du)','Congo (la República Democrática del)'],
                   'COG' => ['Congo','Congo (el)'],'COK' => ['Cook (les Îles)','Cook,  Islas'],'COL' => ['Colombie','Colombia'],'COM' => ['Comores','Comoras '], 
                   'CPV' => ['Cabo Verde','Cabo Verde'],'CRI' => ['Costa Rica','Costa Rica'],'CUB' => ['Cuba','Cuba'],'CUW' => ['Curaçao','Curaçao'],'CXR' => ['Christmas (l\'Île)','Navidad, Isla de'],
                   'CYM' => ['Caïmans (les Îles)','Caimán,  Islas'],'CYP' => ['Chypre','Chipre'],'CZE' => ['tchèque (la République)','República Checa '],'DEU' => ['Allemagne','Alemania'],
                   'DJI' => ['Djibouti','Djibouti'],'DMA' => ['Dominique','Dominica'],'DNK' => ['Danemark','Dinamarca'],'DOM' => ['dominicaine (la République)','Dominicana,  República'],
                   'DZA' => ['Algérie','Argelia'],'ECU' => ['Équateur','Ecuador'],'EGY' => ['Égypte','Egipto'],'ERI' => ['Érythrée','Eritrea'],
                   'ESH' => ['Sahara occidental *','Sahara Occidental'],'ESP' => ['Espagne','España'],'EST' => ['Estonie','Estonia'],'ETH' => ['Éthiopie','Etiopía'],
                   'FIN' => ['Finlande','Finlandia'],'FJI' => ['Fidji','Fiji'],'FLK' => ['Falkland (les Îles)/Malouines (les Îles)','Malvinas [Falkland],  Islas'],'FRA' => ['France','Francia'],
                   'FRO' => ['Féroé (les Îles)','Feroe,  Islas'],'FSM' => ['Micronésie (États fédérés de)','Micronesia (Estados Federados de)'],'GAB' => ['Gabon','Gabón'], 
                   'GBR' => ['Royaume-Uni de Grande-Bretagne et d\'Irlande du Nord','Reino Unido de Gran Bretaña e Irlanda del Norte (el)'],'GEO' => ['Géorgie','Georgia'],'GGY' => ['Guernesey','Guernsey'],
                   'GHA' => ['Ghana','Ghana'],'GIB' => ['Gibraltar','Gibraltar'],'GIN' => ['Guinée','Guinea'],'GLP' => ['Guadeloupe','Guadeloupe'],'GMB' => ['Gambie','Gambia '],
                   'GNB' => ['Guinée-Bissau','Guinea Bissau'],'GNQ' => ['Guinée équatoriale','Guinea Ecuatorial'],'GRC' => ['Grèce','Grecia'],'GRD' => ['Grenade','Granada'],
                   'GRL' => ['Groenland','Groenlandia'],'GTM' => ['Guatemala','Guatemala'],'GUF' => ['Guyane française (la )','Guayana Francesa'],'GUM' => ['Guam','Guam'],'GUY' => ['Guyana','Guyana'],
                   'HKG' => ['Hong Kong','Hong Kong'],'HMD' => ['Heard-et-Îles MacDonald (l\'Île)','Heard (Isla) e Islas McDonald'],'HND' => ['Honduras','Honduras'],'HRV' => ['Croatie','Croacia'],
                   'HTI' => ['Haïti','Haití'],'HUN' => ['Hongrie','Hungría'],'IDN' => ['Indonésie','Indonesia'],'IMN' => ['Île de Man','Isla de Man'],'IND' => ['Inde','India'],
                   'IOT' => ['Indien (le Territoire britannique de l\'océan)','Territorio Británico del Océano Índico (el)'],'IRL' => ['Irlande','Irlanda'],
                   'IRN' => ['Iran (République Islamique d\')','Irán (República Islámica de)'],'IRQ' => ['Iraq','Iraq'],'ISL' => ['Islande','Islandia'],'ISR' => ['Israël','Israel'],'ITA' => ['Italie','Italia'],
                   'JAM' => ['Jamaïque','Jamaica'],'JEY' => ['Jersey','Jersey'],'JOR' => ['Jordanie','Jordania'],'JPN' => ['Japon','Japón'],'KAZ' => ['Kazakhstan','Kazajstán'],
                   'KEN' => ['Kenya','Kenya'],'KGZ' => ['Kirghizistan','Kirguistán'],'KHM' => ['Cambodge','Camboya'],'KIR' => ['Kiribati','Kiribati'],'KNA' => ['Saint-Kitts-et-Nevis','Saint Kitts y Nevis'],
                   'KOR' => ['Corée (la République de)','Corea (la República de)'],'KWT' => ['Koweït','Kuwait'],'LAO' => ['Lao, République démocratique populaire','Lao,  República Democrática Popular'],
                   'LBN' => ['Liban','Líbano'],'LBR' => ['Libéria','Liberia'],'LBY' => ['Libye','Libia'],'LCA' => ['Sainte-Lucie','Santa Lucía'],'LIE' => ['Liechtenstein','Liechtenstein'],
                   'LKA' => ['Sri Lanka','Sri Lanka'],'LSO' => ['Lesotho','Lesotho'],'LTU' => ['Lituanie','Lituania'],'LUX' => ['Luxembourg','Luxemburgo'],'LVA' => ['Lettonie','Letonia'],
                   'MAC' => ['Macao','Macao'],'MAF' => ['Saint-Martin (partie française)','Saint Martin (parte francesa)'],'MAR' => ['Maroc','Marruecos'],'MCO' => ['Monaco','Mónaco'],
                   'MDA' => ['Moldova , République de','Moldova (la República de)'],'MDG' => ['Madagascar','Madagascar'],'MDV' => ['Maldives','Maldivas'],'MEX' => ['Mexique','México'],
                   'MHL' => ['Marshall (Îles)','Marshall,  Islas'],'MKD' => ['Macédoine (l\'ex‑République yougoslave de)','Macedonia (la ex República Yugoslava de)'],'MLI' => ['Mali','Malí'],
                   'MLT' => ['Malte','Malta'],'MMR' => ['Myanmar','Myanmar'],'MNE' => ['Monténégro','Montenegro'],'MNG' => ['Mongolie','Mongolia'],
                   'MNP' => ['Mariannes du Nord (les Îles)','Marianas del Norte,  Islas'],'MOZ' => ['Mozambique','Mozambique'],'MRT' => ['Mauritanie','Mauritania'],
                   'MSR' => ['Montserrat','Montserrat'],'MTQ' => ['Martinique','Martinique'],'MUS' => ['Maurice','Mauricio'],'MWI' => ['Malawi','Malawi'],'MYS' => ['Malaisie','Malasia'],
                   'MYT' => ['Mayotte','Mayotte'],'NAM' => ['Namibie','Namibia'],'NCL' => ['Nouvelle-Calédonie','Nueva Caledonia'],'NER' => ['Niger','Níger (el)'], 
                   'NFK' => ['Norfolk (l\'Île)','Norfolk, Isla'],'NGA' => ['Nigéria','Nigeria'],'NIC' => ['Nicaragua','Nicaragua'],'NIU' => ['Niue','Niue'],'NLD' => ['Pays-Bas','Países Bajos '],
                   'NOR' => ['Norvège','Noruega'],'NPL' => ['Népal','Nepal'],'NRU' => ['Nauru','Nauru'],'NZL' => ['Nouvelle-Zélande','Nueva Zelandia'],'OMN' => ['Oman','Omán'],
                   'PAK' => ['Pakistan','Pakistán'],'PAN' => ['Panama','Panamá'],'PCN' => ['Pitcairn','Pitcairn'],'PER' => ['Pérou','Perú'],'PHL' => ['Philippines','Filipinas '],
                   'PLW' => ['Palaos','Palau'],'PNG' => ['Papouasie-Nouvelle-Guinée','Papua Nueva Guinea'],'POL' => ['Pologne','Polonia'],'PRI' => ['Porto Rico','Puerto Rico'],
                   'PRK' => ['Corée (la République populaire démocratique de)','Corea (la República Popular Democrática de)'],'PRT' => ['Portugal','Portugal'],'PRY' => ['Paraguay','Paraguay'],
                   'PSE' => ['Palestine, État de','Palestina, Estado de'],'PYF' => ['Polynésie française','Polinesia Francesa'],'QAT' => ['Qatar','Qatar'],'REU' => ['Réunion','Reunión'],
                   'ROU' => ['Roumanie','Rumania'],'RUS' => ['Russie (la Fédération de)','Rusia,  Federación de'],'RWA' => ['Rwanda','Rwanda'],'SAU' => ['Arabie saoudite','Arabia Saudita'],
                   'SDN' => ['Soudan','Sudán (el)'],'SEN' => ['Sénégal','Senegal'],'SGP' => ['Singapour','Singapur'], 
                   'SGS' => ['Géorgie du Sud-et-les Îles Sandwich du Sud','Georgia del Sur  y las Islas Sandwich del Sur'],'SHN' => ['Sainte-Hélène, Ascension et Tristan da Cunha','Santa Helena, Ascensión y Tristán de Acuña'],
                   'SJM' => ['Svalbard et l\'Île Jan Mayen','Svalbard y Jan Mayen'],'SLB' => ['Salomon (Îles)','Salomón, Islas'],'SLE' => ['Sierra Leone','Sierra leona'],'SLV' => ['El Salvador','El Salvador'],
                   'SMR' => ['Saint-Marin','San Marino'],'SOM' => ['Somalie','Somalia'],'SPM' => ['Saint-Pierre-et-Miquelon','San Pedro y Miquelón'],'SRB' => ['Serbie','Serbia'],
                   'SSD' => ['Soudan du Sud','Sudán del Sur'],'STP' => ['Sao Tomé-et-Principe','Santo Tomé y Príncipe'],'SUR' => ['Suriname','Suriname'],'SVK' => ['Slovaquie','Eslovaquia'],
                   'SVN' => ['Slovénie','Eslovenia'],'SWE' => ['Suède','Suecia'],'SWZ' => ['Swaziland','Swazilandia'],'SXM' => ['Saint-Martin (partie néerlandaise)','Sint Maarten (parte neerlandesa)'],
                   'SYC' => ['Seychelles','Seychelles'],'SYR' => ['République arabe syrienne','República Árabe Siria'],'TCA' => ['Turks-et-Caïcos (les Îles)','Turcas y Caicos,  Islas'],
                   'TCD' => ['Tchad','Chad'],'TGO' => ['Togo','Togo'],'THA' => ['Thaïlande','Tailandia'],'TJK' => ['Tadjikistan','Tayikistán'],'TKL' => ['Tokelau','Tokelau'],
                   'TKM' => ['Turkménistan','Turkmenistán'],'TLS' => ['Timor-Leste','Timor-Leste'],'TON' => ['Tonga','Tonga'],'TTO' => ['Trinité-et-Tobago','Trinidad y Tabago'],
                   'TUN' => ['Tunisie','Túnez'],'TUR' => ['Turquie','Turquía'],'TUV' => ['Tuvalu','Tuvalu'],'TWN' => ['Taïwan (Province de Chine)','Taiwán (Provincia de China)'],
                   'TZA' => ['Tanzanie, République-Unie de','Tanzania, República Unida de'],'UGA' => ['Ouganda','Uganda'],'UKR' => ['Ukraine','Ucrania'],
                   'UMI' => ['Îles mineures éloignées des États-Unis','Islas Ultramarinas Menores de los Estados Unidos '],'URY' => ['Uruguay','Uruguay'],
                   'USA' => ['États-Unis d\'Amérique','Estados Unidos de América '],'UZB' => ['Ouzbékistan','Uzbekistán'],'VAT' => ['Saint-Siège','Santa Sede '],
                   'VCT' => ['Saint-Vincent-et-les Grenadines','San Vicente y las Granadinas'],'VEN' => ['Venezuela (République bolivarienne du)','Venezuela (República Bolivariana de)'],
                   'VGB' => ['Vierges britanniques (les Îles)','Vírgenes británicas, Islas'],'VIR' => ['Vierges des États-Unis (les Îles)','Vírgenes de los Estados Unidos, Islas'],'VNM' => ['Viet Nam','Viet Nam'],
                   'VUT' => ['Vanuatu','Vanuatu'],'WLF' => ['Wallis-et-Futuna','Wallis y Futuna'],'WSM' => ['Samoa','Samoa'],'YEM' => ['Yémen','Yemen'],'ZAF' => ['Afrique du Sud','Sudáfrica'],
                   'ZMB' => ['Zambie','Zambia'],'ZWE' => ['Zimbabwe','Zimbabwe'],
                ];
                foreach ($items as $key => $item){
                    $id = $objectModel->getOne(['where' => ['name' => $key], 'cols' => ['id']]);
                    if (!empty($id)){
                        $objectModel->updateOne(['id' => $id['id'], 'setname' => 'tukoslib',  'permission' => 'RO', 'grade' => 'NORMAL', 'contextid' => 1, 'fr_fr' => $item[0],  'es_es' => $item[1]]);
                    }
                }

                $storeProfiles = Tfk::$registry->get('store')->getProfiles();
                $storeProfilesOutput = HUtl::page('Tukos Profiler Results',  HUtl::table($storeProfiles, []));
                file_put_contents('/tukosstoreprofiles.html', $storeProfilesOutput);
            }catch(\Exception $e){
                Tfk::error_message('on', ' Exception in tukossportsfrescountrytranslations: ', $e->getMessage());
            }
        }catch(Getopt_exception $e){
            Tfk::error_message('on', 'an exception occured while parsing command arguments : ', $e->getUsageMessage());
        }
    }
}
?>
