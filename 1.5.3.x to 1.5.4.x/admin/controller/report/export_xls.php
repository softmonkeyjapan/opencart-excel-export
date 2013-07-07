<?php
/**
 * This is Export XLSX controller
 *
 * Use it to export your orders into an Excel file compatible with Excel 2003-2007-2010
 *
 * PHP 5
 *
 * OpenCart(tm) : Webshop (http://www.opencart.com/)
 * Copyright 2005-2012, SoftMonkey (KARTONO Loïc). http://www.soft-monkey.com
 *
 * @copyright     Copyright 2005-2012, SoftMonkey (KARTONO Loïc). http://www.soft-monkey.com
 * @link          http://www.kartono-loic.com
 * @package       admin/report
 * @since         OpenCart(tm) 1.5.4.1
 */
class ControllerReportExportXLS extends Controller{


	/**
	 * Globales variable
	 */
	private $objPHPExcel;			// Use to create our Excel worksheet
	private $mainCounter;			// The actual order when using 'Export all'
	private $filter_date_start; 	// The start date from data filter
	private $filter_date_end; 		// The end date from data filter
	private $filter_order_status_id;// Status order filter





	/**
	 * Index page of the Export XLSX module
	 */
	public function index(){

		// Loading language file
		$this->load->language('report/export_xls');
		$this->document->setTitle($this->language->get('heading_title'));


		// Check if date start, date end and status were set as filter
		if (isset($this->request->get['filter_date_start'])) {
			$this->filter_date_start = $this->request->get['filter_date_start'];
		} else 
			$this->filter_date_start = date('Y-m-d', strtotime(date('Y') . '-' . date('m') . '-01'));
		if (isset($this->request->get['filter_date_end'])) {
			$this->filter_date_end = $this->request->get['filter_date_end'];
		} else 
			$this->filter_date_end = date('Y-m-d');
		if (isset($this->request->get['filter_order_status_id'])) {
			$this->filter_order_status_id = $this->request->get['filter_order_status_id'];
		} else
			$this->filter_order_status_id = 0;


		// Add filter dates to the url (GET)
		$url = '';			
		if (isset($this->request->get['filter_date_start'])) {
			$url .= '&filter_date_start=' . $this->request->get['filter_date_start'];
		}
		if (isset($this->request->get['filter_date_end'])) {
			$url .= '&filter_date_end=' . $this->request->get['filter_date_end'];
		}
		if (isset($this->request->get['filter_order_status_id'])) {
			$url .= '&filter_order_status_id=' . $this->request->get['filter_order_status_id'];
		}


		// Creating breadcrumb
		$this->data['breadcrumbs'] 		= array();
   		$this->data['breadcrumbs'][] 	= array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('report/export_xls', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);


   		// Set the text to the view from language file
		$this->data['heading_title'] 	= $this->language->get('heading_title');
		$this->data['text_customer'] 	= $this->language->get('text_customer');
		$this->data['text_date'] 		= $this->language->get('text_date');
		$this->data['text_order'] 		= $this->language->get('text_order');
		$this->data['text_amount'] 		= $this->language->get('text_amount');
		$this->data['text_product'] 	= $this->language->get('text_product');
		$this->data['text_status']		= $this->language->get('text_status');
		$this->data['text_action'] 		= $this->language->get('text_action');
		$this->data['text_export_all']	= $this->language->get('text_export_all');
		$this->data['text_all_status']	= $this->language->get('text_all_status');
		$this->data['entry_date_start'] = $this->language->get('entry_date_start');
		$this->data['entry_date_end'] 	= $this->language->get('entry_date_end');
		$this->data['entry_status']		= $this->language->get('entry_status');
		$this->data['button_export'] 	= $this->url->link('report/export_xls/export', 'token=' . $this->session->data['token'] . $url . '&order=all', 'SSL');
		$this->data['button_filter'] 	= $this->language->get('button_filter');


		// Loading order status model
		$this->load->model('localisation/order_status');
    	$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();


		// Loading export xls model
		$this->load->model('report/export_xls');
		$data = array(
			'filter_date_start'	     => $this->filter_date_start, 
			'filter_date_end'	     => $this->filter_date_end,
			'filter_order_status_id' => $this->filter_order_status_id
		);
		$results = $this->model_report_export_xls->getOrders($data);
		$this->data['orders'] = array();



		// Loop on all results (orders) found
		foreach ($results as $result) {

			// Create action associate to each order (line)
			$action = array();
			$action[] = array(
				'text' 	=> $this->language->get('text_view'),
				'href' 	=> $this->url->link('sale/order/info', 'token=' . $this->session->data['token'] . '&order_id=' . $result['order_id'], 'SSL')
			);
			$action[] = array(
				'text' 	=> $this->language->get('text_export'),
				'href'	=> $this->url->link('report/export_xls/export', 'token=' . $this->session->data['token'] . '&order_id=' . $result['order_id'], 'SSL')
			);
			$action[] = array(
				'text' 	=> $this->language->get('text_invoice'),
				'href'	=> $this->url->link('report/export_xls/createInvoice', 'token=' . $this->session->data['token'] . '&order_id=' . $result['order_id'], 'SSL')
			);


			// Create new line
			$this->data['orders'][] = array(
				'firstname' 	=> $result['firstname'],
				'lastname'  	=> $result['lastname'],
				'nb_product'	=> $this->model_report_export_xls->getTotalProductFromOrder($result['order_id']),
				'order_id'  	=> $result['order_id'],
				'date_added'	=> date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'total'     	=> $this->currency->format($result['total'], $result['currency_code'], $result['currency_value']),
				'status'		=> $result['name'],
				'action' 		=> $action
			);
		}


		// Send to the view the filter's value + token
		$this->data['token'] = $this->session->data['token'];
		$this->data['filter_date_start'] = $this->filter_date_start;
		$this->data['filter_date_end'] = $this->filter_date_end;
		$this->data['filter_order_status_id'] = $this->filter_order_status_id;


		// Use export xls template to display datas
		$this->template = 'report/export_xls.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);


		// Display
		$this->response->setOutput($this->render());
	}








	/**
	 * Export an order or all orders to an Excel File
	 * @return File to download objPHPExcel
	 */
	private function getDownloadXlsFile($order_id = null){
		// Redirect output to a client’s web browser (Excel2007)
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="export-order-'.$order_id.'.xlsx"');
		header('Cache-Control: max-age=0');

		$objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
		exit();
	}








	/**
	 * Export an order or all orders to an Excel File
	 * @return File to download
	 */
	public function export(){
		
		// Loading PHP Excel library
		require_once DIR_SYSTEM . 'library/excel/PHPExcel.php';
		require_once DIR_SYSTEM . 'library/excel/PHPExcel/IOFactory.php';


		// Create a new worksheet Excel and set the counter at 1
		$this->objPHPExcel = new PHPExcel();
		$this->mainCounter = 1;


		// If it's an individual export
		if(isset($this->request->get['order_id'])){
			$order_id = $this->request->get['order_id'];// Get the order ID
			$this->createExcelWorksheet($order_id);		// Create the Excel worksheet
			$this->getDownloadXlsFile($order_id);		// Then download the file
		}


		// If user click on "Export all"
		if(isset($this->request->get['order'])) {
			$this->load->model('report/export_xls');	
			$data = array(
				'filter_date_start'	     => $this->filter_date_start, 
				'filter_date_end'	     => $this->filter_date_end,
				'filter_order_status_id' => $this->filter_order_status_id
			);
			$result = $this->model_report_export_xls->getOrders($data);

			foreach ($result as $res) {
				$this->createExcelWorksheet($res['order_id']);
				$this->mainCounter++;
			}
			$this->getDownloadXlsFile('all');
		}
	}





	/**
	 * Export the invoice associate to an order
	 * Same display than "Print Invoice" (Sale info) but add the logo on the top
	 */
	public function createInvoice(){

		// Loading Excel Library
		require_once DIR_SYSTEM . 'library/excel/PHPExcel.php';
		require_once DIR_SYSTEM . 'library/excel/PHPExcel/IOFactory.php';


		// Loading language file
		$this->load->language('report/export_xls');


		// Loading model
		$this->load->model('report/export_xls');
		$this->load->model('sale/order');
		

		// If an order is define, we get the number
		if(isset($this->request->get['order_id'])){
			$order_id = $this->request->get['order_id'];
		}else $order_id = 0;


		// Get the information relative to the order
		$result = $this->model_report_export_xls->getOrder($order_id);


		// Adding order info to an array
		foreach ($result as $res) {
			$this->data['orders'][] = array(
				'order_id' 				=> $res['order_id'],
				'store_name' 			=> $res['store_name'],
				'customer' 				=> $res['firstname'] . ' ' . $res['lastname'],
				'email'					=> $res['email'],
				'telephone'				=> $res['telephone'],
				'total'					=> $this->currency->format($res['total'], $res['currency_code'], $res['currency_value']),
				'date_added'			=> date($this->language->get('date_format_short'), strtotime($res['date_added'])),

				'currency_code'			=> $res['currency_code'],
				'currency_value'		=> $res['currency_value'],

				'payment_firstname'		=> $res['payment_firstname'],
				'payment_lastname'		=> $res['payment_lastname'],
				'payment_address_1'		=> $res['payment_address_1'],
				'payment_address_2'		=> $res['payment_address_2'],
				'payment_city'			=> $res['payment_city'],
				'payment_postcode'		=> $res['payment_postcode'],
				'payment_zone'			=> $res['payment_zone'],
				'payment_country'		=> $res['payment_country'],
				'payment_method'		=> $res['payment_method'],

				'shipping_firstname'	=> $res['shipping_firstname'],
				'shipping_lastname'		=> $res['shipping_lastname'],
				'shipping_address_1'	=> $res['shipping_address_1'],
				'shipping_address_2'	=> $res['shipping_address_2'],
				'shipping_city'			=> $res['shipping_city'],
				'shipping_postcode'		=> $res['shipping_postcode'],
				'shipping_zone'			=> $res['shipping_zone'],
				'shipping_country'		=> $res['shipping_country'],
				'shipping_method'		=> $res['shipping_method']
			);
		}

		// Put the index of the array into invoice (more simple to call)
		$invoice = $this->data['orders'][0];


		// Creating a new instance of Excel with properties
		$this->objPHPExcel = new PHPExcel();
		$this->objPHPExcel->getProperties()->setCreator("SoftMonkey")
										   ->setLastModifiedBy("SoftMonkey")
										   ->setTitle("Office 2007 XLSX")
										   ->setSubject("Office 2007 XLSX")
										   ->setDescription("Document for Office 2007 XLSX, generated by SoftMonkey")
										   ->setKeywords("office 2007 excel")
										   ->setCategory("soft-monkey.com");


		// Create a first sheet, representing order data
		$this->objPHPExcel->setActiveSheetIndex(0);


		// Writing store info left top
		$this->objPHPExcel->getActiveSheet()->setCellValue('A4', $this->config->get('config_name'));
		$this->objPHPExcel->getActiveSheet()->setCellValue('A5', $this->config->get('config_address'));
		$this->objPHPExcel->getActiveSheet()->setCellValue('A6', 'Telephone : ' . $this->config->get('config_telephone'));
		$this->objPHPExcel->getActiveSheet()->setCellValue('A7', $this->config->get('config_email'));


		// Writing general info about the order
		$this->objPHPExcel->getActiveSheet()->setCellValue('G4', $this->language->get('header_date'));
		$this->objPHPExcel->getActiveSheet()->setCellValue('G5', $this->language->get('header_order_id'));
		$this->objPHPExcel->getActiveSheet()->setCellValue('G6', $this->language->get('header_payment_method'));
		$this->objPHPExcel->getActiveSheet()->setCellValue('G7', $this->language->get('header_shipping_method'));
		$this->objPHPExcel->getActiveSheet()->getStyle('G4')->getFont()->setBold(true);
		$this->objPHPExcel->getActiveSheet()->getStyle('G5')->getFont()->setBold(true);
		$this->objPHPExcel->getActiveSheet()->getStyle('G6')->getFont()->setBold(true);
		$this->objPHPExcel->getActiveSheet()->getStyle('G7')->getFont()->setBold(true);
		$this->objPHPExcel->getActiveSheet()->setCellValue('H4', $invoice['date_added']);
		$this->objPHPExcel->getActiveSheet()->setCellValue('H5', $invoice['order_id']);
		$this->objPHPExcel->getActiveSheet()->setCellValue('H6', $invoice['payment_method']);
		$this->objPHPExcel->getActiveSheet()->setCellValue('H7', $invoice['shipping_method']);
		$this->objPHPExcel->getActiveSheet()->getStyle('H4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
		$this->objPHPExcel->getActiveSheet()->getStyle('H5')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
		$this->objPHPExcel->getActiveSheet()->getStyle('H6')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
		$this->objPHPExcel->getActiveSheet()->getStyle('H7')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);


		// Set thin black border outline around column
		$styleThinBlackBorderOutline = array(
			'borders' => array(
				'outline' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array('argb' => 'FF000000'),
				),
			),
		);


		// Address info header
		$this->objPHPExcel->getActiveSheet()->getStyle('A11:D11')->applyFromArray($styleThinBlackBorderOutline);
		$this->objPHPExcel->getActiveSheet()->getStyle('A11:D11')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		$this->objPHPExcel->getActiveSheet()->getStyle('A11:D11')->getFill()->getStartColor()->setARGB('E7EFEF');
		$this->objPHPExcel->getActiveSheet()->setCellValue('A11', $this->language->get('header_to'));
		$this->objPHPExcel->getActiveSheet()->mergeCells('A11:D11');
		$this->objPHPExcel->getActiveSheet()->getStyle('E11:H11')->applyFromArray($styleThinBlackBorderOutline);
		$this->objPHPExcel->getActiveSheet()->getStyle('E11:H11')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		$this->objPHPExcel->getActiveSheet()->getStyle('E11:H11')->getFill()->getStartColor()->setARGB('E7EFEF');
		$this->objPHPExcel->getActiveSheet()->setCellValue('E11', $this->language->get('header_ship_to'));
		$this->objPHPExcel->getActiveSheet()->mergeCells('E11:H11');
		$this->objPHPExcel->getActiveSheet()->getStyle('A12:D19')->applyFromArray($styleThinBlackBorderOutline);
		$this->objPHPExcel->getActiveSheet()->getStyle('E12:H19')->applyFromArray($styleThinBlackBorderOutline);


		// Write customer info
		$this->objPHPExcel->getActiveSheet()->setCellValue('A12', $invoice['payment_firstname'] . ' ' . $invoice['payment_lastname']);
		$this->objPHPExcel->getActiveSheet()->setCellValue('A13', $invoice['payment_address_1']);
		$this->objPHPExcel->getActiveSheet()->setCellValue('A14', $invoice['payment_address_2']);
		$this->objPHPExcel->getActiveSheet()->setCellValue('A15', $invoice['payment_city'] . ' ' . $invoice['payment_postcode']);
		$this->objPHPExcel->getActiveSheet()->setCellValue('A16', $invoice['payment_zone']);
		$this->objPHPExcel->getActiveSheet()->setCellValue('A17', $invoice['payment_country']);
		$this->objPHPExcel->getActiveSheet()->setCellValue('A18', $invoice['email']);
		$this->objPHPExcel->getActiveSheet()->setCellValue('A19', $invoice['telephone']);


		// Write shipping info
		$this->objPHPExcel->getActiveSheet()->setCellValue('E12', $invoice['shipping_firstname'] . ' ' . $invoice['shipping_lastname']);
		$this->objPHPExcel->getActiveSheet()->setCellValue('E13', $invoice['shipping_address_1']);
		$this->objPHPExcel->getActiveSheet()->setCellValue('E14', $invoice['shipping_address_2']);
		$this->objPHPExcel->getActiveSheet()->setCellValue('E15', $invoice['shipping_city'] . ' ' . $invoice['shipping_postcode']);
		$this->objPHPExcel->getActiveSheet()->setCellValue('E16', $invoice['shipping_zone']);
		$this->objPHPExcel->getActiveSheet()->setCellValue('E17', $invoice['shipping_country']);


		// Product header
		$this->objPHPExcel->getActiveSheet()->getStyle('A21:C21')->applyFromArray($styleThinBlackBorderOutline);
		$this->objPHPExcel->getActiveSheet()->getStyle('A21:C21')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		$this->objPHPExcel->getActiveSheet()->getStyle('A21:C21')->getFill()->getStartColor()->setARGB('E7EFEF');
		$this->objPHPExcel->getActiveSheet()->setCellValue('A21', $this->language->get('header_product_name'));
		$this->objPHPExcel->getActiveSheet()->mergeCells('A21:C21');
		$this->objPHPExcel->getActiveSheet()->getStyle('D21:E21')->applyFromArray($styleThinBlackBorderOutline);
		$this->objPHPExcel->getActiveSheet()->getStyle('D21:E21')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		$this->objPHPExcel->getActiveSheet()->getStyle('D21:E21')->getFill()->getStartColor()->setARGB('E7EFEF');
		$this->objPHPExcel->getActiveSheet()->setCellValue('D21', $this->language->get('header_product_model'));
		$this->objPHPExcel->getActiveSheet()->mergeCells('D21:E21');
		$this->objPHPExcel->getActiveSheet()->getStyle('F21')->applyFromArray($styleThinBlackBorderOutline);
		$this->objPHPExcel->getActiveSheet()->getStyle('F21')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		$this->objPHPExcel->getActiveSheet()->getStyle('F21')->getFill()->getStartColor()->setARGB('E7EFEF');
		$this->objPHPExcel->getActiveSheet()->setCellValue('F21', $this->language->get('header_product_quantity'));
		$this->objPHPExcel->getActiveSheet()->getStyle('G21')->applyFromArray($styleThinBlackBorderOutline);
		$this->objPHPExcel->getActiveSheet()->getStyle('G21')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		$this->objPHPExcel->getActiveSheet()->getStyle('G21')->getFill()->getStartColor()->setARGB('E7EFEF');
		$this->objPHPExcel->getActiveSheet()->setCellValue('G21', $this->language->get('header_product_unit_price'));
		$this->objPHPExcel->getActiveSheet()->getStyle('H21')->applyFromArray($styleThinBlackBorderOutline);
		$this->objPHPExcel->getActiveSheet()->getStyle('H21')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		$this->objPHPExcel->getActiveSheet()->getStyle('H21')->getFill()->getStartColor()->setARGB('E7EFEF');
		$this->objPHPExcel->getActiveSheet()->setCellValue('H21', $this->language->get('header_product_price'));


		// Writing product details
		$products = $this->model_report_export_xls->getProductListFromOrder($order_id);
		$counter = 22;

		foreach ($products as $prod) {
			$option_data = array();

			// Get the product option to get the color and the size
			$options = $this->model_sale_order->getOrderOptions($order_id, $prod['order_product_id']);

			if(!empty($options)){
				foreach ($options as $option) {
					if ($option['name'] == 'Size') {
						$option_data['Size'][] = array(
							'name'  => $option['name'],
							'value' => $option['value'],
							'type'  => $option['type']
						);
					} 
					if ($option['name'] == 'Color') {
						$option_data['Color'][] = array(
							'name'  => $option['name'],
							'value' => $option['value'],
							'type'  => $option['type']
						);
					} 
				}
			}

			$color = ''; $size = '';
			if( !empty($option_data['Color']) ){
				$color = '[Color : ' . $option_data['Color'][0]['value'] . ']';
			}
			if( !empty($option_data['Size']) ){
				$size = '[Size : ' . $option_data['Size'][0]['value'] . ']';
			}

			// Add each product line
			$this->objPHPExcel->getActiveSheet()->setCellValue('A' . $counter, html_entity_decode($prod['name'], ENT_QUOTES, 'UTF-8') . ' ' . $color . ' ' . $size);
			$this->objPHPExcel->getActiveSheet()->setCellValue('D' . $counter, $prod['model']);
			$this->objPHPExcel->getActiveSheet()->setCellValue('F' . $counter, $prod['quantity']);
			$this->objPHPExcel->getActiveSheet()->setCellValue('G' . $counter, $this->currency->format($prod['price'], $invoice['currency_code'], $invoice['currency_value']));
			$this->objPHPExcel->getActiveSheet()->setCellValue('H' . $counter, $this->currency->format($prod['total'], $invoice['currency_code'], $invoice['currency_value']));

			$counter++;
		}


		// Draw a frame around the products list
		$this->objPHPExcel->getActiveSheet()->getStyle('A22:C'.($counter-1))->applyFromArray($styleThinBlackBorderOutline);
		$this->objPHPExcel->getActiveSheet()->getStyle('D22:E'.($counter-1))->applyFromArray($styleThinBlackBorderOutline);
		$this->objPHPExcel->getActiveSheet()->getStyle('F22:F'.($counter-1))->applyFromArray($styleThinBlackBorderOutline);
		$this->objPHPExcel->getActiveSheet()->getStyle('G22:G'.($counter-1))->applyFromArray($styleThinBlackBorderOutline);
		$this->objPHPExcel->getActiveSheet()->getStyle('H22:H'.($counter-1))->applyFromArray($styleThinBlackBorderOutline);


		// Get the sub total, tax and total from this order
		$total_data = $this->model_sale_order->getOrderTotals($order_id);
		

		// Write this under the product list on the right
		foreach ($total_data as $total) {
			$this->objPHPExcel->getActiveSheet()->setCellValue('G'.$counter, $total['title']);
			$this->objPHPExcel->getActiveSheet()->setCellValue('H'.$counter, $total['text']);
			$this->objPHPExcel->getActiveSheet()->getStyle('G'.$counter)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
			$this->objPHPExcel->getActiveSheet()->getStyle('G'.$counter)->getFont()->setBold(true);
			$this->objPHPExcel->getActiveSheet()->getStyle('H'.$counter)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
			$this->objPHPExcel->getActiveSheet()->getStyle('G'.$counter.':H'.$counter)->applyFromArray($styleThinBlackBorderOutline);

			$counter++;
		}


		// Add the shop logo on the top of the document
		$this->objPHPExcel->getActiveSheet()->insertNewRowBefore(1,1);
		$objDrawing = new PHPExcel_Worksheet_Drawing();
		$objDrawing->setPath(DIR_IMAGE . $this->config->get('config_logo'));
		$objDrawing->setHeight(64);
		$objDrawing->setCoordinates('A1');
		$objDrawing->setWorksheet($this->objPHPExcel->getActiveSheet());


		// Set the orientation page an give a name to the worksheet
		$this->objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
		$this->objPHPExcel->getActiveSheet()->setTitle('Invoice #' . $order_id);


		// Set the column in autosize
		$columns = array('A',/*'B','C','D','E','F',*/'G','H');
		foreach($columns as $col){
			$this->objPHPExcel->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
		}
		$this->objPHPExcel->getActiveSheet()->getStyle('A:H')->getFont()->setSize(10);


		$this->objPHPExcel->getActiveSheet()->removeColumn('B', 2);


		$this->getDownloadXlsFile($order_id);
	}



	


	/**
	 * Create a list of orders (one or many order)
	 * If an order has 3 products, 3 lines will be create with all the info
	 * @param order_id Create lines for this order
	 */
	private function createExcelWorksheet($order_id){

		// Loading language
		$this->load->language('report/export_xls');


		// Loading model
		$this->load->model('report/export_xls');
		$this->load->model('sale/order');


		// Get the the info relative to this order
		$result = $this->model_report_export_xls->getOrder($order_id);


		// Add it into an array
		foreach ($result as $res) {
			$this->data['orders'][] = array(
				'order_id' 				=> $res['order_id'],
				'store_name' 			=> $res['store_name'],
				'customer' 				=> $res['firstname'] . ' ' . $res['lastname'],
				'email'					=> $res['email'],
				'telephone'				=> $res['telephone'],
				'total'					=> $this->currency->format($res['total'], $res['currency_code'], $res['currency_value']),
				'date_added'			=> date($this->language->get('date_format_short'), strtotime($res['date_added'])),

				'currency_code'			=> $res['currency_code'],
				'currency_value'		=> $res['currency_value'],

				'shipping_firstname'	=> $res['shipping_firstname'],
				'shipping_lastname'		=> $res['shipping_lastname'],
				'shipping_address_1'	=> $res['shipping_address_1'],
				'shipping_address_2'	=> $res['shipping_address_2'],
				'shipping_city'			=> $res['shipping_city'],
				'shipping_postcode'		=> $res['shipping_postcode'],
				'shipping_zone'			=> $res['shipping_zone'],
				'shipping_country'		=> $res['shipping_country'],
				'shipping_method'		=> $res['shipping_method']
			);
		}


		// If the counter = 1, then we write the heading part (legend)
		if($this->mainCounter == 1){
			// Set document properties
			$this->objPHPExcel->getProperties()->setCreator("SoftMonkey")
										 	   ->setLastModifiedBy("SoftMonkey")
										 	   ->setTitle("Office 2007 XLSX")
										 	   ->setSubject("Office 2007 XLSX")
										 	   ->setDescription("Document for Office 2007 XLSX, generated by SoftMonkey from Hachiaki backend.")
										 	   ->setKeywords("office 2007 excel")
										 	   ->setCategory("soft-monkey.com");


			// Create a first sheet, representing order data
			$this->objPHPExcel->setActiveSheetIndex(0);
		

			// Writing the heading part
			$this->objPHPExcel->getActiveSheet()->setCellValue('A' . $this->mainCounter, $this->language->get('header_order_id'));
			$this->objPHPExcel->getActiveSheet()->setCellValue('B' . $this->mainCounter, $this->language->get('header_store_name'));
			$this->objPHPExcel->getActiveSheet()->setCellValue('C' . $this->mainCounter, $this->language->get('header_customer'));
			$this->objPHPExcel->getActiveSheet()->setCellValue('D' . $this->mainCounter, $this->language->get('header_email'));
			$this->objPHPExcel->getActiveSheet()->setCellValue('E' . $this->mainCounter, $this->language->get('header_telephone'));
			$this->objPHPExcel->getActiveSheet()->setCellValue('F' . $this->mainCounter, $this->language->get('header_total'));
			$this->objPHPExcel->getActiveSheet()->setCellValue('G' . $this->mainCounter, $this->language->get('header_date'));
			$this->objPHPExcel->getActiveSheet()->setCellValue('H' . $this->mainCounter, $this->language->get('header_product_quantity'));
			$this->objPHPExcel->getActiveSheet()->setCellValue('I' . $this->mainCounter, $this->language->get('header_product_name'));
			$this->objPHPExcel->getActiveSheet()->setCellValue('J' . $this->mainCounter, $this->language->get('header_product_model'));
			$this->objPHPExcel->getActiveSheet()->setCellValue('K' . $this->mainCounter, $this->language->get('header_product_color'));
			$this->objPHPExcel->getActiveSheet()->setCellValue('L' . $this->mainCounter, $this->language->get('header_product_size'));
			$this->objPHPExcel->getActiveSheet()->setCellValue('M' . $this->mainCounter, $this->language->get('header_shipping_firstname'));
			$this->objPHPExcel->getActiveSheet()->setCellValue('N' . $this->mainCounter, $this->language->get('header_shipping_lastname'));
			$this->objPHPExcel->getActiveSheet()->setCellValue('O' . $this->mainCounter, $this->language->get('header_shipping_address'));
			$this->objPHPExcel->getActiveSheet()->setCellValue('P' . $this->mainCounter, $this->language->get('header_shipping_city'));
			$this->objPHPExcel->getActiveSheet()->setCellValue('Q' . $this->mainCounter, $this->language->get('header_shipping_postcode'));
			$this->objPHPExcel->getActiveSheet()->setCellValue('R' . $this->mainCounter, $this->language->get('header_shipping_zone'));
			$this->objPHPExcel->getActiveSheet()->setCellValue('S' . $this->mainCounter, $this->language->get('header_shipping_country'));
			$this->objPHPExcel->getActiveSheet()->setCellValue('T' . $this->mainCounter, $this->language->get('header_shipping_method'));
		}


		// Get the associate products
		$products = $this->model_report_export_xls->getProductListFromOrder($order_id);
		// Set the counter just after the header line
		$counter  = $this->mainCounter+1;
		//print_r($products);die();

		// Loop on all the product
		foreach ($products as $prod) {
			$option_data = array();

			// Get the option to try to get the color and the size
			$options = $this->model_sale_order->getOrderOptions($order_id, $prod['order_product_id']);

			if(!empty($options)){
				foreach ($options as $option) {
					if ($option['name'] == 'Size') {
						$option_data['Size'][] = array(
							'name'  => $option['name'],
							'value' => $option['value'],
							'type'  => $option['type']
						);
					} 
					if ($option['name'] == 'Color') {
						$option_data['Color'][] = array(
							'name'  => $option['name'],
							'value' => $option['value'],
							'type'  => $option['type']
						);
					} 
				}
			}

			$color = 'N/A'; $size = 'N/A';
			if( !empty($option_data['Color']) ){
				$color = $option_data['Color'][0]['value'];
			}
			if( !empty($option_data['Size']) ){
				$size = $option_data['Size'][0]['value'];
			}


			// Add each product line
			$this->objPHPExcel->getActiveSheet()->setCellValue('A' . $counter, $this->data['orders'][0]['order_id']);
			$this->objPHPExcel->getActiveSheet()->setCellValue('B' . $counter, $this->data['orders'][0]['store_name']);
			$this->objPHPExcel->getActiveSheet()->setCellValue('C' . $counter, $this->data['orders'][0]['customer']);
			$this->objPHPExcel->getActiveSheet()->setCellValue('D' . $counter, $this->data['orders'][0]['email']);
			$this->objPHPExcel->getActiveSheet()->setCellValue('E' . $counter, $this->data['orders'][0]['telephone']);
			$this->objPHPExcel->getActiveSheet()->setCellValue('F' . $counter, $this->data['orders'][0]['total']);
			$this->objPHPExcel->getActiveSheet()->setCellValue('G' . $counter, $this->data['orders'][0]['date_added']);
			$this->objPHPExcel->getActiveSheet()->setCellValue('H' . $counter, $prod['quantity']);
			$this->objPHPExcel->getActiveSheet()->setCellValue('I' . $counter, html_entity_decode($prod['name'], ENT_QUOTES, 'UTF-8'));
			$this->objPHPExcel->getActiveSheet()->setCellValue('J' . $counter, html_entity_decode($prod['model'], ENT_QUOTES, 'UTF-8'));
			$this->objPHPExcel->getActiveSheet()->setCellValue('K' . $counter, $color);
			$this->objPHPExcel->getActiveSheet()->setCellValue('L' . $counter, $size);
			$this->objPHPExcel->getActiveSheet()->setCellValue('M' . $counter, $this->data['orders'][0]['shipping_firstname']);
			$this->objPHPExcel->getActiveSheet()->setCellValue('N' . $counter, $this->data['orders'][0]['shipping_lastname']);
			$this->objPHPExcel->getActiveSheet()->setCellValue('O' . $counter, $this->data['orders'][0]['shipping_address_1'] . ' ' . 	$this->data['orders'][0]['shipping_address_2']);
			$this->objPHPExcel->getActiveSheet()->setCellValue('P' . $counter, $this->data['orders'][0]['shipping_city']);
			$this->objPHPExcel->getActiveSheet()->setCellValue('Q' . $counter, $this->data['orders'][0]['shipping_postcode']);
			$this->objPHPExcel->getActiveSheet()->setCellValue('R' . $counter, $this->data['orders'][0]['shipping_zone']);
			$this->objPHPExcel->getActiveSheet()->setCellValue('S' . $counter, $this->data['orders'][0]['shipping_country']);
			$this->objPHPExcel->getActiveSheet()->setCellValue('T' . $counter, $this->data['orders'][0]['shipping_method']);

			$counter++;
			$this->mainCounter++;
		}


		// Set thin black border outline around column
		$styleThinBlackBorderOutline = array(
			'borders' => array(
				'outline' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array('argb' => 'FF000000'),
				),
			),
		);


		// Set the style of heading cells
		$this->objPHPExcel->getActiveSheet()->getStyle('A1:T1')->applyFromArray($styleThinBlackBorderOutline);
		$this->objPHPExcel->getActiveSheet()->getStyle('A1:T1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		$this->objPHPExcel->getActiveSheet()->getStyle('A1:T1')->getFill()->getStartColor()->setARGB('FF808080');


		// Set column widths
		$columns = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T');


		// General style
		foreach ($columns as $col) {
			$this->objPHPExcel->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
			$this->objPHPExcel->getActiveSheet()->getStyle($col)->getFont()->setSize(11);
			$this->objPHPExcel->getActiveSheet()->getStyle($col)->getFont()->setBold(false);
			$this->objPHPExcel->getActiveSheet()->getStyle($col)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_BLACK);
			$this->objPHPExcel->getActiveSheet()->getStyle($col)->getAlignment()->setShrinkToFit(true);
		}


		unset($this->data['orders']);
	}
}
?>