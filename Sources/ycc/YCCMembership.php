<?php
if (!defined('SMF'))
die('Hacking attempt...');

require_once("/home/yorkcano/public_html/helper/functions.php");

function YCCMembership() {

    global $context;

    isAllowedTo(array('view_ownylist'));
    
    $memberDB = array("localhost", "yorkcano_web", "web", "yorkcano_smf");

    $memberConnection = mysql_connect($memberDB[0], $memberDB[1], $memberDB[2], true) or die("Could not connect: " . mysql_error());
    mysql_select_db($memberDB[3], $memberConnection) or die ('Cannot Connect to DB: ' . mysql_error());

    $memberQuery = "select 
                            members.*, 
                            DATE_FORMAT(membershipStarted, '%d/%m/%Y') as membershipStartedF, 
                            DATE_FORMAT(membershipExpires, '%d/%m/%Y') as membershipExpiresF 
                    from ycc_members as members 
                    left join ycc_memberForumCR as crossref
                    on members.membershipNumber = crossref.membershipNumber
                    where (members.forumId = '" . $context["user"]["id"] . "' 
                    or crossref.forumId = '" . $context["user"]["id"] . "') 
                    order by members.membershipNumber";
    $result = mysql_query($memberQuery, $memberConnection);

    $memberCount = mysql_num_rows($result);
    $members = array();
    
    while ($row = mysql_fetch_assoc($result)) {

        $contactsQuery = "select * from ycc_memberscontacts where membershipNumber = " . $row["membershipNumber"] . " and contactsSequence = 10";
        $contactsResult = mysql_query($contactsQuery, $memberConnection);
        $contactsRow = mysql_fetch_assoc($contactsResult);

        if ($row["memberFirstName"] == ""
         || $row["memberLastName"] == ""
         || $row["memberGender"] == ""
         || $row["memberDob"] == ""
         || $row["memberAddress"] == ""
         || $row["memberPostcode"] == ""
         || $row["memberPhone1"] == ""
         || $row["memberEmail"] == ""
         || $row["memberContact"] == ""
         || $contactsRow["contactsName"] == ""
         || $contactsRow["contactsRelationship"] == ""
         || $contactsRow["contactsPhone1"] == ""       
        ) {
            $row["complete"] = "";
        } else {
            $row["complete"] = "1";
        }
        
        if ($row["forumId"] == $context["user"]["id"]) {
            $row["primary"] = "1";
        } else {
            $row["primary"] = "";
        }
        
        array_push($members, $row);

    }        

    mysql_close($memberConnection);

    $context['pageTitle'] = 'YCC Memberships';
    $context['memberCount'] = $memberCount;
    $context['memberArray'] = $members;

    loadTemplate('ycc/YCCMembership');

}
?>