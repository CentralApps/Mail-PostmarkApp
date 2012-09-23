<?php
namespace CentralApps\PostMarkApp;

class Message extends \CentralApps\Mail\Message {
	
	protected $tag = null;
	
	public function generateSendableArray()
	{
		$sendable = array();
		$sendable['From'] = (string) $this->sender;
		$sendable['Subject'] = $this->subject;
		$headers = $this->generateHeadersArray();
		if( empty( $headers ) ) {
			$sendable['Headers'] = $headers;
		}
		
		if( ! is_null($this->tag) ) {
			$sendable['Tag'] = $tag;
		}
		
		if( ! is_null($this->replyTo) ) {
			$sendable['ReplyTo'] = (string) $this->replyTo;
		}
		
		if( ! is_null($this->plainTextMessage) ) {
			$sendable['TextBody'] = $this->plainTextMessage;
		}
		
		if( ! is_null($this->htmlMessage) ) {
			$sendable['HtmlBody'] = $this->htmlMessage;
		}
		
		$sendable['To'] = implode(', ', $this->to->flattern() );
		
		if(count($this->bcc) > 0 ) {
			$sendable['Bcc'] = implode(', ' , $this->bcc->flattern() );
		}
		
		if(count($this->cc) > 0 ) {
			$sendable['Cc'] = implode(', ', $this->cc->flattern() );
		}

		
		return $sendable;
		
	}
	
	protected function generateHeadersArray()
	{
		$headers = array();
		foreach( $this->headers as $header ) {
			$headers[] = array( 'Name' => $header->name, 'Value' => $header->value );
		}
		return $headers;
	}
	
}
