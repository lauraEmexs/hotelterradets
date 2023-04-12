<?php
namespace App\Models;

use CodeIgniter\Model;

class User_general_model extends Model
{

	function __construct() {
		parent::__construct();
	}

	public function get_dynamic_table_data($params)
	{
		$slug     = (isset($params['slug'])) ? $params['slug'] : null;
		$language = (isset($params['language'])) ? $params['language'] : false;
		$id       = (isset($params['id'])) ? $params['id'] : false;
		$order    = (isset($params['order'])) ? $params['order'] : null;
		$domain   = (isset($params['domain'])) ? $params['domain'] : false;
		$where_in = (isset($params['where_in'])) ? $params['where_in'] : [];
        $conditions = (isset($params['conditions'])) ? $params['conditions'] : [];
        $like = (isset($params['like'])) ? $params['like'] : [];

		$table = 'dynamic_' . $slug;
		if (!$this->table_exists($table)) {
			return [];
		}
        $builder = $this->db->table($table);
        if (!empty($conditions)) {
            $builder->where($conditions);
        }
        if (!empty($like)) {
            $builder->like($like);
        }
		foreach ($where_in as $k => $conditions_array) {
			$builder->whereIn($k, $conditions_array);
		}

		if ($language)
			$builder->where('language', $language);

		if ($id)
			$builder->where('id', $id);
		if ($domain)
			$builder->where('domain', $domain);

		if (!is_null($order)) {
			$builder->orderBy($order);
		}
		$query = $builder->get();

		return ( count($query->getResult()) == 1 ) ? [$query->getFirstRow()] : $query->getResult();
	}

	public function get_related_table_data($slug, $index_1, $index_2) {
		$table = 'related_' . $slug;
		if (!$this->table_exists($table))
			return array();
		$query = $this->db->get($table);
		$results = $query->result();
		$aRet = array();
		foreach ($results as $row)
			$aRet[$row->$index_1][] = $row->$index_2;
		return $aRet;
	}

	public function exist_element($slug, $id, $idioma) {
		$table = 'dynamic_' . $slug;
		if (!$this->table_exists($table))
			return false;
		if (!$id)
			return false;
		if (!$idioma)
			return false;
		$this->db->where('id', $id);
		$this->db->where('language', $idioma);
		$query = $this->db->get($table);
		$results = $query->result();
		return (!empty($results)) ? true : false;
	}

	public function table_exists($table) {
		$query = $this->db->query("SHOW TABLES LIKE '" . $table . "'");
		$results = $query->getResult();
		return (empty($results)) ? false : true;
	}

	public function column_exists($table, $column) {
		$query = $this->db->query("SHOW COLUMNS FROM `". $table ."` LIKE '". $column ."'");
		$results = $query->result();
		return (empty($results)) ? false : true;
	}

	public function get_related_data($slug,$params=array(),$order=null){
		$table='related_'.$slug;
		if(!$this->table_exists($table)) return array();
		if(!empty($params))
			$this->db->where($params);
		if(!is_null($order))
			$this->db->order_by($order);
		$query=$this->db->get($table);
		return ( count($query->result())==1 ) ? $query->row():$query->result();
	}

	/* Moved from MY_controller */
	public function get_gallery($gal_id, $lang, $domain = false)
	{
        $builder    = $this->db->table('dynamic_images');
		$builder->where('dynamic_gallery_id', $gal_id);
		$builder->where('language', $lang);
		if ($domain) {
			$builder->where('domain', $domain);
		}
		$builder->orderBy('position');
		$query = $builder->get();
		$result = $query->getResult();

		return $result;
	}

	public function prepare_related_data($page)
	{
		foreach ($page as $field => $value) {
			if (strpos($field, 'multiple_') === FALSE
				&& strpos($field, 'dynamic_') === FALSE) {
				continue;
			}
			$related_table = str_replace('multiple_', 'dynamic_', $field);
            $multiples = explode('_', $related_table);

            if (count($multiples) > 2) {
                $related_table = $multiples[0] . '_' . $multiples[1];
            }
			if (!self::table_exists($related_table)) {
				continue;
			}
			if (!empty($value)) {
				$page->{$field} = (new BaseBlock(''))->prepare_dynamic_objects($value, $related_table);
			}
		}
	}

    /**
     * POSSIBLY WE NEED TO CACHE THIS FUNCTION!
     *
     * @param $slug
     * @param $object
     * @param bool $url_return
     * @return bool|string
     */
    public function get_dynamic_parent_url($slug, $object, $site_lang, $site_domain, $url_return = false)
    {
        $sql = 'SELECT id, container_of, representative_of, uri_string, path FROM dynamic_pages 
                    WHERE container_of = ? AND language = ? AND domain = ?';
        $query = $this->db->query($sql, ['dynamic_'. $slug, $site_lang, $site_domain]);
        $result = $query->getResult();
        foreach ($result as $row) {
            /**
             * Page is representative, it means that its url is not correct and we need to find url from dynamic content
             */
            if ($row->representative_of) {
                /**
                 * If objects was preloaded with @prepare_related_data we just take one
                 */
                if (is_array($object->{$row->representative_of}) && count($object->{$row->representative_of}) == 1) {
                    $object = current($object->{$row->representative_of});
                } else {
                    $object = current($this->get_dynamic_table_data([
                        'slug' => str_replace('dynamic_', '', $row->representative_of),
                        'id' => $object->{$row->representative_of},
                        'language' => $site_lang,
                        'domain' => $site_domain,
                    ]));
                }

                $url_return_2 = $this->get_dynamic_parent_url(str_replace('dynamic_', '', $row->representative_of), $object, $site_lang, $object->slug);

                return implode('/', [$url_return_2, $url_return]);
            } else {
                return implode('/', [$row->uri_string, $url_return]);
            }
        }
        return $url_return;
    }

    /**
     *
     * @param $slug
     * @param $object
     * @param $lang
     * @param bool $url_return
     * @return bool|string
     */
    public function get_dynamic_parent_url_v2($slug, $object, $lang, $site_domain, $url_return = false)
    {
        $sql = 'SELECT id, container_of, representative_of, uri_string, path FROM dynamic_pages 
                    WHERE container_of = ? AND language = ? AND domain = ?';
        $query = $this->db->query($sql, ['dynamic_'. $slug, $lang, $site_domain]);
        $result = $query->getResult();
        foreach ($result as $row) {
            /**
             * Page is representative, it means that its url is not correct and we need to find url from dynamic content
             */
            if ($row->representative_of) {
                /**
                 * If objects was preloaded with @prepare_related_data we just take one
                 */
                if (isset($object->{$row->representative_of}) && is_array($object->{$row->representative_of}) && count($object->{$row->representative_of}) == 1) {
                    $object = current($object->{$row->representative_of});
                } else {
                    $object = current($this->get_dynamic_table_data([
                        'slug' => str_replace('dynamic_', '', $row->representative_of),
                        'id' => isset($object->{$row->representative_of})?$object->{$row->representative_of}:'',
                        'language' => $lang,
                        'domain' => $site_domain,
                    ]));
                }

                $url_return_2 = $this->get_dynamic_parent_url_v2(str_replace('dynamic_', '', $row->representative_of), $object, $lang, $site_domain, $object->slug);

                return implode('/', [$url_return_2, $url_return]);
            } else {
                return implode('/', [$row->uri_string, $url_return]);
            }
        }
        return $url_return;
    }

    /**
     * POSSIBLY WE NEED TO CACHE THIS FUNCTION!
     *
     * @param $slug
     * @param $object
     * @param bool $url_return
     * @return bool|string
     */
    public function get_dynamic_container_url($slug, $object,$site_lang, $site_domain, $url_return = false)
    {
        $sql = 'SELECT id, container_of, representative_of, uri_string, path FROM dynamic_pages 
                    WHERE representative_of = ? AND language = ? AND domain = ?';
        $query = $this->db->query($sql, ['dynamic_'. $slug, $site_lang, $site_domain]);
        $result = $query->getResult();

        $object1 = current($result);

        return $this->get_dynamic_parent_url(str_replace('dynamic_', '', $object1->representative_of), $object,$site_lang, $site_domain);

    }

    /**
     * POSSIBLY WE NEED TO CACHE THIS FUNCTION!
     *
     * @param $slug
     * @param $object
     * @param $lang
     * @param bool $url_return
     * @return bool|string
     */
    public function get_dynamic_container_url_v2($slug, $object, $lang, $site_domain, $url_return = false)
    {
        $sql = 'SELECT id, container_of, representative_of, uri_string, path FROM dynamic_pages 
                    WHERE representative_of = ? AND language = ? AND domain = ?';
        $query = $this->db->query($sql, ['dynamic_'. $slug, $lang, $site_domain]);
        $result = $query->getResult();

        $object1 = current($result);

        return $this->get_dynamic_parent_url_v2(str_replace('dynamic_', '', $object1->representative_of), $object, $lang, $site_domain);

    }

	/**
	 * Check if uri_string is a container
	 * @param $uri_string
	 * @param $lang
	 * @param bool $recursive
	 * @return bool|array
	 */
	public function checkContainer($uri_string, $lang, $recursive = false, $site_domain = null)
	{
		$status = (\Config\Services::session()->get('userdata')) ? '%' : 'ACTIVED';
		$segments = explode('/', $uri_string);
		$segments_count = count($segments);

		$last_segment = array_pop($segments);
		$uri_string_wo_last = implode('/', $segments);
		$before_last_segment = array_pop($segments);

		$cnt_to_test = $segments_count - 2;
		$sql = 'SELECT id, container_of, representative_of, uri_string, path,
                        ROUND ((CHAR_LENGTH(uri_string) - CHAR_LENGTH( REPLACE ( uri_string, "/", "") ) ) / CHAR_LENGTH("/")) AS cnt 
                FROM dynamic_pages 
                WHERE container_of != "" AND language = ? AND domain = ? AND status like "'. $status .'"
                HAVING cnt = '. $cnt_to_test .' order by id';
		$query = $this->db->query($sql, [$lang, $site_domain]);
		$result = $query->getResult();
		$return = [];
		$flag = false;
		$stop_recursive = false;
		foreach ($result as $row) {
			/* If founded page is not a representative and has different uri_string, we should move on */
			if (!$row->representative_of && $uri_string_wo_last != $row->uri_string) {
				continue;
			}
			/* If founded page is not a representative and have same uri_string, we found parent simple page(s) and should stop */
			if (!$row->representative_of) {
				$stop_recursive = true;
			}

			$children = $this->findChild($row->container_of, $last_segment, $lang, $site_domain);
			/* Not unique slug for dynamic */
			foreach ($children as $child) {

				if ($row->representative_of) {
					$parents = $this->findChild($row->representative_of, $before_last_segment, $lang, $site_domain, $child->{$row->representative_of});
				} else {
					$parents[] = [];
				}

				if (count($parents) == 1) {
					if (!$recursive) {
						$return = [
							'related_table' => $row->container_of,
							'object' => $child,
							'path' => $row->path,
						];
					}

					$flag = true;
					break;
				}
			}

		}

		if ($flag == false) {
			return false;
		}

		if (count($segments) > 0 && !$stop_recursive) {
			$flag = $this->checkContainer(implode('/', $segments) .'/'. $before_last_segment, $lang,  true, $site_domain);
		}

		if (!$recursive) {
			return $return;
		} else {
			return $flag;
		}
	}

	public function findChild($table, $slug, $lang, $site_domain, $id = false)
	{
        $builder    = $this->db->table($table);
		if ($id) {
			$builder->where('id', $id);
		}
		$builder->where([
			'slug' => urldecode($slug),
			'language' => $lang,
			'domain' => $site_domain,
		]);
        $builder->like('status', (\Config\Services::session()->get('userdata')) ? '%' : 'ACTIVED');
        $query = $builder->get();

		return $query->getResult();
	}

	/**
	 * @param $uri_string
	 * @param $collected
	 * @param bool $recursive
	 * @return bool|array
	 */
	public function buildBreadcrumb($uri_string, $collected, $recursive = false)
	{
		$segments = explode('/', $uri_string);
		$segments_count = count($segments);

		$last_segment = array_pop($segments);
		$uri_string_wo_last = implode('/', $segments);
		$before_last_segment = array_pop($segments);
		$status = ($this->session->userdata('logged_in')) ? '%' : 'ACTIVED';
		$sql = "SELECT a.id, a.container_of, a.uri_string, a.text_title_menu FROM dynamic_pages a
                WHERE   a.uri_string = ?
                    AND a.language = ?
                    AND a.domain = ?
                    AND a.status like '". $status ."'
                LIMIT 1
        ";

		$query = $this->db->query($sql, [urldecode($uri_string), $this->site_lang, $this->site_domain]);
		if ($page = $query->first_row()) {
			$new = $this->user_general_model->get_dynamic_table_data([
				'slug' => 'pages',
				'language' => $this->site_lang,
				'id' => $page->id,
				'domain' => $this->site_domain,
			]);
			if (isset($new[0])) {
				$new = $new[0];
			}
			$collected[] = [
				'url' => base_url($this->slug_lang .'/'. $new->uri_string),
				'title' => $new->text_title_menu,
			];
		} else {
			if (($dynamic_page_data = ($this->user_general_model->checkContainer($uri_string, $this->site_lang))) !== false) {
				$new = $this->user_general_model->get_dynamic_table_data([
					'slug' => str_replace('dynamic_', '', $dynamic_page_data['related_table']),
					'language' => $this->site_lang,
					'id' => $dynamic_page_data['object']->id,
					'domain' => $this->site_domain,
				]);
				if (isset($new[0])) {
					$new = $new[0];
				}

				$new_url = $this->user_general_model->get_dynamic_parent_url(str_replace('dynamic_', '', $dynamic_page_data['related_table']), $new);
				$new_url .= $new->slug;
				$collected[] = [
					'url' => base_url($this->slug_lang .'/'. $new_url),
					'title' => $new->text_title_web,
				];
			}
		}

		if ($segments_count > 1) {
			$collected = $this->buildBreadcrumb($uri_string_wo_last, $collected);
		}

		return $collected;
	}

	/**
	 * @param $token
	 * @param $secret_apikey
	 * @param float $score
	 * @param int $version
	 * @return bool
	 */
	public function validCaptcha($token, $secret_apikey, $score = 0.8, $version = 3)
	{
		$url = 'https://www.google.com/recaptcha/api/siteverify';
		$apicall = array(
			'secret' => $secret_apikey,
			'response' => $token
		);

		$recaptcha = file_get_contents($url . '?secret=' . $apicall['secret'] . '&response=' . $apicall['response']);
		$recaptcha = json_decode($recaptcha);

		switch ($version) {
			case 2 :
				if (isset($recaptcha->success) && $recaptcha->success == 1) {
					return true;
				}
				break;
			case 3 :
				if (isset($recaptcha->score) && $recaptcha->score > $score) {
					return true;
				}
				break;
		}

		return false;
	}

	/**
	 * Save submitted form to database (contact, newsletter etc)
	 * @param $table
	 * @param $data
	 * @return bool
	 */
	public function save_form_to_db($table, $data)
	{
		$data = array_merge($data, [
            'language' => \Config\Services::session()->get('site_lang'),
			'text_user_language' => \Config\Services::session()->get('site_lang'),
			'domain' => \Config\Services::session()->get('site_domain'),
			'updated_at' => date('Y-m-d H:i:s', time()),
		]);

		$this->db->transStart();
        $this->db->table($table)->insert($data);
		$this->db->transComplete();

		if ($this->db->transStatus() === FALSE) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * @param $email
	 * @param $apikey
	 * @param $listid
	 * @return bool
	 */
	public function save_api_mailchimp_user($email, $apikey, $listid)
	{
		//Create mailchimp API url
		$memberId = md5(strtolower($email));
		$dataCenter = substr($apikey,strpos($apikey,'-') + 1);
		$url = 'https://'. $dataCenter .'.api.mailchimp.com/3.0/lists/'. $listid .'/members/'. $memberId;

		$data = array(
			'email_address' => $email,
			'status' => 'subscribed',
		);
		$jsonString = json_encode($data);

		// send a HTTP POST request with curl
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_USERPWD, 'user:' . $apikey);
		curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonString);
		$result = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		if ($httpCode == '200' || $httpCode == '214') {
			return true;
		} else {
			$msg = 'UserGeneralModel_save_api_mailchimp_user - [msg_code='.$httpCode.']';
			log_message('error', $msg );
			return false;
		}
	}

	/**
	 * @param $template
	 * @param $data
	 * @param $subject
	 * @param $to
	 * @return mixed
	 */
    public function send_admin_email_notification($template, $data, $subject, $to, $from, $fromName)
    {
        $email = \Config\Services::email();

        $email->clear();
        $email->initialize(['mailType' => 'html', 'charset' => 'UTF-8', 'CRLF' => "\r\n", 'protocol' => 'mail']);
        $email->setNewline("\r\n");
        $email->setFrom($from, $fromName);
        $email->setTo($to);
        $email->setSubject($subject);
        $email->setMessage(view('email/'. $template, $data));


        return $email->send();
    }

	/**
	 * @param $template
	 * @param $data
	 * @param $subject
	 * @param $to
	 * @return mixed
	 */
	public function send_user_email_notification($template, $data, $subject, $to)
	{
		$this->load->library('email');
		$this->email->clear();
		$this->email->initialize(['mailtype' => 'html', 'charset' => 'UTF-8', 'crlf' => "\r\n", 'protocol' => 'mail']);
		$this->email->set_newline("\r\n");
		$this->email->from($this->site_preferences->mail_from_mail, $this->site_preferences->mail_from_name);
		$this->email->to($to);
		$this->email->subject($subject);
		$this->email->message($this->load->view('email/'. $template, $data, true));

		return $this->email->send();
	}

	public static function replace_extension($filename, $new_extension)
	{
		$info = pathinfo($filename);
		return ($info['dirname'] ? $info['dirname'] . DIRECTORY_SEPARATOR : '')
			. $info['filename']
			. '.'
			. $new_extension;
	}

    public function get_domain_preferences($domain_menu)
    {
        $builder    = $this->db->table('preferences');
        $query  = $builder->where('domain', $domain_menu)->get();
        return $query->getFirstRow();
    }
    public function get_home($segment, $home_page_id, $status, $uri_string, $site_lang, $site_domain ){

        /* If we have no url-slug and we have configured home page */
        if ($segment == '') {
            $selected_home_condition_exception = '';
            $selected_home_condition = ($home_page_id) ? "OR a.id = " . $home_page_id : '';
        } else {
            $selected_home_condition_exception = ($home_page_id) ? "AND a.id != " . $home_page_id : '';
            $selected_home_condition = '';
        }

        $sql = "SELECT a.id, a.container_of, a.uri_string FROM dynamic_pages a
                WHERE (a.uri_string = ? ". $selected_home_condition ." /*" /*.$container_condition*/ ."*/)
                    AND a.language = ?
                    AND a.domain = ?
                    AND a.status like '". $status ."'
                    ". $selected_home_condition_exception ."
                LIMIT 1
        ";
        return $this->db->query($sql, [$uri_string, $site_lang, $site_domain])->getFirstRow();
    }
	public function get_previous ( $status, $uri_string, $site_lang, $site_domain ){
		

        $sql = "SELECT a.id, a.container_of, a.uri_string FROM dynamic_pages a
                WHERE (a.uri_string = ? /*" /*.$container_condition*/ ."*/)
                    AND a.language = ?
                    AND a.domain = ?
                    AND a.status like '". $status ."'
                LIMIT 1
        ";
        /* Looking for Id for previous page */
        return $this->db->query($sql, [$uri_string, $site_lang, $site_domain])->getFirstRow();
	}
}
