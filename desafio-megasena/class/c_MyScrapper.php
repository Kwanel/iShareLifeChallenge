<?php 
class MyScrapper {
	var $file_scrapped_filename = 'megasena.html';
	var $url = '';

	function __construct($url = false) {
		if (!$url) return false;
		$this->set_url($url);
	}


	/*
		string $url
		return array [scrapped page, filetime]
	*/
	public function curl_scrap() {
		if (!isset($this->url))  return false;

		$curl_session = curl_init($this->url);

		// CURLOPT_RETURNTRANSFER this option means that the curl_exec must put the output in a var instead of only printing it.
		curl_setopt($curl_session, CURLOPT_RETURNTRANSFER, true);

		// CURLOPT_FILETIME this option means that the scraping will occur whilst retrieving the modification time of the remote file scrapped
		curl_setopt($curl_session, CURLINFO_FILETIME, true);
		
		$scrapped_page = curl_exec($curl_session);
		if (curl_errno($curl_session))
			//kill the script and throw an error if there is an error in curl_exec;
			die('An error occured while scraping: '.$this->url.' ERROR::'. curl_error($curl_session)."\n");

		$filetime = curl_getinfo($curl_session, CURLINFO_FILETIME); //unix time or -1 if undef
		curl_close($curl_session);

		return $scrapped_page? array(
					'scrapped_page' => $scrapped_page,
					'filetime' => $filetime,
				):
				false;
	}

	/*
		string $scrapped_page
		int filetime

		bool return [true] if the file does exist or [false] if it does not
	*/
	public function check_n_save_page($scrapped_page, $filetime) {
		if (!$scrapped_page)  return false;

		// file time returns unix time, knowing that you can just compare with the [CURLINFO_FILETIME] runnning out from the function date()
		if (!file_exists($this->file_scrapped_filename) || $filetime > filemtime($this->file_scrapped_filename)) {
			clearstatcache(); // filetime() stores his results in application cache, we must clean it out.

			$file_handle = fopen($this->file_scrapped_filename, 'w');
			fwrite($file_handle, $scrapped_page);
			print 'File created'."\n";
		} 

		return file_exists($this->file_scrapped_filename)? true: false;
	}

	public function set_url($url) {
		$this->url = $url;
		return true;
	}



}

//not closing the PHP tag coz while handling large files it can cause problems in the buffer, dont ask me why