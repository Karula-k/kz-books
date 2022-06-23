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
    $begin = isset($_GET['begin']) ? $_GET['begin']:0;
    $data = mysqli_query($this->conn,"select * from titles order by title asc Limit {$begin},9");
    $hasil=[];
		while($d = mysqli_fetch_array($data,MYSQLI_ASSOC)){
      if($d['image']==null){
        $d['image'] = file_exists("assets/img/{$d['title_id']}.jpg")?"assets/img/{$d['title_id']}.jpg":"https://thumbs.dreamstime.com/b/no-image-available-icon-flat-vector-no-image-available-icon-flat-vector-illustration-132482953.jpg";
      }
      $hasil[] = $d;
		}
    header("Content-Type: application/json");
    echo json_encode($hasil);
    // return $hasil;
  }
  function topLimit() {
    $data = mysqli_query($this->conn,"select * from titles order by title_id desc Limit 5");
    $hasil=[];
		while($d = mysqli_fetch_array($data,MYSQLI_ASSOC)){
      if($d['image']==null){
        $d['image'] = file_exists("assets/img/{$d['title_id']}.jpg")?"assets/img/{$d['title_id']}.jpg":"https://thumbs.dreamstime.com/b/no-image-available-icon-flat-vector-no-image-available-icon-flat-vector-illustration-132482953.jpg";
      }
      $hasil[] = $d;
		}
    header("Content-Type: application/json");
    echo json_encode($hasil);
    // return $hasil;
  }
  function search($name = "",$sort ='title',$order='ASC'){
    $query = "SELECT * FROM titles WHERE title = '{$name}' order by {$sort} {$order}";
    $data = mysqli_query($this->conn,$query);
    $hasil=[];
    while($d = mysqli_fetch_array($data,MYSQLI_ASSOC)){
      if($d['image']==null){
        $d['image'] = file_exists("assets/img/{$d['title_id']}.jpg")?"assets/img/{$d['title_id']}.jpg":"https://thumbs.dreamstime.com/b/no-image-available-icon-flat-vector-no-image-available-icon-flat-vector-illustration-132482953.jpg";
      }
      $hasil[] = $d;
    }
    header("Content-Type: application/json");
    echo json_encode($hasil);
    // return $hasil;
  }
  function detail($title_id) {
    $data = mysqli_query($this->conn,"select * from titles where title_id ={$title_id}");
    $hasil=[];
    while($d = mysqli_fetch_array($data,MYSQLI_ASSOC)){
      if($d['image']==null){
        $d['image'] = file_exists("assets/{$d['title_id']}.jpg")?"assets/{$d['title_id']}.jpg":"https://thumbs.dreamstime.com/b/no-image-available-icon-flat-vector-no-image-available-icon-flat-vector-illustration-132482953.jpg";
      }
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
    // $query = "INSERT INTO titles VALUES(null,null,?,?,?,?,?)";
    $title= $data['title'];
    $type = $data['type'];
    $pub_id = $data['pub_id'];
    $price = $data['price'];
    $sinopsis = $data['sinopsis'];
    $relese_date =$data['relese_date'];
    $query = mysqli_query($this->conn, "INSERT INTO titles(title,type,pub_id,price,sinopsis,relese_date) VALUES('$title','$type','$pub_id','$price','$sinopsis','$relese_date')");
    if ($query)
    {
      echo " Data Berhasil";
    }
    else{
    echo " Data Gagal :" . mysqli_error($this->conn);
    }
  //   $sql = $this->conn->prepare($query);
  //   $sql->bind_param('ssiis',
  //   $title,$type,$pub_id,$price,$relese_date);
  try {

  } catch (\Exception $e) {
    $sql->close();
    http_response_code(500);
    die($e->getMassage());
  }
  $title_id =$this->conn->insert_id;
  echo $title_id;
  $this->conn->close();
  return $title_id;
  }
  function update($data){
    foreach ($data as $key => $value) {
      $value = is_array($value)?trim(implode(",",$value)):trim($value);
      $data[$key] = (strlen($value)>0?$value:NULL);
    }
    $query = "UPDATE `titles` SET `title`=?,`type`=?,`pub_id`=?,`price`=?,`sinopsis`=?,`relese_date`=? WHERE title_id=?";
    $sql = $this->conn->prepare($query);
    $sql->bind_param('ssiisi',
    $data['title'],
    $data['type'],
    $data['pub_id'],
    $data['price'],
    $data['sinopsis'],
    $data['relese_date'],
    $data['title_id']
  );
  try {
    $sql->execute();
  } catch (\Exception $e) {
    $sql->close();
    http_response_code(500);
    die($e->getMassage());
  }
  $title_id = $sql->insert_id;
  $sql->close();
  }
  function delete($title_id){
    $command = "delete from titles where title_id={$title_id}";
    $query = mysqli_query($this->conn, $command);
    if ($query)
    {
      echo " Data Berhasil";
    }
    else{
    echo " Data Gagal :" . mysqli_error($this->conn);
    }
  }
}
$db = new Mysql();
switch ($_GET['action']) {
  case 'create':
    $title_id = $db->create($_POST);
    move_uploaded_file($_FILES['file']['tmp_name'],"assets/img/{$title_id}.jpg");
    break;
  case 'detail':
    $db->detail($_GET['title_id']);
    break;
  case 'update':
    move_uploaded_file($_FILES['file']['tmp_name'],"assets/img/{$_POST['title_id']}.jpg");
    $db->update($_POST);
    break;
  case 'delete':
    $db->delete($_GET['title_id']);
    // $assets = getcwd() ."assets/img/$_GET['title_id'].jpg"
    // unlink($assets);
    header("Location: " . $_SERVER["HTTP_REFERER"]);
    break;
  case 'search':
    $db->search((isset($_GET['name']) ? $_GET['name']:'""'),(isset($_GET['sort']) ? $_GET['sort']:'title'),(isset($_GET['order']) ? $_GET['order']:'asc'));
    break;
  case 'topLimit':
    $db->topLimit();
    break;
  default:
    $db->read();
    break;
}
?>
