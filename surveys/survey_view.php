<?php
/**
 * demo_idb.php is both a test page for your IDB shared mysqli connection, and a starting point for 
 * building DB applications using IDB connections
 *
 * @package nmCommon
 * @author Bill Newman <williamnewman@gmail.com>
 * @version 2.09 2011/05/09
 * @link http://www.newmanix.com/
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License ("OSL") v. 3.0
 * @see config_inc.php  
 * @see header_inc.php
 * @see footer_inc.php 
 * @todo none
 */
# '../' works for a sub-folder.  use './' for the root
require '../inc_0700/config_inc.php'; #provides configuration, pathing, error handling, db credentials
spl_autoload_register('MyAutoLoader::NamespaceLoader');

$config->titleTag = smartTitle(); #Fills <title> tag. If left empty will fallback to $config->titleTag in config_inc.php
$config->metaDescription = smartTitle() . ' - ' . $config->metaDescription; 

//END CONFIG AREA ---------------------------------------------------------- 

# check variable of item passed in - if invalid data, forcibly redirect back to demo_list.php page
if(isset($_GET['id']) && (int)$_GET['id'] > 0){#proper data must be on querystring
	 $myID = (int)$_GET['id']; #Convert to integer, will equate to zero if fails
}else{
	myRedirect(VIRTUAL_PATH . "surveys/index.php");
}

get_header(); #defaults to header_inc.php
?>
<h3 align="center">Survey View</h3>
<?php

$mySurvey = new SurveySez\Survey($myID);
//dumpDie($mySurvey);

if($mySurvey->isValid)
{//if the survey exists, show data
    echo '<p>Survey Title:<b>' . $mySurvey->Title . '</b></p>'; 
    echo $mySurvey->showQuestions();
    echo responseList($myID);
}else{//apologize!
    echo '<div>There appears to be no such survey</div>';
}
get_footer(); #defaults to footer_inc.php

function responseList($id)
{
    $myReturn = '';
    
    
    $sql = "select 
DateAdded, ResponseID from sm15_responses where SurveyID=$id";
    #reference images for pager
    $prev = '<img src="' . VIRTUAL_PATH . 'images/arrow_prev.gif" border="0" />';
    $next = '<img src="' . VIRTUAL_PATH . 'images/arrow_next.gif" border="0" />';

    # Create instance of new 'pager' class
    $myPager = new Pager(10,'',$prev,$next,'');
    $sql = $myPager->loadSQL($sql);  #load SQL, add offset

    # connection comes first in mysqli (improved) function
    $result = mysqli_query(IDB::conn(),$sql) or die(trigger_error(mysqli_error(IDB::conn()), E_USER_ERROR));

    if(mysqli_num_rows($result) > 0)
    {#records exist - process
        if($myPager->showTotal()==1){$itemz = "response";}else{$itemz = "responses";}  //deal with plural
        $myReturn .= '<div align="center">We have ' . $myPager->showTotal() . ' ' . $itemz . '!</div>';
        while($row = mysqli_fetch_assoc($result))
        {# process each row
             $myReturn .= '<div align="center"><a href="' . VIRTUAL_PATH . 'surveys/response_view.php?id=' . (int)$row['ResponseID'] . '">' . dbOut($row['DateAdded']) . '</a>';
             $myReturn .= '</div>';
        }
        $myReturn .= $myPager->showNAV(); # show paging nav, only if enough records	 
    }else{#no records
        $myReturn .= "<div align=center>There are currently no surveys</div>";	
    }
    @mysqli_free_result($result);

    //$myReturn .= $id;

    return $myReturn;
}



















