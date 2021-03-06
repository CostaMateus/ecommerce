<?php 

namespace Hcode;

use Rain\Tpl;

class Page 
{
	private $tpl;
	private $options = [];
	private $defaults = [
		"header"=>true,
		"footer"=>true,
		"data"=>[]
	];

	/**
	 * Carrega o cabeçalho da pagina
	 * @param type|array $opts 
	 * @param type|string $tpl_dir 
	 * @return type
	 */
	public function __construct($opts = array(), $tpl_dir = "/views/")
	{
		$this->options = array_merge($this->defaults, $opts);

		$config = [
		    // "base_url"      => null,
		    "tpl_dir"       => $_SERVER['DOCUMENT_ROOT'].$tpl_dir,
		    "cache_dir"     => $_SERVER['DOCUMENT_ROOT']."/views-cache/",
		    // "tpl_dir"       => "/home/u265008281/public_html" . $tpl_dir,
		    // "cache_dir"     => "/home/u265008281/public_html" . "/views-cache/",
		    "debug"         => false
		];

		Tpl::configure( $config );

		$this->tpl = new Tpl();

		if ($this->options['data']) $this->setData($this->options['data']);
		
		if ($this->options['header'] === true) $this->tpl->draw("header", false);
	}

	private function setData($data = array())
	{
		foreach($data as $key => $val)
		{
			$this->tpl->assign($key, $val);
		}
	}
 
	/**
	 * Carrega o conteudo da pagina
	 * @param type $tplName 
	 * @param type|array $data 
	 * @param type|bool $returnHTML 
	 * @return type
	 */
	public function setTpl($tplName, $data = array(), $returnHTML = false)
	{
		$this->setData($data);

		return $this->tpl->draw($tplName, $returnHTML);
	}
 
	/**
	 * Carrega o rodape da pagina
	 * @return type
	 */
	public function __destruct()
	{
		if ($this->options['footer'] === true) $this->tpl->draw("footer", false);
	}
}
 ?>