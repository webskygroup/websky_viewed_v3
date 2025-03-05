<?php
class ModelExtensionModuleWebskyViewed extends Model
{
    
	protected array $statement = [];

	/**
	 * @param \Opencart\System\Engine\Registry $registry
	 */

    public function __construct(\Opencart\System\Engine\Registry $registry) {
		$this->registry = $registry;

		// Storing some sub queries so that we are not typing them out multiple times.
		$this->statement['discount'] = "(SELECT `pd2`.`price` FROM `" . DB_PREFIX . "product_discount` `pd2` WHERE `pd2`.`product_id` = `p`.`product_id` AND `pd2`.`customer_group_id` = '" . (int)$this->config->get('config_customer_group_id') . "'AND `pd2`.`quantity` = '1' AND ((`pd2`.`date_start` = '0000-00-00' OR `pd2`.`date_start` < NOW()) AND (`pd2`.`date_end` = '0000-00-00' OR `pd2`.`date_end` > NOW())) ORDER BY `pd2`.`priority` ASC, `pd2`.`price` ASC LIMIT 1) AS `discount`";
		$this->statement['special'] = "(SELECT `ps`.`price` FROM `" . DB_PREFIX . "product_special` `ps` WHERE `ps`.`product_id` = `p`.`product_id` AND `ps`.`customer_group_id` = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((`ps`.`date_start` = '0000-00-00' OR `ps`.`date_start` < NOW()) AND (`ps`.`date_end` = '0000-00-00' OR `ps`.`date_end` > NOW())) ORDER BY `ps`.`priority` ASC, `ps`.`price` ASC LIMIT 1) AS `special`";
		$this->statement['reward'] = "(SELECT `pr`.`points` FROM `" . DB_PREFIX . "product_reward` `pr` WHERE `pr`.`product_id` = `p`.`product_id` AND `pr`.`customer_group_id` = '" . (int)$this->config->get('config_customer_group_id') . "') AS `reward`";
		$this->statement['review'] = "(SELECT COUNT(*) FROM `" . DB_PREFIX . "review` `r` WHERE `r`.`product_id` = `p`.`product_id` AND `r`.`status` = '1' GROUP BY `r`.`product_id`) AS `reviews`";
	}

    public function getVisitedProductData(int $product_id): array
    {

        $sql = "SELECT `product_id` FROM `" . DB_PREFIX . "websky_viewed` WHERE `customer_id` = '" . (int) $this->customer->getId() . "' AND `product_id`='" . (int) $product_id . "'";
        $query = $this->db->query($sql);
        if (isset($query->num_rows)) {
            return $query->row;
        } else {
            return array();
        }
    }


    public function getProductsVisited($data): array
    {

        $sql = "SELECT * , " . $this->statement['discount'] . ", " . $this->statement['special'] . " FROM `" . DB_PREFIX . "websky_viewed` `wv`";
        $sql .= " LEFT JOIN `" . DB_PREFIX . "product_to_store` `p2s` ON (`wv`.`product_id` = `p2s`.`product_id`) LEFT JOIN `" . DB_PREFIX . "product` `p` ON (`p`.`product_id` = `p2s`.`product_id` AND `p`.`status` = '1' AND `p2s`.`store_id` = '" . (int)$this->config->get('config_store_id') . "' AND `p`.`date_available` <= NOW())";
		$sql .= " LEFT JOIN `" . DB_PREFIX . "product_description` `pd` ON (`p`.`product_id` = `pd`.`product_id`) WHERE `pd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "' AND `wv`.`customer_id` = '" . (int) $this->customer->getId() . "'";

        $sort_data = [
			'pd.name',
			'p.quantity',
			'p.price',
			'p.sort_order',
			'p.date_added'
		];
        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			if ($data['sort'] == 'pd.name' || $data['sort'] == 'p.model') {
				$sql .= " ORDER BY LCASE(" . $data['sort'] . ")";
			} elseif ($data['sort'] == 'p.price') {
				$sql .= " ORDER BY (CASE WHEN `special` IS NOT NULL THEN `special` WHEN `discount` IS NOT NULL THEN `discount` ELSE p.`price` END)";
			} else {
				$sql .= " ORDER BY " . $data['sort'];
			}
		} else {
			$sql .= " ORDER BY p.`sort_order`";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC, LCASE(`pd`.`name`) DESC";
		} else {
			$sql .= " ASC, LCASE(`pd`.`name`) ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

        // print_r($sql);

        $query = $this->db->query($sql);
        if (isset($query->num_rows)) {
            return $query->rows;
        } else {
            return array();
        }
    }
    public function insertVisitedProduct(array $product_data)
    {
            // print_r($product_data);
        $this->db->query("INSERT INTO `" . DB_PREFIX . "websky_viewed` SET `product_id` = '" . (int)$product_data['product_id'] . "', `customer_id` = '" . (int)$this->customer->getId() . "', `category_id` = '" . (int)$product_data['category_id'] . "', `manufacturer_id` = '" . (int)$product_data['manufacturer_id'] . "'");
   
    }

    public function getCategoryVisited(array $filter_data): array
    {

        $sql = "SELECT `category_id` FROM `" . DB_PREFIX . "websky_viewed` WHERE `customer_id` = '" . (int) $this->customer->getId() . "' AND `category_id` != 0 GROUP BY `category_id` ORDER BY `websky_viewed_id` DESC LIMIT 0," . (int) $filter_data['limit'] . "";
        $query = $this->db->query($sql);
        if (isset($query->num_rows)) {
            return $query->rows;
        } else {
            return array();
        }
    }

    public function getManufacturerVisited(array $filter_data): array
    {

        $sql = "SELECT `manufacturer_id` FROM `" . DB_PREFIX . "websky_viewed` WHERE `customer_id` = '" . (int) $this->customer->getId() . "' AND `manufacturer_id` != 0 GROUP BY `manufacturer_id` ORDER BY `websky_viewed_id` DESC LIMIT 0," . (int) $filter_data['limit'] . "";
        $query = $this->db->query($sql);
        if (isset($query->num_rows)) {
            return $query->rows;
        } else {
            return array();
        }
    }

    public function getManufacturerID(int $product_id): int {
        $sql = "SELECT `manufacturer_id` AS manufacturer_id FROM oc_product WHERE product_id = '" .(int)$product_id. "'";
        $query = $this->db->query($sql);
        if (isset($query->num_rows)) {
            return $query->row['manufacturer_id'];
        } else {
            return 0;
        }
    }

    public function getCategoryID(int $product_id): int {
        $sql = "SELECT `cp`.`path_id` AS category_id FROM `" . DB_PREFIX . "category_to_store` `c2s` LEFT JOIN `" . DB_PREFIX . "category_path` `cp` ON (`cp`.`category_id` = `c2s`.`category_id` AND `c2s`.`store_id` = '" . (int)$this->config->get('config_store_id') . "') LEFT JOIN `" . DB_PREFIX . "product_to_category` `p2c` ON (`p2c`.`category_id` = `cp`.`category_id`) WHERE `p2c`.`product_id` = '" .(int)$product_id. "'";
        //print_r($sql);
        $query = $this->db->query($sql);
       //print_r(end($query->rows)) ;
        if (isset($query->num_rows)) {
            $category=end($query->rows);
            return $category['category_id'];
        } else {
            return 0;
        }
    }

    public function getEnd($product_id = 0): string
    {
        $query = $this->db->query("SELECT date_end FROM " . DB_PREFIX . "product_special  WHERE product_id = '" . (int) $product_id . "' AND date_end > NOW()");
        if ($query->num_rows) {
            return $query->row['date_end'];
        } else {
            return "";

        }
    }

}
