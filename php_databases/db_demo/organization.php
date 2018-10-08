<?php
class Organization implements JsonSerializable {
  public $profile_id;
  public $region;
  public $country;
  public $country_income_level;
  public $organization_name;
  public $organization_type;
  public $sectors;
  public $description;
  public $city;
  public $state_or_region;
  public $founding_year;
  public $size;
  public $type_of_data_used;

  private $from_map = array(
    'profileID' => 'profile_id',
    'Region' => 'region',
    'Country' => 'country',
    'Country Income Level' => 'country_income_level',
    'Organization Name' => 'organization_name',
    'Organization Type' => 'organization_type',
    'Sectors' => 'sectors',
    'Description' => 'description',
    'City' => 'city',
    'State/Region' => 'state_or_region',
    'Founding Year' => 'founding_year',
    'Size' => 'size',
    'Type of Data Used' => '
    ');

  private $to_map = array();

  function __construct(array $org_array) {
    foreach($this->from_map as $key => $val) {
      $this->to_map[$val] = $key;
    }
    foreach ($org_array as $key => $value) {
      if (property_exists($this, $key)) {
        $this->$key = $value;
      }
    }
  }

  public function jsonSerialize() {
    $json_assoc = array();
    foreach($this->from_map as $key => $val) {
      $json_assoc[$key] =  $this->$val;
    }
    return $json_assoc;
  }

  static function fetchOne(mysqli $conn, $profile_id) {
    $stmt = $conn->prepare("SELECT * FROM organizations WHERE profile_id = ?");
    $stmt->bind_param("i", $profile_id);
    $stmt->execute();
    if ($result = $stmt->get_result()) {
      $org = $result->fetch_assoc();
      return new Organization($org);
    } else {
      return false;
    }
  }

  static function fetchAll(mysqli $conn) {
    $orgs = array();
    $sql = 'SELECT * FROM organizations';
    if ($result = $conn->query($sql)) {
      while ($row = $result->fetch_assoc()) {
        $org_obj = new Organization($row);

        $orgs[] = $org_obj;
      }
    }
    return $orgs;
  }
  static function fetchMany(mysqli $conn, $offset = 0, $limit = -1) {
    $stmt = $conn->prepare("SELECT * FROM organizations LIMIT ?, ?");
    $stmt->bind_param("ii", $offset,  $limit);
    $stmt->execute();
    if ($result = $stmt->get_result()) {
      $orgs = array();
      while ($row = $result->fetch_assoc()) {
        $org_obj = new Organization($row);

        $orgs[] = $org_obj;
      }
      return $orgs;
    }
  }

  public function save(mysqli $conn) {
    if ($this->profile_id == 0) {
      $stmt = $conn->prepare("INSERT INTO organizations
                              (organization_name, organization_type, region,
                               country, country_income_level, sectors,
                               description, city, state_or_region,
                               founding_year, size, type_of_data_used)
                              VALUES (?, ?, ?,
                                      ?, ?, ?,
                                      ?, ?, ?,
                                      ?, ?, ?)");
      $stmt->bind_param("sssssssssiss",
                        $this->organization_name,
                        $this->organization_type,
                        $this->region,
                        $this->country,
                        $this->country_income_level,
                        $this->sectors,
                        $this->description,
                        $this->city,
                        $this->state_or_region,
                        $this->founding_year,
                        $this->size,
                        $this->type_of_data_used);
    } else {
      $stmt = $conn->prepare("UPDATE organizations SET
                              organization_name = ?, organization_type = ?,
                              region = ?, country = ?, country_income_level  = ?,
                              sectors = ?, description = ?, city  = ?,
                              state_or_region = ?, founding_year = ?, size = ?,
                              type_of_data_used = ?
                              WHERE profile_id = ?");
      $stmt->bind_param("sssssssssissi",
                        $this->organization_name,
                        $this->organization_type,
                        $this->region,
                        $this->country,
                        $this->country_income_level,
                        $this->sectors,
                        $this->description,
                        $this->city,
                        $this->state_or_region,
                        $this->founding_year,
                        $this->size,
                        $this->type_of_data_used,
                        $this->profile_id);
    }
    $stmt->execute();
    $stmt->close();
  }

  static function fetchTotalCount(mysqli $conn) {
    $sql = 'SELECT COUNT(*) FROM organizations';
    if ($result = $conn->query($sql)) {
      $row = $result->fetch_array(MYSQLI_NUM);
      return $row[0];
    } else {
      return 0;
    }
  }
}
 ?>
