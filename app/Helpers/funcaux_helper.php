<?php
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use App\Models\User_general_model;

if(!function_exists('url_semantica')){
	/*      Copyright 2011  Trung Kien  (email : trungkientn@gmail.com)

			This program is free software; you can redistribute it and/or modify
			it under the terms of the GNU General Public License as published by
			the Free Software Foundation; either version 2 of the License, or
			(at your option) any later version.

			This program is distributed in the hope that it will be useful,
			but WITHOUT ANY WARRANTY; without even the implied warranty of
			MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
			GNU General Public License for more details.

			You should have received a copy of the GNU General Public License
			along with this program; if not, write to the Free Software
			Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
	*/
	function url_semantica($string,$keyReplace="-",$minuscula=true){
		$cyrillicPattern  = array('а','б','в','г','д','e', 'ё','ж','з','и','й','к','л','м','н','о','п','р','с','т','у',
			'ф','х','ц','ч','ш','щ','ъ','ь', 'э', 'ы', 'ю','я','А','Б','В','Г','Д','Е', 'Ё','Ж','З','И','Й','К','Л','М','Н','О','П','Р','С','Т','У',
			'Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ь', 'Э', 'Ы', 'Ю','Я' );

		$latinPattern = array( 'a','b','v','g','d','e','jo','zh','z','i','y','k','l','m','n','o','p','r','s','t','u',
			'f' ,'h' ,'ts' ,'ch','sh' ,'sht', '', '`', 'je','ji','yu' ,'ya','A','B','V','G','D','E','Jo','Zh',
			'Z','I','Y','K','L','M','N','O','P','R','S','T','U',
			'F' ,'H' ,'Ts' ,'Ch','Sh','Sht', '', '`', 'Je' ,'Ji' ,'Yu' ,'Ya' );

		$string = str_replace($cyrillicPattern, $latinPattern, $string);


		$string    =  RemoveSign($string);
		//neu muon de co dau
		//$string     =  trim(preg_replace("/[^A-Za-z0-9àáạảãâầấậẩẫăằắặẳẵèéẹẻẽêềếệểễìíịỉĩòóọỏõôồốộổỗơờớợởỡùúụủũưừứựửữỳýỵỷỹđÀÁẠẢÃÂẦẤẬẨẪĂẰẮẶẲẴÈÉẸẺẼÊỀẾỆỂỄÌÍỊỈĨÒÓỌỎÕÔỒỐỘỔỖƠ$

		$string     =  trim(preg_replace("/[^A-Za-z0-9]/i"," ",$string)); // khong dau
		$string     =  str_replace(" ","-",$string);
		$string    =    str_replace("--","-",$string);
		$string    =    str_replace("--","-",$string);
		$string    =    str_replace("--","-",$string);
		$string    =    str_replace($keyReplace,"-",$string);
		$string    =    ($minuscula)?strtolower($string):$string;
		return $string;
	}
}
if(!function_exists('RemoveSign')){
	function RemoveSign($str){
		$coDau=array("à","á","ạ","ả","ã","â","ầ","ấ","ậ","ẩ","ẫ","ă",
			"ằ","ắ","ặ","ẳ","ẵ",
			"è","é","ẹ","ẻ","ẽ","ê","ề" ,"ế","ệ","ể","ễ",
			"ì","í","ị","ỉ","ĩ",
			"ò","ó","ọ","ỏ","õ","ô","ồ","ố","ộ","ổ","ỗ","ơ"
		,"ờ","ớ","ợ","ở","ỡ",
			"ù","ú","ụ","ủ","ũ","ư","ừ","ứ","ự","ử","ữ",
			"ỳ","ý","ỵ","ỷ","ỹ",
			"đ",
			"À","Á","Ạ","Ả","Ã","Â","Ầ","Ấ","Ậ","Ẩ","Ẫ","Ă"
		,"Ằ","Ắ","Ặ","Ẳ","Ẵ",
			"È","É","Ẹ","Ẻ","Ẽ","Ê","Ề","Ế","Ệ","Ể","Ễ",
			"Ì","Í","Ị","Ỉ","Ĩ",
			"Ò","Ó","Ọ","Ỏ","Õ","Ô","Ồ","Ố","Ộ","Ổ","Ỗ","Ơ"
		,"Ờ","Ớ","Ợ","Ở","Ỡ",
			"Ù","Ú","Ụ","Ủ","Ũ","Ư","Ừ","Ứ","Ự","Ử","Ữ",
			"Ỳ","Ý","Ỵ","Ỷ","Ỹ",
			"Đ","ê","ù","à",
			"Ñ","ñ");

		$khongDau=array("a","a","a","a","a","a","a","a","a","a","a"
		,"a","a","a","a","a","a",
			"e","e","e","e","e","e","e","e","e","e","e",
			"i","i","i","i","i",
			"o","o","o","o","o","o","o","o","o","o","o","o"
		,"o","o","o","o","o",
			"u","u","u","u","u","u","u","u","u","u","u",
			"y","y","y","y","y",
			"d",
			"A","A","A","A","A","A","A","A","A","A","A","A"
		,"A","A","A","A","A",
			"E","E","E","E","E","E","E","E","E","E","E",
			"I","I","I","I","I",
			"O","O","O","O","O","O","O","O","O","O","O","O"
		,"O","O","O","O","O",
			"U","U","U","U","U","U","U","U","U","U","U",
			"Y","Y","Y","Y","Y",
			"D","e","u","a",
			"N","n");
		return str_replace($coDau,$khongDau,$str);
	}
}
function quitarAcentos($text)
{
	$text = htmlentities($text, ENT_QUOTES, 'UTF-8');
	$text = strtolower($text);
	$patron = array (
		// Espacios, puntos y comas por guion
		'/[\., ]+/' => '-',

		// Vocales
		'/&agrave;/' => 'a',
		'/&egrave;/' => 'e',
		'/&igrave;/' => 'i',
		'/&ograve;/' => 'o',
		'/&ugrave;/' => 'u',

		'/&aacute;/' => 'a',
		'/&eacute;/' => 'e',
		'/&iacute;/' => 'i',
		'/&oacute;/' => 'o',
		'/&uacute;/' => 'u',

		'/&acirc;/' => 'a',
		'/&ecirc;/' => 'e',
		'/&icirc;/' => 'i',
		'/&ocirc;/' => 'o',
		'/&ucirc;/' => 'u',

		'/&atilde;/' => 'a',
		'/&etilde;/' => 'e',
		'/&itilde;/' => 'i',
		'/&otilde;/' => 'o',
		'/&utilde;/' => 'u',

		'/&auml;/' => 'a',
		'/&euml;/' => 'e',
		'/&iuml;/' => 'i',
		'/&ouml;/' => 'o',
		'/&uuml;/' => 'u',
		'/&auml;/' => 'a',
		'/&euml;/' => 'e',
		'/&iuml;/' => 'i',
		'/&ouml;/' => 'o',
		'/&uuml;/' => 'u',

		// Otras letras y caracteres especiales
		'/&aring;/' => 'a',
		'/&ntilde;/' => 'n',
		// Agregar aqui mas caracteres si es necesario
	);

	$text = preg_replace(array_keys($patron),array_values($patron),$text);
	return $text;
}

function limpiar_archivo($name)
{
	if(stristr($name, '.jpg'))
	{
		//quitamos la extension al archivo
		$nombre = str_replace('.jpg', '', $name);
		//limpiamos el nombre
		$nombre = url_semantica($nombre);
		//añadimos la extension al archivo
		$nombre = $nombre.'.jpg';
	}
	elseif(stristr($name, '.jpeg'))
	{
		//quitamos la extension al archivo
		$nombre = str_replace('.jpeg', '', $name);
		//limpiamos el nombre
		$nombre = url_semantica($nombre);
		//añadimos la extension al archivo
		$nombre = $nombre.'.jpeg';
	}
	elseif(stristr($name, '.gif'))
	{
		//quitamos la extension al archivo
		$nombre = str_replace('.gif', '', $name);
		//limpiamos el nombre
		$nombre = url_semantica($nombre);
		//añadimos la extension al archivo
		$nombre = $nombre.'.gif';
	}
	elseif(stristr($name, '.png'))
	{
		//quitamos la extension al archivo
		$nombre = str_replace('.png', '', $name);
		//limpiamos el nombre
		$nombre = url_semantica($nombre);
		//añadimos la extension al archivo
		$nombre = $nombre.'.png';
	}
	elseif(stristr($name, '.pdf'))
	{
		//quitamos la extension al archivo
		$nombre = str_replace('.pdf', '', $name);
		//limpiamos el nombre
		$nombre = url_semantica($nombre);
		//añadimos la extension al archivo
		$nombre = $nombre.'.pdf';
	}

	return $nombre;
}

if(!function_exists('indexar_array')){
	function indexar_array($array,$index){
		if(!is_array($array)) return array($array->$index => $array);

		$return_array=array();
		foreach($array as $data){
			if(is_object($data))
				$return_array[$data->$index]=$data;
			else
				$return_array[$data[$index]]=$data;
		}
		return $return_array;
	}
}

if(!function_exists('p2bd')){
	function p2bd($fecha){
		list($iDia,$iMes,$iAnyo)=explode("/",$fecha);
		return date("Y-m-d",mktime(0,0,0,$iMes,$iDia,$iAnyo));
	}
}

if(!function_exists('bd2p')){
	function bd2p($fecha){
		list($iAnyo,$iMes,$iDia)=explode("-",$fecha);
		return date("d/m/Y",mktime(0,0,0,$iMes,$iDia,$iAnyo));
	}
}

if(!function_exists('bd2p')) {
	function interval($fecha_ini, $fecha_fin)
	{
		$datetime1 = new DateTime($fecha_ini);
		$datetime2 = new DateTime($fecha_fin);
		$interval = $datetime1->diff($datetime2);
		$num_dias = $interval->days;
		return $num_dias;
	}
}

/*
 * $document - page, room, etc
 * */
if (!function_exists('get_page_href')) {
	function get_page_href($document, $home_page_id = null)
	{
		if (isset($document->text_full_url) && $document->text_full_url != '') {
			return $document->text_full_url;
		}

		$href  = '';
		$href .= base_url().'/';
		$href .= \Config\Services::session()->get('slug_lang');
		if ($document->id == $home_page_id) {
			return $href;
		}
		$href .= ($document->uri_string) ? $document->uri_string : '';

		return $href;
	}
}

if (!function_exists('thumb')) {
	/**
	 * @param $file
	 * @param $size|high|medium|low|lowest
	 * @return mixed
	 */
	function thumb($file, $size = null)
	{
		$ext = pathinfo($file,PATHINFO_EXTENSION);
		$thumb_file = str_replace('.'. $ext, '.t'. $size .'.'. $ext, $file);
		return (file_exists(FCPATH .'/'. $thumb_file))
			? $thumb_file
			: $file;
	}
}

/*
 *
 * */
if (!function_exists('get_parent_page_href')) {
	function get_parent_page_href($slug, $document)
	{
		$CI =& get_instance();
		$CI->load->library('session', 'db');
		$CI->load->model('User_general_model');

		$href  = '';
		$href .= base_url();
		$href .= $CI->session->userdata('slug_lang');

		$url = $CI->user_general_model->get_dynamic_parent_url($slug, $document);
		if (mb_substr($url, -1) == '/') {
			$url = mb_substr($url, 0, -1);
		}

		$href .= ($url) ? $url : '';

		return $href;
	}
}

/*
 *
 * */
if (!function_exists('get_dynamic_container_url')) {
	function get_dynamic_container_url($slug, $document,$site_lang, $site_domain, $no_slash = false)
	{

		$href  = '';
		$href .= base_url().'/';
		$href .= \Config\Services::session()->get('slug_lang');

		$user_general_model = new User_general_model ();
		$url = $user_general_model->get_dynamic_container_url($slug, $document,$site_lang, $site_domain);
		if (mb_substr($url, -1) == '/') {
			$url = mb_substr($url, 0, -1);
		}

		if ($url && !$no_slash) {
			$url .= '/';
		}
		$href .= ($url) ? $url : '';

		return $href;
	}
}

if (!function_exists('image_url')) {
	/**
	 * @param $image
	 * @param $desktop |auto|original|high|medium|low|lowest
	 * @param $tablet |auto|original|high|medium|low|lowest
	 * @param $mobile |auto|original|high|medium|low|lowest
	 * @return string
	 */
	function image_url($image, $desktop, $tablet, $mobile): string
	{
		$mobile_detect = new Mobile_Detect();
		if ($mobile_detect->isMobile() && $mobile_detect->isTablet()) {
			$thumbnail_size = 'medium';
			if ($tablet != 'auto') {
				$thumbnail_size = $tablet;
			}
		} elseif ($mobile_detect->isMobile()) {
			$thumbnail_size = 'low';
			if ($mobile != 'auto') {
				$thumbnail_size = $mobile;
			}
		} else {
			$thumbnail_size = 'high';
			if ($desktop != 'auto') {
				$thumbnail_size = $desktop;
			}
		}

		return base_url(thumb($image, $thumbnail_size));
	}
}

/*
*
* */
if (!function_exists('replace_extension')) {
	function replace_extension($filename, $new_extension)
	{
		$user_general_model = new User_general_model ();
		return $user_general_model->replace_extension($filename, $new_extension);

	}
}

if (!function_exists('target_blank')) {
	function target_blank($url)
	{
		$CI =& get_instance();

		return (stristr($url, $CI->config->item('domain')) === FALSE || stristr($url, '/assets/'))
			? 'target="_blank" rel="noopener"' : '';
	}
}

if (!function_exists('form_error')) {
    function form_error ($field) {
        $validation =  \Config\Services::validation();
        echo $validation->getError($field);
    }
}


if (!function_exists('gen_paged_link')) {
    function gen_paged_link($option, $value, $reset = [])
    {
        $other = '';
        $reset[] = $option;
        foreach ($_GET as $g_p => $g_v) if (!in_array($g_p, $reset)) {
            $other .= $g_p . '=' . urlencode($g_v) . '&';
        }
        return '?' . $other . ($option ? $option . '=' . $value : '');
    }
}