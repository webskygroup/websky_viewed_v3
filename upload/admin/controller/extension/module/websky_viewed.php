<?php
class ControllerExtensionModulewebskyViewed extends Controller
{
	private $error = array();

	public function index()
	{
		$this->load->language('extension/module/websky_viewed');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/module');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if (!isset($this->request->get['module_id'])) {
				$this->model_setting_module->addModule('websky_viewed', $this->request->post);
			} else {
				$this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = '';
		}

		if (isset($this->error['width'])) {
			$data['error_width'] = $this->error['width'];
		} else {
			$data['error_width'] = '';
		}

		if (isset($this->error['height'])) {
			$data['error_height'] = $this->error['height'];
		} else {
			$data['error_height'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		if (!isset($this->request->get['module_id'])) {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/websky_viewed', 'user_token=' . $this->session->data['user_token'], true)
			);
		} else {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/websky_viewed', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true)
			);
		}

		if (!isset($this->request->get['module_id'])) {
			$data['action'] = $this->url->link('extension/module/websky_viewed', 'user_token=' . $this->session->data['user_token'], true);
		} else {
			$data['action'] = $this->url->link('extension/module/websky_viewed', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
		}

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
		}

		if (isset($module_info['name'])) {
			$data['name'] = $module_info['name'];
		} else {
			$data['name'] = '';
		}


		if (isset($module_info['heading'])) {
			$data['heading'] = $module_info['heading'];
		} else {
			$data['heading'] = array(
				'1' => 'your favorite product sort by [categoryName]',
				'2' => 'محصولات [categoryName] بر اساس سلیقه شما'
			);
		}

		if (isset($module_info['limit'])) {
			$data['limit'] = $module_info['limit'];
		} else {
			$data['limit'] = '';
		}

		if (isset($module_info['status'])) {
			$data['status'] = $module_info['status'];
		} else {
			$data['status'] = '';
		}

		if (isset($module_info['width'])) {
			$data['width'] = $module_info['width'];
		} else {
			$data['width'] = '';
		}

		if (isset($module_info['height'])) {
			$data['height'] = $module_info['height'];
		} else {
			$data['height'] = '';
		}

		if (isset($module_info['category_amount'])) {
			$data['category_amount'] = $module_info['category_amount'];
		} else {
			$data['category_amount'] = '';
		}

		if (isset($module_info['display_type'])) {
			$data['display_type'] = $module_info['display_type'];
		} else {
			$data['display_type'] = '';
		}

		if (isset($module_info['order'])) {
			$data['order'] = $module_info['order'];
		} else {
			$data['order'] = '';
		}

		if (isset($module_info['sort'])) {
			$data['sort'] = $module_info['sort'];
		} else {
			$data['sort'] = '';
		}

		$data['user_token'] = $this->session->data['user_token'];
		$data['current_version'] = "1.1.3";
		$data['upgrade'] = false;

		$url = 'https://opencart-ir.com/version/index.php?route=extension/websky_lastversion/module/websky_lastversion';
		$feilds = array(
			'extension_name' => 'websky_viewed_v3'
		);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $feilds);
		// Execute post
		$json = curl_exec($ch);
		//   print_r($json);
		if ($json === FALSE) {
			die('Curl failed: ' . curl_error($ch));
		}
		// Close connection
		curl_close($ch);
		$response_info = json_decode($json, true);
		if ($response_info) {
			$data['latest_version'] = $response_info['version_ext'];
			$data['date_added'] = ($this->language->get('code') == 'fa') ? jdate($this->language->get('datetime_format'), strtotime($response_info["date_added"])) : $response_info["date_added"];
			if (!version_compare($data['current_version'], $response_info['version_ext'], '>=')) {
				$data['upgrade'] = true;
			}
		} else {
			$data['latest_version'] = '';
			$data['date_added'] = '';
			$data['log'] = '';
		}

		$this->load->model('localisation/language');
		$data['languages'] = $this->model_localisation_language->getLanguages();
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/websky_viewed', $data));
	}

	protected function validate()
	{
		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}

    if (!$this->request->post['width']) {
        $this->error['width'] = $this->language->get('error_width');
    }

    if (!$this->request->post['height']) {
        $this->error['height'] = $this->language->get('error_height');
    }

		return !$this->error;
	}

	public function install()
	{

		$this->db->query("
 CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "websky_viewed` (
   `websky_viewed_id` int(11) NOT NULL AUTO_INCREMENT,
   `product_id` int(11) NOT NULL ,
   `customer_id` int(11) NOT NULL ,
   `manufacturer_id` int(11) NOT NULL ,
   `category_id` int(11) NOT NULL ,

   PRIMARY KEY (`websky_viewed_id`)
 ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin ;
");

		$this->load->model('setting/event');
		$this->model_setting_event->deleteEventByCode('websky_viewed');
		$this->model_setting_event->addEvent('websky_viewed', 'catalog/controller/product/product/before', 'extension/event/viewedproduct/visitedProduct');
	}

	public function download(): void
	{
		$this->load->language('marketplace/marketplace');

		$json = [];

		if (isset($this->request->get['extension_name'])) {
			$extension_name = $this->request->get['extension_name'];
		} else {
			$json['error'] = 'extension name null';
		}
		if (!$this->user->hasPermission('modify', 'marketplace/install')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$json) {
			$handle = fopen(DIR_UPLOAD . '' . $extension_name . '.ocmod.zip', 'w');

			//move_uploaded_file($this->request->files['file']['tmp_name'], $file);
			$download = $this->get_data('https://opencart-ir.com/dl/v3/' . $extension_name . '.ocmod.zip');

			fwrite($handle, $download);

			fclose($handle);


			$json = [];

			if (!$this->user->hasPermission('modify', 'marketplace/installer')) {
				$json['error'] = $this->language->get('error_permission');
			}

			$this->load->model('setting/extension');

			$file = DIR_UPLOAD . '' . $extension_name . '.ocmod.zip';


			if (!is_file($file)) {
				$json['error'] = sprintf($this->language->get('error_file'), $extension_name . '.ocmod.zip');
			}
			if (!$json) {
				$this->session->data['install'] = token(10);

				$zip = new ZipArchive();

				if ($zip->open($file)) {
					$zip->extractTo(DIR_UPLOAD . 'tmp-' . $this->session->data['install']);
					$zip->close();
				} else {
					$json['error'] = $this->language->get('error_unzip');
				}

				// Remove Zip
				unlink($file);

				$json['text'] = $this->language->get('text_unzip');

				$json['next'] = str_replace('&amp;', '&', $this->url->link('extension/module/websky_viewed/move', 'user_token=' . $this->session->data['user_token'], true));
			}


		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function xml()
	{
		$this->load->language('marketplace/install');

		$json = array();


		if (!$this->user->hasPermission('modify', 'marketplace/install')) {
			$json['error'] = $this->language->get('error_permission');
		}



		if (!isset($this->session->data['install'])) {
			$json['error'] = $this->language->get('error_directory');
		} elseif (!is_dir(DIR_UPLOAD . 'tmp-' . $this->session->data['install'] . '/')) {
			$json['error'] = $this->language->get('error_directory');
		}

		if (!$json) {
			$file = DIR_UPLOAD . 'tmp-' . $this->session->data['install'] . '/install.xml';

			if (is_file($file)) {
				$this->load->model('setting/modification');

				// If xml file just put it straight into the DB
				$xml = file_get_contents($file);

				if ($xml) {
					try {
						$dom = new DOMDocument('1.0', 'UTF-8');
						$dom->loadXml($xml);

						$name = $dom->getElementsByTagName('name')->item(0);

						if ($name) {
							$name = $name->nodeValue;
						} else {
							$name = '';
						}

						$code = $dom->getElementsByTagName('code')->item(0);

						if ($code) {
							$code = $code->nodeValue;

							// Check to see if the modification is already installed or not.
							$modification_info = $this->model_setting_modification->getModificationByCode($code);

							if ($modification_info) {
								$this->model_setting_modification->deleteModification($modification_info['modification_id']);
							}
						} else {
							$json['error'] = $this->language->get('error_code');
						}

						$author = $dom->getElementsByTagName('author')->item(0);

						if ($author) {
							$author = $author->nodeValue;
						} else {
							$author = '';
						}

						$version = $dom->getElementsByTagName('version')->item(0);

						if ($version) {
							$version = $version->nodeValue;
						} else {
							$version = '';
						}

						$link = $dom->getElementsByTagName('link')->item(0);

						if ($link) {
							$link = $link->nodeValue;
						} else {
							$link = '';
						}

						if (!$json) {


							$modification_data = array(
								'extension_install_id' => 0,
								'name' => $name,
								'code' => $code,
								'author' => $author,
								'version' => $version,
								'link' => $link,
								'xml' => $xml,
								'status' => 1
							);

							$this->model_setting_modification->addModification($modification_data);
						}
					} catch (Exception $exception) {
						$json['error'] = sprintf($this->language->get('error_exception'), $exception->getCode(), $exception->getMessage(), $exception->getFile(), $exception->getLine());
					}
				}
			}
		}

		if (!$json) {
			$json['text'] = $this->language->get('text_remove');

			$json['next'] = str_replace('&amp;', '&', $this->url->link('extension/module/websky_viewed/remove', 'user_token=' . $this->session->data['user_token'], true));
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	private function get_data($url)
	{
		$ch = curl_init();
		$timeout = 15;
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}

	public function move()
	{
		$this->load->language('marketplace/install');

		$json = array();


		if (!$this->user->hasPermission('modify', 'marketplace/install')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!isset($this->session->data['install'])) {
			$json['error'] = $this->language->get('error_directory');
		} elseif (!is_dir(DIR_UPLOAD . 'tmp-' . $this->session->data['install'] . '/')) {
			$json['error'] = $this->language->get('error_directory');
		}

		if (!$json) {
			$directory = DIR_UPLOAD . 'tmp-' . $this->session->data['install'] . '/';

			if (is_dir($directory . 'upload/')) {
				$files = array();

				// Get a list of files ready to upload
				$path = array($directory . 'upload/*');

				while (count($path) != 0) {
					$next = array_shift($path);

					foreach ((array) glob($next) as $file) {
						if (is_dir($file)) {
							$path[] = $file . '/*';
						}

						$files[] = $file;
					}
				}

				// A list of allowed directories to be written to
				$allowed = array(
					'admin/controller/extension/',
					'admin/language/',
					'admin/model/extension/',
					'admin/view/image/',
					'admin/view/javascript/',
					'admin/view/stylesheet/',
					'admin/view/template/extension/',
					'catalog/controller/extension/',
					'catalog/language/',
					'catalog/model/extension/',
					'catalog/view/javascript/',
					'catalog/view/theme/',
					'system/config/',
					'system/library/',
					'image/catalog/'
				);

				// First we need to do some checks
				foreach ($files as $file) {
					$destination = str_replace('\\', '/', substr($file, strlen($directory . 'upload/')));

					$safe = false;

					foreach ($allowed as $value) {
						if (strlen($destination) < strlen($value) && substr($value, 0, strlen($destination)) == $destination) {
							$safe = true;

							break;
						}

						if (strlen($destination) > strlen($value) && substr($destination, 0, strlen($value)) == $value) {
							$safe = true;

							break;
						}
					}

					if ($safe) {
						// Check if the copy location exists or not
						if (substr($destination, 0, 5) == 'admin') {
							$destination = DIR_APPLICATION . substr($destination, 6);
						}

						if (substr($destination, 0, 7) == 'catalog') {
							$destination = DIR_CATALOG . substr($destination, 8);
						}

						if (substr($destination, 0, 5) == 'image') {
							$destination = DIR_IMAGE . substr($destination, 6);
						}

						if (substr($destination, 0, 6) == 'system') {
							$destination = DIR_SYSTEM . substr($destination, 7);
						}
					} else {
						$json['error'] = sprintf($this->language->get('error_allowed'), $destination);

						break;
					}
				}

				if (!$json) {
					$this->load->model('setting/extension');

					foreach ($files as $file) {
						$destination = str_replace('\\', '/', substr($file, strlen($directory . 'upload/')));

						$path = '';

						if (substr($destination, 0, 5) == 'admin') {
							$path = DIR_APPLICATION . substr($destination, 6);
						}

						if (substr($destination, 0, 7) == 'catalog') {
							$path = DIR_CATALOG . substr($destination, 8);
						}

						if (substr($destination, 0, 5) == 'image') {
							$path = DIR_IMAGE . substr($destination, 6);
						}

						if (substr($destination, 0, 6) == 'system') {
							$path = DIR_SYSTEM . substr($destination, 7);
						}

						if (is_dir($file) && !is_dir($path)) {
							if (mkdir($path, 0777)) {
								//$this->model_setting_extension->addExtensionPath($extension_install_id, $destination);
							}
						}

						if (is_file($file)) {
							if (rename($file, $path)) {
								//$this->model_setting_extension->addExtensionPath($extension_install_id, $destination);
							}
						}
					}
				}
			}
		}

		if (!$json) {
			$json['text'] = $this->language->get('text_xml');

			$json['next'] = str_replace('&amp;', '&', $this->url->link('extension/module/websky_viewed/xml', 'user_token=' . $this->session->data['user_token'], true));
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	public function remove()
	{
		$this->load->language('marketplace/install');

		$json = array();

		if (!$this->user->hasPermission('modify', 'marketplace/install')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!isset($this->session->data['install'])) {
			$json['error'] = $this->language->get('error_directory');
		}

		if (!$json) {
			$directory = DIR_UPLOAD . 'tmp-' . $this->session->data['install'] . '/';

			if (is_dir($directory)) {
				// Get a list of files ready to upload
				$files = array();

				$path = array($directory);

				while (count($path) != 0) {
					$next = array_shift($path);

					// We have to use scandir function because glob will not pick up dot files.
					foreach (array_diff(scandir($next), array('.', '..')) as $file) {
						$file = $next . '/' . $file;

						if (is_dir($file)) {
							$path[] = $file;
						}

						$files[] = $file;
					}
				}

				rsort($files);

				foreach ($files as $file) {
					if (is_file($file)) {
						unlink($file);
					} elseif (is_dir($file)) {
						rmdir($file);
					}
				}

				if (is_dir($directory)) {
					rmdir($directory);
				}
			}

			$file = DIR_UPLOAD . $this->session->data['install'] . '.tmp';

			if (is_file($file)) {
				unlink($file);
			}

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}