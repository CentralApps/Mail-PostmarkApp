<?php
namespace CentralApps\PostMarkApp;

class Transport implements \CentralApps\Mail\Transport
{
    protected $apiKey;
    protected $apiEndPoint = 'http://api.postmarkapp.com/email';

    /**
     * Allowed attachment file types see: http://developer.postmarkapp.com/developer-build.html#attachments
     * - and where appropriate, their corresponding mimetypes
     * @var array
     */
    protected $permittedAttachmentTypes = array('gif' => 'image/gif', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'swf' => 'application/x-shockwave-flash', 'flv' => 'video/x-flv', 'avi' => 'video/x-msvideo', 'mpg' => 'video/mpeg', 'mp3' => 'audio/mpeg', 'rm' => 'application/vnd.rn-realmedia', 'mov' => 'video/quicktime', 'psd' => 'image/psd', 'ai' => 'application/postscript', 'tif' => 'image/tiff', 'tiff' => 'image/tiff', 'txt' => 'text/plain', 'rtf' => 'text/richtext', 'htm' => 'text/html', 'html' => 'text/html', 'pdf' => 'application/pdf', 'doc' => 'application/msword', 'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'ppt' => 'application/vnd.ms-powerpoint', 'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'xls' => 'application/vnd.ms-excel', 'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'ps' => 'image/psd', 'eps' => 'application/postscript', 'log' => 'text/plain', 'csv' => 'text/csv', 'ics' => 'text/calendar', 'xml' => 'text/xml');

    protected $maxAttachmentSize = 10485760;
    protected $maxNumAttachments = 10;

    protected $configurationMappings = array('api_key' => 'apiKey');

    protected $errors = array();

    public function __construct(\CentralApps\Mail\Configuration $configuration)
    {
        foreach ($this->configurationMappings as $key => $mapping) {
            if (array_key_exists($key, $configuration)) {
                $this->$mapping = $configuration[$key];
            }
        }
    }

    public function interimAttachmentCheck(\splFileInfo $attachment, \CentralApps\Mail\Message $message)
    {
        $errors = array();
        $preexisting_attatchments = $message->getAttachments();
        $existing_size = $this->getAttachmentsSize($preexisting_attatchments);
        if (($attachment->getSize() + $existing_size ) > $this->maxAttachmentSize) {
            $errors[] = "Attachments too large, you have exceeded the attachment size limit for PostMarkApp";
        }
        if ((count($preexisting_attatchments) +1 ) > $this->maxNumAttachments) {
            $errors[] = "Too many attachments. Postmark app only permits " . $this->maxNumAttachments . " attachments per message";
        }
        if (!$this->attachmentCheckFileTypes($attachment)) {
            $errors[] = "The attachment " . $attachment->getFilename() . " is not permitted to be sent via PostMarkApp";
        }

        return $errors;
    }

    protected function attachmentsCheck(Message $message)
    {
        $attachments = $message->getAttachments();
        foreach ($attachments as $attachment) {
            if (!$this->attachmentCheckFileTypes($attachment)) {
                $this->errors[] = "The attachment " . $attachment->getFilename() . " is not permitted to be sent via PostMarkApp";
            }
        }
        $this->attachmentsCheckTooMany($attachments);
        $this->attachmentCheckTooBig($attachments);
    }

    protected function attachmentsCheckTooMany($attachments)
    {
        if (count($attachments) > $this->maxNumAttachments) {
            $this->errors[] = "Too many attachments. Postmark app only permits " . $this->maxNumAttachments . " attachments per message";
        }
    }

    public function getAttachmentsSize($attachments)
    {
        $size = 0;
        foreach ($attachments as $attachment) {
            $size += $attachment->getSize();
        }

        return $size;
    }

    protected function attachmentCheckTooBig($attachments)
    {
        $size = $this->getAttachmentsSize($attachments);

        if ($size > $this->maxAttachmentSize) {
            $this->errors[] = "Attachments too large, you have exceeded the attachment size limit for PostMarkApp";
        }
    }

    protected function attachmentCheckFileTypes($attachment)
    {
        if (!array_key_exists($attachment->getExtension(), $this->permittedAttachmentTypes)) {
            return false;
        }

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
        if (empty($this->errors)) {
            // do something
            $this->sendViaPostmarkApp($email);
        } else {
            // throw something
        }
    }

    protected function sendViaPostmarkApp($sendable_email)
    {
        $email = json_encode($sendable_email);

        $headers = array(
            'Accept: application/json',
            'Content-Type: application/json',
            'X-Postmark-Server-Token: ' . $this->apiKey
        );

        $ch = curl_init();
        curl_setopt($ch, \CURLOPT_URL, $this->apiEndPoint);
        curl_setopt($ch, \CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, \CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, \CURLOPT_POSTFIELDS, $email);
        curl_setopt($ch, \CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);
        $error = curl_error($ch);
        $cleaned_response = json_decode( $response );

        if (curl_getinfo($ch, \CURLINFO_HTTP_CODE) == 200) {
            return true;
        } else {
            return false;
        }
    }
}
