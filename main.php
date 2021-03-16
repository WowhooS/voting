<?php
//1. ensure this code runs only after a POST from AT
if(!empty($_POST)){
    require_once('dbConnector.php');
	require_once('AfricasTalkingGateway.php');
    require_once('config.php');
    //2. receive the POST from AT
	$sessionId=$_POST['sessionId'];
	$serviceCode=$_POST['serviceCode'];
	$phoneNumber=$_POST['phoneNumber'];
    $text=$_POST['text'];
    //3. Explode the text to get the value of the latest interaction - think 1*1
	$textArray=explode('*', $text);
    $userResponse=trim(end($textArray));
    //4. Set the default level of the user
	$level=0;

	//5. Check the level of the user from the DB and retain default level if none is found for this session
	$sql = "select level from session_levels where session_id ='".$sessionId." '";
	$levelQuery = $db->query($sql);
	if($result = $levelQuery->fetch_assoc()) {
  		$level = $result['level'];
	}

	//6. Update level accordingly
	if($result){
		$level = $result['level'];
    }
    //7. Check if the user is in the db
	$sql7 = "SELECT * FROM students WHERE phoneNumber LIKE '%".$phoneNumber."%' LIMIT 1";
	$userQuery=$db->query($sql7);
	$userAvailable=$userQuery->fetch_assoc();

	//8. Check if the user is available (yes)->Serve the menu; (no)->Register the user
	if($userAvailable && $userAvailable['student_ID']!=NULL && $userAvailable['username']!=NULL){
        //9. Serve the Services Menu
        switch ($userResponse) {
            case "":
                if($level==0){
                    //9b. Graduate user to next level & Serve Main Menu
                    $sql9b = "INSERT INTO `session_levels`(`session_id`,`phoneNumber`,`level`) VALUES('".$sessionId."','".$phoneNumber."',1)";
                    $db->query($sql9b);
        
                    //Serve our services menu
                    $response = "CON Welcome to TEAU IVote " . $userAvail['username']  . ". To vote, press.\n";
                    $response .= " 1. President.\n";
                    $response .= " 2. Education minister.\n";
                    $response .= " 3. Environment minister.\n";
                    $response .= " 4. Entertainment minister\n";
                    $response= "5. See Results";

        
                      // Print the response onto the page so that our gateway can read it
                      header('Content-type: text/plain');
                          echo $response;						
                }
                break;
            case "1":
                if($level==1){
                    //9c. Send the user todays voice tip via AT SMS API
                    $response= "President;\n Press:"; 
                    $response= "0. Samuel Kimei \n"; $response ++;
                    $response= "1. Margaret Kobi \n"; $response ++;
                    $response= "2. Alice Mwea \n"; $response ++;
                    
                
        
                      // Print the response onto the page so that our gateway can read it
                      header('Content-type: text/plain');
                          echo $response;	            						        	
                }
                break;
            case "2":
                if($level==1){
                    $response= "Education minister;\n Press:";
                    $response= "0. Alex Wambua \n"; $response ++;
                    $response= "1. Collins Kamau \n"; $response ++;
                    $response= "2. Esther Bett \n"; $response ++;
                
                      $gateway = new AfricasTalkingGateway($username, $apikey);
                      try { $gateway->call($from, $to); }
                      catch ( AfricasTalkingGatewayException $e ){echo "Encountered an error when calling: ".$e->getMessage();}
        
                      // Print the response onto the page so that our gateway can read it
                      header('Content-type: text/plain');
                          echo $response;	 
                }
                break;
            case "3":
                if($level==1){
                    //9e. Send user airtime
                    $response= "Environment minister;\n Press:";
                    $response= "0. David Hosh \n"; $response ++;
                    $response= "1. Koni Tabu \n"; $response ++;
                    $response= "2. Edward Riar \n"; $response ++;
                    $gateway = new AfricasTalkingGateway($username, $apiKey);    
                    try { $results = $gateway->sendAirtime($recipientStringFormat);}
                    catch(AfricasTalkingGatewayException $e){ echo $e->getMessage(); }
        
                      // Print the response onto the page so that our gateway can read it
                      header('Content-type: text/plain');
                          echo $response;	 			    		
                }
                break;
                case "4":
                    if($level==1){
                        //9e. Send user airtime
                        $response= "Entertainment minster;\n Press:";
                        $response= "0. Lily Mathu \n"; $response ++;
                        $response= "1. Emma Mutai \n"; $response ++;
                        $response= "2. Dennis Oloo \n"; $response ++;
                    
                        $gateway = new AfricasTalkingGateway($username, $apiKey);    
                        try { $results = $gateway->sendAirtime($recipientStringFormat);}
                        catch(AfricasTalkingGatewayException $e){ echo $e->getMessage(); }
            
                          // Print the response onto the page so that our gateway can read it
                          header('Content-type: text/plain');
                              echo $response;	 			    		
                    }	
                    break;
                    case "5":
                        if($level==1){
                            //9e. Send user airtime
                            $response= "Entertainment minster;\n Press:";
                            $response= "0. Lily Mathu \n"; $response ++;
                            $response= "1. Emma Mutai \n"; $response ++;
                            $response= "2. Dennis Oloo \n"; $response ++;
                        
                            $gateway = new AfricasTalkingGateway($username, $apiKey);    
                            try { $results = $gateway->sendAirtime($recipientStringFormat);}
                            catch(AfricasTalkingGatewayException $e){ echo $e->getMessage(); }
                
                              // Print the response onto the page so that our gateway can read it
                              header('Content-type: text/plain');
                                  echo $response;	 	
                        }              		        
            default:
                if($level==1){
                    // Return user to Main Menu & Demote user's level
                    $response = "CON You have to vote.\n";
                    $response .= "Press 0 to go back.\n";
                    //demote
                    $sqlLevelDemote="UPDATE `session_levels` SET `level`=0 where `session_id`='".$sessionId."'";
                    $db->query($sqlLevelDemote);
        
                    // Print the response onto the page so that our gateway can read it
                      header('Content-type: text/plain');
                          echo $response;	
                }
                //10. Register the user
if($userResponse==""){
    //10a. On receiving a Blank. Advise user to input correctly based on level
    switch ($level) {
        case 0:
            //10b. Graduate the user to the next level, so you dont serve them the same menu
             $sql10b = "INSERT INTO `session_levels`(`session_id`, `phoneNumber`,`level`) VALUES('".$sessionId."','".$phoneNumber."', 1)";
             $db->query($sql10b);
    
             //10c. Insert the phoneNumber, since it comes with the first POST
             $sql10c = "INSERT INTO `students`(`phonenumber`) VALUES ('".$phoneNumber."')";
             $db->query($sql10c);
    
             //10d. Serve the menu request for name
             $response = "CON Please register to vote.\n";
             $response= "Enter your name.\n";
             $response= "Admission number\n";
            
    
              // Print the response onto the page so that our gateway can read it
              header('Content-type: text/plain');
                  echo $response;	
            break;
    
        case 1:
            //10e. Request again for name
            $response = "CON Name not supposed to be empty. Please enter your name \n";
    
              // Print the response onto the page so that our gateway can read it
              header('Content-type: text/plain');
                  echo $response;	
            break;
    
        case 2:
            //10f. Request fir city again
            $response = "CON Admission number not supposed to be empty. Please reply with your admission number \n";
    
              // Print the response onto the page so that our gateway can read it
              header('Content-type: text/plain');
                  echo $response;	
            break;
    
        default:
            //10g. Request fir city again
            $response = "END Apologies, something went wrong-  \n";
    
              // Print the response onto the page so that our gateway can read it
              header('Content-type: text/plain');
                  echo $response;	
            break;
    }
    }
    else{
        //11. Update User table based on input to correct level
        switch ($level) {
            case 0:
                 //11a. Serve the menu request for name
                 $response = "END This level should not be seen- ";
        
                  // Print the response onto the page so that our gateway can read it
                  header('Content-type: text/plain');
                      echo $response;	
                break;
        
            case 1:
                //11b. Update Name, Request for city
                $sql11b = "UPDATE `users` SET `username`='".$userResponse."' WHERE `phonenumber` LIKE '%". $phoneNumber ."%'";
                $db->query($sql11b);
        
                //11c. We graduate the user to the city level
                $sql11c = "UPDATE `session_levels` SET `level`=2 WHERE `session_id`='".$sessionId."'";
                $db->query($sql11c);
        
                //We request for the city
                $response = "CON Please enter your admission number";
        
                  // Print the response onto the page so that our gateway can read it
                  header('Content-type: text/plain');
                      echo $response;	
                break;
        
            case 2:
                //11d. Update city
                $sql11d = "UPDATE `users` SET `admission_number`='".$userResponse."' WHERE `phonenumber` = '". $phoneNumber ."'";
                $db->query($sql11d);
        
                //11e. Change level to 0
                $sql11e = "INSERT INTO `session_levels`(`session_id`,`phoneNumber`,`level`) VALUES('".$sessionId."','".$phoneNumber."',1)";
                $db->query($sql11e);   
        
                //11f. Serve services menu- 
                $response = "CON Welcome to TEAU IVote. To vote, press.\n";
                $response .= " 1. President.\n";
                $response .= " 2. Education minister.\n";
                $response .= " 3. Environment minister.\n";
                $response .= " 4. Entertainment minister\n";

                  // Print the response onto the page so that our gateway can read it
                  header('Content-type: text/plain');
                      echo $response;	
                break;
        
            default:
                //11g. Request for city again
                $response = "END Apologies, something went wrong-  \n";
        
                  // Print the response onto the page so that our gateway can read it
                  header('Content-type: text/plain');
                      echo $response;	
                break;
                }			
            }
        }
        }
        
    }
        ?>   