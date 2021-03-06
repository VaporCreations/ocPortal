<?php /*

 ocPortal
 Copyright (c) ocProducts, 2004-2011

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license		http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright	ocProducts Ltd
 * @package		core
 */

/**
 * Find the mime type for the given file extension. It does not take into account whether the file type has been white-listed or not, and returns a binary download mime type for any unknown extensions.
 *
 * @param  string			The file extension (no dot)
 * @return string			The MIME type
 */
function get_mime_type($extension)
{
	$mime_types=array(

		// Plain text
		'1st'=>'text/plain',
		'txt'=>'text/plain',
		''=>'text/plain', // No file type implies a plain text file, e.g. README

		// Documents
		'pdf'=>'application/pdf',
		'rtf'=>'text/richtext',
		'ps'=>'application/postscript',
		'html'=>'application/octet-stream', // to prevent XSS
		'htm'=>'application/octet-stream', // to prevent XSS

		// Open office
		'odt'=>'application/vnd.oasis.opendocument.text',
		'ods'=>'application/vnd.oasis.opendocument.spreadsheet',
		'odp'=>'application/vnd.oasis.opendocument.presentation',

		// Microsoft office
		'doc'=>'application/msword',
		'mdb'=>'application/x-msaccess',
		'xls'=>'application/vnd.ms-excel',
		'docx'=>'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
		'xlsx'=>'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',

		// XML
		'xml'=>'application/octet-stream', // to prevent XSS	// 'text/xml',
		'rss'=>'application/rss+xml',

		// Presentations/Animations/3D
		'ppt'=>'application/powerpoint',
		'pptx'=>'application/vnd.openxmlformats-officedocument.presentationml.presentation',
		'svg'=>'application/octet-stream', // to prevent XSS	//'image/svg+xml',
		'wrl'=>'model/vrml',
		'vrml'=>'model/vrml',
		'swf'=>'application/octet-stream', // to prevent XSS	// 'application/x-shockwave-flash',

		// Images
		'png'=>'image/png',
		'gif'=>'image/gif',
		'jpg'=>'image/jpeg',
		'jpe'=>'image/jpeg',
		'jpeg'=>'image/jpeg',
		'psd'=>'image/x-photoshop',

		// Non/badly compressed images
		'bmp'=>'image/x-MS-bmp',
		'tga'=>'image/x-targa',
		'tif'=>'image/tiff',
		'tiff'=>'image/tiff',
		'pcx'=>'image/x-pcx',

		// Movies
		'avi'=>'video/mpeg',//'video/x-ms-asf' works with the plugin on Windows Firefox but nothing else,//'video/x-msvideo' is correct but does not get recognised by Microsoft Firefox WMV plugin and confuses RealMedia Player if it sees data transferred under that mime type,
		'mpg'=>'video/mpeg',
		'mpe'=>'video/mpeg',
		'3gp'=>'video/3gpp',
		'mp4'=>'video/mp4',
		'm4v'=>'video/mp4',
		'mpeg'=>'video/mpeg',
		'ogv'=>'video/ogg',
		'webm'=>'video/webm',

		// Proprietary movie formats
		'mov'=>'video/quicktime',
		'qt'=>'video/quicktime',
		'wmv'=>'video/x-ms-wmv',
		'ram'=>'audio/x-pn-realaudio',
		'rm'=>'audio/x-pn-realaudio',
		'asf'=>'video/x-ms-asf',

		// Audio
		'ra'=>'audio/x-pn-realaudio-plugin',
		'wma'=>'audio/x-ms-wma',
		'wav'=>'audio/x-wav',
		'mp3'=>'audio/x-mpeg',
		'ogg'=>'audio/ogg',
		'mid'=>'audio/midi',

		// File sharing
		'torrent'=>'application/x-bittorrent',
	);
	if (file_exists(get_file_base().'/data/flv_player.swf'))
	{
		$mime_types['flv']='video/x-flv';
	}

	if (array_key_exists($extension,$mime_types)) return $mime_types[$extension];

	return 'application/octet-stream';
}

