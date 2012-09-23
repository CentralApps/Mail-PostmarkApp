# PostmarkApp Mail Component for Abtract Mail Component 

This is the postmarkapp integration for the centralapps\mail component, an abstract mail sending and receiving component.

Created by [Michael Peacock](http://www.michaelpeacock.co.uk)

## Installation

### Using Composer

1. Add to your `composer.json` file

	{
		"require": {
			"centralapps/mail": "dev-master",
			"centralapps/mail-postmarkapp": "dev-master"
		}
	}

2. Download composer

	curl -s https://getcomposer.org/installer | php

3. Install the dependencies

	php composer.phar install

## Usage

	<?php
	require_once( __DIR__ . '/../vendor/autoload.php');
	
	// Configs & Create a mail transportation layer and a dispatcher
	$configuration = new \CentralApps\PostMarkApp\Configuration();
	$configuration['api_key'] = "YOUR POSTMARKAPP API KEY GOES HERE";
	$transport = new \CentralApps\PostMarkApp\Transport($configuration);
	$dispatcher = new \CentralApps\Mail\Dispatcher($transport);
	
	// Create a sender
	$sender = new \CentralApps\Mail\SendersReceiversEtc\Sender("michael@peacocknet.co.uk", "Michael Peacock");
	// Create a recipient
	$recipient = new \CentralApps\Mail\SendersReceiversEtc\Recipient("mkpeacock@gmail.com", "Michael Peacock");
	
	// Create a message
	$message = new \CentralApps\PostMarkApp\Message();
	$message->setSender($sender);
	$message->addRecipient($recipient);
	$message->setSubject('Subject');
	$message->setPlainTextMessage("Hi there");
	
	// Send the message
	$dispatcher->send($message);

