<?php
namespace Adianti\Service;

use Adianti\Core\AdiantiCoreTranslator;
use Adianti\Core\AdiantiApplicationConfig;

/**
 * File uploader listener
 *
 * @version    7.6
 * @package    service
 * @author     Nataniel Rabaioli
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    https://adiantiframework.com.br/license
 */
class AdiantiUploaderService
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
	
	
    function show($param)
    {
        $ini  = AdiantiApplicationConfig::get();
        $seed = APPLICATION_NAME . ( !empty($ini['general']['seed']) ? $ini['general']['seed'] : 's8dkld83kf73kf094' );
        $block_extensions = ['php', 'php3', 'php4', 'phtml', 'pl', 'py', 'jsp', 'asp', 'htm', 'shtml', 'sh', 'cgi', 'htaccess' ,'phar'];
        
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
                
                if (!empty($param['extensions']))
                {
                    $name = $param['name'];
                    $extensions = unserialize(base64_decode( $param['extensions'] ));
                    $hash = md5("{$seed}{$name}".base64_encode(serialize($extensions)));
                    
                    if ($hash !== $param['hash'])
                    {
                        $response = array();
                        $response['type'] = 'error';
                        $response['msg']  = AdiantiCoreTranslator::translate('Hash error');
                        echo json_encode($response);
                        return;
                    }
                    
                    // check allowed file extension
                    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                    
                    if (!in_array(strtolower($ext),  $extensions))
                    {
                        $response = array();
                        $response['type'] = 'error';
                        $response['msg']  = AdiantiCoreTranslator::translate('Extension not allowed');
                        echo json_encode($response);
                        return;
                    }
					

					
			       
                }

                //check files contents
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
                    $response['msg']  = AdiantiCoreTranslator::translate('Permission denied') . ": {$path}";
                }
                echo json_encode($response);
            }
            else
            {
                $response['type'] = 'error';
                $response['msg']  = AdiantiCoreTranslator::translate('Server has received no file') . '. ' . AdiantiCoreTranslator::translate('Check the server limits') .  '. ' . AdiantiCoreTranslator::translate('The current limit is') . ' ' . self::getMaximumFileUploadSizeFormatted();
                echo json_encode($response);
            }
        }
        else
        {
            $response['type'] = 'error';
            $response['msg']  = AdiantiCoreTranslator::translate('Server has received no file') . '. ' . AdiantiCoreTranslator::translate('Check the server limits') .  '. ' . AdiantiCoreTranslator::translate('The current limit is') . ' ' . self::getMaximumFileUploadSizeFormatted();
            echo json_encode($response);
        }
    }
			
    /**
     *
     */
    public static function getMaximumFileUploadSizeFormatted()  
    {  
        $post_max_size = self::convertSizeToBytes(ini_get('post_max_size'));
        $upld_max_size = self::convertSizeToBytes(ini_get('upload_max_filesize'));  
        
        if ($post_max_size < $upld_max_size)
        {
            return 'post_max_size: ' . ini_get('post_max_size');
        }
        
        return 'upload_max_filesize: ' .ini_get('upload_max_filesize');
    }
    
    /**
     *
     */
    public static function getMaximumFileUploadSize()  
    {  
        return min(self::convertSizeToBytes(ini_get('post_max_size')), self::convertSizeToBytes(ini_get('upload_max_filesize')));  
    }  
    
    /**
     *
     */
    public static function convertSizeToBytes($size)
    {
        $suffix = strtoupper(substr($size, -1));
        if (!in_array($suffix,array('P','T','G','M','K'))){
            return (int)$size;  
        } 
        $value = substr($size, 0, -1);
        switch ($suffix) {
            case 'P':
                $value *= 1024;
                // intended
            case 'T':
                $value *= 1024;
                // intended
            case 'G':
                $value *= 1024;
                // intended
            case 'M':
                $value *= 1024;
                // intended
            case 'K':
                $value *= 1024;
                break;
        }
        return (int)$value;
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
