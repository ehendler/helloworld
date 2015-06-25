<?php
//You'll need to change the password for the database to your root password if you wish to run this locally.
//Also, you need the following rewrite rule in Apache:
//	RewriteEngine on
//	RewriteCond %{REQUEST_URI} !(index\.php|\.svg|\.png|\.jpe?g|\.ico)
//	RewriteRule ^(.+)$ /index.php?page=$1 [L,QSA]


//This required PHP 5.6.6. If you do not have it, the inheritance properties of the class constants will cause it to fail to compile.
namespace{
	document::load();
	class conf{const js_root='_.js';const css_root='_.css';}
	class document{/**/
                function mime($c){
                        $mime=(
                                !strpos($_GET['page'][$c],'.js')&&
                                !strpos($_GET['page'][$c],'.css')
                                )?
                                'html':(strpos($_GET['page'][$c],'.css')?'css':'js');
                        header("Content-type: text/$mime");
                        $dot=strpos($_GET['page'][$c],'.');
                        if($dot&&$mime==='html')
                                $_GET['page'][$c]=substr($_GET['page'][$c],0,$dot);
                        return$mime;
                }
                function load(){
                        $_GET['page']=explode('/',$_GET['page']);
                        $c=count($_GET['page']);
                        $c=$_GET['page'][$c-1]===""?--$c:$c;
                        $pages=count($_GET['page']);
                        $mime=self::mime($c-1);
			if($_GET['page'][1]==="admin")
				\helloworld\admin::$mime();
			else
				\helloworld\register::$mime();
                        session_start();
                        exit;
                }
	}
}
namespace helloworld{
	class db{
		const db='helloworld';
		const host='localhost';
		const _password='************';
		const user='root';
		const password=self::user.self::_password;
		const hash='md5';
		const where=false;
		const table='';
		const structure=self::db.self::table;
		const fqun=self::user.'@'.self::host;
		function __construct(){
			$query="{$this->create_db()}{$this->create_table()}";
			$this->_create($query,false);
		}
		function login(){
			return new \mysqli(
				$this::host,
				$this::user,
				$this::user==='root'?
					$this::_password:
					hash(
						$this::hash,	
						$this::password
					),
				''
			);
		}
		function _create($query,$debug){
			if($debug)
				file_put_contents(
					'/var/www/evanhendler.com/dumps/dump-'.
						time().'.sql',
					$query
				);
			$mysql=$this->login();
			$r=$mysql->multi_query($query);
			$mysql->close();
		}
		function _retrieve($query){
			$mysql=static::login();
			$r=$mysql->query($query);
			for($i=1;$i<=$r->num_rows;$i++){
				$r->data_seek($i-1);
				$a[$i-1]=$r->fetch_array(MYSQLI_ASSOC);
			}
			$mysql->close();
			return$a;
		}
		function create_db(){
			return'create database if not exists`'.static::db.'`;';
		}
		function create_table(){
			$query='create table if not exists`'.
				static::db.'`.`'.static::table.'`(';
			$properties=$this->get_properties();
			foreach($properties as$class){
				$col=	constant("$class::name").'`'.
					constant("$class::type").'('.
					constant("$class::size").')';
				$query.="`$col,";
			}
			return$query.'date datetime,id bigint(20) AUTO_INCREMENT,PRIMARY KEY(id));';
		}
		function get_properties(){
			$i=0;
			foreach((array)$this as$column=>$null){
				$properties[$i++]='\\'.__NAMESPACE__.'\\'.substr(
					$column,strlen('\\'.static::class)+1);
			}
			return$properties;
		}
	}
	class record extends db{
		const table='record';
		function get($var){
			return$this->$var;
		}
		function set($var,$val){
			$this->$var=$val;
		}
		function create(){
			foreach(get_object_vars($this)as$var=>$val){
				$class='\\'.__NAMESPACE__."\\$var";
				$c.=constant("$class::name").",";
				$v.='"'.$_POST[$class::$name].'",';
			}
			$this->_create('insert into`'.$this::db.'`.`'.$this::table.'`('.$c.'date)values('.$v.'now());');
		}
		function retrieve($vars){
			return$this->_retrieve('select * from`'.$this::db.'`.`'.$this::table.'`order by`date`desc');
		}
		function update(){
		}
		function destroy(){
		}
		private$first;
		private$last;
		private$address1;
		private$address2;
		private$city;
		private$state;
		private$zip;
		private$country;
	}
	class _tag{
		const err_empty='';
		const err_invalid='';
	}
	class tag extends _tag{
		const label='';
		const _err_empty='Please ';
		const _err_invalid='Invalid ';
		const err_invalid=parent::err_invalid.self::_err_invalid;
		const err_empty=parent::err_empty.self::_err_empty;
		function innerHTML(){
			return'';
		}
		function validate(){
			if(static::simple_validate())return 1;
			return static::extended_validate();
		}
		function html($additional_props){
			echo
			'<',static::tag;
			foreach(static::$props as$prop){
				echo" $prop=\"",static::$$prop,'"';
			}
			foreach($additional_props as$prop=>$value){
				echo" $prop=\"",$value,'"';
			}
			echo
			static::tag==='input'?(
				'value="'.(
				isset($value)?
					$value:(
					$_POST[static::$name]?
					$_POST[static::$name]:''
					)
				)
				.'"'
			):'',
			'>',
				static::innerHTML(),
			'</',static::tag,'>';
		}
	}
	class select extends tag{
		const tag='select';
		const _err_empty='Select a ';
		const err_invalid=parent::err_invalid.self::label;
		function innerHTML(){
			foreach(static::$options as$value=>$innerHTML){
				echo
				'<option',
					' value="',$value,'"',(
					$_POST[static::$name]===$value?
						' selected':''
					),'>',
					$innerHTML,
				'</option>';
			}
		}
		function simple_validate(){
			if(!$_POST[static::$name])return 1;
		}
		protected static$props=[
			'name'
		];
	}
	class input extends tag{
		const tag='input';
		const _err_empty='Include a ';
		const err_invalid=parent::err_invalid.self::label;
		function simple_validate(){
			if(empty($_POST[static::$name]))return 1;
		}
	}
	class text extends input{
		const err_empty=parent::err_empty.self::label;
		protected static$type='text';
		protected static$props=[
			'placeholder',
			'type',
			'name'
		];
		function extended_validate(){
			if(preg_match("/[^a-z\s-]/i",$_POST[static::$name]))
				return 2;
		}
	}
	class submit extends input{
		public static$name='r';
		protected static$type='submit';
		protected static$props=[
			'type',
			'name'
		];
	}
	class number extends input{
		const err_empty=parent::err_empty.self::label;
		protected static$type='number';
		protected static$props=[
			'placeholder',
			'type',
			'name'
		];
	}
	class first extends text{
		const label='First Name';
		const name='f';
		const type='varchar';
		const size=64;
		public static$name='f';
		protected static$placeholder='First Name';
	}
	class last extends text{
		const label='Last Name';
		const name='l';
		const type='varchar';
		const size=128;
		public static$name='l';
		protected static$placeholder='Last Name';
	}
	class address1 extends text{
		const name='a1';
		const type='varchar';
		const size='256';
		public static$name='a1';
		//I had to call it street address because "a Address"
			//wasn't grammatically sound :/
		protected static$placeholder='Street Address';
		const label='Address';
		function extended_validate(){
			if(preg_match("/[^a-z0-9\s-]/i",$_POST[static::$name]))
				return 2;
		}
	}
	class address2 extends text{
		const label='Line Two';
		const name='a2';
		const type='varchar';
		const size='256';
		public static$name='a2';
		protected static$placeholder='Address (Optional)';
	}
	class city extends text{
		const label='City';
		const name='ci';
		const type='varchar';
		const size='128';
		public static$name='m';
		protected static$placeholder='City';
	}
	class zip extends text{
		const label='Zip';
		const name='z';
		const type='varchar';
		const size='10';
		public static$name='z';
		protected static$placeholder='Zip';
		function extended_validate(){
			if(preg_match("/[^0-9]-/",$_POST[static::$name]))
				return 2;
			$l=strlen($_POST[static::$name]);
			if(preg_match("/-/",$_POST[static::$name])){
				if(strpos($_POST[static::$name],'-')!=5||$l!=10)
					return 2;
				return;
			}
			if($l!=5&&$l!=9)
				return 2;
		}
	}
	class country extends select{
		const label='Country';
		const name='co';
		const type='varchar';
		const size='2';
		public static$name='c';
		protected static$options=[
0=>'(Select a country)','US'=>'United States'
		];
		const err_empty=parent::err_empty.self::label;
		function extended_validate(){
		}
	}
	class state extends select{
		const label='State';
		const name='s';
		const type='varchar';
		const size='2';
		public static$name='s';
		protected static$options=[
0=>'(Select a state)','AL'=>"Alabama",'AK'=>"Alaska",'AZ'=>"Arizona",'AR'=>"Arkansas",'CA'=>"California",'CO'=>"Colorado",'CT'=>"Connecticut",'DE'=>"Delaware",'DC'=>"District Of Columbia",'FL'=>"Florida",'GA'=>"Georgia",'HI'=>"Hawaii",'ID'=>"Idaho",'IL'=>"Illinois",'IN'=>"Indiana",'IA'=>"Iowa",'KS'=>"Kansas",'KY'=>"Kentucky",'LA'=>"Louisiana",'ME'=>"Maine",'MD'=>"Maryland",'MA'=>"Massachusetts",'MI'=>"Michigan",'MN'=>"Minnesota",'MS'=>"Mississippi",'MO'=>"Missouri",'MT'=>"Montana",'NE'=>"Nebraska",'NV'=>"Nevada",'NH'=>"New Hampshire",'NJ'=>"New Jersey",'NM'=>"New Mexico",'NY'=>"New York",'NC'=>"North Carolina",'ND'=>"North Dakota",'OH'=>"Ohio",'OK'=>"Oklahoma",'OR'=>"Oregon",'PA'=>"Pennsylvania",'RI'=>"Rhode Island",'SC'=>"South Carolina",'SD'=>"South Dakota",'TN'=>"Tennessee",'TX'=>"Texas",'UT'=>"Utah",'VT'=>"Vermont",'VA'=>"Virginia",'WA'=>"Washington",'WV'=>"West Virginia",'WI'=>"Wisconsin",'WY'=>"Wyoming"
		];
		const err_empty=parent::err_empty.self::label;
		function extended_validate(){
		}
	}
	class __{
		const title='';
	}
	class _ extends __{
		const _title='Hello World | ';
		const title=parent::title.self::_title;
		function html(){
			echo
			'<html>',
			'<head>',
			'<title>',static::title,'</title>',
			'<link rel=stylesheet type=text/css href=styles.css>',
			'</head>',
			'<body>';
			static::h();
			echo
			'<script src=script.js></script>',
			'</body>',
			'</html>';
		}
	}
	class register extends _{
		const _title='Register';
		protected static$fields=[
			'first',
			'last',
			'address1',
			'address2',
			'city',
			'state',
			'zip',
			'country'
		];
		function css(){
			echo
			'body{',
				'background-image:url(http://www.helloworld.com/img/global/logo-nav.png);',
				'font-size:2vmin;',
				'text-align:center;',
				'font-family:arial;',
				'margin:0;',
				'padding:0;',
			'}',
			'body>h1{',
				'padding:1em;',
			'}',
			'body>h1,body>form{',
				'background-color:#ECE9E9;',
				'margin-top:5em;',
				'margin-bottom:0;',
				'box-shadow:0 0.025em 0.025em 0.025em #050708;',
			'}',
			'body>a{',
				'display:inline-block;',
				'padding:1em;',
			'}',
			'body>a,form>div>label{',
				'border-radius:0 0 0.5em 0.5em;',
				'background:#1F96D3;',
			'}',
			'body>div{',
				'width:100%;',
				'height:100%;',
				'position:fixed;',
				'top:0;',
				'left:0;',
				'background-color:#000;',
				'opacity:0.75;',
			'}',
			'form>b{',
				'position:absolute;',
				'top:5em;',
				'left:0;',
				'width:100%;',
				'color:red;',
			'}',
			'form>h1{',
				'margin-bottom:1em;',
			'}',
			'form>div{',
				'width:80%;',
				'padding:0 10%;',
			'}',
			'form>div>label,form>div>input,form>div>select{',
				'display:inline-block;',
				'line-height:2em;',
				'height:2em;',
			'}',
			'form>div>input.i,form>div>select.i{',
				'border-color:red;',
			'}',
			'form>div>label{',
				'width:47%;',
				'padding:0 10%;',
				'text-align:left;',
				'font-size:0.75em;',
				'margin-right:33%;',
				'margin-bottom:0.5em;',
				'color:#050708;',
			'}',
			'form>input{',
				'background:#1F96D3;',
				'font-size:2em;',
				'padding:0.25em;',
				'border-radius:0.25em;',
				'border-width:0.1em;',
				'border-style:solid;',
				'border-color:#0088C7;',
				'cursor:pointer;',
			'}',
			'form>input:hover{',
				'border-color:#1F96D3;',
				'background:#62B6E0;',
			'}',
			'form>input:focus{',
				'background:#1978A9;',
			'}',
			'form>div>input,form>div>select{',
				'text-align:center;',
				'width:100%;',
				'font-size:1em;',
				'border:0.1em solid #1F96D3;',
				'box-shadow:0 0.025em 0.025em 0.025em #050708 inset;',
				'border-radius:0.5em 0.5em 0.5em 0;',
				'margin:0;',
			'}',
			'form{',
				'position:relative;',
				'display:inline-block;',
				'width:40em;',
				'padding:1em;',
				'border:0.1em solid #000;',
				'max-width:600px;',
				'border-radius:0.5em 0.5em 0.5em 0;',
			'}';
		}
		function js(){
			echo'
			var fields=document.getElementsByClassName("f");
			function test_fields(){
				var error=false;
				for(k in fields){
					var v=fields[k].value;
					switch(fields[k].name){
						case"f":
							var re=/[^a-z\\s-]/i;
							var vt="";
							if(re.exec(v)||v===vt)
								error=true;
						break;
						case"l":
							var re=/[^a-z\\s-]/i;
							var vt="";
							if(re.exec(v)||v===vt)
								error=true;
						break;
						case"a1":
							var re=/[^a-z0-9\\s-]/i;
							var vt="";
							if(re.exec(v)||v===vt)
								error=true;
						break;
						case"a2":
						break;
						case"m":
							var re=/[^a-z\\s-]/i;
							var vt="";
							if(re.exec(v)||v===vt)
								error=true;
						break;
						case"s":
							var re=/[^a-z]/i;
							var vt=0;
							if(re.exec(v)||v===vt)
								error=true;
						break;
						case"z":
							var re=/[^0-9\\s-]/i;
							var vt="";
							if(re.exec(v)||v===vt)
								error=true;
						break;
						case"c":
							var re=/[^a-z]/i;
							var vt=0;
							if(re.exec(v)||v===vt)
								error=true;
						break;
					}
				}
				if(error)
					r.disabled=true;
				else
					r.disabled=false;
			}
			for(k in fields){
				fields[k].onblur=function(e,k){
					var elem=e.target;
					var v=elem.value;
					node=elem;
					i=[].indexOf.call(elem.parentNode.parentNode.children,elem.parentNode);
					switch(elem.name){
						case"f":
							var re=/[^a-z\\s-]/i;
							var vt="";
						break;
						case"l":
							var re=/[^a-z\\s-]/i;
							var vt="";
						break;
						case"a1":
							var re=/[^a-z0-9\\s-]/i;
							var vt="";
						break;
						case"a2":
						break;
						case"m":
							var re=/[^a-z\\s-]/i;
							var vt="";
						break;
						case"s":
							var re=/[^a-z]/i;
							var vt=0;
						break;
						case"z":
							var re=/[^0-9\\s-]/i;
							var vt="";
						break;
						case"c":
							var re=/[^a-z]/i;
							var vt=0;
						break;
					}
					if(v.name!="a2"){
						if(
							re.exec(v)||v===vt
						){
							elem.className="f i";
							err.innerHTML="Invalid "+elem.parentNode.children[1].innerHTML;
						}
						else{
							elem.className="f";
							err.innerHTML="";
						}
					}
					test_fields();
				}
			}
			test_fields();
			';
		}
		function h(){
			if($_POST[\helloworld\submit::$name]!='Confirm')
				self::form();
			else{
				foreach(self::$fields as$field){
					$class='\\'.__NAMESPACE__.'\\'.$field;
					if(
						$field!='address2'&&
						$_POST[\helloworld\submit::$name]
					)
						$error=$class::validate();
					if($error){
						$invalid=1;
						self::form();
						exit;
					}
				}
				self::success();
			}
		}
		function success(){
			$class='\\'.__NAMESPACE__.'\\record';
			$object=new $class;
			echo$object->create();
			echo
			'<h1>Congratulations on signing up!</h1>',
			'<a href=admin/>View Signups</a>';
		}
		function form(){
			echo'<div></div><form method=post>',
				'<h1>','Hello World! Register Here:','</h1>';
			foreach(self::$fields as$field){
				$class='\\'.__NAMESPACE__.'\\'.$field;
				if(
					$field!='address2'&&
					$_POST[\helloworld\submit::$name]
				)
					$error=$class::validate();
				if($error){
					$invalid=1;
				}
				if(!$err_msg)
					if($error===1)
						$err_msg=
						constant("$class::err_empty");
					elseif($error)
						$err_msg=
						constant("$class::err_invalid");
				echo'<div>';
				$class::html([
					'value'=>$_POST[$class::$name],
					'class'=>'f'.($error?' i':'')
				]);
				echo'<label>',constant("$class::label"),'</label></div>';
			}
			if(
				$_POST[\helloworld\submit::$name]==='Register'&&
				!$invalid
			){
				$properties=
					[
						'value'=>'Confirm',
						'id'=>'r',
					];
				echo
				'<b>',
				'Is everything below correct?',
				'</b>';
			}
			else
				$properties=
					[
						'value'=>'Register',
						'id'=>'r',
					];
			\helloworld\submit::html($properties);
			echo
				'<b id="err">',
					$err_msg,
				'</b>',
			'</form>';
		}
	}
	class admin extends _{
		const _title='Admin';
		function css(){
			echo
			'body{',
				'background-image:url(http://www.helloworld.com/img/global/logo-nav.png);',
				'text-align:center;',
				'font-size:1.5vmin;',
				'font-family:arial;',
			'}',
			'body>a{',
				'display:inline-block;',
				'font-size:1.5em;',
				'border-radius:0.5em 0.5em 0 0;',
				'padding:1em;',
			'}',
			'body>table{',
				'border:1em solid #1978A9;',
				'background-color:#ECE9E9;',
				'border-collapse:collapse;',
				'display:inline-block;',
				'font-size:1em;',
			'}',
			'body>table tr>*{',
				'padding:0.5em;',
			'}',
			'body>a,body>table tr:nth-child(even){',
				'background:#1F96D3;',
			'}';
		}
		function h(){
			$class='\\'.__NAMESPACE__.'\\record';
			$object=new $class;
			$records=$object->retrieve();
			$properties=$object->get_properties();
			echo'<a href=../>Back to Register Page</a><br>';
			echo'<table>',
				'<tr>';
				foreach($properties as$k=>$property){
					echo'<th>',
						constant("$property::label"),
					'</th>';
				}
				echo'<th>Registration Date</th></tr>';
			foreach($records as $k=>$record){
				echo'<tr>';
					foreach($properties as $j=>$property){
					echo'<td>',
						$record[constant("$property::name")],
					'</td>';
				}
				echo'<td>'.$record['date'].'</td></tr>';
			}
			echo'</table>';
		}
	}
}
?>
