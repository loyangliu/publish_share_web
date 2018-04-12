<?php 


if (defined('SCAKE_APP_CONTROLLER_PHP'))
{
	return;
}
else
{
	define('SCAKE_APP_CONTROLLER_PHP', 1);
}

include_once 'controller.php';


class ActionPair
{
	public $pageAction;
	public $rightAction;
	public $datatype;
}

class TokenActionPair
{
	public $pageAction;
	public $datatype;
}

  /**
   * AppController , override the default
   * 
   * support permission check
   *
   */

class AppController extends Controller
{

	public $datatype;
	public $actionMap;

	public $whiteListActions = array();
	public $rights;
	public $dict;
	public $safe;

	public $dblog;
	public $needRightCheck = true;
	public $tokenMap;	//用于是否需要检查token值
	
	public function __construct()
	{
		parent::__construct();
		$this->actionMap = array();
	}
	
	public function registerWhiteListAction($action, $datatype=null)
	{
		if(!$datatype) $datatype = intval($this->datatype);
		$this->whiteListActions[$datatype][] = $action;
	}

	public function registerRightAction($action, $rightAction, $datatype=null)
	{
		if(!$datatype) $datatype = intval($this->datatype);
		$pair = new ActionPair;
		$pair->pageAction = $action;
		$pair->rightAction = $rightAction;
		$pair->datatype = $datatype;

		$key = $action . '-' . $datatype;
		$this->actionMap[$key] = $pair;
	}
	
	//注册需要检查token的action
	public function registerTokenAction($action, $datatype=null)
	{
		if(!$datatype) $datatype = intval($this->datatype);
		$pair = new TokenActionPair;
		$pair->pageAction = $action;
		$pair->datatype = $datatype;

		$key = $action . '-' . $datatype;
		$this->tokenMap[$key] = $pair;
	}
	
	public function initialize()
	{
		parent::initialize();
	}
	
	// 把上传目录中的相对路径, 转换为绝对路径, 为了兼容邮件客户端
	public function resourceToAbsolute($richHtml, $staticHost){
		$staticHost = ($staticHost) ? $staticHost : $this->hostUrl;
		return preg_replace( "/(['\"])\/upload/", "$1{$staticHost}/upload", $richHtml );
	}

	public function loginfo($action,
							$id,
							&$model,
							$newParentId = 0)
	{
		return $this->dblog->loginfo($action, $id, $model, $newParentId);
	}


	public function hasright($rightAction, $datatype = null)
	{
		return $this->rights->hasright($rightAction, $datatype);
	}
	
	public function userHasRight($rightAction, $datatype = null)
	{
		return $this->rights->userHasRight($rightAction, $datatype);
	}


	public function beforeFilter()
	{}
	

	public function afterFilter(){}


	protected function buildwhere()
	{
		$queryField = $this->getParam('qfield');
		$queryValue = $this->getParam('qvalue');
		$where = $this->getParam('where');
		$this->view->assign('where', $where);
		$this->view->assign('qvalue', $queryValue);
		$this->view->assign('qfield', $queryField);

		$re = "/(author.*?)\s*=\s*'(.*?)'/i";
		if(preg_match($re, $where, $r))
		{
		    
			$user = $r[2];
			if(!is_numeric($user))
			{
				$user = $this->dict->get('user', $user);
				if (!$user)
				{
				    $user = -1;
				}
			}

			$old = "{$r[1]}\s*=\s*'{$r[2]}'"; 
			$new = "{$r[1]}='{$user}'";
			$where = preg_replace("/{$old}/i", $new, $where);
		}
		
		$re = "/(modifier.*?)\s*=\s*'(.*?)'/i";
		if(preg_match($re, $where, $r))
		{
			$user = $r[2];
			if(!is_numeric($user))
			{
				$user = $this->dict->get('user', $user);
				if (!$user)
				{
				    $user = -1;
				}
			}

			$old = "{$r[1]}\s*=\s*'{$r[2]}'"; 
			$new = "{$r[1]}='{$user}'";
			$where = preg_replace("/{$old}/i", $new, $where);
		}

		$re = "/(user.*?)\s*=\s*'(.*?);?'/i";
		if(preg_match($re, $where, $r))
		{	    
			$user = $r[2];
			if(!is_numeric($user))
			{
				$user = $this->dict->get('user', $user);
				if (!$user)
				{
				    $user = -1;
				}
			}
			$old = "{$r[1]}\s*=\s*'{$r[2]};?'"; 
			$new = "{$r[1]}='{$user}'";
			$where = preg_replace("/{$old}/i", $new, $where);
		}
		
		$re = "/(datatype.*?)\s*=\s*'(.*?)'/i";
		if(preg_match($re, $where, $r))
		{
			$datatype = $r[2];
			if(!is_numeric($datatype))
			{
				$datatype = $this->dict->get('dataname', $datatype);
			}

			$old = "{$r[1]}\s*=\s*'{$r[2]}'"; 
			$new = "{$r[1]}='{$datatype}'";
			$where = preg_replace("/{$old}/i", $new, $where);
		}


		$curfolder = $this->getParam('curfolder');
		$parentId = intval($this->getParam('parentId'));
		if($curfolder)
		{
			if($where && $parentId)
			{
				$where .= " and parentId={$parentId} ";
			}
			$this->view->assign('curfolder', 'checked');
		}
		else
		{
			$this->view->assign('curfolder', '');
		}

		return $where;
	}
	
	
	public function buildWhere2()
    {
        $queryFields = $this->getParam('queryFields');
		$this->view->assign('queryFields', $queryFields);	
		$this->view->assign('qfield', $this->getParam('qfield'));
		$this->view->assign('qvalue', $this->getParam('qvalue'));
		$this->view->assign('curfolder', $this->getParam('curfolder') ? 'checked' : '');
		
        if(!$queryFields)
            return;
        $fieldArr = split('☻', $queryFields);
        $fields = array();
        foreach ($fieldArr as $field)
        {
            $temp = split('♠', $field);
            $temp[0] = trim($temp[0]);
            if($temp[0])
            {
                $fields[$temp[0]] = $this->model->db->escape($temp[1]);
                if(preg_match('/(name|desc|createTime|modifyTime|author|modifier|user|datatype|ids)/i', $temp[0], $matches) == 0)
                    $conditions[] = "{$temp[0]}='{$fields[$temp[0]]}'";
                else if(preg_match('/(name|desc)/i', $temp[0], $matches) > 0)
                    $conditions[] = "{$temp[0]} like '%{$fields[$temp[0]]}%'";
                else if($temp[0] == 'createTimeStart')
                    $conditions[] = "createTime >= '{$fields[$temp[0]]}'";
                else if($temp[0] == 'createTimeEnd')
                    $conditions[] = "createTime <= '{$fields[$temp[0]]} 23:59:59'";
                else if($temp[0] == 'modifyTimeStart')
                    $conditions[] = "modifyTime >= '{$fields[$temp[0]]}'";
                else if($temp[0] == 'modifyTimeEnd')
                    $conditions[] = "modifyTime <= '{$fields[$temp[0]]} 23:59:59'";
                else if(preg_match('/(author|modifier|user)/i', $temp[0], $matches) > 0)
                {
                    $user = trim($temp[1], "'");
                    if(!is_numeric($user))
        			{
        				$user = $this->dict->get('user', $user);
        				if (!$user)
        				{
        				    $user = -1;
        				}
        			}
        			$conditions[] = "{$temp[0]}='{$user}'";
                }
                else if(preg_match('/datatype/i', $temp[0], $matches) > 0)
                {
                    $datatype = $fields[$temp[0]];
        			if(!is_numeric($datatype))
        			{
        				$datatype = $this->dict->get('dataname', $datatype);
        			}
        			$conditions[] = "{$temp[0]}='{$datatype}'";
                }
                else if($temp[0] == 'ids')
                {
                    $conditions[] = "id in ({$fields[$temp[0]]})";
                }
            }
        }
        return array($fields, $conditions);
    }	
    
    public function buildWhere3()
    {
        $queryFields = $this->getParam('queryFields');
		$this->view->assign('queryFields', $queryFields);	
		$this->view->assign('qfield', $this->getParam('qfield'));
		$this->view->assign('qvalue', $this->getParam('qvalue'));
		$this->view->assign('curfolder', $this->getParam('curfolder') ? 'checked' : '');
		
        if(!$queryFields)
            return;
        $fieldArr = explode('^@^', $queryFields);
        $fields = array();
        foreach ($fieldArr as $field)
        {
            $temp = explode("^-^", $field);
            $temp[0] = trim($temp[0]);
            if($temp[0])
            {
                $fields[$temp[0]] = $this->model->db->escape($temp[1]);
                if(preg_match('/(name|desc|createTime|modifyTime|author|modifier|user|datatype|ids)/i', $temp[0], $matches) == 0)
                    $conditions[$temp[0]] = "{$temp[0]}='{$fields[$temp[0]]}'";
                else if(preg_match('/(name|desc)/i', $temp[0], $matches) > 0)
                    $conditions[$temp[0]] = "{$temp[0]} like '%{$fields[$temp[0]]}%'";
                else if($temp[0] == 'createTimeStart')
                    $conditions[$temp[0]] = "createTime >= '{$fields[$temp[0]]}'";
                else if($temp[0] == 'createTimeEnd')
                    $conditions[$temp[0]] = "createTime <= '{$fields[$temp[0]]} 23:59:59'";
                else if($temp[0] == 'modifyTimeStart')
                    $conditions[$temp[0]] = "modifyTime >= '{$fields[$temp[0]]}'";
                else if($temp[0] == 'modifyTimeEnd')
                    $conditions[$temp[0]] = "modifyTime <= '{$fields[$temp[0]]} 23:59:59'";
                else if(preg_match('/(author|modifier|user)/i', $temp[0], $matches) > 0)
                {
                    $user = trim($temp[1], "'");
                    if(!is_numeric($user))
        			{
        				$user = $this->dict->get('user', $user);
        				if (!$user)
        				{
        				    $user = -1;
        				}
        			}
        			$conditions[$temp[0]] = "{$temp[0]}='{$user}'";
                }
                else if(preg_match('/datatype/i', $temp[0], $matches) > 0)
                {
                    $datatype = $fields[$temp[0]];
        			if(!is_numeric($datatype))
        			{
        				$datatype = $this->dict->get('dataname', $datatype);
        			}
        			$conditions[$temp[0]] = "{$temp[0]}='{$datatype}'";
                }
                else if($temp[0] == 'ids')
                {
                    $conditions[$temp[0]] = "id in ({$fields[$temp[0]]})";
                }
            }
        }
        return array($fields, $conditions);
    }
    
	protected function pageinfo($__limit = DEFAULT_PAGE_SIZE)
	{
		$page = intval($this->getParam('page'));
		
		if($page)
		{
			$maxpage = intval($this->getParam('maxpage'));
			if($page >= $maxpage) $page = $maxpage;
			if($page <= 1) $page=1;
		}
		
		$start = intval($this->getParam('start'));
		$limit = $this->getParam('limit') ?
			$this->getParam('limit') : $__limit;
		if($page)
		{
			$start = ($page-1) * $limit;
		}

		return Array($start, $limit, $page);
	}
	



	public function redirect2($url, $pathinfo, $reloadFlag = 1, $nodeId = 0)
	{
		if(!$url)
		{
			return;
		}
		if($url && $url[0] != '/')
		{
			$url = env('SCRIPT_NAME') . '/' . $url;
		}
		$this->view->display('header');
		echo "<script language=javascript>";
		echo "if(top.tree) { location.href = '$url'; top.tree.locate('{$pathinfo}', {$reloadFlag}, '{$url}', $nodeId);}";
		echo "else {location.href = '$url';}";
		echo "</script>";
		exit;
	}
	
	public function selfRedirect2($url)
	{
		if(!$url)
		{
			return;
		}
		if($url && $url[0] != '/')
		{
			$url = env('SCRIPT_NAME') . '/' . $url;
		}
		$this->view->display('header');
		echo "	<script language=javascript>
					location.href = '$url';
				</script>";
		exit;
	}
	

	public function topRedirect($url)
	{
		if(!$url)
		{
			$url = "/";
		}
		
		if($url && $url[0] != '/')
		{
			$url = env('SCRIPT_NAME') . '/' . $url;
		}

		$this->view->display('header');
		echo "<script language=javascript>";
		echo "top.location.href = '{$url}';";
		echo "</script>";
		exit;
	}
	
	
	//对输入的评审人以及关注人作过滤
	public function filterUser($users)
	{
		if($users)
		{
			$userArr = split(';',$users);
			$userArr = array_unique($userArr);
			$users = join(';', $userArr);
		}
		return $users;
	}
	
	//检查日期的有效性
	public function checkdate($date)
	{
		$k = explode('-', $date);
		if(checkdate($k[1], $k[2], $k[0]))
		{
		 	return true;
		}
		else
		{
			return false;
		}
	}
	
	//检查token的合法性
	public function checkToken()
	{
		$token = $this->getParam('token');
		$sid = $_COOKIE['PHPSESSID'];
		if($token == $sid )
		{
			return true;
		}
		return false;
	}
	
	//统一的上传文件检查及保存处理
	public function fileSave($savepath)
	{
		$ret = $this->uploadFile->fileSave(SITE_CONFIG::$filetypes, DEFAULT_MAX_SIZE, $savepath);
		return $ret;
	}
	
	//解析二维参数
	public function parseParams($headerStr, $hSeparater, $valueStr, $itemSeparater, $vSeparater)
	{
		$values = Array();
		if($headerStr)
		    $headerArr = explode($hSeparater, $headerStr);
		$valueRow = explode($itemSeparater, $valueStr);
		
		for($i = 0; $i < count($valueRow); $i++)
		{
			$itemValues = explode($vSeparater, $valueRow[$i]);
			$values[$i] = Array();
			if($headerArr)
			{
    			for($j = 0; $j < count($headerArr); $j++)
    			{
    				$values[$i][$headerArr[$j]] = $itemValues[$j];
    			}
			}
			else 
			{
			    $values[$i] = $itemValues;
			}
		}

		return $values;
	}
	
	//生成xml格式的xls文件 $data包含 sheets => title, header, data
	public function createXLSFile($sheets)
	{
		uses('xmlexcel'.DS.'phpxls');
	    $xls = new PHP_XLS();
	    
	    foreach ($sheets as $sheetKey => $sheet)
	    {
	    	$xls->AddSheet($sheet['title']);	//每张表的名称
	    	$xls->SetActiveStyle('title');
	    	$rowindex = 1;
	    	//写入每行标题
	    	$headers = $sheet['header'];
	    	$i = 1;
	    	foreach ($headers as $field => $description)
	    	{
	    		$xls->Text($rowindex, $i, $description);
	    		$i ++;
	    	}
	    	//写入数据部分
			$rowindex ++;
			$data = $sheet['data'];
			foreach ($data as $row)
			{
				$i = 1;
				foreach ($headers as $field => $description)
				{
					$xls->Text($rowindex, $i, $row[$field]);	//写入每一列的值
					$i ++;
				}
				$rowindex ++;
			}
	    }
    	$xls->Close();
	    return $xls->GetBuffer();
	}
	
	function getJsonReturn($retCode, $retMsg, $data=NULL) {
		$ret = array ( 'ret' => $retCode, 'msg' => $retMsg );
		if (isset($data)) {
			$ret['data'] = $data;
		}
		return json_encode ($ret, JSON_UNESCAPED_UNICODE);
	}

	/**
	 * 返回json格式消息，并exit
	 * @param $msg
	 * @param bool|false $type
	 */
	protected function ajax_data($msg, $type=false, $data){
		$type = $type?true:false;
		echo json_encode(array('result'=>$type, 'message'=>$msg, 'data'=>$data));exit;
	}
}

function selected($value1, $value2, $selected='selected')
{
	if($value1 == $value2)
	{
		return $selected;
	}
	
	return "";
}


function alert($msg)
{
	header('Content-Type: text/html, charset=' . CHARSET);
	echo "<script language=javascript>";
	echo "alert('{$msg}')";
	echo "</script>";
}

function filterUser($users)
{
	if($users)
	{
		$userArr = explode(';',$users);
		$userArr = array_unique($userArr);
		$users = join(';', $userArr);
	}
	return $users;
}

function computeDate($startDate, $endDate, $type = 'day')
{
	$days = 0;
	$start = strtotime($startDate);
	$end = strtotime($endDate);
	$difference = $end-$start;
	if($type == 'day')
	{
		$data = intval($difference/3600/24);
	}
	else if($type == 'hour')
	{
		$data = intval($difference/3600);
	}
	else if($type == 'week')
	{
		$data = intval($difference/3600/24/7);
	}
	else if($type == 'month')
	{
		$start = split('-', $startDate);
		$end = split('-', $endDate);
		$data = intval($end[1] - $start[1]
                + 12 * ($end[0] - $start[0])
                + ($end[2] - $start[2] + 1)
                / date('t', $endDate));
	}
	return $data;
}

//将日期自动增加/减少天数
function addDay($date, $day)
{
	if($date)
	{
		$dateArr = split('-',$date);
		$date = date('Y-m-d', mktime(0, 0, 0, $dateArr[1], $dateArr[2] + $day, $dateArr[0]));
	}
	return $date;
}

//暂时不考虑工作时逻辑 $startHour = 9;$endHour = 17;
function computeWorkSeconds($startDate, $endDate)
{
    //将非工作日以数组形式存储
    $freeDaysConfig = array(0, 6); //周六以及周日
    $days = 0;
    $diffSeconds = strtotime($endDate) - strtotime($startDate);
	$diffDays = intval($diffSeconds/3600/24);
	$start = split('-',$startDate);
	$freeDays = 0;
	$workSecodes = 0;
	if($diffDays > 0)
	{
		//遍历中间是否含有周末的情况
		for ($i = 1; $i < $diffDays; $i++)
		{
			$newDate = date('w', mktime(0, 0, 0, $start[1], $start[2] + $i, $start[0])); //取当天星期几
			if(in_array($newDate, $freeDaysConfig))
			{
				$freeDays ++ ;
			}
		}
		$workSecodes = $diffSeconds - $freeDays * 24 * 3600;
	}
    return $workSecodes;
}

//ajax方式返回数据给前台，added by cassiehe 2010-09-09
function exitJsonError($msg, $data)
{
    $json['errno'] = 1;
    $json['errmsg'] = $msg;
    $json['data'] = $data;
    echo json_encode($json);
    return;
}  

function exitJsonSucc($data = '', $msg = '操作成功')
{
    $json['errno'] = 0;
    $json['errmsg'] = $msg;
    $json['data'] = $data;
    echo json_encode($json);
    return;
}

function echoJavaScriptMsg($msg)
{
    //echo "<script language=javascript>alert('".$msg."')</script>";
    echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">".
    			 "<script type=\"text/javascript\">".
    			 "alert(\"".$msg."\");</script>";
}
// end of script
