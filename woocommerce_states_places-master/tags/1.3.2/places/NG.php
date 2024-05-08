<?php

/**
 * Nigerian Places
 *
 * @author Bughacker Janaan <bughackerjanaan@gmail.com>
 * @version 1.0.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * Credits: https://donjajo.com/ngstateslga-nigerian-36-states-local-government-areas-php-class/
 */

global $places;

$places['NG'] = array(
	'AB' => array(
		'Uyo'
	),
	'AN' => array(
		'Onitsha North',
	),
	'BA' => array(
		'Bauchi',
	),
	'BY' => array(
		'Yenegoa'
	),
	'BE' => array(
		'Makurdi',
	),
	'BO' => array(
		'Maiduguri',
	),
	'CR' => array(
		'Calabar'
	),
	'DE' => array(
		'Sapele'
	),
	'EB' => array(
		'Abakaliki'
	),
	'ED'  => array(
		'Esan South-East'
	),
	'EK' => array(
		'Ekiti-East'
	),
	'EN' => array(
		'Enugu'
	),
	'GO' => array(
		'Gombe'
	),
	'IM' => array(
		'Owerri',
	),
	'JI' => array(
		'Dutse'
	),
	'KD' => array(
		'Kaduna'
	),
	'KE' => array(
		'Birnin Kebbi'
	),
	'KN' => array(
		'Kano'
	),
	'KT' => array(
		'Katsina'
	),
	'KO' => array(
		'Lokoja'
	),
	'KW' => array(
		'Ilorin'
	),
	'LA' => array(
		'Agege',
		'Ajeromi-Ifelodun',
		'Alimosho',
		'Amuwo-Odofin',
		'Apapa',
		'Badagry',
		'Epe',
		'Eti-Osa',
		'Ibeju/Lekki',
		'Ifako-Ijaye',
		'Ikeja',
		'Ikorodu',
		'Kosofe',
		'Lagos Island',
		'Lagos Mainland',
		'Ikota',
		'Ikoyi',
		'Ajah',
		'Mushin',
		'Ojo',
		'Oshodi-Isolo',
		'Shomolu',
		'Surulere'
	),
	'NA' => array(
		'Lafia'
	),
	'NI' => array(
		'Muya'
	),
	'OG'  => array(
		'Abeokuta'
	),
	'ON' => array(
		'Akure'
	),
	'OS' => array(
		'Osogbo'
	),
	'OY' => array(
		'Ibadan'
	),
	'PL' => array(
		'Jos'
	),
	'RI' => array(
		'Port-Harcourt'
	),
	'SO' => array(
		'Sokoto'
	),
	'TA' => array(
		'Jalingo'
	),
	'YO' => array(
		'Damaturu'
	),
	'ZA' => array(
		'Gusau'
	),
	'FC' => array(
		'Abuja'
	)
);

// Use this filter to handle the Nigerian Places
$places['NG'] = apply_filters('scpwoo_custom_places_ng', $places['NG']);
