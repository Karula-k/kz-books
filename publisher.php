<?php


class Mysql{
  var $servername = "localhost";
  var $username = "root";
  var $password = "";
  var $dbname = "worldbook";
  var $port = "3307";
  var $conn='';


  function __construct(){
    $this->conn = mysqli_connect($this->servername, $this->username, $this->password, $this->dbname,$this->port);
    if (mysqli_connect_errno()){
      echo "Koneksi database gagal : " . mysqli_connect_error();
    }
  }
  function read() {
    $data = mysqli_query($this->conn,"select * from publisher order by pub_name asc");
    $hasil=[];
		while($d = mysqli_fetch_array($data,MYSQLI_ASSOC)){
			$hasil[] = $d;
		}
    header("Content-Type: application/json");
    echo json_encode($hasil);
    // return $hasil;
  }
  function detail($pub_id) {
    $data = mysqli_query($this->conn,"select * from publisher where pub_id ={$pub_id}");
    $hasil=[];
    while($d = mysqli_fetch_array($data,MYSQLI_ASSOC)){
      $hasil[] = $d;
    }
    header("Content-Type: application/json");
    echo json_encode($hasil[0]);
    // return $hasil;
  }
  function create($data){
    foreach ($data as $key => $value) {
      $value = is_array($value)?trim(implode(",",$value)):trim($value);
      $data[$key] = (strlen($value)>0?$value:NULL);
    }
    $query = "insert into publisher values(null,?,?,?)";
    $sql = $this->conn->prepare($query);
    $sql->bind_param('sss',
    $data['pub_name'],
    $data['city'],
    $data['country']
  );
  try {
    $sql->execute();
  } catch (\Exception $e) {
    $sql->close();
    http_response_code(500);
    die($e->getMassage());
  }
  $pub_id = $sql->insert_id;
  $sql->close();
  }
  function update($data){
    foreach ($data as $key => $value) {
      $value = is_array($value)?trim(implode(",",$value)):trim($value);
      $data[$key] = (strlen($value)>0?$value:NULL);
    }
    $query = "update publisher set pub_name=?, city=?,country =?,where title_id=?";
    $sql = $this->conn->prepare($query);
    $sql->bind_param('sssi',
    $data['pub_name'],
    $data['city'],
    $data['country'],
    $data['title_id']
  );
  try {
    $sql->execute();
  } catch (\Exception $e) {
    $sql->close();
    http_response_code(500);
    die($e->getMassage());
  }
  $pub_id = $sql->insert_id;
  $sql->close();
  }
  function delete($pub_id){
    $query = "delete from publisher where pub_id=?";
    $sql = $this->conn->prepare($query);
    $sql->bind_param('i',
    $data['pub_id']
  );
  try {
    $sql->execute();
  } catch (\Exception $e) {
    $sql->close();
    http_response_code(500);
    die($e->getMassage());
  }
  $pub_id = $sql->insert_id;
  $sql->close();
  }
}
$db = new Mysql();
switch ($_GET['action']) {
  case 'create':
    $db->create($_POST);
    break;
  case 'detail':
    $db->detail($_GET['pub_id']);
    break;
  case 'update':
    $db->update($_POST);
    break;
  case 'delete':
    $db->delete($_GET['pub_id']);
    header("Location: " . $_SERVER["HTTP_REFERER"]);
    break;
  default:
    $db->read();
    break;
}
?>
