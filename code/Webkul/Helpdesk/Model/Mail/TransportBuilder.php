<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Helpdesk\Model\Mail;

class TransportBuilder extends \Magento\Framework\Mail\Template\TransportBuilder
{
    /**
     * Mail character set
     * @var string
     */
    protected $_charset = 'iso-8859-1';

    /**
     * Encoding of Mail headers
     * @var string
     */
    protected $_headerEncoding = \Zend_Mime::ENCODING_QUOTEDPRINTABLE;

    /**
     * Mail headers
     * @var array
     */
    protected $_headers = [];

    /**
     * Sets the HTML body for the message
     *
     * @param  string    $html
     * @param  string    $charset
     * @param  string    $encoding
     * @return Zend_Mail Provides fluent interface
     */
    public function setBodyHtml($html, $charset = null, $encoding = \Zend_Mime::ENCODING_QUOTEDPRINTABLE)
    {
        if ($charset === null) {
            $charset = $this->_charset;
        }

        $mp = new \Zend_Mime_Part($html);
        $mp->encoding = $encoding;
        $mp->type = \Zend_Mime::TYPE_HTML;
        $mp->disposition = \Zend_Mime::DISPOSITION_INLINE;
        $mp->charset = $charset;
        $this->_bodyHtml = $mp;
        return $this;
    }

    /**
     * @param Api\AttachmentInterface $attachment
     */
    public function addAttachment($content, $attachment)
    {
        $this->message->createAttachment(
            $content,
            $attachment->type,
            $attachment->disposition,
            $attachment->encoding,
            $attachment->filename
        );
        return $this;
    }

    /**
     * Add a custom header to the message
     *
     * @param  string              $name
     * @param  string              $value
     * @param  boolean             $append
     * @return Zend_Mail           Provides fluent interface
     * @throws Zend_Mail_Exception on attempts to create standard headers
     */
    public function addHeader($name, $value, $append = false)
    {
        $prohibit = [
            'to', 'cc', 'bcc', 'from', 'subject',
            'reply-to', 'return-path',
            'date', 'message-id',
        ];
        if (in_array(strtolower($name), $prohibit)) {
            /**
             * @see Zend_Mail_Exception
             */
            throw new \Zend_Mail_Exception('Cannot set standard header from addHeader()');
        }

        $value = $this->_filterOther($value);
        $value = $this->_encodeHeader($value);
        $this->_storeHeader($name, $value, $append);

        return $this;
    }

    /**
     * mime_content_type Return Mime type
     * @param String $filename File Name
     * @return String Mime type
     */
    public function getMimeContentType($filename)
    {
        $mimeTypesArr = [
            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet'
        ];
        $tmp = explode('.', $filename);
        $ext = strtolower(array_pop($tmp));
        if (array_key_exists($ext, $mimeTypesArr)) {
            return $mimeTypesArr[$ext];
        } elseif (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $filename);
            finfo_close($finfo);
            return $mimetype;
        } else {
            return 'application/octet-stream';
        }
    }

    /**
     * Filter of other data
     *
     * @param string $data
     * @return string
     */
    protected function _filterOther($data)
    {
        $rule = [
            "\r" => '',
            "\n" => '',
            "\t" => '',
        ];
        return strtr($data, $rule);
    }

    /**
     * Encode header fields
     *
     * Encodes header content according to RFC1522 if it contains non-printable
     * characters.
     *
     * @param  string $value
     * @return string
     */
    protected function _encodeHeader($value)
    {
        if (\Zend_Mime::isPrintable($value) === false) {
            if ($this->getHeaderEncoding() === \Zend_Mime::ENCODING_QUOTEDPRINTABLE) {
                $value = \Zend_Mime::encodeQuotedPrintableHeader(
                    $value,
                    $this->getCharset(),
                    \Zend_Mime::LINELENGTH,
                    \Zend_Mime::LINEEND
                );
            } else {
                $value = \Zend_Mime::encodeBase64Header(
                    $value,
                    $this->getCharset(),
                    \Zend_Mime::LINELENGTH,
                    \Zend_Mime::LINEEND
                );
            }
        }

        return $value;
    }

    /**
     * Return the encoding of mail headers
     *
     * Either Zend_Mime::ENCODING_QUOTEDPRINTABLE or Zend_Mime::ENCODING_BASE64
     *
     * @return string
     */
    public function getHeaderEncoding()
    {
        return $this->_headerEncoding;
    }

    /**
     * Return charset string
     *
     * @return string
     */
    public function getCharset()
    {
        return $this->_charset;
    }

    /**
     * Add a header to the message
     *
     * Adds a header to this message. If append is true and the header already
     * exists, raises a flag indicating that the header should be appended.
     *
     * @param string  $headerName
     * @param string  $value
     * @param bool $append
     */
    protected function _storeHeader($headerName, $value, $append = false)
    {
        if (isset($this->_headers[$headerName])) {
            $this->_headers[$headerName][] = $value;
        } else {
            $this->_headers[$headerName] = [$value];
        }

        if ($append) {
            $this->_headers[$headerName]['append'] = true;
        }
    }
}
