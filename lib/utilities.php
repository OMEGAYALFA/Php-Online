<?
function exceptionHandler($exception) {

    // these are our templates
    $traceline = "#%s %s(%s): %s(%s)";
    $msg = "PHP Fatal error:  Uncaught exception '%s' with message '%s' in %s:%s\nStack trace:\n%s\n  thrown in %s on line %s";

    // alter your trace as you please, here
    $trace = $exception->getTrace();
    foreach ($trace as $key => $stackPoint) {
        // I'm converting arguments to their type
        // (prevents passwords from ever getting logged as anything other than 'string')
        $trace[$key]['args'] = array_map('gettype', $trace[$key]['args']);
    }

    // build your tracelines
    $result = array();
    foreach ($trace as $key => $stackPoint) {
        $result[] = sprintf(
            $traceline,
            $key,
            $stackPoint['file'],
            $stackPoint['line'],
            $stackPoint['function'],
            implode(', ', $stackPoint['args'])
        );
    }
    // trace always ends with {main}
    $result[] = '#' . ++$key . ' {main}';

    // write tracelines into main template
    $msg = sprintf(
        $msg,
        get_class($exception),
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine(),
        implode("\n", $result),
        $exception->getFile(),
        $exception->getLine()
    );

    // log or echo as you please
	error_log($msg , 3 , ROOT_PATH . "/log/error.log");

}


function rand_string($length=6) {
	$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPRQSTUVWXYZ0123456789";
	$code = "";
	while (strlen($code) < $length) {
		$code .= $chars[mt_rand(0,strlen($chars)-1)];
	}
	return $code;
}

function rand_num($n=5){
	return rand(0, pow(10, $n));
}

function sql_safe($s)
{
	if (get_magic_quotes_gpc())
		$s = stripslashes($s);

	return mysql_real_escape_string($s);
}

function check_session($type, $msg=''){
	if(!isset($_SESSION[$type][$type.'_id'])){
		$_SESSION['msg'] = ($msg) ? $msg : 'Effettua il login';
		header("Location: index.php?p=".$type.".index");
		exit();
	}
}

//yyyy-mm-dd
function age($dob){
	list($Y,$m,$d)    = explode("-",$dob);
	return ( date("md") < $m.$d ? date("Y")-$Y-1 : date("Y")-$Y );
}

function convertDateFormat($date, $from, $to) {
	
	$MONTH = 'MM'; $YEAR  = 'YYYY'; $DAY = 'DD';
	
	$fmonth = substr($date, strpos($from, $MONTH), strlen($MONTH));
	$fyear = substr($date, strpos($from, $YEAR), strlen($YEAR));
	$fday = substr($date, strpos($from, $DAY), strlen($DAY));
	
	return str_replace(array($YEAR, $MONTH, $DAY), array($fyear, $fmonth, $fday), $to );
}

function validateDate( $date, $format='YYYY-MM-DD')
{
	switch( $format )
	{
		case 'YYYY/MM/DD':
		case 'YYYY-MM-DD':
		list( $y, $m, $d ) = preg_split( '/[-\.\/ ]/', $date );
		break;

		case 'YYYY/DD/MM':
		case 'YYYY-DD-MM':
		list( $y, $d, $m ) = preg_split( '/[-\.\/ ]/', $date );
		break;

		case 'DD-MM-YYYY':
		case 'DD/MM/YYYY':
		$date = preg_split( '/[-\.\/ ]/', $date );
		$d = (isset($date[0]) && $date[0]!='') ? $date[0] : 0;
		$m = (isset($date[1]) && $date[1]!='') ? $date[1] : 0;		
		$y = (isset($date[2]) && $date[2]!='') ? $date[2] : 0;
		break;

		case 'MM-DD-YYYY':
		case 'MM/DD/YYYY':
		list( $m, $d, $y ) = preg_split( '/[-\.\/ ]/', $date );
		break;

		case 'YYYYMMDD':
		$y = substr( $date, 0, 4 );
		$m = substr( $date, 4, 2 );
		$d = substr( $date, 6, 2 );
		break;

		case 'YYYYDDMM':
		$y = substr( $date, 0, 4 );
		$d = substr( $date, 4, 2 );
		$m = substr( $date, 6, 2 );
		break;

		default:
		return false;
	}
	
	//echo $m.'--'.$d.'---'.$y;
	return checkdate( $m, $d, $y );
}


function pagination($pg_no,$total_results,$per_page,$url,$url_type='link',$content_div='content')
{
	$total_pgs = ceil($total_results / $per_page );
	$prev_pg = ""; $next_pg = "";
	
	if ( $total_pgs > 1 )
	{ 
		if($pg_no > 1)
		{
		 $prev_pg_no = $pg_no-1;
		 if($url_type == 'link')
		 	$prev_pg = "<a href='{$url}&pg_no=$prev_pg_no'  id='pg_link_prev'><b class='txt1'>Previous</b></a>";
		 else
		 	$prev_pg = "<a href='#.' id='pg_link_prev' class='navigation_on' onclick=\"{$url_type}('{$url}&pg_no=$prev_pg_no','".$content_div."'); return false;\" ><b>Previous</b></a>";
		}
		else
			$prev_pg = "<span class='txt1'>Previous</span>";
			
		if($pg_no < ceil($total_results/$per_page))
		{
		 $next_pg_no = $pg_no+1;
		 if($url_type == 'link')
		 	$next_pg = "<a href='{$url}&pg_no=$next_pg_no' id='pg_link_next' ><b class='txt1'>Next</b></a>";
		 else
		 	$next_pg = "<a href='#.' id='pg_link_next' class='navigation_on' onclick = \"{$url_type}('{$url}&pg_no=$next_pg_no','".$content_div."'); return false;\" ><b>Next</b></a>";
		}
		else
			$next_pg = "<span class='txt1'>Next</span>";
		$page_anchors = " &nbsp; ";
		
	
		$i_start = ( $pg_no > 12 ) ? ( $pg_no - 10 ) : 1  ;
		
		$i_end = ( $total_pgs > ( $pg_no + 12 ) ) ? ( $pg_no + 10 ) : $total_pgs;
		
		
		if( $i_start != 1 )
		{
			if($url_type == 'link')
		 		$page_anchors .= "<a href='{$url}&pg_no=1' ><b>1</b></a>";
			else
		 		$page_anchors .= "<a href='#.' ><b>1</b></a>";
			
		}
		
		
		for( $i = $i_start ; $i <= $i_end ; $i++)
		{
			if($i == $pg_no)
				$page_anchors .= "<b class='txt1'>$pg_no</b>&nbsp;";
			else
			{
			
				if($url_type == 'link')
		 			$page_anchors .= "<a href='{$url}&pg_no=$i' id=\"pg_link_{$i}\"  >$i</a>&nbsp;";
				else
		 			$page_anchors .= "<a  href='#.'  id=\"pg_link_{$i}\" onclick = \"{$url_type}('{$url}&pg_no=$i','".$content_div."'); return false;\"  >$i</a>&nbsp;";
			
			}
		}
		
		
		if( $i_end != ceil($total_results/$per_page) )
		{

			if($url_type == 'link')
				$page_anchors .= "<a href='{$url}&pg_no=".ceil($total_results/$per_page)."'  id=\"pg_link_{$i}\"  ><b>".ceil($total_results/$per_page)."</b></a>&nbsp;";
			else
				$page_anchors .= "<a href='#.' onclick = \"{$url_type}('{$url}&pg_no=".ceil($total_results/$per_page)."','".$content_div."'); return false;\"   id=\"pg_link_{$i}\" ><b>".ceil($total_results/$per_page)."</b></a>&nbsp;";
				
		}
		
		return $prev_pg.$page_anchors.$next_pg;
	}
	else
	{
		return "";
	}
	
}



?>