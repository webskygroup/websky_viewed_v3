<?php
class ControllerExtensionModulewebskyViewed extends Controller
{
	public function index(array $setting): string
	{
		if ($setting['status'] && $this->customer->isLogged()) {
			$this->load->model('extension/module/websky_viewed');
			$this->document->addStyle('catalog/view/theme/default/stylesheet/swiper-bundle.min.css');
			$this->document->addStyle('catalog/view/theme/default/stylesheet/websky_viewed.css');
			$this->document->addScript('catalog/view/theme/default/javascript/swiper-bundle.min.js');

			$this->load->model('tool/image');
			$this->load->language('extension/module/websky_viewed');

			$data = array();

			$data['heading'] = $setting['heading'][$this->config->get('config_language_id')];
			$this->load->model('extension/module/websky_viewed');
			$this->load->model('catalog/product');
			$data['products'] = [];
			$display_type = $setting['display_type'];
			$products = [];
			if ($display_type == 'all_product') {
				$filter_data = [
					'start' => 0,
					'limit' => $setting['limit'],
					'sort' => $setting['sort'],
					'order' => $setting['order']
				];

				$products_visited = $this->model_extension_module_websky_viewed->getProductsVisited($filter_data);

				if ($products_visited) {
					$data['isset_special'] = 0;
					$product_data = array();
					foreach ($products_visited as $product) {
						$result = $this->model_catalog_product->getProduct($product['product_id']);

						if ($result['image']) {
							$image = $this->model_tool_image->resize(html_entity_decode($result['image'], ENT_QUOTES, 'UTF-8'), $setting['width'], $setting['height']);
						} else {
							$image = $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']);
						}

						if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
							$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
						} else {
							$price = false;
						}
						if (preg_replace("/[^0-9]/", '', $price) == 0) {
							$price = 0;
						}

						if ((float) $result['special'] && $result['price'] != 0) {
							$data['isset_special'] = $data['isset_special'] + 1;
							$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
							$special_price = (float) $result['price'] - (float) $result['special'];
							$discount = round(($special_price / $result['price']) * 100);

						} else {
							$special = false;
							$discount = false;
						}

						$get_end = $this->model_extension_module_websky_viewed->getEnd($result['product_id']);

						if ($get_end) {
							$date_end = $get_end;
						} else {
							$date_end = false;
						}

						if ($this->config->get('config_tax')) {
							$tax = $this->currency->format((float) $result['special'] ? $result['special'] : $result['price'], $this->session->data['currency']);
						} else {
							$tax = false;
						}

						$product_data[] = [
							'product_id' => $result['product_id'],
							'thumb' => $image,
							'name' => $result['name'],
							'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
							'price' => $price,
							'special' => $special,
							'discount' => '%' . $discount,
							'quantity' => $result['quantity'],
							'tax' => $tax,
							'date_end' => $date_end,
							'minimum' => $result['minimum'] > 0 ? $result['minimum'] : 1,
							'href' => $this->url->link('product/product', 'product_id=' . $result['product_id'])
						];

					}

				}
				$heading = $setting['heading'][$this->config->get('config_language_id')];
				$title = str_replace("[manufacturerName]", '', $heading);
				$title = str_replace("[categoryName]", '', $title);
                $sub_title =$setting['sub_heading'][$this->config->get('config_language_id')];

				$data['modules'][] = [
					'product_data' => $product_data,
					'title' => $title,
					'sub_title' => $sub_title,
					'href' =>false,
				];

				
			} elseif ($display_type == 'by_category') {
				$this->load->model('catalog/category');

				$filter_data = [
					'limit' => $setting['category_amount']
				];
				$category_visited = $this->model_extension_module_websky_viewed->getCategoryVisited($filter_data);

				$design2_random_fill = !empty($setting['design2_random_fill']);

				if ((int) $setting['design'] === 2 && $design2_random_fill && count($category_visited) < 4) {
					$extra_limit = 4 - count($category_visited);

					if ($extra_limit > 0) {
						$category_ids = array_column($category_visited, 'category_id');
						$random_categories = $this->model_extension_module_websky_viewed->getRandomCategories($extra_limit, $category_ids);

						if ($random_categories) {
							$category_visited = array_merge($category_visited, $random_categories);
						}
					}
				}

				if ($category_visited) {

					foreach ($category_visited as $category) {

						$filter_data = [
							'filter_category_id' => $category['category_id'],
							'filter_sub_category' => false,
							'start' => 0,
							'limit' => $setting['limit'],
							'sort' => $setting['sort'],
							'order' => $setting['order']
						];

						$category_info = $this->model_catalog_category->getCategory($category['category_id']);
						$product_in_category = $this->model_catalog_product->getProducts($filter_data);

						// echo '<pre>';
						// print_r($filter_data);
						// echo '</pre>';

						if ($product_in_category) {
							$data['isset_special'] = 0;
							$product_data = array();
							foreach ($product_in_category as $product) {
								$result = $this->model_catalog_product->getProduct($product['product_id']);

								if ($result['image']) {
									$image = $this->model_tool_image->resize(html_entity_decode($result['image'], ENT_QUOTES, 'UTF-8'), $setting['width'], $setting['height']);
								} else {
									$image = $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']);
								}

								if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
									$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
								} else {
									$price = false;
								}
								if (preg_replace("/[^0-9]/", '', $price) == 0) {
									$price = 0;
								}

								if ((float) $result['special'] && $result['price'] != 0) {
									$data['isset_special'] = $data['isset_special'] + 1;
									$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
									$special_price = (float) $result['price'] - (float) $result['special'];
									$discount = round(($special_price / $result['price']) * 100);

								} else {
									$special = false;
									$discount = false;
								}

								$get_end = $this->model_extension_module_websky_viewed->getEnd($result['product_id']);

								if ($get_end) {
									$date_end = $get_end;
								} else {
									$date_end = false;
								}

								if ($this->config->get('config_tax')) {
									$tax = $this->currency->format((float) $result['special'] ? $result['special'] : $result['price'], $this->session->data['currency']);
								} else {
									$tax = false;
								}

								$product_data[] = [
									'product_id' => $result['product_id'],
									'thumb' => $image,
									'name' => $result['name'],
									'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
									'price' => $price,
									'special' => $special,
									'discount' => '%' . $discount,
									'quantity' => $result['quantity'],
									'tax' => $tax,
									'date_end' => $date_end,
									'minimum' => $result['minimum'] > 0 ? $result['minimum'] : 1,
									'href' => $this->url->link('product/product', 'language=' . $this->config->get('config_language') . '&product_id=' . $result['product_id'])
								];

							}
						}

						$heading = $setting['heading'][$this->config->get('config_language_id')];
						$title = str_replace("[categoryName]", $category_info['name'], $heading);
						$title = str_replace("[manufacturerName]", '', $title);
                       $sub_title =$setting['sub_heading'][$this->config->get('config_language_id')];

						$data['modules'][] = [
							'product_data' => $product_data,
							'title' => $title,
							'sub_title' => $sub_title,
							'href' => $this->url->link('product/category', '&path=' .  $category['category_id'])
						];

					}
				}

			} elseif ($display_type == 'by_manufacturer') {
				$this->load->model('catalog/manufacturer');

				$filter_data = [
					'limit' => $setting['category_amount']
				];
				$manufaturer_visited = $this->model_extension_module_websky_viewed->getManufacturerVisited($filter_data);

				// echo '<pre>';
				// print_r($manufaturer_visited);

				if ($manufaturer_visited) {
					$product_data = array();
					$data['isset_special'] = 0;

					foreach ($manufaturer_visited as $manufacturer) {
						$filter_data = [
							'filter_manufacturer_id' => $manufacturer['manufacturer_id'],
							'start' => 0,
							'limit' => $setting['limit'],
							'sort' => $setting['sort'],
							'order' => $setting['order']
						];

						$manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($manufacturer['manufacturer_id']);
						$product_in_manufacturer = $this->model_catalog_product->getProducts($filter_data);

						// echo '--------------------------------------------------------------------------------------------';
						// echo '<pre>';
						// print_r($product_in_manufacturer);
						// echo '</pre>';

						if ($product_in_manufacturer) {
							$data['isset_special'] = 0;
							$product_data = array();
							foreach ($product_in_manufacturer as $product) {
								$result = $this->model_catalog_product->getProduct($product['product_id']);

								if ($result['image']) {
									$image = $this->model_tool_image->resize(html_entity_decode($result['image'], ENT_QUOTES, 'UTF-8'), $setting['width'], $setting['height']);
								} else {
									$image = $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']);
								}

								if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
									$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
								} else {
									$price = false;
								}
								if (preg_replace("/[^0-9]/", '', $price) == 0) {
									$price = 0;
								}

								if ((float) $result['special'] && $result['price'] != 0) {
									$data['isset_special'] = $data['isset_special'] + 1;
									$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
									$special_price = (float) $result['price'] - (float) $result['special'];
									$discount = round(($special_price / $result['price']) * 100);

								} else {
									$special = false;
									$discount = false;
								}

								$get_end = $this->model_extension_module_websky_viewed->getEnd($result['product_id']);

								if ($get_end) {
									$date_end = $get_end;
								} else {
									$date_end = false;
								}

								if ($this->config->get('config_tax')) {
									$tax = $this->currency->format((float) $result['special'] ? $result['special'] : $result['price'], $this->session->data['currency']);
								} else {
									$tax = false;
								}

								$product_data[] = [
									'product_id' => $result['product_id'],
									'thumb' => $image,
									'name' => $result['name'],
									'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
									'price' => $price,
									'special' => $special,
									'discount' => '%' . $discount,
									'quantity' => $result['quantity'],
									'tax' => $tax,
									'date_end' => $date_end,
									'minimum' => $result['minimum'] > 0 ? $result['minimum'] : 1,
									'href' => $this->url->link('product/product', 'language=' . $this->config->get('config_language') . '&product_id=' . $result['product_id'])
								];


							}

						}

						$heading = $setting['heading'][$this->config->get('config_language_id')];
						$title = str_replace("[manufacturerName]", $manufacturer_info['name'], $heading);
						$title = str_replace("[categoryName]", '', $title);
                              $sub_title =$setting['sub_heading'][$this->config->get('config_language_id')];

						$data['modules'][] = [
							'product_data' => $product_data,
							'title' => $title,
							'sub_title' => $sub_title,
							'href' => $this->url->link('product/manufacturer/info', 'language=' . $this->config->get('config_language') . '&manufacturer_id=' .  $manufacturer['manufacturer_id'])
						];

					}
				}
			
			}
			return $this->load->view('extension/module/websky_viewed/design'.$setting['design'], $data);


		} else {
			return '';

		}
	}
}
