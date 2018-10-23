<?php
namespace Classes;

use Yaf\Log;

class Upload {

	public $max_size				= 0;
	public $max_width				= 0;
	public $max_height				= 0;
	public $max_filename			= 0;
	public $allowed_types			= "";
	public $file_temp				= "";
	public $file_name				= "";
	public $orig_name				= "";
	public $file_type				= "";
	public $file_size				= "";
	public $file_ext				= "";
	public $file_md5				= "";
	public $upload_path				= "";
	public $overwrite				= false;
	public $encrypt_name			= false;
	public $use_file_md5			= false;
	public $is_image				= false;
	public $image_width				= '';
	public $image_height			= '';
	public $image_type				= '';
	public $image_size_str			= '';
	public $error_msg				= array();
	public $mimes					= array();
	public $remove_spaces			= true;
	public $xss_clean				= false;
	public $temp_prefix				= "temp_file_";
	public $client_name				= '';

	protected $_file_name_override	= '';
	protected $_not_need_move_file 	= false;

	/**
	 * Constructor
	 *
	 * @access	public
	 */
	public function __construct($props = array())
	{
		if (count($props) > 0){
			$this->initialize($props);
		}
	}
	
	
	/**
	 * Initialize preferences
	 *
	 * @param	array
	 * @return	void
	 */
	public function initialize($config = array())
	{
		$defaults = array(
							'max_size'			=> 0,
							'max_width'			=> 0,
							'max_height'		=> 0,
							'max_filename'		=> 0,
							'allowed_types'		=> "",
							'file_temp'			=> "",
							'file_name'			=> "",
							'orig_name'			=> "",
							'file_type'			=> "",
							'file_size'			=> "",
							'file_ext'			=> "",
							'upload_path'		=> "",
							'overwrite'			=> false,
							'use_file_md5'		=> false,
							'encrypt_name'		=> false,
							'is_image'			=> false,
							'image_width'		=> '',
							'image_height'		=> '',
							'image_type'		=> '',
							'image_size_str'	=> '',
							'error_msg'			=> array(),
							'mimes'				=> array(),
							'remove_spaces'		=> true,
							'xss_clean'			=> false,
							'temp_prefix'		=> "temp_file_",
							'client_name'		=> ''
						);


		foreach ($defaults as $key => $val){
			if (isset($config[$key])){
				$method = 'set_'.$key;
				if (method_exists($this, $method)){
					$this->$method($config[$key]);
				}else{
					$this->$key = $config[$key];
				}
			}else{
				$this->$key = $val;
			}
		}

		// if a file_name was provided in the config, use it instead of the user input
		// supplied file name for all uploads until initialized again
		$this->_file_name_override = $this->file_name;
	}
	
	protected function _prepare()
	{
		if(! isset($_FILES) ){
			$this->set_error('upload_no_file_selected');
			return false;
		}
		
		$files = array();
		foreach($_FILES as $key => $value){
			if(is_array($_FILES[$key]['name'])){
				$_key = $this->_recursion($_FILES[$key]['name']);
				$array = explode("_", $_key);
				$name = $_FILES[$key]['name'];
				$type = $_FILES[$key]['type'];
				$tmp_name = $_FILES[$key]['tmp_name'];
				$error = $_FILES[$key]['error'];
				$size = $_FILES[$key]['size'];
				foreach($array as $v){
					$name = $name[$v];
					$type = $type[$v];
					$tmp_name = $tmp_name[$v];
					$error = $error[$v];
					$size = $size[$v];
				}
				$tmpfiles = array(
						'name' => $name,
						'type' => $type,
						'tmp_name' => $tmp_name,
						'error' => $error,
						'size' => $size,
					);
				$files[$key ."_". $_key] = $tmpfiles;
			}else{
				$files[$key] = $value;
			}
		}
		
		$_FILES = $files;
		return true;
	}
	
	protected function _recursion($unit)
	{
		$res = '';
		foreach($unit as $key => $value){
			if( is_array($unit[$key]) ){
				$_key = $this->_recursion($unit[$key]);
				$res = $key ."_". $_key;
			}else{
				$res = $key;
			}
		}
		return $res;
	}
	
	/**
	 * Upload more file
	 *
	 * $param       string  field
	 * @return      array
	 */
	public function do_multiple_upload()
	{

		if( !$this->_prepare() ){
			return false;
		}
		
		$errors = array();
		$files = array();
		$original_upload_path = $this->upload_path;
		foreach ($_FILES as $key => $value){
			$this->upload_path = $original_upload_path;
			if( ! $this->do_upload($key)){
				$files[$key] = $this->display_errors('', '');
				$this->error_msg = array();
			}else{
				$files[$key] = $this->data();
			}
		}
		
		return array(
				'error' => $errors,
				'files' => $files
			);
	}
	
	
	/**
	 * Perform the file upload
	 *
	 * @return	bool
	 */
	public function do_upload($field = 'userfile')
	{
		// Is $_FILES[$field] set? If not, no reason to continue.
		if ( ! isset($_FILES[$field]) || count($_FILES[$field]['name']) > 1 ){
			$this->set_error('upload_no_file_selected');
			return false;
		}

		// Is the upload path valid?
		if ( ! $this->validate_upload_path()){
			// errors will already be set by validate_upload_path() so just return false
			return false;
		}

		// Was the file able to be uploaded? If not, determine the reason why.
		if (!is_uploaded_file($_FILES[$field]['tmp_name'])){
			
			$error = ( ! isset($_FILES[$field]['error'])) ? 4 : $_FILES[$field]['error'];

			switch($error){
				case 1:	// UPLOAD_ERR_INI_SIZE
					$this->set_error('upload_file_exceeds_limit');
					break;
				case 2: // UPLOAD_ERR_FORM_SIZE
					$this->set_error('upload_file_exceeds_form_limit');
					break;
				case 3: // UPLOAD_ERR_PARTIAL
					$this->set_error('upload_file_partial');
					break;
				case 4: // UPLOAD_ERR_NO_FILE
					$this->set_error('upload_no_file_selected');
					break;
				case 6: // UPLOAD_ERR_NO_TMP_DIR
					$this->set_error('upload_no_temp_directory');
					break;
				case 7: // UPLOAD_ERR_CANT_WRITE
					$this->set_error('upload_unable_to_write_file');
					break;
				case 8: // UPLOAD_ERR_EXTENSION
					$this->set_error('upload_stopped_by_extension');
					break;
				default :
					$this->set_error('upload_no_file_selected');
					break;
			}

			return false;
		}


		// Set the uploaded data as class variables
		$this->file_temp = $_FILES[$field]['tmp_name'];
		$this->file_md5 = md5_file($_FILES[$field]['tmp_name']);
		$this->file_size = $_FILES[$field]['size'];
		$this->_file_mime_type($_FILES[$field]);
		$this->file_type = preg_replace("/^(.+?);.*$/", "\\1", $this->file_type);
		$this->file_type = strtolower(trim(stripslashes($this->file_type), '"'));
		$this->file_name = $this->_prep_filename($_FILES[$field]['name']);
		$this->file_ext	 = $this->get_extension($this->file_name);
		$this->client_name = $this->file_name;

		// Is the file type allowed to be uploaded?
		if ( !$this->is_allowed_filetype()){
			$this->set_error('upload_invalid_filetype');
			return false;
		}

		// if we're overriding, let's now make sure the new name and type is allowed
		if ($this->_file_name_override != ''){
			$this->file_name = $this->_prep_filename($this->_file_name_override);

			// If no extension was provided in the file_name config item, use the uploaded one
			if (strpos($this->_file_name_override, '.') === false){
				$this->file_name .= $this->file_ext;
			}else{ // An extension was provided, lets have it!
				$this->file_ext	 = $this->get_extension($this->_file_name_override);
			}

			if ( ! $this->is_allowed_filetype(true)){
				$this->set_error('upload_invalid_filetype');
				return false;
			}
		}

		// Convert the file size to kilobytes
		if ($this->file_size > 0){
			$this->file_size = round($this->file_size/1024, 2);
		}

		// Is the file size within the allowed maximum?
		if ( ! $this->is_allowed_filesize()){
			$this->set_error('upload_invalid_filesize');
			return false;
		}

		// Are the image dimensions within the allowed size?
		// Note: This can fail if the server has an open_basdir restriction.
		if ( ! $this->is_allowed_dimensions()){
			$this->set_error('upload_invalid_dimensions');
			return false;
		}

		// Sanitize the file name for security
		$this->file_name = $this->clean_file_name($this->file_name);

		// Truncate the file name if it's too long
		if ($this->max_filename > 0){
			$this->file_name = $this->limit_filename_length($this->file_name, $this->max_filename);
		}

		// Remove white spaces in the name
		if ($this->remove_spaces == true){
			$this->file_name = preg_replace("/\s+/", "_", $this->file_name);
		}

		/*
		 * Validate the file name
		 * This function appends an number onto the end of
		 * the file if one with the same name already exists.
		 * If it returns false there was a problem.
		 */
		$this->orig_name = $this->file_name;

		if ($this->overwrite == false){
			$this->file_name = $this->set_filename($this->upload_path, $this->file_name);

			if ($this->file_name === false){
				return false;
			}
		}

		/*
		 * Run the file through the XSS hacking filter
		 * This helps prevent malicious code from being
		 * embedded within a file.  Scripts can easily
		 * be disguised as images or other file types.
		 */
		if ($this->xss_clean){
			if ($this->do_xss_clean() === false){
				$this->set_error('upload_unable_to_write_file');
				return false;
			}
		}

		/*
		 * Move the file to the final destination
		 * To deal with different server configurations
		 * we'll attempt to use copy() first.  If that fails
		 * we'll use move_uploaded_file().  One of the two should
		 * reliably work in most environments
		 */
//		var_dump($this->file_temp);
//		var_dump($this->upload_path);
//		var_dump($this->file_name);exit();
		if ( !$this->_not_need_move_file  && ! @copy($this->file_temp, $this->upload_path.$this->file_name)){
			if ( ! @move_uploaded_file($this->file_temp, $this->upload_path.$this->file_name)){
				$this->set_error('upload_destination_error');
				return false;
			}
		}else{
//            $ress=$this->kzUploadCloud('bucket_public_global/decision/lzb/a0/76/'.$this->file_name);
//            $ress1=$this->kzGetFileUrl('bucket_public_global/decision/lzb/a0/76/'.$this->file_name);
        }



		/*
		 * Set the finalized image dimensions
		 * This sets the image width/height (assuming the
		 * file was an image).  We use this information
		 * in the "data" function.
		 */
		$this->set_image_properties($this->upload_path.$this->file_name);

		return true;
	}


    /**
     * @获取文件的访问地址
     * @param $path string 相对路径
     * @param $expires intval 过期时间 单位；s
     * @param $protocol string http协议
     * @return boolean | string
     **/
    public static function kzGetFileUrl($path, $expires = 1800, $protocol = 'https')
    {
        $config = \Yaf\Registry::get("config");

        $private = "bucket_private_global";
        $public = "bucket_public_global";

        if(preg_match("/^".$private."/i", $path) || preg_match("/^".$public."/i", $path)){

            $config = \Yaf\Registry::get("config");
            $fp = fsockopen("unix://" . '/run/file_proxy/store_proxy.sock', -1, $errno, $errstr);
            if (!$fp) {
                return false;
            } else {
                $param = [];
                $param['expires'] = $expires;
                $param['protocol'] = $protocol;
                $params = "action=get_ucloud_url&params=" . json_encode($param);

                $uri = "/" . ltrim($path, '/') . "?" . $params;
                $out = "GET {$uri} HTTP/1.0\r\n";
                $out .= "Host: www.kuaizi.co\r\n";
                $out .= "Connection: Close\r\n\r\n";
                $return = '';
                fwrite($fp, $out);
                while (!feof($fp)) {
                    $return .= fgets($fp, 128);
                }
                fclose($fp);

                $pos = strpos($return, "\r\n\r\n");
                if($pos === false){
                    $res = array('header' => $return);
                }else{
                    $header = substr($return, 0, $pos);
                    $body = substr($return, $pos + 2 * strlen("\r\n"));
                    $res = array('body' => $body, 'header' => $header);
                }

                if(!preg_match("/200/is", $res["header"])){
                    return false;
                }

                $body = json_decode($res['body'], true);
                return $body['url'];
            }
        }

        return $path;
    }


    /**
     * @把图片上传到云端
     * @param $path string 相对路径
     * @param $return_cloud_url intval 是否返回云端地址
     * @param $expires intval 过期时间 单位秒
     * @return boolean | string
     **/
    public static function kzUploadCloud($path, $return_cloud_url = 0, $expires = 5)
    {
        $environ = ini_get('yaf.environ');
        $config = \Yaf\Application::app()->getConfig();;
        $param = [];
        $param['return_external_url'] = $return_cloud_url;
        $param['expires'] = $expires;
        $params = "action=upload&params=" . json_encode($param);
        $uri = "/" . ltrim($path, '/') .'?'. $params;

        $i = 0;
        $return = false;
        while($i < 5){
            $fp = fsockopen("unix://" . '/run/file_proxy/store_proxy.sock', -1, $errno, $errstr);
            if ($fp) {
                $out = "POST {$uri} HTTP/1.0\r\n";
                $out .= "Host: www.kuaizi.co\r\n";
                $out .= "Content-Length: 0\r\n";
                $out .= "Connection: Close\r\n\r\n";
                $return_text = '';
                fwrite($fp, $out);
                stream_set_timeout($fp, 60);
                while (!feof($fp)) {
                    $return_text .= fgets($fp, 128);
                }
                fclose($fp);
                $pos = strpos($return_text, "\r\n\r\n");
                if($pos === false){
                    $res = array('header' => $return_text);
                }else{
                    $header = substr($return_text, 0, $pos);
                    $body = substr($return_text, $pos + 2 * strlen("\r\n"));
                    $res = array('body' => $body, 'header' => $header);
                }

                if(preg_match("/200/is", $res["header"])){
                    $upload_res = $res['body'];
                    if($upload_res){
                        $upload_res = json_decode($upload_res, true);
                        if($upload_res['code'] === 0){
                            if($return_cloud_url){
                                $return = $upload_res['url'];
                            }else{
                                $return = true;
                            }
                        }
                    }else{
                        $return = true;
                    }
                    break;
                }
            }
            $i++;
        }
        return $return;
    }

	// --------------------------------------------------------------------

	/**
	 * Finalized Data Array
	 *
	 * Returns an associative array containing all of the information
	 * related to the upload, allowing the developer easy access in one array.
	 *
	 * @return	array
	 */
	public function data()
	{
		$data = array (
					'file_name'			=> $this->file_name,
					'file_type'			=> $this->file_type,
					'file_path'			=> $this->upload_path,
					'full_path'			=> $this->upload_path.$this->file_name,
					'raw_name'			=> str_replace($this->file_ext, '', $this->file_name),
					'orig_name'			=> $this->orig_name,
					'client_name'		=> $this->client_name,
					'file_ext'			=> $this->file_ext,
					'file_size'			=> $this->file_size,
					'file_md5'			=> $this->file_md5,
				);
					
		if($this->is_image()){
			$data['is_image'] = $this->is_image();
			$data['image_width'] = $this->image_width;
			$data['image_height'] = $this->image_height;
			$data['image_type'] = $this->image_type;
			$data['image_size_str'] = $this->image_size_str;
		}
		
		return $data;
	}

	// --------------------------------------------------------------------

	/**
	 * Set Upload Path
	 *
	 * @param	string
	 * @return	void
	 */
	public function set_upload_path($path)
	{
		// Make sure it has a trailing slash
		$this->upload_path = rtrim($path, '/').'/';
	}

	// --------------------------------------------------------------------

	/**
	 * Set the file name
	 *
	 * This function takes a filename/path as input and looks for the
	 * existence of a file with the same name. If found, it will append a
	 * number to the end of the filename to avoid overwriting a pre-existing file.
	 *
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	public function set_filename($path, $filename)
	{
		if($this->use_file_md5 == true){
//			$path = $path .'lzb/'. substr($this->file_md5, 0, 2) .'/'. substr($this->file_md5, 2, 2) .'/';
//			$path = $path . substr($this->file_md5, 0, 2) .'/'. substr($this->file_md5, 2, 2) .'/';
			$path = $path . 'decision/'.substr($this->file_md5, 0, 2) .'/'. substr($this->file_md5, 2, 2) .'/';
			$this->set_upload_path($path);
			if(!is_dir($path) && !mkdir($path, 0777, true)){
				$this->set_error('failed_to_create_directory');
				return false;
			}
			
			$filename = $this->file_md5.$this->file_ext;
			//以MD5值命名的文件存在就不需要移动了
			if(file_exists($path.$filename)){
				$this->_not_need_move_file = true;
				return $filename;
			}
		}
		
		if ($this->encrypt_name == true){
			mt_srand();
			$filename = md5(uniqid(mt_rand())).$this->file_ext;
		}
		
		if ( ! file_exists($path.$filename)){
			return $filename;
		}

		$filename = str_replace($this->file_ext, '', $filename);

		$new_filename = '';
		for ($i = 1; $i < 100; $i++){
			if ( ! file_exists($path.$filename.$i.$this->file_ext)){
				$new_filename = $filename.$i.$this->file_ext;
				break;
			}
		}

		if ($new_filename == ''){
			$this->set_error('upload_bad_filename');
			return false;
		}else{
			return $new_filename;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Set Maximum File Size
	 *
	 * @param	integer
	 * @return	void
	 */
	public function set_max_filesize($n)
	{
		$this->max_size = ((int) $n < 0) ? 0: (int) $n;
	}

	// --------------------------------------------------------------------

	/**
	 * Set Maximum File Name Length
	 *
	 * @param	integer
	 * @return	void
	 */
	public function set_max_filename($n)
	{
		$this->max_filename = ((int) $n < 0) ? 0: (int) $n;
	}

	// --------------------------------------------------------------------

	/**
	 * Set Maximum Image Width
	 *
	 * @param	integer
	 * @return	void
	 */
	public function set_max_width($n)
	{
		$this->max_width = ((int) $n < 0) ? 0: (int) $n;
	}

	// --------------------------------------------------------------------

	/**
	 * Set Maximum Image Height
	 *
	 * @param	integer
	 * @return	void
	 */
	public function set_max_height($n)
	{
		$this->max_height = ((int) $n < 0) ? 0: (int) $n;
	}

	// --------------------------------------------------------------------

	/**
	 * Set Allowed File Types
	 *
	 * @param	string
	 * @return	void
	 */
	public function set_allowed_types($types)
	{
		if ( ! is_array($types) && $types == '*'){
			$this->allowed_types = '*';
			return;
		}
		$this->allowed_types = explode('|', $types);
	}

	// --------------------------------------------------------------------

	/**
	 * Set Image Properties
	 *
	 * Uses GD to determine the width/height/type of image
	 *
	 * @param	string
	 * @return	void
	 */
	public function set_image_properties($path = '')
	{
		if ( ! $this->is_image()){
			return;
		}

		if (function_exists('getimagesize')){
			if (false !== ($D = @getimagesize($path))){
				
				$types = array(1 => 'gif', 2 => 'jpeg', 3 => 'png');

				$this->image_width		= $D['0'];
				$this->image_height		= $D['1'];
				$this->image_type		= ( ! isset($types[$D['2']])) ? 'unknown' : $types[$D['2']];
				$this->image_size_str	= $D['3'];  // string containing height and width
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Set XSS Clean
	 *
	 * Enables the XSS flag so that the file that was uploaded
	 * will be run through the XSS filter.
	 *
	 * @param	bool
	 * @return	void
	 */
	public function set_xss_clean($flag = false)
	{
		$this->xss_clean = ($flag == true) ? true : false;
	}

	// --------------------------------------------------------------------

	/**
	 * Validate the image
	 *
	 * @return	bool
	 */
	public function is_image()
	{
		// IE will sometimes return odd mime-types during upload, so here we just standardize all
		// jpegs or pngs to the same file type.

		$png_mimes  = array('image/x-png');
		$jpeg_mimes = array('image/jpg', 'image/jpe', 'image/jpeg', 'image/pjpeg');

		if (in_array($this->file_type, $png_mimes)){
			$this->file_type = 'image/png';
		}

		if (in_array($this->file_type, $jpeg_mimes)){
			$this->file_type = 'image/jpeg';
		}

		$img_mimes = array(
							'image/gif',
							'image/jpeg',
							'image/png',
						);

		return (in_array($this->file_type, $img_mimes, true)) ? true : false;
	}

	// --------------------------------------------------------------------

	/**
	 * Verify that the filetype is allowed
	 *
	 * @return	bool
	 */
	public function is_allowed_filetype($ignore_mime = false)
	{
		if ($this->allowed_types == '*'){
			return true;
		}

		if (count($this->allowed_types) == 0 || ! is_array($this->allowed_types)){
			$this->set_error('upload_no_file_types');
			return false;
		}
		$ext = strtolower(ltrim($this->file_ext, '.'));
		if ( !in_array($ext, $this->allowed_types)){
			return false;
		}



		///////////////////////////////////////////////////
		//Added by ocean 20160802
		//CI has some bugs.It will cause the file uploading failed, eg: "test.xls"
		//So do simple tweaks here.
		$not_verify_mime_ext_arr = array("xls");
		if(in_array($ext, $not_verify_mime_ext_arr)){
			if(in_array($ext, $this->allowed_types)){
				return true;
			}
		}
		//////////////////////////////////////////////////
		
		// Images get some additional checks
		$image_types = array('gif', 'jpg', 'jpeg', 'png', 'jpe');

		if (in_array($ext, $image_types)){
			if (getimagesize($this->file_temp) === false){
				return false;
			}
		}
		
		if ($ignore_mime === true){
			return true;
		}

		$mime = $this->mimes_types($ext);
		if (is_array($mime)){
			if (in_array($this->file_type, $mime, true)){
				return true;
			}
		}elseif ($mime == $this->file_type){
			return true;
		}

		return true;
	}

	// --------------------------------------------------------------------

	/**
	 * Verify that the file is within the allowed size
	 *
	 * @return	bool
	 */
	public function is_allowed_filesize()
	{
		if ($this->max_size != 0  AND  $this->file_size > $this->max_size){
			return false;
		}else{
			return true;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Verify that the image is within the allowed width/height
	 *
	 * @return	bool
	 */
	public function is_allowed_dimensions()
	{
		if ( ! $this->is_image()){
			return true;
		}

		if (function_exists('getimagesize')){
			$D = @getimagesize($this->file_temp);

			if ($this->max_width > 0 && $D['0'] > $this->max_width){
				return false;
			}

			if ($this->max_height > 0 && $D['1'] > $this->max_height){
				return false;
			}

			return true;
		}

		return true;
	}

	// --------------------------------------------------------------------

	/**
	 * Validate Upload Path
	 *
	 * Verifies that it is a valid upload path with proper permissions.
	 *
	 *
	 * @return	bool
	 */
	public function validate_upload_path()
	{
		if ($this->upload_path == ''){
			$this->set_error('upload_no_filepath1');
			return false;
		}

		if (function_exists('realpath') AND @realpath($this->upload_path) !== false){
			$this->upload_path = str_replace("\\", "/", realpath($this->upload_path));
		}

		if ( ! @is_dir($this->upload_path)){
			$this->set_error('upload_no_filepath2');
			return false;
		}

		/* if ( ! is_really_writable($this->upload_path)){
			$this->set_error('upload_not_writable');
			return false;
		} */

		$this->upload_path = preg_replace("/(.+?)\/*$/", "\\1/",  $this->upload_path);
		return true;
	}

	// --------------------------------------------------------------------

	/**
	 * Extract the file extension
	 *
	 * @param	string
	 * @return	string
	 */
	public function get_extension($filename)
	{
		$x = explode('.', $filename);
		return '.'.end($x);
	}

	// --------------------------------------------------------------------

	/**
	 * Clean the file name for security
	 *
	 * @param	string
	 * @return	string
	 */
	public function clean_file_name($filename)
	{
		$bad = array(
						"<!--",
						"-->",
						"'",
						"<",
						">",
						'"',
						'&',
						'$',
						'=',
						';',
						'?',
						'/',
						"%20",
						"%22",
						"%3c",		// <
						"%253c",	// <
						"%3e",		// >
						"%0e",		// >
						"%28",		// (
						"%29",		// )
						"%2528",	// (
						"%26",		// &
						"%24",		// $
						"%3f",		// ?
						"%3b",		// ;
						"%3d"		// =
					);

		$filename = str_replace($bad, '', $filename);

		return stripslashes($filename);
	}

	// --------------------------------------------------------------------

	/**
	 * Limit the File Name Length
	 *
	 * @param	string
	 * @return	string
	 */
	public function limit_filename_length($filename, $length)
	{
		if (strlen($filename) < $length){
			return $filename;
		}

		$ext = '';
		if (strpos($filename, '.') !== false){
			$parts		= explode('.', $filename);
			$ext		= '.'.array_pop($parts);
			$filename	= implode('.', $parts);
		}

		return substr($filename, 0, ($length - strlen($ext))).$ext;
	}

	// --------------------------------------------------------------------

	/**
	 * Runs the file through the XSS clean function
	 *
	 * This prevents people from embedding malicious code in their files.
	 * I'm not sure that it won't negatively affect certain files in unexpected ways,
	 * but so far I haven't found that it causes trouble.
	 *
	 * @return	void
	 */
	public function do_xss_clean()
	{
		$file = $this->file_temp;

		if (filesize($file) == 0){
			return false;
		}

		if (function_exists('memory_get_usage') && memory_get_usage() && ini_get('memory_limit') != '')
		{
			$current = ini_get('memory_limit') * 1024 * 1024;

			// There was a bug/behavioural change in PHP 5.2, where numbers over one million get output
			// into scientific notation.  number_format() ensures this number is an integer
			// http://bugs.php.net/bug.php?id=43053

			$new_memory = number_format(ceil(filesize($file) + $current), 0, '.', '');

			ini_set('memory_limit', $new_memory); // When an integer is used, the value is measured in bytes. - PHP.net
		}

		// If the file being uploaded is an image, then we should have no problem with XSS attacks (in theory), but
		// IE can be fooled into mime-type detecting a malformed image as an html file, thus executing an XSS attack on anyone
		// using IE who looks at the image.  It does this by inspecting the first 255 bytes of an image.  To get around this
		// CI will itself look at the first 255 bytes of an image to determine its relative safety.  This can save a lot of
		// processor power and time if it is actually a clean image, as it will be in nearly all instances _except_ an
		// attempted XSS attack.

		if (function_exists('getimagesize') && @getimagesize($file) !== false){
			 // "b" to force binary
			if (($file = @fopen($file, 'rb')) === false){
				return false; // Couldn't open the file, return false
			}

			$opening_bytes = fread($file, 256);
			fclose($file);

			// These are known to throw IE into mime-type detection chaos
			// <a, <body, <head, <html, <img, <plaintext, <pre, <script, <table, <title
			// title is basically just in SVG, but we filter it anyhow

			if ( ! preg_match('/<(a|body|head|html|img|plaintext|pre|script|table|title)[\s>]/i', $opening_bytes)){
				return true; // its an image, no "triggers" detected in the first 256 bytes, we're good
			}else{
				return false;
			}
		}

		if (($data = @file_get_contents($file)) === false){
			return false;
		}

		$CI =& get_instance();
		return $CI->security->xss_clean($data, true);
	}

	// --------------------------------------------------------------------

	/**
	 * Set an error message
	 *
	 * @param	string
	 * @return	void
	 */
	public function set_error($msg)
	{
		if (is_array($msg)){
			foreach ($msg as $val){
				$this->error_msg[] = $val;
			}
		}else{
			$this->error_msg[] = $msg;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Display the error message
	 *
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	public function display_errors($open = '<p>', $close = '</p>')
	{
		$str = '';
		foreach ($this->error_msg as $val){
			$str .= $open.$val.$close;
		}

		return $str;
	}

	// --------------------------------------------------------------------

	/**
	 * List of Mime Types
	 *
	 * This is a list of mime types.  We use it to validate
	 * the "allowed types" set by the developer
	 *
	 * @param	string
	 * @return	string
	 */
	public function mimes_types($mime)
	{
		$mimes = array(	'hqx'	=>	'application/mac-binhex40',
						'cpt'	=>	'application/mac-compactpro',
						'csv'	=>	array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel'),
						'bin'	=>	'application/macbinary',
						'dms'	=>	'application/octet-stream',
						'lha'	=>	'application/octet-stream',
						'lzh'	=>	'application/octet-stream',
						'exe'	=>	array('application/octet-stream', 'application/x-msdownload'),
						'class'	=>	'application/octet-stream',
						'psd'	=>	'application/x-photoshop',
						'so'	=>	'application/octet-stream',
						'sea'	=>	'application/octet-stream',
						'dll'	=>	'application/octet-stream',
						'oda'	=>	'application/oda',
						'pdf'	=>	array('application/pdf', 'application/x-download'),
						'ai'	=>	'application/postscript',
						'eps'	=>	'application/postscript',
						'ps'	=>	'application/postscript',
						'smi'	=>	'application/smil',
						'smil'	=>	'application/smil',
						'mif'	=>	'application/vnd.mif',
						'xls'	=>	array('application/excel', 'application/vnd.ms-excel', 'application/msexcel'),
						'ppt'	=>	array('application/powerpoint', 'application/vnd.ms-powerpoint'),
						'wbxml'	=>	'application/wbxml',
						'wmlc'	=>	'application/wmlc',
						'dcr'	=>	'application/x-director',
						'dir'	=>	'application/x-director',
						'dxr'	=>	'application/x-director',
						'dvi'	=>	'application/x-dvi',
						'gtar'	=>	'application/x-gtar',
						'gz'	=>	'application/x-gzip',
						'php'	=>	'application/x-httpd-php',
						'php4'	=>	'application/x-httpd-php',
						'php3'	=>	'application/x-httpd-php',
						'phtml'	=>	'application/x-httpd-php',
						'phps'	=>	'application/x-httpd-php-source',
						'js'	=>	'application/x-javascript',
						'swf'	=>	'application/x-shockwave-flash',
						'sit'	=>	'application/x-stuffit',
						'tar'	=>	'application/x-tar',
						'tgz'	=>	array('application/x-tar', 'application/x-gzip-compressed'),
						'xhtml'	=>	'application/xhtml+xml',
						'xht'	=>	'application/xhtml+xml',
						'zip'	=>  array('application/x-zip', 'application/zip', 'application/x-zip-compressed'),
						'mid'	=>	'audio/midi',
						'midi'	=>	'audio/midi',
						'mpga'	=>	'audio/mpeg',
						'mp2'	=>	'audio/mpeg',
						'mp3'	=>	array('audio/mpeg', 'audio/mpg', 'audio/mpeg3', 'audio/mp3'),
						'aif'	=>	'audio/x-aiff',
						'aiff'	=>	'audio/x-aiff',
						'aifc'	=>	'audio/x-aiff',
						'ram'	=>	'audio/x-pn-realaudio',
						'rm'	=>	'audio/x-pn-realaudio',
						'rpm'	=>	'audio/x-pn-realaudio-plugin',
						'ra'	=>	'audio/x-realaudio',
						'rv'	=>	'video/vnd.rn-realvideo',
						'wav'	=>	array('audio/x-wav', 'audio/wave', 'audio/wav'),
						'bmp'	=>	array('image/bmp', 'image/x-windows-bmp'),
						'gif'	=>	array('image/gif', 'image/png',  'image/x-png','image/jpeg', 'image/pjpeg'),
						'jpeg'	=>	array('image/gif', 'image/png',  'image/x-png','image/jpeg', 'image/pjpeg'),
						'jpg'	=>	array('image/gif', 'image/png',  'image/x-png','image/jpeg', 'image/pjpeg'),
						'jpe'	=>	array('image/gif', 'image/png',  'image/x-png','image/jpeg', 'image/pjpeg'),
						'png'	=>	array('image/gif', 'image/png',  'image/x-png','image/jpeg', 'image/pjpeg'),
						'tiff'	=>	'image/tiff',
						'tif'	=>	'image/tiff',
						'css'	=>	'text/css',
						'html'	=>	'text/html',
						'htm'	=>	'text/html',
						'shtml'	=>	'text/html',
						'txt'	=>	'text/plain',
						'text'	=>	'text/plain',
						'log'	=>	array('text/plain', 'text/x-log'),
						'rtx'	=>	'text/richtext',
						'rtf'	=>	'text/rtf',
						'xml'	=>	'text/xml',
						'xsl'	=>	'text/xml',
						'mpeg'	=>	'video/mpeg',
						'mpg'	=>	'video/mpeg',
						'mpe'	=>	'video/mpeg',
						'qt'	=>	'video/quicktime',
						'mov'	=>	'video/quicktime',
						'avi'	=>	'video/x-msvideo',
						'movie'	=>	'video/x-sgi-movie',
						'doc'	=>	'application/msword',
						'docx'	=>	array('application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip'),
						'xlsx'	=>	array('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip'),
						'word'	=>	array('application/msword', 'application/octet-stream'),
						'xl'	=>	'application/excel',
						'eml'	=>	'message/rfc822',
						'flv'	=>	'video/x-flv',
						'json' => array('application/json', 'text/json')
					);
					
		$this->mimes = $mimes;
		return ( ! isset($this->mimes[$mime])) ? false : $this->mimes[$mime];
	}

	// --------------------------------------------------------------------

	/**
	 * Prep Filename
	 *
	 * Prevents possible script execution from Apache's handling of files multiple extensions
	 * http://httpd.apache.org/docs/1.3/mod/mod_mime.html#multipleext
	 *
	 * @param	string
	 * @return	string
	 */
	protected function _prep_filename($filename)
	{
		if (strpos($filename, '.') === false || $this->allowed_types == '*'){
			return $filename;
		}

		$parts		= explode('.', $filename);
		$ext		= array_pop($parts);
		$filename	= array_shift($parts);

		foreach ($parts as $part){
			if ( ! in_array(strtolower($part), $this->allowed_types) || $this->mimes_types(strtolower($part)) === false){
				$filename .= '.'.$part.'_';
			}else{
				$filename .= '.'.$part;
			}
		}

		$filename .= '.'.$ext;

		return $filename;
	}

	// --------------------------------------------------------------------

	/**
	 * File MIME type
	 *
	 * Detects the (actual) MIME type of the uploaded file, if possible.
	 * The input array is expected to be $_FILES[$field]
	 *
	 * @param	array
	 * @return	void
	 */
	protected function _file_mime_type($file)
	{
		// We'll need this to validate the MIME info string (e.g. text/plain; charset=us-ascii)
		$regexp = '/^([a-z\-]+\/[a-z0-9\-\.\+]+)(;\s.+)?$/';

		/* Fileinfo extension - most reliable method
		 *
		 * Unfortunately, prior to PHP 5.3 - it's only available as a PECL extension and the
		 * more convenient FILEINFO_MIME_TYPE flag doesn't exist.
		 */
		if (function_exists('finfo_file')){
			$finfo = finfo_open(FILEINFO_MIME);
			 // It is possible that a false value is returned, if there is no magic MIME database file found on the system
			if (is_resource($finfo)){
				$mime = @finfo_file($finfo, $file['tmp_name']);
				finfo_close($finfo);

				/* According to the comments section of the PHP manual page,
				 * it is possible that this function returns an empty string
				 * for some files (e.g. if they don't exist in the magic MIME database)
				 */
				if (is_string($mime) && preg_match($regexp, $mime, $matches)){
					$this->file_type = $matches[1];
					return;
				}
			}
		}

		/* This is an ugly hack, but UNIX-type systems provide a "native" way to detect the file type,
		 * which is still more secure than depending on the value of $_FILES[$field]['type'], and as it
		 * was reported in issue #750 (https://github.com/EllisLab/CodeIgniter/issues/750) - it's better
		 * than mime_content_type() as well, hence the attempts to try calling the command line with
		 * three different functions.
		 *
		 * Notes:
		 *	- the DIRECTORY_SEPARATOR comparison ensures that we're not on a Windows system
		 *	- many system admins would disable the exec(), shell_exec(), popen() and similar functions
		 *	  due to security concerns, hence the function_exists() checks
		 */
		if (DIRECTORY_SEPARATOR !== '\\'){
			$cmd = 'file --brief --mime ' . escapeshellarg($file['tmp_name']) . ' 2>&1';

			if (function_exists('exec')){
				/* This might look confusing, as $mime is being populated with all of the output when set in the second parameter.
				 * However, we only neeed the last line, which is the actual return value of exec(), and as such - it overwrites
				 * anything that could already be set for $mime previously. This effectively makes the second parameter a dummy
				 * value, which is only put to allow us to get the return status code.
				 */
				$mime = @exec($cmd, $mime, $return_status);
				if ($return_status === 0 && is_string($mime) && preg_match($regexp, $mime, $matches)){
					$this->file_type = $matches[1];
					return;
				}
			}

			if ( (bool) @ini_get('safe_mode') === false && function_exists('shell_exec')){
				$mime = @shell_exec($cmd);
				if (strlen($mime) > 0){
					$mime = explode("\n", trim($mime));
					if (preg_match($regexp, $mime[(count($mime) - 1)], $matches)){
						$this->file_type = $matches[1];
						return;
					}
				}
			}

			if (function_exists('popen')){
				$proc = @popen($cmd, 'r');
				if (is_resource($proc)){
					$mime = @fread($proc, 512);
					@pclose($proc);
					if ($mime !== false){
						$mime = explode("\n", trim($mime));
						if (preg_match($regexp, $mime[(count($mime) - 1)], $matches)){
							$this->file_type = $matches[1];
							return;
						}
					}
				}
			}
		}

		// Fall back to the deprecated mime_content_type(), if available (still better than $_FILES[$field]['type'])
		if (function_exists('mime_content_type')){
			$this->file_type = @mime_content_type($file['tmp_name']);
			 // It's possible that mime_content_type() returns false or an empty string
			if (strlen($this->file_type) > 0){
				return;
			}
		}

		$this->file_type = $file['type'];
	}
}
