<?php 

namespace Hcode;

use Rain\Tpl;

class Page {

	private $tpl;
	private $options = [];
	private $defaults = [
		// "header"=>true,
		// "footer"=>true,
		"data"=>[]
	];

	// carrega o cabeçalho dad paginas 
	public function __construct($opts = array())
	{
		$this->options = array_merge($this->defaults, $opts);

		$config = array(
		    // "base_url"      => null,
		    "tpl_dir"       => $_SERVER['DOCUMENT_ROOT']."/views/",
		    "cache_dir"     => $_SERVER['DOCUMENT_ROOT']."/views-cache/",
		    "debug"         => false
		);

		Tpl::configure( $config );

		$this->tpl = new Tpl();

		// if ($this->options['data']) $this->setData($this->options['data']);
		$this->setData($this->options['data']);

		// if ($this->options['header'] === true) $this->tpl->draw("header", false);
		$this->tpl->draw("header");

	}

	private function setData($data = array())
	{
		foreach($data as $key => $val)
		{
			$this->tpl->assign($key, $val);
		}
	}

	// carrega o conteudo da pagina 
	public function setTpl($tplName, $data = array(), $returnHTML = false)
	{
		$this->setData($data);

		return $this->tpl->draw($tplName, $returnHTML);
	}

	// carrega o rodape da pagina 
	public function __destruct()
	{
		// if ($this->options['footer'] === true) $this->tpl->draw("footer", false);
		$this->tpl->draw("footer");
	}
}
 ?>