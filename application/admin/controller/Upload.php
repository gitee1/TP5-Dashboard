<?php
namespace app\admin\controller;

use app\admin\controller\Base;
use app\admin\model\AdminNews;
use app\admin\model\AdminCases;
use think\Session;
use ueditor\Uploader;

/**
 * 百度ueditor上传，及案例/新闻等图片上传
 */
class Upload extends Base{
	/**
	 * 重写百度Ueditor上传方法
	 * 1、将ueditor/php下action_crawler.php/action_list.php/action_upload.php/
	 * 		controller.php四个文件放入控制器中，合并为一个文件。
	 *   	controller.php该为index(){},其他文件依次修改为函数。
	 * 2、同时注意引用原ueditor/php下Uploader.class.php。
	 * 3、注意：(1)-函数间的调用。
	 * 			(2)-每个方法开头新增获取$CONFIG的语句。
	 * 			(3)-注意原ueditor/php目录下config.json的目录，方便获取内容。
	 * 			(4)-修改后，改ueditor/ueditor.config.js中的serverUrl为新请求的文件。
	 */
	public function index(){
		date_default_timezone_set("Asia/Chongqing");
		error_reporting(E_ERROR);
		header("Content-Type: text/html; charset=utf-8");

		$CONFIG = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents(Env::get('app_path')."admin/config/ueditorconfig.json")), true);
		$action = $_GET['action'];

		switch ($action) {
		    case 'config':
		        $result =  json_encode($CONFIG);
		        break;

		    /* 上传图片 */
		    case 'uploadimage':
		    /* 上传涂鸦 */
		    case 'uploadscrawl':
		    /* 上传视频 */
		    case 'uploadvideo':
		    /* 上传文件 */
		    case 'uploadfile':
		        $result = $this->actionUpload();
		        break;

		    /* 列出图片 */
		    case 'listimage':
		        $result =$this->actionList();
		        break;
		    /* 列出文件 */
		    case 'listfile':
		        $result = $this->actionList();
		        break;

		    /* 抓取远程文件 */
		    case 'catchimage':
		        $result = $this->actionCrawler();
		        break;

		    default:
		        $result = json_encode(array(
		            'state'=> '请求地址出错'
		        ));
		        break;
		}

		/* 输出结果 */
		if (isset($_GET["callback"])) {
		    if (preg_match("/^[\w_]+$/", $_GET["callback"])) {
		        echo htmlspecialchars($_GET["callback"]) . '(' . $result . ')';
		    } else {
		        echo json_encode(array(
		            'state'=> 'callback参数不合法'
		        ));
		    }
		} else {
		    echo $result;
		}	
	}

	public function actionCrawler(){
		$CONFIG = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents(Env::get('app_path')."admin/config/ueditorconfig.json")), true);
		/* 上传配置 */
		$config = array(
		    "pathFormat" => $CONFIG['catcherPathFormat'],
		    "maxSize" => $CONFIG['catcherMaxSize'],
		    "allowFiles" => $CONFIG['catcherAllowFiles'],
		    "oriName" => "remote.png"
		);
		$fieldName = $CONFIG['catcherFieldName'];

		/* 抓取远程图片 */
		$list = array();
		if (isset($_POST[$fieldName])) {
		    $source = $_POST[$fieldName];
		} else {
		    $source = $_GET[$fieldName];
		}
		foreach ($source as $imgUrl) {
		    $item = new Uploader($imgUrl, $config, "remote");
		    $info = $item->getFileInfo();
		    array_push($list, array(
		        "state" => $info["state"],
		        "url" => $info["url"],
		        "size" => $info["size"],
		        "title" => htmlspecialchars($info["title"]),
		        "original" => htmlspecialchars($info["original"]),
		        "source" => htmlspecialchars($imgUrl)
		    ));
		}

		/* 返回抓取数据 */
		return json_encode(array(
		    'state'=> count($list) ? 'SUCCESS':'ERROR',
		    'list'=> $list
		));
	}

	public function actionList(){
		$CONFIG = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents(Env::get('app_path')."admin/config/ueditorconfig.json")), true);
		/* 判断类型 */
		switch ($_GET['action']) {
		    /* 列出文件 */
		    case 'listfile':
		        $allowFiles = $CONFIG['fileManagerAllowFiles'];
		        $listSize = $CONFIG['fileManagerListSize'];
		        $path = $CONFIG['fileManagerListPath'];
		        break;
		    /* 列出图片 */
		    case 'listimage':
		    default:
		        $allowFiles = $CONFIG['imageManagerAllowFiles'];
		        $listSize = $CONFIG['imageManagerListSize'];
		        $path = $CONFIG['imageManagerListPath'];
		}
		$allowFiles = substr(str_replace(".", "|", join("", $allowFiles)), 1);

		/* 获取参数 */
		$size = isset($_GET['size']) ? htmlspecialchars($_GET['size']) : $listSize;
		$start = isset($_GET['start']) ? htmlspecialchars($_GET['start']) : 0;
		$end = $start + $size;

		/* 获取文件列表 */
		$path = $_SERVER['DOCUMENT_ROOT'] . (substr($path, 0, 1) == "/" ? "":"/") . $path;
		$files = $this->getFiles($path, $allowFiles);
		if (!count($files)) {
		    return json_encode(array(
		        "state" => "no match file",
		        "list" => array(),
		        "start" => $start,
		        "total" => count($files)
		    ));
		}

		/* 获取指定范围的列表 */
		$len = count($files);
		for ($i = min($end, $len) - 1, $list = array(); $i < $len && $i >= 0 && $i >= $start; $i--){
		    $list[] = $files[$i];
		}
		//倒序
		//for ($i = $end, $list = array(); $i < $len && $i < $end; $i++){
		//    $list[] = $files[$i];
		//}

		/* 返回数据 */
		$result = json_encode(array(
		    "state" => "SUCCESS",
		    "list" => $list,
		    "start" => $start,
		    "total" => count($files)
		));

		return $result;

	}

	public function getFiles($path, $allowFiles, &$files = array()){
		if (!is_dir($path)) return null;
	    if(substr($path, strlen($path) - 1) != '/') $path .= '/';
	    $handle = opendir($path);
	    while (false !== ($file = readdir($handle))) {
	        if ($file != '.' && $file != '..') {
	            $path2 = $path . $file;
	            if (is_dir($path2)) {
	                getFiles($path2, $allowFiles, $files);
	            } else {
	                if (preg_match("/\.(".$allowFiles.")$/i", $file)) {
	                    $files[] = array(
	                        'url'=> substr($path2, strlen($_SERVER['DOCUMENT_ROOT'])),
	                        'mtime'=> filemtime($path2)
	                    );
	                }
	            }
	        }
	    }
	    return $files;
	}

	public function actionUpload(){
		$CONFIG = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents(Env::get('app_path')."admin/config/ueditorconfig.json")), true);
		/* 上传配置 */
		$base64 = "upload";
		switch (htmlspecialchars($_GET['action'])) {
		    case 'uploadimage':
		        $config = array(
		            "pathFormat" => $CONFIG['imagePathFormat'],
		            "maxSize" => $CONFIG['imageMaxSize'],
		            "allowFiles" => $CONFIG['imageAllowFiles']
		        );
		        $fieldName = $CONFIG['imageFieldName'];
		        break;
		    case 'uploadscrawl':
		        $config = array(
		            "pathFormat" => $CONFIG['scrawlPathFormat'],
		            "maxSize" => $CONFIG['scrawlMaxSize'],
		            "allowFiles" => $CONFIG['scrawlAllowFiles'],
		            "oriName" => "scrawl.png"
		        );
		        $fieldName = $CONFIG['scrawlFieldName'];
		        $base64 = "base64";
		        break;
		    case 'uploadvideo':
		        $config = array(
		            "pathFormat" => $CONFIG['videoPathFormat'],
		            "maxSize" => $CONFIG['videoMaxSize'],
		            "allowFiles" => $CONFIG['videoAllowFiles']
		        );
		        $fieldName = $CONFIG['videoFieldName'];
		        break;
		    case 'uploadfile':
		    default:
		        $config = array(
		            "pathFormat" => $CONFIG['filePathFormat'],
		            "maxSize" => $CONFIG['fileMaxSize'],
		            "allowFiles" => $CONFIG['fileAllowFiles']
		        );
		        $fieldName = $CONFIG['fileFieldName'];
		        break;
		}
		/* 生成上传实例对象并完成上传 */
		$up = new Uploader($fieldName, $config, $base64);
		/**
		 * 得到上传文件所对应的各个参数,数组结构
		 * array(
		 *     "state" => "",          //上传状态，上传成功时必须返回"SUCCESS"
		 *     "url" => "",            //返回的地址
		 *     "title" => "",          //新文件名
		 *     "original" => "",       //原始文件名
		 *     "type" => ""            //文件类型
		 *     "size" => "",           //文件大小
		 * )
		 */
		/* 返回数据 */
		return json_encode($up->getFileInfo());
	}



	/**
	 * 案例、新闻等缩略图的图片上传
	 * @return [type] [description]
	 */
	public function upload(){
		$file = request()->file('file');
	    // 移动到框架应用根目录/uploads/ 目录下
	    $info = $file->validate(['size'=>3145728,'ext'=>'jpeg,jpg,png'])->move( '../public/uploads');
	    if($info){
	        // 成功上传后 获取上传信息
	        // 输出 jpg
	        // echo $info->getExtension();
	        // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
	        //echo $info->getSaveName();
	        // 输出 42a79759f284b767dfcb2a0197904287.jpg
	        // echo $info->getFilename(); 
	        return json(['code'=>1,'msg'=>'/uploads/'.$info->getSaveName()]);
	    }else{
	        // 上传失败获取错误信息
	        // echo $file->getError();
	       	return json(['code'=>0,'msg'=>$file->getError()]);
	    }
	}

	public function cutImg($img_url,$width,$height=0){
		$image = \think\Image::open($img_url);
		if($height===0){
			$height=($width/$image->width())*$image->height();
		}else{
			$width=(int)$width;
			$height=(int)$height;
		}

		if(is_file('../public/uploads/test.png')){
			unlink('../public/uploads/test.png');
		}

		$thumb=$image->thumb($width,$height,\think\Image::THUMB_FIXED)->save('../public/uploads/test.png');
		return '/public/uploads/test.png';		
	}

	
}