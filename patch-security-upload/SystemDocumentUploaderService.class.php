<?php
/**
 * Document uploader listener
 *
 * @version    7.6
 * @package    service
 * @author     Nataniel Rabaioli
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006-2014 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    https://adiantiframework.com.br/license-template
 */
class SystemDocumentUploaderService
{

    private $log_file = 'malicious_uploads.log';

    // Content type list based on file extensions
    private $content_type_list = [
        'txt'  => 'text/plain',
        'html' => 'text/html',
        'csv'  => 'text/csv',
        'pdf'  => 'application/pdf',
        'rtf'  => 'application/rtf',
        'doc'  => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls'  => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'ppt'  => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'odt'  => 'application/vnd.oasis.opendocument.text',
        'ods'  => 'application/vnd.oasis.opendocument.spreadsheet',
        'jpeg' => 'image/jpeg',
        'jpg'  => 'image/jpeg',
        'png'  => 'image/png',
        'gif'  => 'image/gif',
        'svg'  => 'image/svg+xml',
        'xml'  => 'application/xml',
        'zip'  => 'application/zip',
        'rar'  => 'application/x-rar-compressed',
        'bz'   => 'application/x-bzip',
        'bz2'  => 'application/x-bzip2',
        'tar'  => 'application/x-tar'
    ];

    public static function getFontAwesomeIcon($file_path)
    {
        $content_type_list = array();
        $content_type_list['text/plain']  = 'far fa-file-alt';
        $content_type_list['text/html'] = 'far fa-file-code';
        $content_type_list['text/csv']  = 'fas fa-file-csv';
        $content_type_list['application/pdf']  = 'far fa-file-pdf';
        $content_type_list['application/zip']  = 'far fa-file-archive';
        $content_type_list['application/x-bzip']  = 'far fa-file-archive';
        $content_type_list['application/x-bzip2'] = 'far fa-file-archive';
        $content_type_list['application/x-tar'] = 'far fa-file-archive';
        $content_type_list['application/x-rar-compressed']  = 'far fa-file-archive';
        $content_type_list['application/rtf']  = 'far fa-file-word';
        $content_type_list['application/csv']  = 'fas fa-file-csv';
        $content_type_list['application/msword']  = 'far fa-file-word';
        $content_type_list['application/vnd.openxmlformats-officedocument.wordprocessingml.document'] = 'far fa-file-word';
        $content_type_list['application/vnd.ms-excel']  = 'far fa-file-excel';
        $content_type_list['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'] = 'far fa-file-excel';
        $content_type_list['application/vnd.ms-powerpoint']  = 'far fa-file-powerpoint';
        $content_type_list['application/vnd.openxmlformats-officedocument.presentationml.presentation'] = 'far fa-file-powerpoint';
        $content_type_list['application/vnd.oasis.opendocument.text']  = 'far fa-file-word';
        $content_type_list['application/vnd.oasis.opendocument.spreadsheet']  = 'far fa-file-word';
        $content_type_list['image/jpeg'] = 'far fa-file-image';
        $content_type_list['image/jpg'] = 'far fa-file-image';
        $content_type_list['image/png'] = 'far fa-file-image';
        $content_type_list['image/gif'] = 'far fa-file-image';
        $content_type_list['image/svg+xml'] = 'far fa-file-code';
        $content_type_list['application/xml'] = 'far fa-file-code';
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $icon = $content_type_list[$finfo->file($file_path)]??'far fa-file';

        return $icon;
    }

    function show()
    {
        $content_type_list = array();
        $content_type_list['txt']  = 'text/plain';
        $content_type_list['html'] = 'text/html';
        $content_type_list['csv']  = 'text/csv';
        $content_type_list['pdf']  = 'application/pdf';
        $content_type_list['rtf']  = 'application/rtf';
        $content_type_list['doc']  = 'application/msword';
        $content_type_list['docx'] = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
        $content_type_list['xls']  = 'application/vnd.ms-excel';
        $content_type_list['xlsx'] = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        $content_type_list['ppt']  = 'application/vnd.ms-powerpoint';
        $content_type_list['pptx'] = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
        $content_type_list['odt']  = 'application/vnd.oasis.opendocument.text';
        $content_type_list['ods']  = 'application/vnd.oasis.opendocument.spreadsheet';
        $content_type_list['jpeg'] = 'image/jpeg';
        $content_type_list['jpg']  = 'image/jpeg';
        $content_type_list['png']  = 'image/png';
        $content_type_list['gif']  = 'image/gif';
        $content_type_list['svg']  = 'image/svg+xml';
        $content_type_list['xml']  = 'application/xml';
        $content_type_list['zip']  = 'application/zip';
        $content_type_list['rar']  = 'application/x-rar-compressed';
        $content_type_list['bz']   = 'application/x-bzip';
        $content_type_list['bz2']  = 'application/x-bzip2';
        $content_type_list['tar']  = 'application/x-tar';

        $block_extensions = ['php', 'php3', 'php4', 'phtml', 'pl', 'py', 'jsp', 'asp', 'htm', 'shtml', 'sh', 'cgi', 'htaccess','phar','pht'];


        $folder = 'tmp/';
        $response = array();
        if (isset($_FILES['fileName']))
        {
            $file = $_FILES['fileName'];
            if( $file['error'] === 0 && $file['size'] > 0 )
            {
                $path = $folder.$file['name'];

                // check blocked file extension, not using finfo because file.php.2 problem
                foreach ($block_extensions as $block_extension)
                {
                    if (strpos(strtolower($file['name']), ".{$block_extension}") !== false)
                    {
                        $response = array();
                        $response['type'] = 'error';
                        $response['msg']  = AdiantiCoreTranslator::translate('Extension not allowed');
                        echo json_encode($response);
                        return;
                    }
                }



                // check file extension
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                if (!in_array($finfo->file($file['tmp_name']), array_values($content_type_list)))
                {
                    $response = array();
                    $response['type'] = 'error';
                    $response['msg'] = AdiantiCoreTranslator::translate('Extension not allowed');
                    echo json_encode($response);
                    return;
                }

                $this->checkSecurity($file);


                if (is_writable($folder) )
                {
                    if( move_uploaded_file( $file['tmp_name'], $path ) )
                    {
                        $response['type'] = 'success';
                        $response['fileName'] = $file['name'];
                    }
                    else
                    {
                        $response['type'] = 'error';
                        $response['msg'] = '';
                    }
                }
                else
                {
                    $response['type'] = 'error';
                    $response['msg'] = "Permission denied: {$path}";
                }
                echo json_encode($response);
            }
        }
    }

    /**
     * Check the security of the uploaded file
     */
    public function checkSecurity($file)
    {
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!isset($this->content_type_list[$file_extension])) {
            $this->renameAndLog($file, 'Unknown or unsupported file type');
        }

        $expected_mime_type = $this->content_type_list[$file_extension];
        $actual_mime_type = $this->getMimeType($file['tmp_name']);

        if ($actual_mime_type === false) {
            $this->renameAndLog($file, 'Failed to detect MIME type');
        }

        $file_content = file_get_contents($file['tmp_name']);
        $malicious_info = $this->containsObfuscatedMaliciousCode($file_content);

        if ($malicious_info) {
            $this->renameAndLog(
                $file,
                'File Malicous detected',
                $malicious_info['line'],
                $malicious_info['pattern']
            );
        }


    }

    /**
     * Get MIME type using finfo or alternative methods
     */
    private function getMimeType($file_path)
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file_path);
        finfo_close($finfo);

        if (!$mime_type && @getimagesize($file_path)) {
            $image_info = getimagesize($file_path);
            return $image_info['mime'];
        }

        return $mime_type ?: false;
    }

    /**
     * Get MIME type using finfo or alternative methods
     */
    private function containsObfuscatedMaliciousCode($content)
    {
        $pattern = '/<\?(php|=)|\b(eval|exec|shell_exec|passthru|base64_decode|gzinflate|str_rot13)\b/i';

        if (preg_match($pattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
            $offset = $matches[0][1];
            $line = substr_count(substr($content, 0, $offset), "\n") + 1;

            return [
                'line' => $line,
                'pattern' => $matches[0][0]
            ];
        }

        return false;
    }

    /**
     * Rename the file to .suspicious-detected and log the event
     */
    private function renameAndLog($file, $reason, $line = null, $pattern = null)
    {
        $new_file_name = $file['tmp_name'] . '.suspicious-detected';
        rename($file['tmp_name'], $new_file_name);

        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
        $session_id = session_id() ?: 'NO_SESSION';
        $session_file = $this->getSessionFilePath($session_id);

        $log_message = sprintf(
            "[%s] Upload rejected and file renamed: %s\nIP Address: %s\nSession ID: %s\nSession File: %s\nFile: %s\nNew File Name: %s\nLine: %s\nPattern: %s\n\n",
            date('Y-m-d H:i:s'),
            $reason,
            $ip_address,
            $session_id,
            $session_file,
            $file['name'],
            $new_file_name,
            $line ?? 'N/A',
            $pattern ?? 'N/A'
        );

        file_put_contents($this->log_file, $log_message, FILE_APPEND);

        $response = [
            'type' => 'error',
            'msg'  => "Upload rejected: $reason. "
        ];
        echo json_encode($response);
        exit;
    }

    /**
     * Get the full path of the session file based on ID
     */
    private function getSessionFilePath($session_id)
    {
        $session_save_path = session_save_path() ?: sys_get_temp_dir();
        return $session_save_path . DIRECTORY_SEPARATOR . 'sess_' . $session_id;
    }

}
