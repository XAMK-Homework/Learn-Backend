<?php

class msgform
{
	protected $actionURL = NULL;
	protected $threadID = NULL;
	protected $hiddenInputs ="";

	

	public function __construct($actionURL = NULL) {
		if(isset($actionURL)) $this->actionURL = $actionURL;
	}
	
	public function getMsgForm($actionURL = NULL, $threadID = NULL, $msgID = NULL){
		global $database;
		$title = "";
		$content = "";

		if(isset($actionURL)) $this->actionURL = $actionURL;
		elseif(!isset($this->actionURL)) return "ERROR: no action URL defined for msgform";
		if(isset($threadID)) $this->addHidden("thread_id", $threadID);
		
		if(isset($msgID)) {
			// Retrieve all message information for an id
			$message = $database->query("SELECT title, content
										FROM msg
										WHERE msg.id = ? AND msg.hidden = 0 LIMIT 1;", $msgID)->fetchArray();
									
			if($message){
				$title = $message['title'];
				$content = $message['content'];
			}
			$this->addHidden("msgid", $msgID);
		}
		
		$formStr = 
		'<br>
		<style>
		#title, #msg {
			box-sizing: border-box;
			width: 500px;
			padding: 5px;
			border: 1px solid #ccc;
		}
		#msg {
			resize: vertical;
		}
		label {
			display: block;
			margin-bottom: 5px;
		}
		</style>
		<form action="' . $this->actionURL . '" method="post">
	        <label for="title">Otsikko:</label><br>
	        <input type="text" id="title" name="title" value="' . htmlspecialchars($title) . '" required><br><br>

	        <label for="msg">Viesti:</label><br>
	        <textarea id="msg" name="msg" rows="6">' . htmlspecialchars($content) . '</textarea><br><br>

	        <input type="submit" value="Submit">';

    	$formStr .= $this->hiddenInputs . '</form>';

    	return $formStr;

	}

	public function addHidden($name, $value)
	{
		$this->hiddenInputs .= '<input type="hidden" id="'.$name.'" name="'.$name.'" value="'.$value.'" >';
	}

}


/*
USAGE:
$form = new msgform("inputHandler.php");
$formHTMLstr = $form->getMsgForm();
OR:
$form = new msgform();
$formHTMLstr = $form->getMsgForm("inputHandler.php");
*/

?>