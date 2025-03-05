<?php
class ControllerEventWebskyViewed extends Controller{
 
    public function visitedProduct(&$route = false, &$data = array()){
        if($this->customer->isLogged()){

            $this->load->language('extension/module/websky_viewed');
            $this->load->model("extension/module/websky_viewed");

            $results = $this->model_extension_module_websky_viewed->getVisitedProductData($this->request->get['product_id']);
            $manufacturer_id = $this->model_extension_module_websky_viewed->getManufacturerID($this->request->get['product_id']);
            $category_id = $this->model_extension_module_websky_viewed->getCategoryID($this->request->get['product_id']);
            if(empty($results)){
                $porduct_data=[
                    'product_id' => $this->request->get['product_id'],
                    'manufacturer_id' => $manufacturer_id,
                    'category_id' => $category_id
                ];
                $this->model_extension_module_websky_viewed->insertVisitedProduct($porduct_data);
            }
        }
    }
}