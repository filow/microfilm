<?php
class TagLibConfig extends TagLib {
	protected $tags   =  array(
        // 标签定义： attr 属性列表 close 是否闭合（0 或者1 默认1） alias 标签别名 level 嵌套层次
        'cfgrow'    =>  array('attr'=>'var,name,form-action,type','close'=>0),
    );

	public function _cfgrow($attr,$content)
	{
		$attr=$this->parseXmlAttr($attr);
		$var=$attr['var'];
		$name=$attr['name'];
		$type=$attr['type'];
		$form_action=$attr['form-action'];
		$string = "<tr>\n";
		$string .="<form action=\"<?php echo ({$attr['form-action']}); ?>\" method=\"post\">\n";
		$string .="<input type=\"hidden\" name=\"id\" value=\"<?php echo ({$var}['{$name}']['id']) ?>\">\n";
		$string .="<td><?php echo {$var}['{$name}']['comment'] ?></td>\n";
		$string .="<td>";
		if($type=="time"){
			$string .="<div class=\"input-append date form_datetime\">\n";
			$string .="<input name=\"value\" size=\"16\" type=\"text\" value=\"<?php echo date('Y-m-d G:i',{$var}['{$name}']['value']) ?>\" readonly>";
			$string .="<span class=\"add-on\"><i class=\"icon-th\"></i></span></div>";
		}else{
			$string.="<input type=\"text\" name=\"value\" value=\"<?php echo {$var}['{$name}']['value'] ?>\"/>";
		}
		$string .="</td><td>";
		$string .="<input type=\"submit\" class=\"btn btn-warning\" value=\"提交\" />";
		$string .="</td></form></tr>";
		return $string;
		
	}
}
