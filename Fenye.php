<?php
class Fenye {
	private $total;      //总记录  
	private $pagesize;    //每页显示多少条  
	private $limit;          //limit  
	private $page;           //当前页码  
	private $pagenum;      //总页码  
	private $url;           //地址  
	private $bothnum;      //两边保持数字分页的量  
	private $postdata;      //get post 数据  
	private $result;      //查询 数据  
	//构造方法初始化  
	public function __construct($m, $_pagesize) {  
		$this->total = $this->setTotal($m);
		$this->pagesize = $_pagesize;  
		$this->pagenum = ceil($this->total / $this->pagesize);
		$this->page = $this->setPage($_POST['currentpage']);  
		$this->limit = ($this->page-1)*$this->pagesize.",$this->pagesize";
		$_REQUEST['page_snum'] = ($this->page-1)*$this->pagesize;
		$this->result = $m->limit($this->limit)->select();
		//echo M()->_sql();
		$this->url = $this->setUrl();  
		$this->postdata = $this->setPostdata();  
		$this->bothnum = 5;
	}  

	//拦截器  
	private function __get($_key) {  
		return $this->$_key;  
	}  

	//获取当前页码  
	private function setPage($currentpage) {  
		if ($currentpage) {  
			if ($currentpage > 0) {  
			   if ($currentpage > $this->pagenum) {  
					  return $this->pagenum;  
			   } else {  
					  return $currentpage;  
			   }  
			} else {  
			   return 1;  
			}  
		} else {  
			return 1;  
		}  
	}   

	//获取地址  
	private function setUrl() {
		$_url = $_SERVER["REQUEST_URI"];
		/* $_par = parse_url($_url);  
		if (isset($_par['query'])) {  
			parse_str($_par['query'],$_query);  
			unset($_query['page']);  
			$_url = $_par['path'].'?'.http_build_query($_query);  
		}   */
		return $_url;  
	}     
	private function setPostdata() {
		$pdata = $_POST;
		unset($pdata['currentpage']);
		if($pdata){
			$pdata_str = http_build_query($pdata).'&currentpage=';
		}else{
			$pdata_str = 'currentpage=';
		}
		return $pdata_str;
	}
	private function createATag($_page,$_name){
		return ' <li><a postdata="'.$this->postdata.$_page.'" url="'.$this->url.'" >'.$_name.'</a></li> ';
		
	}
	//数字目录  
	private function pageList() {  
		for ($i=$this->bothnum;$i>=1;$i--) {  
		$_page = $this->page-$i;  
		if ($_page < 1) continue;  
			$_pagelist .= $this->createATag($_page,$_page); 
		}  
		$_pagelist .= ' <li><a class="currentpage">'.$this->page.'</a></li> ';  
		for ($i=1;$i<=$this->bothnum;$i++) {  
		$_page = $this->page+$i;  
			if ($_page > $this->pagenum) break;  
			$_pagelist .= $this->createATag($_page,$_page);  
		}  
		return $_pagelist;  
	}  

	//首页  
	private function first() {  
		if ($this->page > $this->bothnum+1) {  
			return $this->createATag(1,1);;  
		}  
	}  

	//上一页  
	private function prev() {  
		if ($this->page == 1) {  
			return '<li class="disabled"><a>上一页</a></li>';  
		}  
		return $this->createATag($this->page-1,'上一页');  
	}  

	//下一页  
	private function next() {  
		if ($this->page == $this->pagenum) {  
			return '<li class="disabled"><a >下一页</a></li>';  
		}  
		return $this->createATag($this->page+1,'下一页');  
	}  

	//尾页  
	private function last() {  
		if ($this->pagenum - $this->page > $this->bothnum) {  
			return $this->createATag($this->pagenum,'尾页('.$this->pagenum.')');  
		}  
	}  

	//分页信息  
	public function showpage() { 
		$_page .= '<div class=" text-center" id="fenyelist"><ul class="pagination">';  
		$_page .= $this->prev();
		$_page .= $this->first();  
		$_page .= $this->pageList();   
		$_page .= $this->next();   
		$_page .= $this->last();
		$_page .= '</ul></div>';
		$_page .= $this->currentpageCSSAndJS();
		return $_page;  
	}  
	private function currentpageCSSAndJS(){
		return 
		<<<EOT
			<style>#fenyelist .currentpage{color:#C1C326}</style>
			<script>
			$(function(){
				$('#fenyelist').on('click','li:not(.disabled) a:not(.currentpage)',function(){
					$.post($(this).attr('url'),$(this).attr('postdata'),function(data){
					
						$("#mycontent").html(data);
					});
				});
			});
			</script>
EOT;
	}
	private function setTotal($m){
		$m2 = clone $m;
		$_total = M()->query('select count(0) as c from ('.$m2->buildSql().') as a')[0]['c'];
		return $_total ? $_total : 1;
	}
	public function getResult(){
		return $this->result;
	}
}

?>