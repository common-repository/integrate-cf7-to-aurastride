<?php

/**
 * The file that defines the core functions here 
 *
 * @link       https://profiles.wordpress.org/vsourz1td/
 * @since      1.0.0
 *
 * @package    Cf7_aurastride_crm
 * @subpackage Cf7_aurastride_crm/includes
 */

/*
 * Define the static fields that will be considered in the aurastride API
 */

if(!function_exists('cf7au_to_aurastride_api_fields')){

	function cf7au_to_aurastride_api_fields(){

		$aurastride_fields = array(
							"lead_name"			     => "EnquiryName",
							"organization_name"		 => "CompanyName",
							"organization_number"	 => "CompanyContactNumber",
							"organization_email"	 => "CompanyEmail",
							"contact_first_name"	 => "ContactName",
							"contact_middle_name"	 => "ContactMiddleName",
							"contact_last_name"		 => "ContactLastName",
							"contact_primary_number" => "ContactNumber",
							"contact_primary_email"	 => "ContactEmail",
							"lead_note"				 => "Note",
							"lead_received_date" 	 => "ReceivedDate",
						);

		$aurastride_fields = (array) apply_filters('cf7au_to_aurastride_api_fields_list',$aurastride_fields);

		return $aurastride_fields;
	}
}

/* Define function for render input fields here */

if(!function_exists('cf7au_to_aurastride_option_fields')){
	
	function cf7au_to_aurastride_option_fields($arr_fields,$selectedVal=''){
		if(empty($arr_fields)) return ;
		$option = '';
		foreach($arr_fields as $opt_key => $opt_val){
			$selected = '';
			if($selectedVal == $opt_key ) $selected = 'selected';
			$option .= '<option value="'.$opt_key.'" '.$selected.'>'.$opt_val.'</option>';
		}
		return $option;
	}
}

//Setup country code related master data value here
if(!function_exists('cf7au_aurastride_country_code_with_master_data')){
	
	function cf7au_aurastride_country_code_with_master_data($countryCode=''){

		$countries = array(
							'AF' => 'afghanistan',//'Afghanistan',
							'AK' => 'alaska',// Alaska
							'AX' => '',//'Aland Islands',
							'AL' => 'albania',//'Albania',
							'DZ' => 'algeria',//'Algeria',
							'AS' => '',//'American Samoa',
							'AD' => '',//'Andorra',
							'AO' => 'angola',//'Angola',
							'AI' => '',//'Anguilla',
							'AQ' => '',//'Antarctica',
							'AG' => '',//'Antigua And Barbuda',
							'AR' => 'MVALA',//'Argentina',
							'AM' => 'armenia',//'Armenia',
							'AW' => '',//'Aruba',
							'AU' => 'australia',//'Australia',
							'AT' => 'austria',//'Austria',
							'AZ' => 'azerbaijan',//'Azerbaijan',
							'BS' => '',//'Bahamas',
							'BH' => 'bahrain',//'Bahrain',
							'BD' => 'bangladesh',//'Bangladesh',
							'BB' => 'barbados',//'Barbados',
							'BY' => '',//'Belarus',
							'BE' => 'belgium',//'Belgium',
							'BZ' => 'belize',//'Belize',
							'BJ' => '',//'Benin',
							'BM' => '',//'Bermuda',
							'BT' => '',//'Bhutan',
							'BO' => 'bolivia_-_south_america',//'Bolivia',
							'BA' => 'bosnia_and_herzegovina',//'Bosnia And Herzegovina',
							'BW' => 'botswana',//'Botswana',
							'BV' => '',//'Bouvet Island',
							'BR' => 'brazil',//'Brazil',
							'IO' => '',//'British Indian Ocean Territory',
							'BN' => '',//'Brunei Darussalam',
							'BG' => 'bulgaria',//'Bulgaria',
							'BF' => '',//'Burkina Faso',
							'BI' => '',//'Burundi',
							'KH' => 'cambodia',//'Cambodia',
							'CM' => '',//'Cameroon',
							'CA' => 'canada',//'Canada',
							'CV' => '',//'Cape Verde',
							'KY' => '',//'Cayman Islands',
							'CF' => '',//'Central African Republic',
							'TD' => '',//'Chad',
							'CL' => 'south_america_-_chile',//'Chile',
							'CN' => 'china',//'China',
							'CX' => '',//'Christmas Island',
							'CC' => '',//'Cocos (Keeling) Islands',
							'CO' => 'colombia',//'Colombia',
							'KM' => '',//'Comoros',
							'CG' => '',//'Congo',
							'CD' => '',//'Congo, Democratic Republic',
							'CK' => '',//'Cook Islands',
							'CR' => 'costa_rica',//'Costa Rica',
							'CI' => '',//'Cote D\'Ivoire',
							'HR' => 'croatia',//'Croatia',
							'CU' => '',//'Cuba',
							'CY' => 'cyprus',//'Cyprus',
							'CZ' => 'czech_republic',//'Czech Republic',
							'DK' => 'denmark',//'Denmark',
							'DJ' => '',//'Djibouti',
							'DM' => '',//'Dominica',
							'DO' => '',//'Dominican Republic',
							'EC' => 'ecuador',//'Ecuador',
							'EG' => 'egypt',//'Egypt',
							'SV' => '',//'El Salvador',
							'GQ' => '',//'Equatorial Guinea',
							'ER' => '',//'Eritrea',
							'EE' => 'estonia',//'Estonia',
							'ET' => 'ethiopia',//'Ethiopia',
							'FK' => '',//'Falkland Islands (Malvinas)',
							'FO' => '',//'Faroe Islands',
							'FJ' => 'fiji',//'Fiji',
							'FI' => 'finland',//'Finland',
							'FR' => 'france',//'France',
							'GF' => '',//'French Guiana',
							'PF' => '',//'French Polynesia',
							'TF' => '',//'French Southern Territories',
							'GA' => 'gabon',//'Gabon',
							'GM' => '',//'Gambia',
							'GE' => 'georgia',//'Georgia',
							'DE' => 'germany',//'Germany',
							'GH' => 'ghana',//'Ghana',
							'GI' => '',//'Gibraltar',
							'GR' => 'greece',//'Greece',
							'GL' => 'greenland',//'Greenland',
							'GD' => '',//'Grenada',
							'GP' => '',//'Guadeloupe',
							'GU' => '',//'Guam',
							'GT' => '',//'Guatemala',
							'GG' => '',//'Guernsey',
							'GN' => '',//'Guinea',
							'GW' => '',//'Guinea-Bissau',
							'GY' => '',//'Guyana',
							'HT' => '',//'Haiti',
							'HM' => '',//'Heard Island & Mcdonald Islands',
							'VA' => '',//'Holy See (Vatican City State)',
							'HN' => '',//'Honduras',
							'HK' => 'hong_kong',//'Hong Kong',
							'HU' => 'hungary',//'Hungary',
							'IS' => 'iceland',//'Iceland',
							'IN' => 'india',//'India',
							'ID' => 'indonesia',//'Indonesia',
							'IR' => 'iran',//'Iran, Islamic Republic Of',
							'IQ' => 'iraq',//'Iraq',
							'IE' => 'ireland',//'Ireland',
							'IM' => 'isle_of_man',//'Isle Of Man',
							'IL' => 'israel',//'Israel',
							'IT' => 'italy',//'Italy',
							'JM' => 'jamaica',//'Jamaica',
							'JP' => 'japan',//'Japan',
							'JE' => '',//'Jersey',
							'JO' => 'jordan',//'Jordan',
							'KZ' => '',//'Kazakhstan',
							'KE' => 'kenya,_east_africa',//'Kenya',
							'KI' => '',//'Kiribati',
							'KR' => 'south_korea',//'Korea',
							'KW' => 'kuwait',//'Kuwait',
							'KG' => '',//'Kyrgyzstan',
							'LA' => '',//'Lao People\'s Democratic Republic',
							'LV' => 'latvia',//'Latvia',
							'LB' => 'lebanon',//'Lebanon',
							'LS' => '',//'Lesotho',
							'LR' => 'liberia',//'Liberia',
							'LY' => '',//'Libyan Arab Jamahiriya',
							'LI' => '',//'Liechtenstein',
							'LT' => 'lithuania',//'Lithuania',
							'LU' => 'luxembourg',//'Luxembourg',
							'MO' => '',//'Macao',
							'MK' => 'north_macedonia',//'Macedonia',
							'MG' => 'madagascar',//'Madagascar',
							'MW' => '',//'Malawi',
							'MY' => 'malaysia',//'Malaysia',
							'MV' => 'maldives',//'Maldives',
							'ML' => '',//'Mali',
							'MT' => 'malta',//'Malta',
							'MH' => '',//'Marshall Islands',
							'MQ' => '',//'Martinique',
							'MR' => '',//'Mauritania',
							'MU' => 'mauritius',//'Mauritius',
							'YT' => '',//'Mayotte',
							'MX' => 'mexico',//'Mexico',
							'FM' => '',//'Micronesia, Federated States Of',
							'MD' => 'moldova_-_eastern_europe',//'Moldova',
							'MC' => 'monaco',//'Monaco',
							'MN' => 'mongolia',//'Mongolia',
							'ME' => 'montenegro',//'Montenegro',
							'MS' => '',//'Montserrat',
							'MA' => 'morocco',//'Morocco',
							'MZ' => 'mozambique',//'Mozambique',
							'MM' => 'myanmar',//'Myanmar',
							'NA' => 'namibia',//'Namibia',
							'NR' => '',//'Nauru',
							'NP' => 'nepal',//'Nepal',
							'NL' => 'netherlands',//'Netherlands',
							'AN' => '',//'Netherlands Antilles',
							'NC' => '',//'New Caledonia',
							'NZ' => 'new_zealand',//'New Zealand',
							'NI' => '',//'Nicaragua',
							'NE' => '',//'Niger',
							'NG' => 'nigeria',//'Nigeria',
							'NU' => '',//'Niue',
							'NF' => '',//'Norfolk Island',
							'MP' => '',//'Northern Mariana Islands',
							'NO' => 'norway',//'Norway',
							'OM' => 'oman',//'Oman',
							'PK' => 'pakistan',//'Pakistan',
							'PW' => '',//'Palau',
							'PS' => '',//'Palestinian Territory, Occupied',
							'PA' => 'panama',//'Panama',
							'PG' => '',//'Papua New Guinea',
							'PY' => '',//'Paraguay',
							'PE' => 'peru',//'Peru',
							'PH' => 'philippines',//'Philippines',
							'PN' => '',//'Pitcairn',
							'PL' => 'poland',//'Poland',
							'PT' => 'portugal',//'Portugal',
							'PR' => 'puerto_rico',//'Puerto Rico',
							'QA' => 'qatar',//'Qatar',
							'RE' => '',//'Reunion',
							'RO' => 'romania',//'Romania',
							'RU' => '',//'Russian Federation',
							'RW' => 'rwanda',//'Rwanda',
							'BL' => '',//'Saint Barthelemy',
							'SH' => '',//'Saint Helena',
							'KN' => '',//'Saint Kitts And Nevis',
							'LC' => '',//'Saint Lucia',
							'MF' => '',//'Saint Martin',
							'PM' => '',//'Saint Pierre And Miquelon',
							'VC' => '',//'Saint Vincent And Grenadines',
							'WS' => '',//'Samoa',
							'SM' => '',//'San Marino',
							'ST' => '',//'Sao Tome And Principe',
							'SA' => 'saudi_arabia',//'Saudi Arabia',
							'SN' => 'senegal',//'Senegal',
							'RS' => 'serbia',//'Serbia',
							'SC' => 'seychelles',//'Seychelles',
							'SL' => '',//'Sierra Leone',
							'SG' => 'singapore',//'Singapore',
							'SK' => 'slovakia',//'Slovakia',
							'SI' => 'slovenia',//'Slovenia',
							'SB' => '',//'Solomon Islands',
							'SO' => '',//'Somalia',
							'ZA' => 'south_africa',//'South Africa',
							'GS' => '',//'South Georgia And Sandwich Isl.',
							'ES' => 'spain',//'Spain',
							'LK' => 'sri_lanka',//'Sri Lanka',
							'SD' => '',//'Sudan',
							'SR' => '',//'Suriname',
							'SJ' => '',//'Svalbard And Jan Mayen',
							'SZ' => '',//'Swaziland',
							'SE' => 'sweden',//'Sweden',
							'CH' => 'switzerland',//'Switzerland',
							'SY' => '',//'Syrian Arab Republic',
							'TW' => 'taiwan',//'Taiwan',
							'TJ' => '',//'Tajikistan',
							'TZ' => 'tanzania',//'Tanzania',
							'TH' => 'thailand',//'Thailand',
							'TL' => '',//'Timor-Leste',
							'TG' => '',//'Togo',
							'TK' => '',//'Tokelau',
							'TO' => '',//'Tonga',
							'TT' => 'trinidad_and_tobago',//'Trinidad And Tobago',
							'TN' => 'tunisia',//'Tunisia',
							'TR' => 'turkey',//'Turkey',
							'TM' => '',//'Turkmenistan',
							'TC' => '',//'Turks And Caicos Islands',
							'TV' => '',//'Tuvalu',
							'UG' => 'uganda',//'Uganda',
							'UA' => 'ukraine',//'Ukraine',
							'AE' => 'uae',//'United Arab Emirates',
							'GB' => 'uk',//'United Kingdom',
							'US' => 'usa',//'United States',
							'UM' => '',//'United States Outlying Islands',
							'UY' => '',//'Uruguay',
							'UZ' => 'uzbekistan',//'Uzbekistan',
							'VU' => '',//'Vanuatu',
							'VE' => '',//'Venezuela',
							'VN' => 'vietnam',//'Viet Nam',
							'VG' => '',//'Virgin Islands, British',
							'VI' => '',//'Virgin Islands, U.S.',
							'WF' => '',//'Wallis And Futuna',
							'EH' => '',//'Western Sahara',
							'YE' => '',//'Yemen',
							'ZM' => '',//'Zambia',
							'ZW' => 'zimbabwe',//'Zimbabwe',
						);
						
		
		//define filter for change country relevant data here				
		$countries = (array) apply_filters('cf7au_to_aurastride_crm_country_list',$countries);

		if(!empty($countries) && !empty($countryCode) && array_key_exists($countryCode,$countries)){
			return $countries[$countryCode];
		}
		else{
			return $countries;
		}
	}
}