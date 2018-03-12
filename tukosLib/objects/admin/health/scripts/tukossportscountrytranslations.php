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

class TukosSportsCountryTranslations {

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
                    ['AFG', 'Afghanistan'], ['ALA', 'Åland Islands'], ['ALB', 'Albania'], ['DZA', 'Algeria'], ['ASM', 'American Samoa'], ['AND', 'Andorra'], ['AGO', 'Angola'], ['AIA', 'Anguilla'], ['ATA', 'Antarctica'], ['ATG', 'Antigua and Barbuda'], ['ARG', 'Argentina'],
                    ['ARM', 'Armenia'], ['ABW', 'Aruba'], ['AUS', 'Australia'], ['AUT', 'Austria'], ['AZE', 'Azerbaijan'], ['BHS', 'Bahamas'], ['BHR', 'Bahrain'], ['BGD', 'Bangladesh'], ['BRB', 'Barbados'], ['BLR', 'Belarus'], ['BEL', 'Belgium'], ['BLZ', 'Belize'], ['BEN', 'Benin'],
                    ['BMU', 'Bermuda'], ['BTN', 'Bhutan'], ['BOL', 'Bolivia, Plurinational State of'], ['BES', 'Bonaire, Sint Eustatius and Saba'], ['BIH', 'Bosnia and Herzegovina'], ['BWA', 'Botswana'], ['BVT', 'Bouvet Island'], ['BRA', 'Brazil'], ['IOT', 'British Indian Ocean Territory'],
                    ['BRN', 'Brunei Darussalam'], ['BGR', 'Bulgaria'], ['BFA', 'Burkina Faso'], ['BDI', 'Burundi'], ['KHM', 'Cambodia'], ['CMR', 'Cameroon'], ['CAN', 'Canada'], ['CPV', 'Cape Verde'], ['CYM', 'Cayman Islands'], ['CAF', 'Central African Republic'], ['TCD', 'Chad'],
                    ['CHL', 'Chile'], ['CHN', 'China'], ['CXR', 'Christmas Island'], ['CCK', 'Cocos (Keeling) Islands'], ['COL', 'Colombia'], ['COM', 'Comoros'], ['COG', 'Congo'], ['COD', 'Congo, the Democratic Republic of the'], ['COK', 'Cook Islands'], ['CRI', 'Costa Rica'],
                    ['CIV', "Côte d'Ivoire"], ['HRV', 'Croatia'], ['CUB', 'Cuba'], ['CUW', 'Curaçao'], ['CYP', 'Cyprus'], ['CZE', 'Czech Republic'], ['DNK', 'Denmark'], ['DJI', 'Djibouti'], ['DMA', 'Dominica'], ['DOM', 'Dominican Republic'], ['ECU', 'Ecuador'], ['EGY', 'Egypt'],
                    ['SLV', 'El Salvador'], ['GNQ', 'Equatorial Guinea'], ['ERI', 'Eritrea'], ['EST', 'Estonia'], ['ETH', 'Ethiopia'], ['FLK', 'Falkland Islands (Malvinas)'], ['FRO', 'Faroe Islands'], ['FJI', 'Fiji'], ['FIN', 'Finland'], ['FRA', 'France'], ['GUF', 'French Guiana'],
                    ['PYF', 'French Polynesia'], ['ATF', 'French Southern Territories'], ['GAB', 'Gabon'], ['GMB', 'Gambia'], ['GEO', 'Georgia'], ['DEU', 'Germany'], ['GHA', 'Ghana'], ['GIB', 'Gibraltar'], ['GRC', 'Greece'], ['GRL', 'Greenland'], ['GRD', 'Grenada'], ['GLP', 'Guadeloupe'],
                    ['GUM', 'Guam'], ['GTM', 'Guatemala'], ['GGY', 'Guernsey'], ['GIN', 'Guinea'], ['GNB', 'Guinea-Bissau'], ['GUY', 'Guyana'], ['HTI', 'Haiti'], ['HMD', 'Heard Island and McDonald Islands'], ['VAT', 'Holy See (Vatican City State)'], ['HND', 'Honduras'], ['HKG', 'Hong Kong'],
                    ['HUN', 'Hungary'], ['ISL', 'Iceland'], ['IND', 'India'], ['IDN', 'Indonesia'], ['IRN', 'Iran, Islamic Republic of'], ['IRQ', 'Iraq'], ['IRL', 'Ireland'], ['IMN', 'Isle of Man'], ['ISR', 'Israel'], ['ITA', 'Italy'], ['JAM', 'Jamaica'], ['JPN', 'Japan'], ['JEY', 'Jersey'], ['JOR', 'Jordan'],
                    ['KAZ', 'Kazakhstan'], ['KEN', 'Kenya'], ['KIR', 'Kiribati'], ['PRK', "Korea, Democratic People's Republic of"], ['KOR', 'Korea, Republic of'], ['KWT', 'Kuwait'], ['KGZ', 'Kyrgyzstan'], ['LAO', "Lao People's Democratic Republic"], ['LVA', 'Latvia'], ['LBN', 'Lebanon'],
                    ['LSO', 'Lesotho'], ['LBR', 'Liberia'], ['LBY', 'Libya'], ['LIE', 'Liechtenstein'], ['LTU', 'Lithuania'], ['LUX', 'Luxembourg'], ['MAC', 'Macao'], ['MKD', 'Macedonia, the former Yugoslav Republic of'], ['MDG', 'Madagascar'], ['MWI', 'Malawi'], ['MYS', 'Malaysia'], ['MDV', 'Maldives'],
                    ['MLI', 'Mali'], ['MLT', 'Malta'], ['MHL', 'Marshall Islands'], ['MTQ', 'Martinique'], ['MRT', 'Mauritania'], ['MUS', 'Mauritius'], ['MYT', 'Mayotte'], ['MEX', 'Mexico'], ['FSM', 'Micronesia, Federated States of'], ['MDA', 'Moldova, Republic of'], ['MCO', 'Monaco'], ['MNG', 'Mongolia'],
                    ['MNE', 'Montenegro'], ['MSR', 'Montserrat'], ['MAR', 'Morocco'], ['MOZ', 'Mozambique'], ['MMR', 'Myanmar'], ['NAM', 'Namibia'], ['NRU', 'Nauru'], ['NPL', 'Nepal'], ['NLD', 'Netherlands'], ['NCL', 'New Caledonia'], ['NZL', 'New Zealand'], ['NIC', 'Nicaragua'], ['NER', 'Niger'],
                    ['NGA', 'Nigeria'], ['NIU', 'Niue'], ['NFK', 'Norfolk Island'], ['MNP', 'Northern Mariana Islands'], ['NOR', 'Norway'], ['OMN', 'Oman'], ['PAK', 'Pakistan'], ['PLW', 'Palau'], ['PSE', 'Palestinian Territory, Occupied'], ['PAN', 'Panama'], ['PNG', 'Papua New Guinea'], 
                    ['PRY', 'Paraguay'], ['PER', 'Peru'], ['PHL', 'Philippines'], ['PCN', 'Pitcairn'], ['POL', 'Poland'], ['PRT', 'Portugal'], ['PRI', 'Puerto Rico'], ['QAT', 'Qatar'], ['REU', 'Réunion'], ['ROU', 'Romania'], ['RUS', 'Russian Federation'], ['RWA', 'Rwanda'], ['BLM', 'Saint Barthélemy'],
                    ['SHN', 'Saint Helena, Ascension and Tristan da Cunha'], ['KNA', 'Saint Kitts and Nevis'], ['LCA', 'Saint Lucia'], ['MAF', 'Saint Martin (French part)'], ['SPM', 'Saint Pierre and Miquelon'], ['VCT', 'Saint Vincent and the Grenadines'], ['WSM', 'Samoa'], ['SMR', 'San Marino'],
                    ['STP', 'Sao Tome and Principe'], ['SAU', 'Saudi Arabia'], ['SEN', 'Senegal'], ['SRB', 'Serbia'], ['SYC', 'Seychelles'], ['SLE', 'Sierra Leone'], ['SGP', 'Singapore'], ['SXM', 'Sint Maarten (Dutch part)'], ['SVK', 'Slovakia'], ['SVN', 'Slovenia'], ['SLB', 'Solomon Islands'],
                    ['SOM', 'Somalia'], ['ZAF', 'South Africa'], ['SGS', 'South Georgia and the South Sandwich Islands'], ['SSD', 'South Sudan'], ['ESP', 'Spain'], ['LKA', 'Sri Lanka'], ['SDN', 'Sudan'], ['SUR', 'Suriname'], ['SJM', 'Svalbard and Jan Mayen'], ['SWZ', 'Swaziland'], ['SWE', 'Sweden'],
                    ['CHE', 'Switzerland'], ['SYR', 'Syrian Arab Republic'], ['TWN', 'Taiwan, Province of China'], ['TJK', 'Tajikistan'], ['TZA', 'Tanzania, United Republic of'], ['THA', 'Thailand'], ['TLS', 'Timor-Leste'], ['TGO', 'Togo'], ['TKL', 'Tokelau'], ['TON', 'Tonga'], ['TTO', 'Trinidad and Tobago'],
                     ['TUN', 'Tunisia'], ['TUR', 'Turkey'], ['TKM', 'Turkmenistan'], ['TCA', 'Turks and Caicos Islands'], ['TUV', 'Tuvalu'], ['UGA', 'Uganda'], ['UKR', 'Ukraine'], ['ARE', 'United Arab Emirates'], ['GBR', 'United Kingdom'], ['USA', 'United States'], ['UMI', 'United States Minor Outlying Islands'],
                     ['URY', 'Uruguay'], ['UZB', 'Uzbekistan'], ['VUT', 'Vanuatu'], ['VEN', 'Venezuela, Bolivarian Republic of'], ['VNM', 'Viet Nam'], ['VGB', 'Virgin Islands, British'], ['VIR', 'Virgin Islands, U.S.'], ['WLF', 'Wallis and Futuna'], ['ESH', 'Western Sahara'], ['YEM', 'Yemen'], ['ZMB', 'Zambia'],
                     ['ZWE', 'Zimbabwe'], 
                ];
                foreach ($items as $item){
                    $objectModel->insert(['name' => $item[0], 'setname' => 'tukoslib',  'en_us' => $item[1], 'contextid' => 1, 'configstatus' => 'tukos'], true);
                }

                $storeProfiles = Tfk::$registry->get('store')->getProfiles();
                $storeProfilesOutput = HUtl::page('Tukos Profiler Results',  HUtl::table($storeProfiles, []));
                file_put_contents('/tukosstoreprofiles.html', $storeProfilesOutput);
            }catch(\Exception $e){
                Tfk::error_message('on', ' Exception in tukossportscountrytranslations: ', $e->getMessage());
            }
        }catch(Getopt_exception $e){
            Tfk::error_message('on', 'an exception occured while parsing command arguments : ', $e->getUsageMessage());
        }
    }
}
?>
