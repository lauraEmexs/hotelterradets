<?php

/**
 * Class User_social_model
 * Actualized 28.01.2020
 *  - Curl or file_get_contents
 *  - File cache or url cache
 * Actualized 18.09.2020
 *  - Emexs instagram API
 */

namespace App\Models;

use CodeIgniter\Model;

class User_social_model extends Model {
	/* Now we cache image URL, not a file */
	const USE_FILE_CACHE = false;

	/**
	 * @param string $source Instagram username
	 * @return mixed
	 */
	public function get_instagram($source = '')
	{
		if ($source) {
			$user = $source;
		} else {
			$user = $this->site_preferences->instagram_user;
		}
		
        $builder = $this->db->table('instagram');
        $builder->where('user', $user);
		$builder->where('date_updated', date("Y-m-d" ));
		$builder->orderBy('date_publish', 'desc');
		$builder->orderBy('id', 'desc');
		$builder->limit('10');
		$query = $builder->get();
		$results = $query->getResult();
		
		
		if (empty($results)) {
			$this->update_instagram_emexs($user);
		}

		//sacamos ultimos 10 lineas con distintos fotos

        $builder = $this->db->table('instagram');
		$builder->select('foto, id, max(date_updated) date_updated, max(date_publish) date_publish, post_url, foto_webp');
        $builder->where('user', $user);
		$builder->orderBy('date_updated', 'desc');
		$builder->orderBy('date_publish', 'desc');
		$builder->orderBy('id', 'desc');
		$builder->groupBy('foto');
		$builder->limit('10');
		$query = $builder->get();
		print_r($query->getResult()); die ();
		return $query->getResult();
	}

	public function update_instagram_emexs($feed)
	{
		$data_url = 'https://instagram.emexsdigital.com/site/posts?feed='. $feed;

		if (function_exists('curl_version')) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_COOKIESESSION, true);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_MAXREDIRS, 4);
			curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
			curl_setopt($ch, CURLOPT_URL, $data_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_exec($ch);
			$json = curl_exec($ch);
			curl_close($ch);
		} elseif (ini_get('allow_url_fopen')) {
			$json = file_get_contents($data_url);
		} else {
			return false;
		}

		$raw_data = json_decode($json, true);
		if (is_array($raw_data)) {
			$raw_data = array_reverse($raw_data);
		} else {
			return false;
		}
		$cnt = 0;
		foreach ($raw_data as $key => $post) {
			if ($cnt == 10) {
				break;
			}

			$data = array(
				'user'         => $feed,
				'foto'         => $post['image'],
				'foto_webp'    => $post['image_webp'],
				'date_publish' => date('Y-m-d', $post['original_created_at']),
				'date_updated' => date('Y-m-d'),
				'post_url'     => $post['external_url'],
			);
			$builder = $this->db->table('instagram');
			$builder->insert($data);
			$cnt++;
		}
	}

	//https://gist.github.com/cosmocatalano/4544576
	//
	private function update_instagram_new($user)
	{
		if (function_exists('curl_version')) {
			$full_array = $this->scrape_insta_curl($user);
		} else {
			$full_array = $this->scrape_insta_fgt($user);
		}

		$posts = @$full_array['entry_data']['ProfilePage'][0]['graphql']['user']['edge_owner_to_timeline_media']['edges'];
		if (!is_array($posts)) {
			return false;
		}

		foreach ($posts as $key => $post) {
			$post = $post['node'];

			if ($key == 10) {
				break;
			}

			if (self::USE_FILE_CACHE) {
				$save_dir = THEME_PATH . 'img/instagram/';
				if (!is_dir(FCPATH . $save_dir)) {
					mkdir(FCPATH . $save_dir, 0777, true);
				}

				#Descargando img => /assets/img/instagram/
				$md5 = md5($post['display_url']);
				$file_img = FCPATH . $save_dir . $md5 . '.jpg';

				if (!file_exists($file_img)) {
					$fw = fopen($file_img, 'w');
					if (($file_content = file_get_contents($post['display_url'])) === false) {
						continue;
					}
					fwrite($fw, $file_content);
					fclose($fw);
				}
				$photo = $save_dir . $md5 . '.jpg';
			} else {
				$photo = 'https://www.instagram.com/p/' . $post['shortcode'] . '/media?size=l';
			}

			$data = array(
				'user'         => $user,
				'foto'         => $photo,
				'date_publish' => date( "Y-m-d", $post['taken_at_timestamp'] ),
				'date_updated' => date( "Y-m-d" ),
				'post_url'     => 'https://www.instagram.com/p/' . $post['shortcode'],
			);
			$builder = $this->db->table('instagram');
			$builder->insert($data);
		}

		return false;
	}

	//funciona 2018/03/27
	public function scrape_insta_curl($username)
	{
		$site_url = 'https://instagram.com/'.$username;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_COOKIESESSION, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 4);
		curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_URL, $site_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_exec($ch);
		$insta_source = curl_exec($ch);
		curl_close($ch);

		if (!$insta_source) {
			return [];
		}
		$shards = explode('window._sharedData = ', $insta_source);
		$insta_json = explode(';</script>', $shards[1]);
		$insta_array = json_decode($insta_json[0], TRUE);

		return $insta_array;
	}

	//funciona 2018/03/27
	private function scrape_insta_fgt($username)
	{
		$insta_source = @file_get_contents('https://instagram.com/'. $username);
		if (!$insta_source) {
			return [];
		}
		$shards = explode('window._sharedData = ', $insta_source);
		$insta_json = explode(';</script>', $shards[1]);
		$insta_array = json_decode($insta_json[0], TRUE);

		return $insta_array;
	}
}
