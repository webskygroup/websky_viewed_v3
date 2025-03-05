<?php
class ControllerExtensionModulewebskyViewed extends Controller {
	public function index(array $setting): string{
		if ($setting['status'] && $this->customer->isLogged()) {
			$this->load->model('extension/module/websky_viewed');
			$this->document->addStyle('catalog/view/theme/default/stylesheet/swiper-bundle.min.css');
			$this->document->addScript('catalog/view/theme/default/stylesheet/swiper-bundle.min.js');

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

				$products_visited = $this->model_extension_websky_viewed_module_websky_viewed->getProductsVisited($filter_data);
				if ($products_visited) {
					$data['isset_special'] = 0;
					$products = array();
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

						$get_end = $this->model_extension_websky_viewed_module_websky_viewed->getEnd($result['product_id']);

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

						$product_data = [
							'product_id' => $result['product_id'],
							'thumb' => $image,
							'name' => $result['name'],
							'description' => substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('config_product_description_length')) . '..',
							'price' => $price,
							'special' => $special,
							'discount' => '%' . $discount,
							'quantity' => $result['quantity'],
							'tax' => $tax,
							'date_end' => $date_end,
							'minimum' => $result['minimum'] > 0 ? $result['minimum'] : 1,
							'href' => $this->url->link('product/product', 'language=' . $this->config->get('config_language') . '&product_id=' . $result['product_id'])
						];

						$products[] = $this->load->controller('product/thumb', $product_data);
					}

				}
				$heading = $setting['heading'][$this->config->get('config_language_id')];
				$title = str_replace("[manufacturerName]", '', $heading);
				$title = str_replace("[categoryName]", '', $title);

				$data['modules'][] = [
					'product_data' => $products,
					'title' => $title,
				];

				return $this->load->view('extension/module/websky_viewed', $data);

			} elseif ($display_type == 'by_category') {
				$this->load->model('catalog/category');

				$filter_data = [
					'limit' => $setting['category_amount']
				];
				$category_visited = $this->model_extension_websky_viewed_module_websky_viewed->getCategoryVisited($filter_data);
				// echo '<pre>';
				// print_r($category_visited);
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
							$products = array();
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

								$get_end = $this->model_extension_websky_viewed_module_websky_viewed->getEnd($result['product_id']);

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

								$product_data = [
									'product_id' => $result['product_id'],
									'thumb' => $image,
									'name' => $result['name'],
									'description' => substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('config_product_description_length')) . '..',
									'price' => $price,
									'special' => $special,
									'discount' => '%' . $discount,
									'quantity' => $result['quantity'],
									'tax' => $tax,
									'date_end' => $date_end,
									'minimum' => $result['minimum'] > 0 ? $result['minimum'] : 1,
									'href' => $this->url->link('product/product', 'language=' . $this->config->get('config_language') . '&product_id=' . $result['product_id'])
								];

								$products[] = $this->load->controller('product/thumb', $product_data);

							}
						}

						$heading = $setting['heading'][$this->config->get('config_language_id')];
						$title = str_replace("[categoryName]", $category_info['name'], $heading);
						$title = str_replace("[manufacturerName]", '', $title);

						$data['modules'][] = [
							'product_data' => $products,
							'title' => $title,
						];

					}
				}

				return $this->load->view('extension/module/websky_viewed', $data);

			} elseif ($display_type == 'by_manufacturer') {
				$this->load->model('catalog/manufacturer');

				$filter_data = [
					'limit' => $setting['category_amount']
				];
				$manufaturer_visited = $this->model_extension_websky_viewed_module_websky_viewed->getManufacturerVisited($filter_data);

				// echo '<pre>';
				// print_r($manufaturer_visited);

				if ($manufaturer_visited) {
					$products = array();
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
							$products = array();
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

								$get_end = $this->model_extension_websky_viewed_module_websky_viewed->getEnd($result['product_id']);

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

								$product_data = [
									'product_id' => $result['product_id'],
									'thumb' => $image,
									'name' => $result['name'],
									'description' => substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('config_product_description_length')) . '..',
									'price' => $price,
									'special' => $special,
									'discount' => '%' . $discount,
									'quantity' => $result['quantity'],
									'tax' => $tax,
									'date_end' => $date_end,
									'minimum' => $result['minimum'] > 0 ? $result['minimum'] : 1,
									'href' => $this->url->link('product/product', 'language=' . $this->config->get('config_language') . '&product_id=' . $result['product_id'])
								];

								$products[] = $this->load->controller('product/thumb', $product_data);

							}

						}

						$heading = $setting['heading'][$this->config->get('config_language_id')];
						$title = str_replace("[manufacturerName]", $manufacturer_info['name'], $heading);
						$title = str_replace("[categoryName]", '', $title);

						$data['modules'][] = [
							'product_data' => $products,
							'title' => $title,
						];

					}
				}
				return $this->load->view('extension/module/websky_viewed', $data);

			}

		} else {
			return '';

		}
	}
}