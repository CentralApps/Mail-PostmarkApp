<?php
namespace CentralApps\PostMarkApp;

class Message extends \CentralApps\Mail\Message
{
    protected $tag = null;

    // TODO: try and removed this duplication also in Transport.php
    protected $permittedAttachmentTypes = array('gif' => 'image/gif', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'swf' => 'application/x-shockwave-flash', 'flv' => 'video/x-flv', 'avi' => 'video/x-msvideo', 'mpg' => 'video/mpeg', 'mp3' => 'audio/mpeg', 'rm' => 'application/vnd.rn-realmedia', 'mov' => 'video/quicktime', 'psd' => 'image/psd', 'ai' => 'application/postscript', 'tif' => 'image/tiff', 'tiff' => 'image/tiff', 'txt' => 'text/plain', 'rtf' => 'text/richtext', 'htm' => 'text/html', 'html' => 'text/html', 'pdf' => 'application/pdf', 'doc' => 'application/msword', 'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'ppt' => 'application/vnd.ms-powerpoint', 'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'xls' => 'application/vnd.ms-excel', 'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'ps' => 'image/psd', 'eps' => 'application/postscript', 'log' => 'text/plain', 'csv' => 'text/csv', 'ics' => 'text/calendar', 'xml' => 'text/xml');

    public function generateSendableArray()
    {
        $sendable = array();
        $sendable['From'] = (string) $this->sender;
        $sendable['Subject'] = $this->subject;
        $headers = $this->generateHeadersArray();

        if (empty($headers)) {
            $sendable['Headers'] = $headers;
        }

        if (!is_null($this->tag)) {
            $sendable['Tag'] = $tag;
        }

        if (!is_null($this->replyTo)) {
            $sendable['ReplyTo'] = (string) $this->replyTo;
        }

        if (!is_null($this->plainTextMessage)) {
            $sendable['TextBody'] = $this->plainTextMessage;
        }

        if (!is_null($this->htmlMessage)) {
            $sendable['HtmlBody'] = $this->htmlMessage;
        }

        $sendable['To'] = implode(', ', $this->to->flattern());

        if (count($this->bcc) > 0) {
            $sendable['Bcc'] = implode(', ' , $this->bcc->flattern());
        }

        if (count($this->cc) > 0) {
            $sendable['Cc'] = implode(', ', $this->cc->flattern());
        }

        if (count($this->attachments) > 0) {
            $attachments = array();
            foreach ($this->attachments as $attachment) {
                if ($attachment->isReadable()) {
                    $email_attachment = array();
                    $email_attachment['Name'] = $attachment->getFilename();
                    $email_attachment['Content'] = base64_encode(file_get_contents($attachment->getPath() . '/' . $attachment->getFileName()));
                    $email_attachment['ContentType'] = array_key_exists($attachment->getExtension(), $this->permittedAttachmentTypes) ? $this->permittedAttachmentTypes[$attachment->getExtension()] : 'application/octet-stream';
                    $attachments[] = $email_attachment;
                }
            }
            $sendable['Attachments'] = $attachments;
        }

        return $sendable;
    }

    protected function generateHeadersArray()
    {
        $headers = array();
        foreach ($this->headers as $header) {
            $headers[] = array('Name' => $header->name, 'Value' => $header->value);
        }

        return $headers;
    }

}
