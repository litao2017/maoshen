<?php
namespace app\admin\controller;
use app\admin\controller\Admin;
use think\Request;

class Database extends Admin {
   
    public function _initialize(){
      parent::_initialize();
        $this->Model=new \app\admin\model\Database();
    }
   public function showlist(){
 
      
      $keyword=Request::instance()->param('keyword','','addslashes');
	   
      $database=$this->Model->get_list($keyword);
     
      $this->assign("database_list",$database['list']);
     
	  $this->assign("html_page",$database['html_page']);
      
	     return $this->fetch();
    }
    

 public function copy_database(){
	 
   $database=config('database');
   
  	//test($database);
   
  $dbname=$database['database'];
  $user=$database['username'];
  $password=$database['password'];
  $host = $database['hostname'];
//pp($host.' '.$user.' '.$password);
// 这里的账号、密码、名称都是从页面传过来的
$conn=mysqli_connect($host,$user,$password);

if (!$conn) // 连接mysql数据库
    {
        echo mysqli_errno().' '.mysqli_error();
    exit;
} 
if (!mysqli_select_db($conn,$dbname)) // 是否存在该数据库
    {
        echo '不存在数据库:' . $dbname . ',请核对后再试';
    exit;
} 
mysqli_query($conn,"set names 'utf8'");
$mysql = "set charset utf8;\r\n";
$q1 = mysqli_query($conn,"show tables");
while ($t = mysqli_fetch_array($q1))
{
    $table = $t[0];
    $q2 = mysqli_query($conn,"show create table `$table`");
    $sql = mysqli_fetch_array($q2);
    $mysql .= $sql['Create Table'] . ";\r\n";
    $q3 = mysqli_query($conn,"select * from `$table`");
    while ($data = mysqli_fetch_assoc($q3))
    {
        $keys = array_keys($data);
        $keys = array_map('addslashes', $keys);
        $keys = join('`,`', $keys);
        $keys = "`" . $keys . "`";
        $vals = array_values($data);
        $vals = array_map('addslashes', $vals);
        $vals = join("','", $vals);
        $vals = "'" . $vals . "'";
        $mysql .= "insert into `$table`($keys) values($vals);\r\n";
    } 
} 
 $filename=$dbname . date('Ymjgi') . ".sql";
$file = DATABASE_PATH .$filename ; //存放路径，默认存放到项目最外层
$fp = fopen($file, 'w');
fputs($fp, $mysql);
fclose($fp);
$data['db_name']=$filename;
$data['create_time']=time();
$this->Model->add_database($data);
$this->error("数据备份成功",'showlist');
 }

 public function download(){  
 
		$db_id = input('db_id/d');
		
		$fileName = $this->Model->get_db($db_id);
		
		$fileUrl = DATABASE_PATH.$fileName;
		
		$data = file_get_contents($fileUrl);
		header("Content-type: application/octet-stream");
		header("Accept-Ranges: bytes");
		header("Accept-Length: ".filesize($fileUrl));
		header("Content-Disposition: attachment; filename=" . $fileName);
		echo $data;
		
		
		
 	
	
	}
	
	public function delete_ajax(){
		
		$db_id = input('db_id/d');
		
		$this->Model->del_db($db_id);
		
		$location = '/index.php/admin/database/showlist';
		
		return $location;
		
		}







 
}

