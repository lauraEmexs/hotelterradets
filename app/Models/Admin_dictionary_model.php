<?php

namespace App\Models;

use CodeIgniter\Model;

class Admin_dictionary_model extends Model {
    
    var $dictionary = 'theme_lang.php';
    
    function __construct() {
        parent::__construct();
    }
    
    function get_all($languages = array()) {
        $data = array();
        foreach ($languages as $lang)
            $data[$lang->id] = $this->get_dictionary($lang->id);
        return $data;
    }
    
    function get_txt_lang($id) {
        $builder = $this->db->table('languages');
        $builder->select('text_lang');
        $builder->where('id', $id);
        $query = $builder->get();
        $res = $query->getResult();
        return $res[0]->text_lang;
    }
    
    function get_dictionary($language) {
        
        $txt_lang = $this->get_txt_lang($language);
        //print_r($txt_lang);die;
        
        $lang = array();
        if (file_exists(APPPATH . 'Language/' . $txt_lang . '/' . $this->dictionary)) {
            if (function_exists('opcache_invalidate')) {
                @opcache_invalidate (APPPATH . 'language/' . $txt_lang . '/' . $this->dictionary);
            }
            include(APPPATH . 'Language/' . $txt_lang . '/' . $this->dictionary);
        }

        return $lang;
    }
    
    function get_all_keys($languages = array()) {
        $data = array();
        foreach ($languages as $lang) {
            $temp = $this->get_dictionary($lang->id);
            foreach ($temp as $k => $v)
                $data[$k] = 1;
        }
        return $data;
    }

    function actualizar($dictionary, $all_keys)
    {
        foreach ($dictionary as $language => $content) {
            $txt_lang = $this->get_txt_lang($language);
            $dictionary_file = APPPATH . 'Language/' . $txt_lang . '/' . $this->dictionary;

            #Comprobar que el fichero existe.
            if (!file_exists($dictionary_file))
                continue;

            #Hacer copia de seguridad, solo una vez al día.
            if (!file_exists(APPPATH . 'Language/' . $txt_lang . '/' . str_replace('.php', '.' . date("Ymd") . '.php', $this->dictionary)))
                copy($dictionary_file, APPPATH . 'Language/' . $txt_lang . '/' . str_replace('.php', '.' . date("Ymd") . '.php', $this->dictionary));

            $string_to_write = '<?php' . "\n" . '/* Theme */' . "\n";
            foreach ($all_keys as $k => $v) {
                if (isset($content[$k])) {
                    $string_to_write .= '$lang[\'' . $k . '\']=\'' . str_replace("'", "\'", $content[$k]) . '\';' . "\n";
                } else {
                    $string_to_write .= '$lang[\'' . $k . '\']=\'\';' . "\n";
                }
            }
            $string_to_write .= "\n\n";
            $string_to_write .= 'return $lang;'."\n";

            file_put_contents($dictionary_file, $string_to_write);
        }
    }
    
}
