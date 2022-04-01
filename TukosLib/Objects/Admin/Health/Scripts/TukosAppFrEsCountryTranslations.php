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

class TukosAppFrEsCountryTranslations {

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
                   'ABW' => ['Aruba','Aruba'],'AFG' => ['Afghanistan','AfganistÃ¡n'],'AGO' => ['Angola','Angola'],'AIA' => ['Anguilla','Anguila'],'ALA' => ['Ã…land(les ÃŽles)','Ã…land, Islas'], 
                   'ALB' => ['Albanie','Albania'],'AND' => ['Andorre','Andorra'],'ARE' => ['Ã‰mirats arabes unis','Emiratos Ã�rabes Unidos '],'ARG' => ['Argentine','Argentina'],
                   'ARM' => ['ArmÃ©nie','Armenia'],'ASM' => ['Samoa amÃ©ricaines','Samoa Americana'],'ATA' => ['Antarctique','AntÃ¡rtida'],
                   'ATF' => ['Terres australes franÃ§aises','Tierras Australes Francesas '],'ATG' => ['Antigua-et-Barbuda','Antigua y Barbuda'],'AUS' => ['Australie','Australia'],
                   'AUT' => ['Autriche','Austria'],'AZE' => ['AzerbaÃ¯djan','AzerbaiyÃ¡n'],'BDI' => ['Burundi','Burundi'],'BEL' => ['Belgique','BÃ©lgica'],'BEN' => ['BÃ©nin','Benin'],
                   'BES' => ['Bonaire, Saint-Eustache et Saba','Bonaire, San Eustaquio y Saba'],'BFA' => ['Burkina Faso','Burkina Faso'],'BGD' => ['Bangladesh','Bangladesh'],'BGR' => ['Bulgarie','Bulgaria'],
                   'BHR' => ['BahreÃ¯n','Bahrein'],'BHS' => ['Bahamas','Bahamas '],'BIH' => ['Bosnie-HerzÃ©govine','Bosnia y Herzegovina'],'BLM' => ['Saint-BarthÃ©lemy','Saint BarthÃ©lemy'],
                   'BLR' => ['BÃ©larus','BelarÃºs'],'BLZ' => ['Belize','Belice'],'BMU' => ['Bermudes','Bermudas'],'BOL' => ['Bolivie (Ã‰tat plurinational de)','Bolivia (Estado Plurinacional de)'],
                   'BRA' => ['BrÃ©sil','Brasil'],'BRB' => ['Barbade','Barbados'],'BRN' => ['BrunÃ©i Darussalam','Brunei Darussalam'],'BTN' => ['Bhoutan','BhutÃ¡n'],
                   'BVT' => ['Bouvet (l\'ÃŽle)','Bouvet, Isla'],'BWA' => ['Botswana','Botswana'],'CAF' => ['RÃ©publique centrafricaine','RepÃºblica Centroafricana '],'CAN' => ['Canada','CanadÃ¡'],
                   'CCK' => ['Cocos (les ÃŽles)/ Keeling (les ÃŽles)','Cocos / Keeling,  Islas'],'CHE' => ['Suisse','Suiza'],'CHL' => ['Chili','Chile'],'CHN' => ['Chine','China'],
                   'CIV' => ['CÃ´te d\'Ivoire','CÃ´te d\'Ivoire'],'CMR' => ['Cameroun','CamerÃºn'],'COD' => ['Congo (la RÃ©publique dÃ©mocratique du)','Congo (la RepÃºblica DemocrÃ¡tica del)'],
                   'COG' => ['Congo','Congo (el)'],'COK' => ['Cook (les ÃŽles)','Cook,  Islas'],'COL' => ['Colombie','Colombia'],'COM' => ['Comores','Comoras '], 
                   'CPV' => ['Cabo Verde','Cabo Verde'],'CRI' => ['Costa Rica','Costa Rica'],'CUB' => ['Cuba','Cuba'],'CUW' => ['CuraÃ§ao','CuraÃ§ao'],'CXR' => ['Christmas (l\'ÃŽle)','Navidad, Isla de'],
                   'CYM' => ['CaÃ¯mans (les ÃŽles)','CaimÃ¡n,  Islas'],'CYP' => ['Chypre','Chipre'],'CZE' => ['tchÃ¨que (la RÃ©publique)','RepÃºblica Checa '],'DEU' => ['Allemagne','Alemania'],
                   'DJI' => ['Djibouti','Djibouti'],'DMA' => ['Dominique','Dominica'],'DNK' => ['Danemark','Dinamarca'],'DOM' => ['dominicaine (la RÃ©publique)','Dominicana,  RepÃºblica'],
                   'DZA' => ['AlgÃ©rie','Argelia'],'ECU' => ['Ã‰quateur','Ecuador'],'EGY' => ['Ã‰gypte','Egipto'],'ERI' => ['Ã‰rythrÃ©e','Eritrea'],
                   'ESH' => ['Sahara occidental *','Sahara Occidental'],'ESP' => ['Espagne','EspaÃ±a'],'EST' => ['Estonie','Estonia'],'ETH' => ['Ã‰thiopie','EtiopÃ­a'],
                   'FIN' => ['Finlande','Finlandia'],'FJI' => ['Fidji','Fiji'],'FLK' => ['Falkland (les ÃŽles)/Malouines (les ÃŽles)','Malvinas [Falkland],  Islas'],'FRA' => ['France','Francia'],
                   'FRO' => ['FÃ©roÃ© (les ÃŽles)','Feroe,  Islas'],'FSM' => ['MicronÃ©sie (Ã‰tats fÃ©dÃ©rÃ©s de)','Micronesia (Estados Federados de)'],'GAB' => ['Gabon','GabÃ³n'], 
                   'GBR' => ['Royaume-Uni de Grande-Bretagne et d\'Irlande du Nord','Reino Unido de Gran BretaÃ±a e Irlanda del Norte (el)'],'GEO' => ['GÃ©orgie','Georgia'],'GGY' => ['Guernesey','Guernsey'],
                   'GHA' => ['Ghana','Ghana'],'GIB' => ['Gibraltar','Gibraltar'],'GIN' => ['GuinÃ©e','Guinea'],'GLP' => ['Guadeloupe','Guadeloupe'],'GMB' => ['Gambie','Gambia '],
                   'GNB' => ['GuinÃ©e-Bissau','Guinea Bissau'],'GNQ' => ['GuinÃ©e Ã©quatoriale','Guinea Ecuatorial'],'GRC' => ['GrÃ¨ce','Grecia'],'GRD' => ['Grenade','Granada'],
                   'GRL' => ['Groenland','Groenlandia'],'GTM' => ['Guatemala','Guatemala'],'GUF' => ['Guyane franÃ§aise (la )','Guayana Francesa'],'GUM' => ['Guam','Guam'],'GUY' => ['Guyana','Guyana'],
                   'HKG' => ['Hong Kong','Hong Kong'],'HMD' => ['Heard-et-ÃŽles MacDonald (l\'ÃŽle)','Heard (Isla) e Islas McDonald'],'HND' => ['Honduras','Honduras'],'HRV' => ['Croatie','Croacia'],
                   'HTI' => ['HaÃ¯ti','HaitÃ­'],'HUN' => ['Hongrie','HungrÃ­a'],'IDN' => ['IndonÃ©sie','Indonesia'],'IMN' => ['ÃŽle de Man','Isla de Man'],'IND' => ['Inde','India'],
                   'IOT' => ['Indien (le Territoire britannique de l\'ocÃ©an)','Territorio BritÃ¡nico del OcÃ©ano Ã�ndico (el)'],'IRL' => ['Irlande','Irlanda'],
                   'IRN' => ['Iran (RÃ©publique Islamique d\')','IrÃ¡n (RepÃºblica IslÃ¡mica de)'],'IRQ' => ['Iraq','Iraq'],'ISL' => ['Islande','Islandia'],'ISR' => ['IsraÃ«l','Israel'],'ITA' => ['Italie','Italia'],
                   'JAM' => ['JamaÃ¯que','Jamaica'],'JEY' => ['Jersey','Jersey'],'JOR' => ['Jordanie','Jordania'],'JPN' => ['Japon','JapÃ³n'],'KAZ' => ['Kazakhstan','KazajstÃ¡n'],
                   'KEN' => ['Kenya','Kenya'],'KGZ' => ['Kirghizistan','KirguistÃ¡n'],'KHM' => ['Cambodge','Camboya'],'KIR' => ['Kiribati','Kiribati'],'KNA' => ['Saint-Kitts-et-Nevis','Saint Kitts y Nevis'],
                   'KOR' => ['CorÃ©e (la RÃ©publique de)','Corea (la RepÃºblica de)'],'KWT' => ['KoweÃ¯t','Kuwait'],'LAO' => ['Lao, RÃ©publique dÃ©mocratique populaire','Lao,  RepÃºblica DemocrÃ¡tica Popular'],
                   'LBN' => ['Liban','LÃ­bano'],'LBR' => ['LibÃ©ria','Liberia'],'LBY' => ['Libye','Libia'],'LCA' => ['Sainte-Lucie','Santa LucÃ­a'],'LIE' => ['Liechtenstein','Liechtenstein'],
                   'LKA' => ['Sri Lanka','Sri Lanka'],'LSO' => ['Lesotho','Lesotho'],'LTU' => ['Lituanie','Lituania'],'LUX' => ['Luxembourg','Luxemburgo'],'LVA' => ['Lettonie','Letonia'],
                   'MAC' => ['Macao','Macao'],'MAF' => ['Saint-Martin (partie franÃ§aise)','Saint Martin (parte francesa)'],'MAR' => ['Maroc','Marruecos'],'MCO' => ['Monaco','MÃ³naco'],
                   'MDA' => ['Moldova , RÃ©publique de','Moldova (la RepÃºblica de)'],'MDG' => ['Madagascar','Madagascar'],'MDV' => ['Maldives','Maldivas'],'MEX' => ['Mexique','MÃ©xico'],
                   'MHL' => ['Marshall (ÃŽles)','Marshall,  Islas'],'MKD' => ['MacÃ©doine (l\'exâ€‘RÃ©publique yougoslave de)','Macedonia (la ex RepÃºblica Yugoslava de)'],'MLI' => ['Mali','MalÃ­'],
                   'MLT' => ['Malte','Malta'],'MMR' => ['Myanmar','Myanmar'],'MNE' => ['MontÃ©nÃ©gro','Montenegro'],'MNG' => ['Mongolie','Mongolia'],
                   'MNP' => ['Mariannes du Nord (les ÃŽles)','Marianas del Norte,  Islas'],'MOZ' => ['Mozambique','Mozambique'],'MRT' => ['Mauritanie','Mauritania'],
                   'MSR' => ['Montserrat','Montserrat'],'MTQ' => ['Martinique','Martinique'],'MUS' => ['Maurice','Mauricio'],'MWI' => ['Malawi','Malawi'],'MYS' => ['Malaisie','Malasia'],
                   'MYT' => ['Mayotte','Mayotte'],'NAM' => ['Namibie','Namibia'],'NCL' => ['Nouvelle-CalÃ©donie','Nueva Caledonia'],'NER' => ['Niger','NÃ­ger (el)'], 
                   'NFK' => ['Norfolk (l\'ÃŽle)','Norfolk, Isla'],'NGA' => ['NigÃ©ria','Nigeria'],'NIC' => ['Nicaragua','Nicaragua'],'NIU' => ['Niue','Niue'],'NLD' => ['Pays-Bas','PaÃ­ses Bajos '],
                   'NOR' => ['NorvÃ¨ge','Noruega'],'NPL' => ['NÃ©pal','Nepal'],'NRU' => ['Nauru','Nauru'],'NZL' => ['Nouvelle-ZÃ©lande','Nueva Zelandia'],'OMN' => ['Oman','OmÃ¡n'],
                   'PAK' => ['Pakistan','PakistÃ¡n'],'PAN' => ['Panama','PanamÃ¡'],'PCN' => ['Pitcairn','Pitcairn'],'PER' => ['PÃ©rou','PerÃº'],'PHL' => ['Philippines','Filipinas '],
                   'PLW' => ['Palaos','Palau'],'PNG' => ['Papouasie-Nouvelle-GuinÃ©e','Papua Nueva Guinea'],'POL' => ['Pologne','Polonia'],'PRI' => ['Porto Rico','Puerto Rico'],
                   'PRK' => ['CorÃ©e (la RÃ©publique populaire dÃ©mocratique de)','Corea (la RepÃºblica Popular DemocrÃ¡tica de)'],'PRT' => ['Portugal','Portugal'],'PRY' => ['Paraguay','Paraguay'],
                   'PSE' => ['Palestine, Ã‰tat de','Palestina, Estado de'],'PYF' => ['PolynÃ©sie franÃ§aise','Polinesia Francesa'],'QAT' => ['Qatar','Qatar'],'REU' => ['RÃ©union','ReuniÃ³n'],
                   'ROU' => ['Roumanie','Rumania'],'RUS' => ['Russie (la FÃ©dÃ©ration de)','Rusia,  FederaciÃ³n de'],'RWA' => ['Rwanda','Rwanda'],'SAU' => ['Arabie saoudite','Arabia Saudita'],
                   'SDN' => ['Soudan','SudÃ¡n (el)'],'SEN' => ['SÃ©nÃ©gal','Senegal'],'SGP' => ['Singapour','Singapur'], 
                   'SGS' => ['GÃ©orgie du Sud-et-les ÃŽles Sandwich du Sud','Georgia del Sur  y las Islas Sandwich del Sur'],'SHN' => ['Sainte-HÃ©lÃ¨ne, Ascension et Tristan da Cunha','Santa Helena, AscensiÃ³n y TristÃ¡n de AcuÃ±a'],
                   'SJM' => ['Svalbard et l\'ÃŽle Jan Mayen','Svalbard y Jan Mayen'],'SLB' => ['Salomon (ÃŽles)','SalomÃ³n, Islas'],'SLE' => ['Sierra Leone','Sierra leona'],'SLV' => ['El Salvador','El Salvador'],
                   'SMR' => ['Saint-Marin','San Marino'],'SOM' => ['Somalie','Somalia'],'SPM' => ['Saint-Pierre-et-Miquelon','San Pedro y MiquelÃ³n'],'SRB' => ['Serbie','Serbia'],
                   'SSD' => ['Soudan du Sud','SudÃ¡n del Sur'],'STP' => ['Sao TomÃ©-et-Principe','Santo TomÃ© y PrÃ­ncipe'],'SUR' => ['Suriname','Suriname'],'SVK' => ['Slovaquie','Eslovaquia'],
                   'SVN' => ['SlovÃ©nie','Eslovenia'],'SWE' => ['SuÃ¨de','Suecia'],'SWZ' => ['Swaziland','Swazilandia'],'SXM' => ['Saint-Martin (partie nÃ©erlandaise)','Sint Maarten (parte neerlandesa)'],
                   'SYC' => ['Seychelles','Seychelles'],'SYR' => ['RÃ©publique arabe syrienne','RepÃºblica Ã�rabe Siria'],'TCA' => ['Turks-et-CaÃ¯cos (les ÃŽles)','Turcas y Caicos,  Islas'],
                   'TCD' => ['Tchad','Chad'],'TGO' => ['Togo','Togo'],'THA' => ['ThaÃ¯lande','Tailandia'],'TJK' => ['Tadjikistan','TayikistÃ¡n'],'TKL' => ['Tokelau','Tokelau'],
                   'TKM' => ['TurkmÃ©nistan','TurkmenistÃ¡n'],'TLS' => ['Timor-Leste','Timor-Leste'],'TON' => ['Tonga','Tonga'],'TTO' => ['TrinitÃ©-et-Tobago','Trinidad y Tabago'],
                   'TUN' => ['Tunisie','TÃºnez'],'TUR' => ['Turquie','TurquÃ­a'],'TUV' => ['Tuvalu','Tuvalu'],'TWN' => ['TaÃ¯wan (Province de Chine)','TaiwÃ¡n (Provincia de China)'],
                   'TZA' => ['Tanzanie, RÃ©publique-Unie de','Tanzania, RepÃºblica Unida de'],'UGA' => ['Ouganda','Uganda'],'UKR' => ['Ukraine','Ucrania'],
                   'UMI' => ['ÃŽles mineures Ã©loignÃ©es des Ã‰tats-Unis','Islas Ultramarinas Menores de los Estados Unidos '],'URY' => ['Uruguay','Uruguay'],
                   'USA' => ['Ã‰tats-Unis d\'AmÃ©rique','Estados Unidos de AmÃ©rica '],'UZB' => ['OuzbÃ©kistan','UzbekistÃ¡n'],'VAT' => ['Saint-SiÃ¨ge','Santa Sede '],
                   'VCT' => ['Saint-Vincent-et-les Grenadines','San Vicente y las Granadinas'],'VEN' => ['Venezuela (RÃ©publique bolivarienne du)','Venezuela (RepÃºblica Bolivariana de)'],
                   'VGB' => ['Vierges britanniques (les ÃŽles)','VÃ­rgenes britÃ¡nicas, Islas'],'VIR' => ['Vierges des Ã‰tats-Unis (les ÃŽles)','VÃ­rgenes de los Estados Unidos, Islas'],'VNM' => ['Viet Nam','Viet Nam'],
                   'VUT' => ['Vanuatu','Vanuatu'],'WLF' => ['Wallis-et-Futuna','Wallis y Futuna'],'WSM' => ['Samoa','Samoa'],'YEM' => ['YÃ©men','Yemen'],'ZAF' => ['Afrique du Sud','SudÃ¡frica'],
                   'ZMB' => ['Zambie','Zambia'],'ZWE' => ['Zimbabwe','Zimbabwe'],
                ];
                foreach ($items as $key => $item){
                    $id = $objectModel->getOne(['where' => ['name' => $key], 'cols' => ['id']]);
                    if (!empty($id)){
                        $objectModel->updateOne(['id' => $id['id'], 'setname' => 'tukoslib',  'fr_fr' => $item[0],  'es_es' => $item[1]]);
                    }
                }

                $storeProfiles = Tfk::$registry->get('store')->profilerMessages();
                $storeProfilesOutput = HUtl::page('Tukos Profiler Results',  HUtl::table($storeProfiles, []));
                file_put_contents('/tukosstoreprofiles.html', $storeProfilesOutput);
            }catch(\Exception $e){
                Tfk::error_message('on', ' Exception in tukosappfrescountrytranslations: ', $e->getMessage());
            }
        }catch(Getopt_exception $e){
            Tfk::error_message('on', 'an exception occured while parsing command arguments : ', $e->getUsageMessage());
        }
    }
}
?>
