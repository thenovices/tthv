<?php defined('SYSPATH') or die('No direct script access.');

class Model_Case extends Model {
	public function select_phcs() {
		return DB::query(Database::SELECT, 'SELECT COUNT(1) as total, phc_name FROM cases GROUP BY phc_name')->execute();
	}

	public function select_by_phc_name($phc_name) {
		return DB::query(Database::SELECT, 'SELECT * FROM cases WHERE phc_name=:phc_name')
			->param(':phc_name', $phc_name)
			->execute();
	}

  public function select_by_village_name($village_name) {
    return DB::query(Database::SELECT,
      'SELECT * FROM cases WHERE village_name=:name')
      ->param(':name', $village_name)
      ->execute();
  }

	public function select_by_id($id) {
    // return DB::query(Database::SELECT, 'SELECT TOP(1) * FROM cases WHERE id=:id')
    return DB::query(Database::SELECT, 'SELECT * FROM cases WHERE id=:id LIMIT 1')
      ->param(':id', $id)
      ->execute()
      ->current();
	}

  public function select_with_appts_today() {
    return DB::query(Database::SELECT, 'SELECT c.id, c.patient_name, c.village_name, 
      c.phc_name, c.location, a.date, a.treatment FROM cases c INNER JOIN appointments a
      ON c.id = a.case_id WHERE a.date = :today')
      ->param(':today', strtotime("today"))
      ->execute();
  }

  public function select_overdue() {
    return DB::query(Database::SELECT, 'SELECT c.id, c.patient_name, c.village_name, c.phc_name, c.location, a.date, a.treatment
      FROM cases c INNER JOIN appointments a ON c.id = a.case_id WHERE a.checked_in = 0
      ORDER BY a.date ASC')
      ->execute();
  }

  public function select_overdue_by_village() {
    return DB::query(Database::SELECT, 'SELECT c.village_name, COUNT(1) as total
      FROM cases c INNER JOIN appointments a ON c.id = a.case_id WHERE a.checked_in = 0
      GROUP BY c.village_name')
      ->execute();
  }

  public function select_with_appts_this_week() {
    return DB::query(Database::SELECT, 'SELECT DISTINCT c.id, c.patient_name, c.village_name,
      c.phc_name, c.location, a.date, a.treatment FROM cases c INNER JOIN appointments a ON c.id=a.case_id
      WHERE a.checked_in = 0 AND a.date >= :today AND a.date < :nextWeek
      ORDER BY a.date ASC')
      ->param(':today', strtotime("today"))
      ->param(':nextWeek', strtotime("+1 week", strtotime("today")))
      ->execute();
  }

  public function select_with_appts_this_week_by_village() {
    return DB::query(Database::SELECT, 'SELECT c.village_name, COUNT(1) as total 
      FROM cases c INNER JOIN appointments a ON c.id=a.case_id WHERE a.checked_in = 0 
      AND a.date >= :today AND a.date < :nextWeek GROUP BY c.village_name')
      ->param(':today', strtotime("today"))
      ->param(':nextWeek', strtotime("+1 week", strtotime("today")))
      ->execute();
  }

  public function select_with_appts_next_week() {
    return DB::query(Database::SELECT, 'SELECT c.id, c.patient_name, c.village_name, c.phc_name, c.location, a.date, a.treatment
      FROM cases c INNER JOIN appointments a ON c.id=a.case_id WHERE
      a.date >= :7days AND a.date < :14days ORDER BY a.date ASC')
      ->param(':7days', strtotime("+1 week", strtotime("today")))
      ->param(':14days', strtotime("+2 weeks", strtotime("today")))
      ->execute();
  }

  public function select_with_appts_next_week_by_village() {
    return DB::query(Database::SELECT, 'SELECT c.village_name, COUNT(1) as total
      FROM cases c INNER JOIN appointments a ON c.id=a.case_id WHERE
      a.date >= :7days AND a.date < :14days GROUP BY c.village_name')
      ->param(':7days', strtotime("+1 week", strtotime("today")))
      ->param(':14days', strtotime("+2 weeks", strtotime("today")))
      ->execute();
  }

  public function select_overdue_last_week() {
    return DB::query(Database::SELECT, 'SELECT DISTINCT c.id, c.patient_name, c.village_name,
      c.phc_name, c.location, a.date, a.treatment FROM cases c INNER JOIN appointments a ON c.id=a.case_id
      WHERE a.checked_in = 0 AND a.date < :today AND a.date > :lastWeek
      ORDER BY a.date ASC')
      ->param(':today', strtotime("today"))
      ->param(':lastWeek', strtotime("-1 week", strtotime("today")))
      ->execute();
  }

  public function select_overdue_last_week_by_village() {
    return DB::query(Database::SELECT, 'SELECT c.village_name, COUNT(1) as total
      FROM cases c INNER JOIN appointments a ON c.id=a.case_id
      WHERE a.checked_in = 0 AND a.date < :today AND a.date > :lastWeek
      GROUP BY c.village_name')
      ->param(':today', strtotime("today"))
      ->param(':lastWeek', strtotime("-1 week", strtotime("today")))
      ->execute();
  }

	public function add_case($post) {
    $id = DB::insert('cases', array_keys($post))
      ->values($post)
      ->execute();
		return $id;
	}

  public function get_villages() {
    return DB::query(Database::SELECT, 'SELECT DISTINCT village_name FROM cases')
      ->execute();
  }

  public function get_villages_like($term='') {
    $term .= '%';
    return DB::query(Database::SELECT, 'SELECT DISTINCT village_name FROM cases WHERE village_name LIKE :term')
      ->param(':term', $term)
      ->execute();
  }

  public function get_phcs() {
    return DB::query(Database::SELECT, 'SELECT DISTINCT phc_name FROM cases')
      ->execute();
  }

  public function get_phcs_like($term='') {
    $term .= '%';
    return DB::query(Database::SELECT, 'SELECT DISTINCT phc_name FROM cases WHERE phc_name LIKE :term')
      ->param(':term', $term)
      ->execute();
  }
}
