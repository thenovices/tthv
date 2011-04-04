<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Api extends Controller {
  public function action_index() {
    error_reporting(E_ALL);
    ini_set('log_errors', '1');
    if (!isset($_POST['action'])) {
      echo json_encode(array("success" => false, "errors" => array("Please provide an API action")));
      return;
    }
    switch ($_POST['action']) {
      // Gets all the patients in a village
      // Required fields:
      // Description | field_name | required_attributes
      // Village Name | village_name | not_empty
    case "getCasesByVillageName":
      $post = Validate::factory($_POST);
      $post->filter(TRUE, 'trim');
      $post->rule('village_name', 'not_empty');

      if ($post->check()) {
        $cases = Model::factory('case')->select_by_village_name($post['village_name']);
        return json_encode(array("success" => true, cases => $cases->as_array()));
      }
      else {
        $errors = $post->errors('validate');
        echo json_encode(array("success" => false, "errors" => $errors));
      }
      break;
      // Adds a new patient
      // Required fields:
      // Description | field_name | required_attributes
      // Patient Name | patient_name | not_empty
      // Village Name | village_name | not_empty
      // Primary Health Center Name | phc_name | not_empty, alphanum
      // Mobile number | mobile | 10 digits
    case "addCase":
      $post = Validate::factory($_POST);
      $post->filter(TRUE, 'trim');
      $post->rule('patient_name', 'not_empty');
      $post->rule('village_name', 'not_empty');
      $post->rule('phc_name', 'not_empty');
      $post->rule('phc_name', 'alpha_numeric');
      $post->rule('mobile', 'not_empty');
      $post->rule('mobile', 'numeric');
      $post->rule('mobile', 'exact_length', array(10));

      if ($post->check()) {
        list($case_id, $num_rows) = Model::factory('case')->add($post->as_array());
        echo json_encode(array("success" => true, "case_id" => $case_id));
      }
      else {
        $errors = $post->errors('validate');
        echo json_encode(array("success" => false, "errors" => $errors));
      }
      break;
      // Adds a child for a case
      // Required fields:
      // Description | field_name | required_attributes
      // Child Name | child_name | not_empty
      // Birth Date | birth_date | date (can be accepted by date())
      // Patient Case Id | case_id | not_empty
    case "addChild":
      $post = Validate::factory($_POST);
      $post->filter(TRUE, 'trim');
      $post->rule('child_name', 'not_empty');
      $post->rule('birth_date', 'not_empty');
      $post->rule('case_id', 'not_empty');
      $post->rule('birth_date', 'date');

      if ($post->check()) {
        Model::factory('appointment')->add_child($_POST);
        echo json_encode(array("success" => true));
      }
      else {
        $errors = $post->errors('validate');
        echo json_encode(array("success" => false, "errors" => $errors));
      }
      break;
      // Adds one appointment for a patient
      // Required fields:
      // Description | field_name | required_attributes
      // Child Name | child_name | not_empty
      // Appointment Date | date | date (must be accepted by date())
      // Reminder Message | message | not_empty, less than 150 chars
      // Patient Case Id | case_id | not_empty
    case "addAppointment":
      $post = Validate::factory($_POST);
      $post->filter(TRUE, 'trim');
      $post->rule('child_name', 'not_empty');
      $post->rule('date', 'not_empty');
      $post->rule('case_id', 'not_empty');
      $post->rule('date', 'date');
      $post->rule('message', 'not_empty');
      $post->rule('message', 'max_length', array(150));

      if ($post->check()) {
        $appts = Model::factory('appointment');
        $_POST['date'] = strtotime($_POST['date']);
        list($id, $num_rows) = $appts->add_appointment($_POST);
        echo json_encode(array("success" => true, "id" => $id));
      }
      else {
        $errors = $post->errors('validate');
        echo json_encode(array("success" => false, "errors" => $errors));
      }
      break;
    case "addCaseWithChildrenAndAppointments":
      // Check case data
      $post = Validate::factory($_POST);
      $post->filter(TRUE, 'trim');
      $post->rule('patient_name', 'not_empty');
      $post->rule('village_name', 'not_empty');
      $post->rule('phc_name', 'not_empty');
      $post->rule('phc_name', 'alpha_numeric');
      $post->rule('mobile', 'not_empty');
      $post->rule('mobile', 'numeric');
      $post->rule('mobile', 'exact_length', array(10));

      if (!$post->check()) {
        $errors = $post->errors('validate');
        echo json_encode(array("success" => false, "errors" => $errors));
      }

      // Check children data
      if (isset($post['children'])) {
        foreach(json_decode($post['children']) as $child) {
          $post = Validate::factory($child);
          $post->filter(TRUE, 'trim');
          $post->rule('child_name', 'not_empty');
          $post->rule('birth_date', 'not_empty');
          $post->rule('case_id', 'not_empty');
          $post->rule('birth_date', 'date');

          if (!$post->check()) {
            $errors = $post->errors('validate');
            echo json_encode(array("success" => false, "errors" => $errors));
          }
        }
      }
      
      // Check appointment data
      if (isset($post['appointments'])) {
        foreach(json_decode($post['appointments']) as $appt) {
          $post = Validate::factory($appt);
          $post->filter(TRUE, 'trim');
          $post->rule('child_name', 'not_empty');
          $post->rule('date', 'not_empty');
          $post->rule('case_id', 'not_empty');
          $post->rule('date', 'date');
          $post->rule('message', 'not_empty');
          $post->rule('message', 'max_length', array(150));

          if (!$post->check()) {
            $errors = $post->errors('validate');
            echo json_encode(array("success" => false, "errors" => $errors));
          }
        }
      }

      // All validation has passed. Let's insert the data!
      $caseModel = Model::factory('case');
      $apptModel = Model::factory('appointment');
      list($case_id, $num_rows) = $caseModel->add_case($post);

      if (isset($post['children'])) {
        foreach(json_decode($post['children']) as $child) {
          $apptModel->add_child($child);
        }
      }
      if (isset($post['appointments'])) {
        foreach(json_decode($post['appointments']) as $appt) {
          $apptModel->add_appointment($appt);
        }
      }
      echo json_encode(array("success" => true, "case_id" => $case_id));
      break;
      // Gets all the appointments for a patient
      // Required fields:
      // Description | field_name | required_attributes
      // Patient Case Id | case_id | not_empty, digit
    case "getAppointments":
      $post = Validate::factory($_POST);
      $post->filter(TRUE, 'trim');
      $post->rule('case_id', 'not_empty');
      $post->rule('case_id', 'digit');

      if ($post->check()) {
        $appointments = Model::factory('appointment')->select_by_case_id($_POST['case_id']);
        echo json_encode(array("success" => true, "appointments" => $appointments->as_array()));
      }
      else {
        $errors = $post->errors('validate');
        echo json_encode(array("success" => false, "errors" => $errors));
      }
      break;
      // Checks in for an appointment
      // Required fields:
      // Description | field_name | required_attributes
      // Appointment Id | id | not_empty, digit
    case "checkInAppointment":
      $post = Validate::factory($_POST);
      $post->filter(TRUE, 'trim');
      $post->rule('id', 'not_empty');
      $post->rule('id', 'digit');

      if ($post->check()) {
        Model::factory('appointment')->check_in($_POST['id']);
        echo json_encode(array("success" => true));
      }
      else {
        $errors = $post->errors('validate');
        echo json_encode(array("success" => false, "errors" => $errors));
      }
      break;
    case "getCasesToday":
      $cases = Model::factory('case')->select_with_appts_today();
      echo json_encode(array("success" => true, "cases" => $cases->as_array()));
      break;
    case "getCasesOverdueByVillage":
      $villages = Model::factory('case')->select_overdue_by_village();
      echo json_encode(array("success" => true, "villages" => $villages->as_array()));
      break;
    case "getCasesNextWeek":
      $cases = Model::factory('case')->select_with_appts_this_week();
      echo json_encode(array("success" => true, "cases" => $cases->as_array()));
      break;
    case "getCasesNextWeekByVillage":
      $cases = Model::factory('case')->select_with_appts_this_week_by_village();
      echo json_encode(array("success" => true, "cases" => $cases->as_array()));
      break;
    case "getCasesOverdueLastWeek":
      $cases = Model::factory('case')->select_overdue_last_week();
      echo json_encode(array("success" => true, "cases" => $cases->as_array()));
      break;
    case "getCasesOverdueLastWeekByVillage":
      $cases = Model::factory('case')->select_overdue_last_week_by_village();
      echo json_encode(array("success" => true, "cases" => $cases->as_array()));
      break;
    default:
      echo json_encode(array("success" => false, "errors" => array("Please use a valid API action")));
    }
  }

}
