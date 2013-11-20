<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Description of diary_sales_controller
 *
 * @author jroque
 */
class DailySales extends ValidateAccess {

    function __construct() {
        parent::__construct();
        $this->load->database();
        $this->loggedin = $this->session->userdata('loggedin');
        $this->layout->isLogin = false;
        $this->load->library(array('session'));
        $this->load->model('ProfileDao');
        $this->load->model('DailySaleDao');
        $this->load->helper('url');
    }

    public function index() {
        $this->layout->assets(base_url() . 'assets/css/lib/fullcalendar.css');
        $this->layout->assets('//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js');
        $this->layout->assets(base_url() . 'assets/js/lib/fullcalendar.min.js');
        $this->layout->assets(base_url() . 'assets/js/daily_sale/list_daily_sales.js');
        $this->layout->view('daily_sales/list_template');
    }

    public function getDailySaleCalendar() {
        $daily_sale_credentials = $this->input->get();
        $dblDailyDao = $this->DailySaleDao->getDailySaleCalendar($daily_sale_credentials['start'], $daily_sale_credentials['end']);
        $timestamp = strtotime(Date('Y-m-d'));

        $jsonArray[] = array('title' => "Agregar Venta \n Estado: Abierto", 'start' => Date('Y-m-d', $timestamp), 'className' => 'label label-important', 'url' => site_url('DailySales/maintenanceForm/'));

        foreach ($dblDailyDao as $dbrDailyDao) {

            $jsonArray[] = array(
                'title' => "Venta del Día \n S/ " . $dbrDailyDao->grand_total_calculated . "\n Estado: " . Status::$statuses[$dbrDailyDao->status],
                'start' => $dbrDailyDao->date_sale,
                'className' => 'label ' . ($dbrDailyDao->status == Status::STATUS_ABIERTO ? 'label-info' : 'label-success'),
                'url' => site_url('DailySales/maintenanceForm/' . $dbrDailyDao->id)
            );

            if ($timestamp == strtotime($dbrDailyDao->date_sale)) {
                unset($jsonArray[0]);
            }
        }

        $jsonArray = array_values($jsonArray);

        echo json_encode($jsonArray);
    }

    public function maintenanceForm($daily_sale_id = null) {
        $this->layout->assets(base_url() . 'assets/css/dist/jquery.handsontable.full.css');
        $this->layout->assets('//cdnjs.cloudflare.com/ajax/libs/numeral.js/1.4.5/numeral.min.js');
        $this->layout->assets(base_url() . 'assets/js/lib/jquery.ajaxQueue.min.js');
        $this->layout->assets(base_url() . 'assets/js/dist/jquery.handsontable.full.js');
        $this->layout->assets(base_url() . 'assets/js/happy/daily.sales.js');

        $is_new = true;
        $dbr_daily_sale = null;
        $dbl_daily_sales_detail = null;
        $daily_sale_id = ($daily_sale_id ? (int) $daily_sale_id : 0);

        if ($daily_sale_id > 0) {
            $dbr_daily_sale = $this->DailySaleDao->getDailySaleById($daily_sale_id);
            $is_new = ($dbr_daily_sale ? false : true);
        }

        if (!$is_new) {
            $dbl_daily_sales_detail = $this->DailySaleDao->getDailySaleDetailBySaleId($dbr_daily_sale->id);

            $dbl_daily_sales_detail_two = $this->DailySaleDao->getDailyOtherSale();

            $dbl_daily_sales_detail = array_merge($dbl_daily_sales_detail, $dbl_daily_sales_detail_two);
        } else {

            $dbr_daily_sale_current = $this->DailySaleDao->getDailySaleByDateSale();

            if ($dbr_daily_sale_current) {
                $this->session->set_flashdata('message_danger', 'Ya existe una venta registrada en el día1');
                redirect('DailySales/index');
            }
            unset($dbr_daily_sale_current);

            $dbl_daily_sales_detail = $this->DailySaleDao->getDailyOtherSale();
        }
        
        $data_daily_sale = array();
        $data_daily_sale_ids = array();
        $index = 0;
        foreach ($dbl_daily_sales_detail as $dbr_daily_sale_detail) {

            $data_daily_sale[$index] = array(
                'type_of_sales_id' => (int)$dbr_daily_sale_detail->type_of_sales_id ,
                'name' => ($is_new ? ($dbr_daily_sale_detail->is_other_sales == 1 ? $dbr_daily_sale_detail->name : '') : $dbr_daily_sale_detail->name),
                'is_other_sales' => (int) $dbr_daily_sale_detail->is_other_sales,
                'operator_id' => ($is_new ? '' : (isset($dbr_daily_sale_detail->operator_id)  ? $dbr_daily_sale_detail->operator_id : '') ),
                'cash_number' => ($is_new ? '' : (isset($dbr_daily_sale_detail->cash_number) && $dbr_daily_sale_detail->cash_number > 0 ? $dbr_daily_sale_detail->cash_number : '') ),
                'opening_cash' => ($is_new ? '' : (isset($dbr_daily_sale_detail->opening_cash) && $dbr_daily_sale_detail->opening_cash > 0 ? $dbr_daily_sale_detail->opening_cash : '') ),
                'closing_cash' => ($is_new ? '' : (isset($dbr_daily_sale_detail->closing_cash) && $dbr_daily_sale_detail->closing_cash > 0 ? $dbr_daily_sale_detail->closing_cash : '') ),
                'master_card_amount' => ($is_new ? '' : (isset($dbr_daily_sale_detail->master_card_amount) && $dbr_daily_sale_detail->master_card_amount > 0 ? $dbr_daily_sale_detail->master_card_amount : '') ),
                'visa_amount' => ($is_new ? '' : (isset($dbr_daily_sale_detail->visa_amount) && $dbr_daily_sale_detail->visa_amount > 0 ? $dbr_daily_sale_detail->visa_amount : '') ),
                'web_payment' => ($is_new ? '' : (isset($dbr_daily_sale_detail->web_payment) && $dbr_daily_sale_detail->web_payment > 0 ? $dbr_daily_sale_detail->web_payment : '') ),
                'retirement_amount_pen' => ($is_new ? '' : (isset($dbr_daily_sale_detail->retirement_amount_pen) ? $dbr_daily_sale_detail->retirement_amount_pen : '') ),
                'retirement_amount_dol' => ($is_new ? '' : (isset($dbr_daily_sale_detail->retirement_amount_dol) ? $dbr_daily_sale_detail->retirement_amount_dol : '') ),
                'total_calculated' => ($is_new ? '' : (isset($dbr_daily_sale_detail->total_calculated) ? $dbr_daily_sale_detail->total_calculated : '') ),
                'total_x_format' => ($is_new ? '' : (isset($dbr_daily_sale_detail->total_x_format) ? $dbr_daily_sale_detail->total_x_format : '') ),
                'difference_money' => ($is_new ? '' : (isset($dbr_daily_sale_detail->difference_money) ? $dbr_daily_sale_detail->difference_money : '') ),
                'difference_values' => ($is_new ? '' : (isset($dbr_daily_sale_detail->difference_values) ? $dbr_daily_sale_detail->difference_values : '') ),
                'num_transacctions' =>  ($is_new ? '' : (isset($dbr_daily_sale_detail->num_transacctions) ? intval($dbr_daily_sale_detail->num_transacctions) : '') ),
                'hour_by_cash' => ($is_new ? '' : (isset($dbr_daily_sale_detail->hour_by_cash) ? intval($dbr_daily_sale_detail->hour_by_cash) : '') ),);

            if (!$is_new && isset($dbr_daily_sale_detail->id)) {
                $data_daily_sale_ids[] = $dbr_daily_sale_detail->type_of_sales_id;
                $data_daily_sale[$index] = array_merge($data_daily_sale[$index], array('id' => (int) $dbr_daily_sale_detail->id));
            }

            $index ++;
        }

        if(count($data_daily_sale_ids) > 0){
            $data_daily_sale_ids = array_unique($data_daily_sale_ids);
            
            foreach ($data_daily_sale as $key => $data) {
                if(in_array($data['type_of_sales_id'], $data_daily_sale_ids) && !isset($data['id'])){
                    unset($data_daily_sale[$key]);
                }
            }
            
        }

        $data['is_readonly'] = 0;
        $data['status'] = Status::STATUS_ABIERTO;
        if ($dbr_daily_sale) {
            $data['dailySaleId'] = $dbr_daily_sale->id;
            $data['is_readonly'] = (int) ($dbr_daily_sale->status == Status::STATUS_ABIERTO ? 0 : 1);
            $data['status'] = $dbr_daily_sale->status;
        }

        $data_daily_sale[] = array(
            'status' => '',
            'name' => 'Totales del Día',
            'is_other_sales' => 2,
            'total_opening_cash' => (isset($dbr_daily_sale->total_opening_cash) ? 'S/. ' . number_format($dbr_daily_sale->total_opening_cash, 2) : ''),
            'total_closing_cash' => (isset($dbr_daily_sale->total_closing_cash) ? 'S/. ' . number_format($dbr_daily_sale->total_closing_cash, 2) : ''),
            'total_master_card' => (isset($dbr_daily_sale->total_master_card) ? 'S/. ' . number_format($dbr_daily_sale->total_master_card, 2) : ''),
            'total_visa_card' => (isset($dbr_daily_sale->total_visa_card) ? 'S/. ' . number_format($dbr_daily_sale->total_visa_card, 2) : ''),
            'total_web_payment' => (isset($dbr_daily_sale->total_web_payment) ? 'S/. ' . number_format($dbr_daily_sale->total_web_payment, 2) : ''),
            'total_retirement_pen' => (isset($dbr_daily_sale->total_retirement_pen) ? 'S/. ' . number_format($dbr_daily_sale->total_retirement_pen, 2) : ''),
            'total_retirementl_dol' => (isset($dbr_daily_sale->total_retirementl_dol) ? 'S/. ' . number_format($dbr_daily_sale->total_retirementl_dol, 2) : ''),
            'grand_total_calculated' => (isset($dbr_daily_sale->grand_total_calculated) ? 'S/. ' . number_format($dbr_daily_sale->grand_total_calculated, 2) : ''),
            'grand_total_z_format' => (isset($dbr_daily_sale->grand_total_z_format) ? 'S/. ' . number_format($dbr_daily_sale->grand_total_z_format, 2) : ''),
            'total_difference_money' => (isset($dbr_daily_sale->total_difference_money) ? 'S/. ' . number_format($dbr_daily_sale->total_difference_money, 2) : ''),
            'total_diferrence_values' => (isset($dbr_daily_sale->total_diferrence_values) ? 'S/. ' . number_format($dbr_daily_sale->total_diferrence_values, 2) : ''),
            'total_num_transactions' => (isset($dbr_daily_sale->total_num_transactions) ? intval($dbr_daily_sale->total_num_transactions) : ''),
            'total_hours_by_cash' => (isset($dbr_daily_sale->total_hours_by_cash) ? intval($dbr_daily_sale->total_hours_by_cash) : '')
        );

        $data['dailySale'] = $data_daily_sale;

        $this->layout->view('daily_sales/maintenance_template', $data);
    }

    public function closeDailySale($daily_sale_id) {

        if (!$daily_sale_id) {
            $this->session->set_flashdata('message_danger', 'No tiene registrada ninguna venta');
            redirect('DailySales/index');
        }

        $dbr_daily_sale = $this->DailySaleDao->getDailySaleById($daily_sale_id);

        if (!$dbr_daily_sale) {
            $this->session->set_flashdata('message_danger', 'No tiene registrada ninguna venta');
            redirect('DailySales/index');
        }

        $dbr_daily_sale_current = $this->DailySaleDao->getDailySaleByDateSale();

        if ($dbr_daily_sale_current && $dbr_daily_sale_current->id == $dbr_daily_sale->id && $dbr_daily_sale->status == Status::STATUS_CERRADO) {
            $this->session->set_flashdata('message_danger', 'Ya existe una venta registrada en el día2');
            redirect('DailySales/index');
        }
        unset($dbr_daily_sale_current);

        if ($dbr_daily_sale->status == Status::STATUS_CERRADO) {
            $this->session->set_flashdata('message_danger', 'Esta venta ya se encuentra registrado en estado ' . Status::getStatusLabel(Status::STATUS_CERRADO));
            redirect('DailySales/index');
        }

        $this->DailySaleDao->saveDailySale(array('status' => Status::STATUS_CERRADO), $dbr_daily_sale->id);
        $this->session->set_flashdata('message_success', 'Venta Registrada Satisfactoriamente');
        redirect('DailySales/index');
    }

    public function processForm() {
        try {
            $daily_sale_credentials = $this->input->post();
            $data_daily_sale = array();
            $response = array();

            $daily_sale_id = ($daily_sale_credentials['daily_sale_id'] > 0 ? $daily_sale_credentials['daily_sale_id'] : null);
            $daily_sale_detail_id = ($daily_sale_credentials['daily_sale_detail_id'] > 0 ? $daily_sale_credentials['daily_sale_detail_id'] : null);
            $operator_id = isset($daily_sale_credentials['operator_id']) ? $daily_sale_credentials['operator_id'] : 0;
            $status = (isset($daily_sale_credentials['is_close']) && $daily_sale_credentials['is_close'] == 1) ? Status::STATUS_CERRADO : Status::STATUS_ABIERTO;

            $dbr_daily_sale = $this->DailySaleDao->getDailySaleById($daily_sale_id);

            $data_daily_sale['status'] = $status;
            if (count($dbr_daily_sale) == 0) {
                $data_daily_sale['subsidiaries_id'] = $this->loggedin['subsidiaries']; // aqui va ir la subsidaria depednde del perfil del usuario
                $data_daily_sale['date_sale'] = date('Y/m/d');
                $response['daily_sale_id'] = $this->DailySaleDao->saveDailySale($data_daily_sale);
            } else {
                if (array_key_exists('data_headers', $daily_sale_credentials)) {
                    $response['daily_sale_id'] = $this->DailySaleDao->saveDailySale($daily_sale_credentials['data_headers'], $dbr_daily_sale->id);
                } else {
                    $response['daily_sale_id'] = $dbr_daily_sale->id;
                }
            }

            if (count($dbr_daily_sale) > 0 && $status == Status::STATUS_CERRADO) {
                $daily_sale_credentials['data']['status'] = $status;
                unset($daily_sale_credentials['data']['is_other_sales']);
                unset($daily_sale_credentials['data']['name']);

                $response['daily_sale_id'] = $this->DailySaleDao->saveDailySale($daily_sale_credentials['data'], $dbr_daily_sale->id);
            }

            if ($response['daily_sale_id'] && $status == Status::STATUS_ABIERTO) {
                $daily_sale_credentials['data']['daily_sales_id'] = $response['daily_sale_id'];
                $daily_sale_credentials['data']['operator_id'] = $operator_id;
                unset($daily_sale_credentials['data']['is_other_sales']);
                unset($daily_sale_credentials['data']['name']);
                unset($daily_sale_credentials['data']['id']);
                $response['daily_sale_detail_id'] = $this->DailySaleDao->saveDailySaleDetail($daily_sale_credentials['data'], $daily_sale_detail_id);
            }

            $response['status'] = 0;
            $response['id'] = date('s');
            $response['url_close_daily_sale'] = site_url('DailySales/closeDailySale/' . $response['daily_sale_id']);
            header("Content-type: application/json");
            echo json_encode($response);
        } catch (Exception $e) {
            echo $e;
        }
    }

    public function getJSONOperators() {
        try {
            $this->load->database();
            $this->load->model('UserDao');
            $array_operators = $this->UserDao->getOperatorUsers();

            $array_operator_names = array();
            $array_operator_names['ids'] = $array_operators;
            foreach ($array_operators as $operator) {
                $full_name = trim($operator['full_name']);
                if (strlen($full_name) == 0) {
                    continue;
                }
                $this->UserDao->get_user_id_by_name($full_name);
                $array_operator_names['full_names'][] = $operator['full_name'];
            }
            header("Content-type: application/json");
            echo json_encode($array_operator_names);
        } catch (Exception $e) {
            echo $e;
        }
    }

    public function getJSONDailySaleOthers() {
        try {
            $this->load->database();
            $this->load->model('DailySaleDao');
            $array_daily_sale_others = $this->DailySaleDao->getDailySaleOthers();

            /* $array_operator_names = array();
              $array_operator_names['ids'] = $array_operators;
              foreach ($array_operators as $operator) {
              $full_name = trim($operator['full_name']);
              if (strlen($full_name) == 0) {
              continue;
              }
              $this->UserDAO->get_user_id_by_name($full_name);
              $array_operator_names['full_names'][] = $operator['full_name'];
              } */
            header("Content-type: application/json");
            echo json_encode($array_daily_sale_others);
        } catch (Exception $e) {
            echo $e;
        }
    }

}
