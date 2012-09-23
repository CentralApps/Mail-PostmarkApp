<?php
namespace CentralApps\PostMarkApp;

class Transport implements \CentralApps\Mail\Transport {
	
	protected $apiKey;
	protected $apiEndPoint = 'http://api.postmarkapp.com/email';
	protected $permittedAttachmentTypes = array();
	protected $maxAttachmentSize;
	protected $maxNumAttachments = 0;
	
	protected $configurationMappings = array( 'api_key' => 'apiKey');
	
	
	protected $errors = array();
	
	public function __construct(\CentralApps\Mail\Configuration $configuration)
	{
		foreach( $this->configurationMappings as $key => $mapping ) {
			if( array_key_exists( $key, $configuration ) ) {
				 $this->$mapping = $configuration[ $key ];
			}
		}
	}
	
	public function interimAttachmentCheck(\splFileInfo $attachment, \CentralApps\Mail\Message $message)
	{
		$errors = array();
		$preExistingAttachments = $message->getAttachments();
		$existingSize = $this->getAttachmentsSize($preExistingAttachments);
		if( ( $attachment->getSize() + $existingSize ) > $this->maxAttachmentSize ) {
			$errors[] = "Attachments too large, you have exceeded the attachment size limit for PostMarkApp";
		}
		if( (count($preExistingAttachments) +1 ) > $this->maxNumAttachments ) {
			$errors[] = "Too many attachments. Postmark app only permits " . $this->maxNumAttachments . " attachments per message";
		}
		if( ! $this->attachmentCheckFileTypes($attachment)) {
			$errors[] = "The attachment " . $attachment->getFilename() . " is not permitted to be sent via PostMarkApp";
		}
		return $errors;
	}
	
	protected function attachmentsCheck(Message $message)
	{
		$attachments = $message->getAttachments();
		foreach( $attachments as $attachment ) {
			if( ! $this->attachmentCheckFileTypes($attachment) ) {
				$this->errors[] = "The attachment " . $attachment->getFilename() . " is not permitted to be sent via PostMarkApp";
			}
		}
		$this->attachmentsCheckTooMany($attachments);
		$this->attachmentCheckTooBig($attachments);
		
	}
	
	protected function attachmentsCheckTooMany($attachments)
	{
		if(count($attachments) > $this->maxNumAttachments) {
			$this->errors[] = "Too many attachments. Postmark app only permits " . $this->maxNumAttachments . " attachments per message";
		}
	}
	
	public function getAttachmentsSize($attachments)
	{
		$size = 0;
		foreach($attachments as $attachment) {
			$size += $attachment->getSize();
		}
		return $size;
	}
	
	protected function attachmentCheckTooBig($attachments)
	{
		$size = $this->getAttachmentsSize($attachments);
		
		if( $size > $this->maxAttachmentSize ) {
			$this->errors[] = "Attachments too large, you have exceeded the attachment size limit for PostMarkApp";
		}
	}
	
	protected function attachmentCheckFileTypes($attachment)
	{
		return true;
	}
	
	public function prepare($message)
	{
		$this->attachmentsCheck($message);
	}
	
	public function send(\CentralApps\Mail\Message $message)
	{
		$this->prepare($message);
		$email = $message->generateSendableArray();
		if(empty($this->errors)) {
			// do something
			$this->sendViaPostmarkApp($email);
		} else {
			// throw something
		}
	}
	
	protected function sendViaPostmarkApp($sendableEmail)
	{
		
		$email = json_encode( $sendableEmail );
		
		$headers = array(
			'Accept: application/json',
			'Content-Type: application/json',
			'X-Postmark-Server-Token: ' . $this->apiKey
		);
		
		$ch = curl_init();
		curl_setopt($ch, \CURLOPT_URL, $this->apiEndPoint );
		curl_setopt($ch, \CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, \CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($ch, \CURLOPT_POSTFIELDS, $email);
		curl_setopt($ch, \CURLOPT_HTTPHEADER, $headers);
		$response = curl_exec($ch);
		$error = curl_error($ch);
		$cleaned_response = json_decode( $response );
		echo '<pre>' . print_r($response,true) . '</pre>';
		if( curl_getinfo($ch, \CURLINFO_HTTP_CODE) == 200 )
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
}
